<?php

namespace App\Services;

use App\Http\Controllers\EvacuationMapController;
use App\Models\AiPrediction;
use App\Models\AlertVerification;
use App\Models\SensorLog;
use App\Models\SensorNode;
use App\Models\User;
use App\Models\WargaContact;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Peringatan WhatsApp LARAS lewat GoWA, dengan VERIFIKASI KADER per DESA.
 *
 *  Tiap 15 detik (scheduler) status terbaru SEMUA node dibaca, lalu dikelompokkan per desa.
 *  Status desa = status terburuk dari node-node di desa itu.
 *
 *  Desa X WASPADA/BAHAYA
 *    -> WA ke SEMUA kader (semua desa): desa, node mana, koordinat, data, link web verifikasi
 *    -> 1 kader menyetujui di web  : WA ke warga Desa X (+ penerima semua desa)
 *    -> 1 kader menolak            : alarm palsu, level sama dibisukan 30 menit
 *    -> tidak ada keputusan:
 *              BAHAYA  -> otomatis dikirim ke warga setelah 10 menit
 *              WASPADA -> kedaluwarsa setelah 30 menit, tidak dikirim
 *
 *  Setelah disetujui, warga desa itu diberi pengingat sesuai cooldown tanpa verifikasi ulang
 *  (BAHAYA 15 menit, WASPADA 60 menit). WASPADA naik ke BAHAYA = verifikasi baru.
 *  Semua node desa kembali AMAN -> verifikasi menunggu dibatalkan, warga yang sempat diberi
 *  peringatan menerima kabar "kembali aman".
 *  Bila belum ada satu pun nomor kader, peringatan langsung ke warga agar tidak hilang.
 */
class WhatsAppAlertService
{
    private const COOLDOWN_MENIT = ['BAHAYA' => 15, 'WASPADA' => 60];
    private const MUTE_MENIT     = 30;
    private const RANK           = ['AMAN' => 0, 'WASPADA' => 1, 'BAHAYA' => 2];
    private const FOOTER         = "LARAS\nLandslide Alert & Rescue Adaptive System";

    protected string $baseUrl;
    protected string $user;
    protected string $pass;
    protected string $deviceId;
    /** @var string[] penerima peringatan semua desa (.env) */
    protected array $globalTargets;
    /** @var string[] */
    protected array $extraKader;
    protected int $autoSendMinutes;
    protected int $waspadaExpireMinutes;
    protected int $freshMinutes;

    public function __construct()
    {
        $cfg = config('services.gowa', []);
        $this->baseUrl  = rtrim((string) ($cfg['url'] ?? ''), '/');
        $this->user     = (string) ($cfg['user'] ?? '');
        $this->pass     = (string) ($cfg['pass'] ?? '');
        $this->deviceId = (string) ($cfg['device_id'] ?? '');
        $this->globalTargets = self::parseTargets((string) ($cfg['targets'] ?? ''));
        $this->extraKader    = self::parseTargets((string) ($cfg['kader_targets'] ?? ''));
        $this->autoSendMinutes      = max(1, (int) ($cfg['auto_send_minutes'] ?? 10));
        $this->waspadaExpireMinutes = max(1, (int) ($cfg['waspada_expire_minutes'] ?? 30));
        $this->freshMinutes         = max(1, (int) ($cfg['fresh_minutes'] ?? 30));
    }

    public function autoSendMinutes(): int { return $this->autoSendMinutes; }
    public function waspadaExpireMinutes(): int { return $this->waspadaExpireMinutes; }

    /** @return string[] penerima semua desa dari WA_ALERT_TARGETS (dipakai laras:wa-test) */
    public function targets(): array
    {
        return $this->globalTargets;
    }

    /** @return string[] warga desa ini + penerima semua desa */
    public function wargaTargets(?string $desa): array
    {
        $out = $this->globalTargets;
        try {
            $rows = WargaContact::where('active', true)
                ->where(fn ($q) => $q->whereNull('desa')->when($desa !== null, fn ($q) => $q->orWhere('desa', $desa)))
                ->pluck('phone');
            foreach ($rows as $p) $out = array_merge($out, self::parseTargets((string) $p));
        } catch (\Throwable $e) {
            Log::warning('[LARAS WhatsApp] Gagal membaca warga_contacts: ' . $e->getMessage());
        }
        return array_values(array_unique($out));
    }

    /**
     * "0812-3456-7890, +62813..., 1203630xxxx@g.us" -> ["6281234567890", "62813...", "1203630xxxx@g.us"]
     */
    public static function parseTargets(string $raw): array
    {
        $out = [];
        foreach (preg_split('/[,;\n]+/', $raw) as $t) {
            $t = trim($t);
            if ($t === '') continue;
            if (!str_contains($t, '@')) {
                $t = self::normalizePhone($t);
                if (strlen($t) < 9) continue;
            }
            $out[] = $t;
        }
        return array_values(array_unique($out));
    }

    /** "0812-3456 7890" / "+62812..." / "62812...@s.whatsapp.net" -> "62812..." */
    public static function normalizePhone(?string $phone): string
    {
        $p = (string) $phone;
        if (str_contains($p, '@')) $p = strstr($p, '@', true);
        $p = preg_replace('/\D/', '', $p);
        if (str_starts_with($p, '0')) $p = '62' . substr($p, 1);
        if (str_starts_with($p, '8')) $p = '62' . $p;
        return $p;
    }

    /**
     * Semua kader penerima (semua desa): akun admin/kader AKTIF yang punya nomor HP,
     * ditambah WA_KADER_TARGETS.
     * @return array<string, ?User> nomor => user
     */
    public function kaderContacts(): array
    {
        $out = [];
        try {
            User::query()->whereIn('role', User::STAFF_ROLES)->whereNotNull('phone')->get()
                ->filter(fn (User $u) => $u->isActive())
                ->each(function (User $u) use (&$out) {
                    $p = self::normalizePhone($u->phone);
                    if (strlen($p) >= 9) $out[$p] = $u;
                });
        } catch (\Throwable $e) {
            Log::warning('[LARAS WhatsApp] Gagal membaca nomor kader: ' . $e->getMessage());
        }
        foreach ($this->extraKader as $p) $out[$p] ??= null;
        return $out;
    }

    // ---------------------------------------------------------------------
    // Kirim
    // ---------------------------------------------------------------------

    /** @return array{ok: bool, error: ?string, message_id: ?string} */
    public function sendTo(string $phone, string $message): array
    {
        if ($this->baseUrl === '') {
            return ['ok' => false, 'error' => 'GOWA_URL belum diisi', 'message_id' => null];
        }
        try {
            $req = Http::timeout(15)->acceptJson();
            if ($this->user !== '') $req = $req->withBasicAuth($this->user, $this->pass);
            if ($this->deviceId !== '') $req = $req->withHeaders(['X-Device-Id' => $this->deviceId]);

            $res = $req->post($this->baseUrl . '/send/message', ['phone' => $phone, 'message' => $message]);

            if ($res->successful() && strtoupper((string) $res->json('code')) === 'SUCCESS') {
                return ['ok' => true, 'error' => null, 'message_id' => $res->json('results.message_id')];
            }
            $err = $res->json('message') ?: mb_substr($res->body(), 0, 200);
            Log::error("[LARAS WhatsApp] Gagal kirim ke {$phone}: HTTP {$res->status()} {$err}");
            return ['ok' => false, 'error' => "HTTP {$res->status()}: {$err}", 'message_id' => null];
        } catch (\Throwable $e) {
            Log::error("[LARAS WhatsApp] Tidak bisa menghubungi GoWA ({$this->baseUrl}): " . $e->getMessage());
            return ['ok' => false, 'error' => $e->getMessage(), 'message_id' => null];
        }
    }

    /** @param string[] $to  @return int jumlah yang berhasil */
    public function sendMany(array $to, string $message): int
    {
        $ok = 0;
        foreach ($to as $t) $ok += $this->sendTo($t, $message)['ok'] ? 1 : 0;
        return $ok;
    }

    // ---------------------------------------------------------------------
    // Alur utama
    // ---------------------------------------------------------------------

    /**
     * Status terkini per desa dari data terbaru tiap node (yang masih segar).
     * @return array<string, array{level: ?string, nodes: array}>
     */
    public function currentByDesa(): array
    {
        $groups = [];
        foreach (SensorNode::orderBy('node_code')->get() as $node) {
            $row = SensorLog::where('node_id', $node->id)
                ->where('timestamp', '>=', now()->subMinutes($this->freshMinutes))
                ->latest('timestamp')->first();
            if (!$row) continue;

            $status = strtoupper(trim((string) ($row->status ?? '')));
            if (!isset(self::RANK[$status])) continue;    // UNKNOWN: AI belum memproses

            $desa = trim((string) $node->desa) ?: $node->node_name;
            $data = array_intersect_key($row->toArray(), array_flip([
                'id', 'timestamp', 'sensor_log_id', 'moisture_percent', 'moisture_percent_deep',
                'rainfall_mm', 'pitch', 'roll', 'forecast_status',
            ]));
            $groups[$desa]['nodes'][] = [
                'node_id' => $node->id,
                'code'    => $node->node_code,
                'name'    => $node->node_name,
                'lat'     => $node->latitude,
                'lng'     => $node->longitude,
                'status'  => $status,
                'data'    => $data,
                'ai'      => $this->aiFor((int) $row->id),
            ];
        }

        foreach ($groups as $desa => &$g) {
            usort($g['nodes'], fn ($a, $b) => self::RANK[$b['status']] <=> self::RANK[$a['status']]);
            $g['level'] = $g['nodes'][0]['status'];
        }
        return $groups;
    }

    /** Ringkasan hasil AI untuk satu baris sensor_data (tabel ai_predictions). */
    private function aiFor(int $sensorDataId): array
    {
        try {
            $p = AiPrediction::where('sensor_data_id', $sensorDataId)->first();
        } catch (\Throwable $e) {
            return [];
        }
        if (!$p) return [];
        return [
            'rain_1h_mm'      => $p->rain_1h_mm,
            'vibration_g'     => $p->vibration_g,
            'tilt_dev_deg'    => $p->tilt_dev_deg,
            'rf_status'       => $p->rf_status,
            'rf_confidence'   => $p->rf_confidence,                     // 0-100
            'lstm_confidence' => $p->lstm_max_std !== null
                ? round((1 - (float) $p->lstm_max_std) * 100, 1) : $p->lstm_confidence,
            'risk_index'      => $p->composite_risk_index,              // 0-100
        ];
    }

    /**
     * Periksa semua desa. Dipanggil scheduler tiap 15 detik (laras:alert-tick).
     * @return array<string, string> desa => ringkasan tindakan
     */
    public function evaluate(): array
    {
        // Dimatikan lewat LARAS_WA_LARAVEL=false: jangan buat verifikasi / kirim WA dari web.
        if (!config('services.gowa.enabled', true)) return [];

        $lock = Cache::lock('wa_evaluate', 60);
        if (!$lock->get()) return [];
        try {
            $this->tick();
            $out = [];
            foreach ($this->currentByDesa() as $desa => $g) {
                $out[$desa] = $g['level'] . ': ' . $this->handleDesa($desa, $g['level'], $g['nodes']);
            }
            return $out;
        } finally {
            $lock->release();
        }
    }

    /** Untuk dashboard: paling sering sekali per 15 detik walau banyak browser polling. */
    public function evaluateThrottled(): void
    {
        if (Cache::add('wa_evaluate_recent', true, 15)) $this->evaluate();
    }

    /** @return string ringkasan tindakan */
    private function handleDesa(string $desa, string $level, array $nodes): string
    {
        $key = Str::slug($desa) ?: md5($desa);

        if ($level === 'AMAN') return $this->handleAman($desa, $key, $nodes);

        $snapshot = ['desa' => $desa, 'nodes' => $nodes];
        $pending  = $this->pending($desa);
        if ($pending) {
            if ($pending->level === $level || $pending->level === 'BAHAYA') {
                // sama (atau turun dari BAHAYA): tetap menunggu kader, perbarui data
                $pending->update(['snapshot' => $snapshot, 'sensor_data_id' => $nodes[0]['data']['id'] ?? null]);
                return "menunggu kader #{$pending->id}";
            }
            $pending->update(['state' => AlertVerification::SUPERSEDED, 'note' => 'Naik menjadi BAHAYA']);
        }

        // Warga desa ini sudah menerima level ini: pengingat sesuai cooldown
        if (Cache::get("wa_last_{$key}") === $level) {
            if (Cache::has("wa_cooldown_{$key}_{$level}")) return 'cooldown';
            $sent = $this->sendMany($this->wargaTargets($desa), $this->wargaMessage($level, $snapshot, ['repeat' => true]));
            if ($sent) $this->rememberWarga($key, $level);
            return $sent ? "pengingat ke {$sent} warga" : 'GAGAL kirim pengingat';
        }

        if (Cache::has("wa_mute_{$key}_{$level}")) return 'dibisukan (baru ditolak/kedaluwarsa)';

        return $this->requestVerification($desa, $level, $snapshot);
    }

    private function requestVerification(string $desa, string $level, array $snapshot): string
    {
        $kader = $this->kaderContacts();
        $warga = $this->wargaTargets($desa);

        if ($kader === []) {
            Log::warning('[LARAS WhatsApp] Belum ada nomor kader. Peringatan langsung ke warga.');
            $sent = $this->sendMany($warga, $this->wargaMessage($level, $snapshot));
            if ($sent) $this->rememberWarga(Str::slug($desa) ?: md5($desa), $level);
            return "tanpa kader, langsung ke {$sent} warga";
        }

        $v = AlertVerification::create([
            'level'          => $level,
            'desa'           => $desa,
            'state'          => AlertVerification::PENDING,
            'sensor_data_id' => $snapshot['nodes'][0]['data']['id'] ?? null,
            'snapshot'       => $snapshot,
            'warga_total'    => count($warga),
        ]);
        $ok = $this->sendMany(array_keys($kader), $this->kaderMessage($v));
        $v->update(['kader_notified_at' => now(), 'kader_sent' => $ok]);

        Log::info("[LARAS WhatsApp] Verifikasi #{$v->id} {$level} {$desa} dikirim ke {$ok}/" . count($kader) . ' kader.');
                return $ok > 0
            ? "verifikasi #{$v->id} dikirim ke {$ok} kader"
            : "verifikasi #{$v->id} dibuat, GAGAL kirim WA ke kader (cek GoWA)";
    }

    private function handleAman(string $desa, string $key, array $nodes): string
    {
        $note = [];
        $pending = $this->pending($desa);
        if ($pending) {
            $pending->update(['state' => AlertVerification::CANCELLED, 'note' => 'Kembali AMAN sebelum diverifikasi']);
            $this->sendMany(array_keys($this->kaderContacts()), $this->kaderNotice('VERIFIKASI DIBATALKAN', $pending, [
                "Kondisi wilayah Desa {$desa} telah kembali *AMAN* sebelum verifikasi dilakukan.",
                '',
                'Status berubah menjadi:',
                '*CANCELLED*',
                '',
                'Peringatan tidak dikirimkan kepada warga. Kader tidak perlu melakukan verifikasi.',
            ]));
            $note[] = "verifikasi #{$pending->id} dibatalkan";
        }
        Cache::forget("wa_mute_{$key}_WASPADA");
        Cache::forget("wa_mute_{$key}_BAHAYA");

        if (in_array(Cache::get("wa_last_{$key}"), ['WASPADA', 'BAHAYA'], true)) {
            $sent = $this->sendMany($this->wargaTargets($desa), $this->wargaMessage('AMAN', ['desa' => $desa, 'nodes' => $nodes]));
            if ($sent) {
                Cache::forever("wa_last_{$key}", 'AMAN');
                Cache::forget("wa_cooldown_{$key}_WASPADA");
                Cache::forget("wa_cooldown_{$key}_BAHAYA");
                $note[] = "kabar aman ke {$sent} warga";
            }
        }
        return $note ? implode(', ', $note) : 'aman';
    }

    /**
     *  - BAHAYA menunggu > 10 menit  -> otomatis ke warga
     *  - WASPADA menunggu > 30 menit -> kedaluwarsa
     */
    public function tick(): void
    {
        AlertVerification::where('state', AlertVerification::PENDING)->where('level', 'BAHAYA')
            ->where('created_at', '<=', now()->subMinutes($this->autoSendMinutes))
            ->get()->each(fn ($v) => $this->deliverToWarga($v, null, 'auto'));

        $old = AlertVerification::where('state', AlertVerification::PENDING)->where('level', 'WASPADA')
            ->where('created_at', '<=', now()->subMinutes($this->waspadaExpireMinutes))->get();
        foreach ($old as $v) {
            $claimed = AlertVerification::whereKey($v->id)->where('state', AlertVerification::PENDING)
                ->update(['state' => AlertVerification::EXPIRED, 'decided_via' => 'auto', 'decided_at' => now(),
                          'note' => "Tidak ada keputusan kader dalam {$this->waspadaExpireMinutes} menit"]);
            if (!$claimed) continue;
            $key = Str::slug((string) $v->desa) ?: md5((string) $v->desa);
            Cache::put("wa_mute_{$key}_WASPADA", true, now()->addMinutes(self::MUTE_MENIT));
            $this->sendMany(array_keys($this->kaderContacts()), $this->kaderNotice('VERIFIKASI KEDALUWARSA', $v, [
                "Tidak ada verifikasi kader dalam {$this->waspadaExpireMinutes} menit.",
                '',
                'Status berubah menjadi:',
                '*EXPIRED*',
                '',
                'Peringatan WASPADA tidak dikirimkan kepada warga. Sistem akan meminta verifikasi kembali apabila status meningkat menjadi BAHAYA.',
            ]));
        }
    }

    // ---------------------------------------------------------------------
    // Keputusan kader (dari web)
    // ---------------------------------------------------------------------

    /** @return array{ok: bool, message: string} */
    public function approve(AlertVerification $v, ?User $by): array
    {
        return $this->deliverToWarga($v, $by, 'web');
    }

    /** @return array{ok: bool, message: string} */
    public function reject(AlertVerification $v, ?User $by, ?string $note = null): array
    {
        $claimed = AlertVerification::whereKey($v->id)->where('state', AlertVerification::PENDING)->update([
            'state' => AlertVerification::REJECTED, 'decided_by' => $by?->id, 'decided_by_phone' => $by?->phone,
            'decided_via' => 'web', 'decided_at' => now(), 'note' => $note,
        ]);
        $v->refresh();
        if (!$claimed) return ['ok' => false, 'message' => $this->alreadyDecidedText($v)];

        $key = Str::slug((string) $v->desa) ?: md5((string) $v->desa);
        Cache::put("wa_mute_{$key}_{$v->level}", true, now()->addMinutes(self::MUTE_MENIT));
        $this->sendMany(array_keys($this->kaderContacts()), $this->kaderNotice('VERIFIKASI DITOLAK', $v, array_filter([
            'Diverifikasi oleh : ' . ($v->deciderName() ?? 'kader'),
            $note ? "Alasan : {$note}" : null,
            '',
            'Status berubah menjadi:',
            '*FALSE ALARM*',
            '',
            'Peringatan tidak dikirimkan kepada warga.',
        ], fn ($l) => $l !== null)));

        return ['ok' => true, 'message' => "Verifikasi #{$v->id} ditolak. Peringatan tidak dikirim ke warga Desa {$v->desa}."];
    }

    /** @return array{ok: bool, message: string} */
    private function deliverToWarga(AlertVerification $v, ?User $by, string $via): array
    {
        // Kunci atomik: cukup 1 kader; keputusan kedua / kiriman otomatis tidak dobel
        $claimed = AlertVerification::whereKey($v->id)->where('state', AlertVerification::PENDING)
            ->update(['state' => AlertVerification::SENDING]);
        $v->refresh();
        if (!$claimed) return ['ok' => false, 'message' => $this->alreadyDecidedText($v)];

        $snapshot = $v->snapshot ?? [];
        $meta = $via === 'auto' ? ['auto' => true] : ['verified_by' => $by?->name];
        $to   = $this->wargaTargets($v->desa);
        $ok   = $this->sendMany($to, $this->wargaMessage($v->level, $snapshot, $meta));

        if ($ok === 0) {
            $v->update(['state' => AlertVerification::PENDING]);
            return ['ok' => false, 'message' => $to
                ? "Gagal mengirim ke warga (GoWA tidak merespons). Verifikasi #{$v->id} masih menunggu, coba lagi."
                : "Belum ada nomor warga untuk Desa {$v->desa}. Tambahkan dulu di bagian Nomor WA warga, lalu coba lagi."];
        }

        $v->update([
            'state'            => $via === 'auto' ? AlertVerification::AUTO_SENT : AlertVerification::APPROVED,
            'decided_by'       => $by?->id,
            'decided_by_phone' => $by?->phone,
            'decided_via'      => $via,
            'decided_at'       => now(),
            'sent_to_warga_at' => now(),
            'warga_sent'       => $ok,
            'warga_total'      => count($to),
        ]);
        $this->rememberWarga(Str::slug((string) $v->desa) ?: md5((string) $v->desa), $v->level);

        $this->sendMany(array_keys($this->kaderContacts()), $via === 'auto'
            ? $this->kaderNotice('PERINGATAN DIKIRIM OTOMATIS', $v, [
                "Tidak ada verifikasi kader dalam {$this->autoSendMinutes} menit.",
                '',
                'Status berubah menjadi:',
                '*AUTO SENT*',
                '',
                "Peringatan BAHAYA telah dikirimkan kepada {$ok} dari " . count($to) . " nomor warga Desa {$v->desa}.",
                'Mohon kader segera bersiaga di posko dan membantu proses evakuasi.',
            ])
            : $this->kaderNotice('VERIFIKASI BERHASIL', $v, [
                'Diverifikasi oleh : ' . ($v->deciderName() ?? 'kader'),
                '',
                'Status berubah menjadi:',
                '*VERIFIED*',
                '',
                "Peringatan telah dikirimkan kepada {$ok} dari " . count($to) . " nomor warga Desa {$v->desa}.",
                $v->level === 'BAHAYA'
                    ? 'Mohon kader segera bersiaga di posko dan membantu proses evakuasi.'
                    : 'Mohon kader memantau kondisi wilayah dan menyiapkan posko.',
            ]));

        return ['ok' => true, 'message' => "Peringatan {$v->level} Desa {$v->desa} terkirim ke {$ok}/" . count($to) . ' nomor warga.'];
    }

    private function alreadyDecidedText(AlertVerification $v): string
    {
        $who = $v->deciderName();
        return "Verifikasi #{$v->id} sudah diputuskan: {$v->stateLabel()}" . ($who ? " oleh {$who}" : '') . '.';
    }

    private function rememberWarga(string $key, string $level): void
    {
        Cache::forever("wa_last_{$key}", $level);
        if (isset(self::COOLDOWN_MENIT[$level])) {
            Cache::put("wa_cooldown_{$key}_{$level}", true, now()->addMinutes(self::COOLDOWN_MENIT[$level]));
        }
    }

    public function pending(?string $desa = null): ?AlertVerification
    {
        return AlertVerification::where('state', AlertVerification::PENDING)
            ->when($desa !== null, fn ($q) => $q->where('desa', $desa))
            ->latest('id')->first();
    }

    // ---------------------------------------------------------------------
    // Isi pesan (format WhatsApp: *tebal*, _miring_)
    // ---------------------------------------------------------------------

    /** Pesan ke kader: permintaan verifikasi lengkap dengan data sensor & hasil AI. */
    public function kaderMessage(AlertVerification $v): string
    {
        $s      = $v->snapshot ?? [];
        $nodes  = $s['nodes'] ?? [];
        $desa   = $v->desa ?? ($s['desa'] ?? '-');
        $url    = rtrim((string) config('app.url'), '/') . '/verifikasi-peringatan?v=' . $v->id;
        $warga  = count($this->wargaTargets($v->desa));
        $worst  = $nodes[0] ?? ['code' => '-', 'name' => null, 'status' => $v->level, 'data' => [], 'ai' => []];
        $d      = $worst['data'] ?? [];
        $ai     = $worst['ai'] ?? [];
        $others = array_slice($nodes, 1);

        $lines = [
            '*LARAS EARLY WARNING*',
            "No. Verifikasi : #{$v->id}",
            '',
            'Halo Kader,',
            'Sistem LARAS mendeteksi potensi tanah longsor pada wilayah berikut:',
            '',
            'Lokasi : ' . $this->nodeLabel($worst) . ", Desa {$desa}",
            'Waktu : ' . $this->waktu($d),
        ];
        if (isset($worst['lat'], $worst['lng'])) {
            $lines[] = "Koordinat : https://maps.google.com/?q={$worst['lat']},{$worst['lng']}";
        }

        array_push($lines,
            '',
            '*HASIL PEMANTAUAN SENSOR*',
            '- Kelembapan Tanah Dangkal : ' . $this->num($d['moisture_percent'] ?? null, '%'),
            '- Kelembapan Tanah Dalam : ' . $this->num($d['moisture_percent_deep'] ?? null, '%'),
            '- Curah Hujan : ' . (isset($ai['rain_1h_mm'])
                ? $this->num($ai['rain_1h_mm'], ' mm/jam') : $this->num($d['rainfall_mm'] ?? null, ' mm')),
            '- Kemiringan Lereng (Pitch) : ' . $this->num($d['pitch'] ?? null, '°'),
            '- Perubahan Kemiringan : ' . $this->signed($ai['tilt_dev_deg'] ?? null, '° dari posisi awal'),
            '- Getaran : ' . $this->num($ai['vibration_g'] ?? null, ' g', 2),
            '',
            '*HASIL ANALISIS AI*',
            "- Status : *{$worst['status']}*",
            '- Prediksi 1 Jam ke Depan : ' . ($ai['rf_status'] ?? $d['forecast_status'] ?? '-'),
            '- Confidence LSTM : ' . $this->num($ai['lstm_confidence'] ?? null, '%'),
            '- Confidence Random Forest : ' . $this->num($ai['rf_confidence'] ?? null, '%'),
            '- Indeks Risiko Gabungan : ' . $this->num($ai['risk_index'] ?? null, ' / 100'),
        );

        if ($others) {
            $lines[] = '';
            $lines[] = "*NODE LAIN DI DESA {$this->upper($desa)}*";
            foreach ($others as $n) {
                $lines[] = '- ' . $this->nodeLabel($n) . " : {$n['status']}";
            }
        }

        array_push($lines,
            '',
            'Peringatan *belum* dikirimkan kepada warga (' . $warga . ' nomor).',
            'Mohon segera melakukan verifikasi lapangan. Verifikasi oleh 1 kader sudah cukup.',
            $url,
            '',
            $v->level === 'BAHAYA'
                ? "Apabila tidak ada verifikasi dalam {$this->autoSendMinutes} menit, peringatan BAHAYA akan dikirimkan kepada warga secara otomatis."
                : "Apabila tidak ada verifikasi dalam {$this->waspadaExpireMinutes} menit, peringatan WASPADA tidak akan dikirimkan.",
            '',
            self::FOOTER,
        );
        return implode("\n", $lines);
    }

    /** Pemberitahuan singkat ke kader (berhasil, ditolak, otomatis, dibatalkan, kedaluwarsa). */
    private function kaderNotice(string $title, AlertVerification $v, array $body): string
    {
        return implode("\n", array_merge([
            '*LARAS EARLY WARNING*',
            "*{$title}*",
            '',
            "No. Verifikasi : #{$v->id}",
            "Wilayah : Desa {$v->desa}",
            "Status Awal : {$v->level}",
            '',
        ], $body, ['', self::FOOTER]));
    }

    /**
     * Pesan ke warga desa.
     * $meta: ['verified_by' => nama] | ['auto' => true] | ['repeat' => true]
     */
    public function wargaMessage(string $status, array $snapshot, array $meta = []): string
    {
        $desa  = $snapshot['desa'] ?? null;
        $nodes = $snapshot['nodes'] ?? [];
        $peta  = rtrim((string) config('app.url'), '/') . '/peta-evakuasi';
        $sapa  = $desa ? "Halo Warga Desa {$desa}," : 'Halo Warga,';
        $area  = collect($nodes)->where('status', $status)->map(fn ($n) => $n['name'] ?: $n['code'])->filter()->implode(', ');
        $waktu = $this->waktu($nodes[0]['data'] ?? []);

        $posko = [];
        try {
            $posko = collect(app(EvacuationMapController::class)->getDaftarPosko())->take(3)->values()
                ->map(fn ($p, $i) => ($i + 1) . ". {$p['nama']}")->all();
        } catch (\Throwable $e) { /* opsional */ }

        $verif = match (true) {
            !empty($meta['auto'])        => "Peringatan ini dikirim otomatis oleh sistem karena kondisi berbahaya dan belum ada verifikasi kader dalam {$this->autoSendMinutes} menit.",
            !empty($meta['verified_by']) => "Informasi ini telah diverifikasi oleh kader setempat ({$meta['verified_by']}).",
            default                      => 'Informasi ini telah diverifikasi oleh kader setempat.',
        };
        $repeat = !empty($meta['repeat']) ? ['*PENGINGAT:* kondisi wilayah masih sama seperti peringatan sebelumnya.', ''] : [];

        $lines = match ($status) {
            'BAHAYA' => array_merge([
                '*PERINGATAN DARURAT LONGSOR*',
                '',
                $sapa,
                'Berdasarkan hasil pemantauan sistem LARAS, kondisi wilayah saat ini menunjukkan potensi *tanah longsor* dengan status wilayah *BAHAYA*.',
                $verif,
                '',
            ], $repeat, array_filter([
                $area ? "Area terdampak : {$area}" : null,
                "Waktu : {$waktu}",
            ]), [
                '',
                '*Mohon segera meninggalkan lokasi dan menuju posko evakuasi terdekat:*',
            ], $posko, [
                '',
                'Rute evakuasi dapat diakses pada link berikut:',
                $peta,
                '',
                'Dahulukan lansia, anak-anak, ibu hamil, dan penyandang disabilitas.',
                'Lakukan check-in kepada petugas setelah tiba di posko.',
                '',
                'Kontak darurat : 112 | BPBD (031) 99860466',
            ]),
            'WASPADA' => array_merge([
                '*PERINGATAN DINI LONGSOR*',
                '',
                $sapa,
                'Berdasarkan hasil pemantauan sistem LARAS, kondisi wilayah saat ini menunjukkan peningkatan risiko *tanah longsor* dengan status wilayah *WASPADA*.',
                $verif,
                '',
            ], $repeat, array_filter([
                $area ? "Area terpantau : {$area}" : null,
                "Waktu : {$waktu}",
            ]), [
                '',
                '*Mohon warga bersiap untuk evakuasi:*',
                '1. Siapkan tas siaga berisi dokumen penting, obat-obatan, senter, air minum, dan uang.',
                '2. Hindari area tebing dan aliran air di sekitar lereng, terutama saat hujan.',
                '3. Pastikan lansia dan anak-anak dapat segera dievakuasi.',
                '4. Pantau informasi lanjutan dari kader dan sistem LARAS.',
                '',
                'Rute evakuasi dapat diakses pada link berikut:',
                $peta,
            ]),
            default => [
                '*INFORMASI KONDISI AMAN*',
                '',
                $sapa,
                'Berdasarkan hasil pemantauan sistem LARAS, kondisi wilayah saat ini telah kembali *AMAN*.',
                '',
                "Waktu : {$waktu}",
                '',
                'Bagi warga yang sedang berada di posko evakuasi, mohon menunggu arahan kader atau BPBD sebelum kembali ke rumah.',
            ],
        };

        return implode("\n", array_merge($lines, ['', self::FOOTER]));
    }

    /** Dipakai laras:wa-test: pesan warga dari 1 baris data. */
    public function buildMessage(string $status, array $data, array $meta = []): string
    {
        return $this->wargaMessage($status, ['desa' => null, 'nodes' => [['status' => $status, 'name' => null, 'code' => '', 'data' => $data]]], $meta);
    }

    private function nodeLabel(array $n): string
    {
        $code = $n['code'] ?? '-';
        $name = $n['name'] ?? null;
        return $name && $name !== $code ? "{$code} - {$name}" : $code;
    }

    private function num($v, string $unit = '', int $dec = 1): string
    {
        return $v === null || $v === '' ? '-' : number_format((float) $v, $dec, '.', '') . $unit;
    }

    private function signed($v, string $unit): string
    {
        if ($v === null || $v === '') return '-';
        $v = (float) $v;
        return ($v > 0 ? '+' : '') . number_format($v, 1, '.', '') . $unit;
    }

    private function upper(string $s): string
    {
        return mb_strtoupper($s);
    }

    /** "16 Okt 2026 | 14.05 WIB" */
    private function waktu(array $data): string
    {
        $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $t = (isset($data['timestamp']) ? Carbon::parse($data['timestamp']) : now())->timezone('Asia/Jakarta');
        return $t->format('j') . ' ' . $bulan[$t->month - 1] . ' ' . $t->format('Y') . ' | ' . $t->format('H.i') . ' WIB';
    }
}