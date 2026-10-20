<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * LARAS – asisten siaga longsor.
 *
 * Alur:
 *  1. Kumpulkan konteks nyata: status & sensor terbaru, tren 1 jam, prediksi AI,
 *     status tiap node, cuaca BMKG (di-cache), daftar posko, panduan kebencanaan.
 *  2. Kirim ke Gemini dengan systemInstruction + riwayat percakapan (multi-turn).
 *  3. Kalau Gemini tidak tersedia (API key kosong, kuota habis, timeout), LARAS tetap
 *     menjawab dengan jawaban berbasis aturan dari data yang sama.
 */
class ChatbotController extends Controller
{
    private const MODEL = 'gemini-2.5-flash';

    /** Ambang kelembapan yang sama dengan tampilan web (persen). */
    private const MOIST_BASAH = 55;
    private const MOIST_JENUH = 75;

    public function chat(Request $request)
    {
        $data = $request->validate([
            'message'        => 'required|string|max:500',
            'mode'           => 'nullable|in:warga,teknis',
            'history'        => 'nullable|array|max:10',
            'history.*.role' => 'required_with:history|in:user,ai',
            'history.*.text' => 'required_with:history|string|max:2000',
        ]);

        $message = trim($data['message']);
        $mode    = $data['mode'] ?? (auth()->check() ? 'teknis' : 'warga');
        $history = $data['history'] ?? [];

        $ctx = $this->buildContext();

        $apiKey = config('services.gemini.api_key');
        if ($apiKey) {
            $reply = $this->askGemini($apiKey, $this->systemInstruction($ctx, $mode), $history, $message);
            if ($reply !== null) {
                return response()->json(['reply' => $reply, 'source' => 'ai']);
            }
        }

        // Cadangan: tetap menjawab dari data walau AI tidak bisa dihubungi
        return response()->json(['reply' => $this->localAnswer($message, $ctx, $mode), 'source' => 'lokal']);
    }

    // =====================================================================
    // 1. KONTEKS
    // =====================================================================

    private function buildContext(): array
    {
        $latest = SensorLog::orderBy('timestamp', 'desc')->first();
        $recent = SensorLog::orderBy('timestamp', 'desc')->limit(60)->get();

        $now  = Carbon::now();
        $ts   = $latest?->timestamp ? Carbon::parse($latest->timestamp) : null;
        $ageMin = $ts ? max(0, (int) round($ts->diffInSeconds($now, false) / 60)) : null;

        $num = fn ($v, $d = 1) => $v === null ? null : round((float) $v, $d);

        $cur = [
            'waktu'          => $ts?->format('d M Y H:i'),
            'umur_menit'     => $ageMin,
            'status'         => strtoupper($latest->status ?? 'UNKNOWN') ?: 'UNKNOWN',
            'prediksi'       => strtoupper($latest->forecast_status ?? '') ?: null,
            'yakin_rf'       => $num($latest?->rf_confidence),
            'yakin_lstm'     => $num($latest?->lstm_confidence),
            'tanah_dangkal'  => $num($latest?->moisture_percent),
            'tanah_dalam'    => $num($latest?->moisture_percent_deep),
            'hujan'          => $num($latest?->rainfall_mm),
            'pitch'          => $num($latest?->pitch, 2),
            'roll'           => $num($latest?->roll, 2),
            'getaran'        => $num($latest?->vibration, 3),
            'baterai'        => $latest?->battery_percent,
            'alasan'         => $this->predictionText($latest, 'reasons'),
            'kualitas_data'  => $this->predictionText($latest, 'data_quality'),
        ];

        // Tren: bandingkan dengan data ±1 jam sebelum data terbaru
        $trend = ['ada' => false];
        if ($latest && $ts && $recent->count() >= 2) {
            $ref = $recent->last();
            foreach ($recent as $row) {
                if (Carbon::parse($row->timestamp)->gte($ts->copy()->subHour())) { $ref = $row; }
            }
            if ($ref && $ref->id !== $latest->id) {
                $refTs = Carbon::parse($ref->timestamp);
                $trend = [
                    'ada'            => true,
                    'rentang_menit'  => (int) round($refTs->diffInSeconds($ts) / 60),
                    'delta_dangkal'  => $this->delta($latest->moisture_percent, $ref->moisture_percent),
                    'delta_dalam'    => $this->delta($latest->moisture_percent_deep, $ref->moisture_percent_deep),
                    'delta_miring'   => ($latest->pitch !== null && $ref->pitch !== null)
                        ? round(max(abs($latest->pitch - $ref->pitch), abs(($latest->roll ?? 0) - ($ref->roll ?? 0))), 2) : null,
                    'hujan_tercatat' => $recent->filter(fn ($r) => Carbon::parse($r->timestamp)->gte($refTs) && (float) ($r->rainfall_mm ?? 0) > 0)->count() > 0,
                    'status_berubah' => strtoupper($ref->status ?? '') !== $cur['status'] ? strtoupper($ref->status ?? 'UNKNOWN') : null,
                ];
            }
        }

        $map = app(EvacuationMapController::class);
        $nodes = collect($map->nodesForMap())->map(fn ($n) => [
            'kode'    => $n->node_code ?? '-',
            'nama'    => $n->node_name ?? ($n->node_code ?? '-'),
            'desa'    => $n->desa ?? null,
            'status'  => $n->status_label ?? ($n->status ?? 'UNKNOWN'),
            'baterai' => $n->battery ?? null,
        ])->all();

        return [
            'cur'      => $cur,
            'trend'    => $trend,
            'estimasi' => $this->moistureEta($recent),
            'nodes'    => $nodes,
            'posko'    => $map->getDaftarPosko(),
            'cuaca'    => $this->weather(),
            'sekarang' => $now->locale('id')->translatedFormat('l, d F Y H:i'),
        ];
    }

    private function predictionText(?SensorLog $row, string $field): ?string
    {
        try {
            $v = $row?->prediction?->{$field};
        } catch (\Throwable $e) {
            return null;
        }
        if (is_array($v)) $v = implode('; ', array_filter($v));
        $v = trim((string) $v);
        return $v === '' ? null : mb_substr($v, 0, 400);
    }

    private function delta($a, $b): ?float
    {
        return ($a === null || $b === null) ? null : round((float) $a - (float) $b, 1);
    }

    /** Perkiraan menit sampai kelembapan dangkal menyentuh ambang jenuh (regresi linear sederhana). */
    private function moistureEta($recent): ?string
    {
        $rows = $recent->take(12)->reverse()->values();
        $vals = $rows->pluck('moisture_percent')->filter(fn ($v) => $v !== null)->map(fn ($v) => (float) $v)->values();
        if ($vals->count() < 4) return null;

        $n = $vals->count(); $mx = ($n - 1) / 2; $my = $vals->avg(); $num = 0; $den = 0;
        foreach ($vals as $x => $y) { $num += ($x - $mx) * ($y - $my); $den += ($x - $mx) ** 2; }
        $slope = $den ? $num / $den : 0;

        $times = $rows->pluck('timestamp')->map(fn ($t) => strtotime($t))->values();
        $gaps = [];
        for ($i = 1; $i < $times->count(); $i++) { $d = $times[$i] - $times[$i - 1]; if ($d > 0) $gaps[] = $d; }
        $step = $gaps ? array_sum($gaps) / count($gaps) : 60;
        $now  = $vals->last();

        if ($slope > 0.05 && $now < self::MOIST_JENUH) {
            $min = (int) round((self::MOIST_JENUH - $now) / $slope * $step / 60);
            if ($min > 0 && $min < 24 * 60) return "Jika tren naik berlanjut, tanah permukaan bisa mencapai kondisi jenuh (" . self::MOIST_JENUH . "%) dalam sekitar {$min} menit.";
        }
        if ($slope < -0.05 && $now > self::MOIST_BASAH) {
            $min = (int) round(($now - self::MOIST_BASAH) / abs($slope) * $step / 60);
            if ($min > 0 && $min < 24 * 60) return "Jika tren turun berlanjut, tanah permukaan bisa kembali di bawah " . self::MOIST_BASAH . "% dalam sekitar {$min} menit.";
        }
        return null;
    }

    /** Cuaca BMKG, di-cache 10 menit (tidak memanggil server sendiri lewat HTTP). */
    private function weather(): ?array
    {
        try {
            return Cache::remember('laras_chat_weather', 600, function () {
                $w = app(DashboardController::class)->getWeather()->getData(true);
                return is_array($w) ? $w : null;
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    // =====================================================================
    // 2. PROMPT
    // =====================================================================

    private function systemInstruction(array $ctx, string $mode): string
    {
        $c = $ctx['cur'];
        $L = [];

        $L[] = 'Kamu adalah LARAS (Landslide Alert and Rescue Adaptive System), asisten siaga longsor resmi di website LARAS Control Center buatan tim Institut Teknologi Sepuluh Nopember (ITS). Wilayah pilot: Kecamatan Pacet, Kabupaten Mojokerto, Jawa Timur.';
        $L[] = 'Tugasmu: menjelaskan kondisi lereng dari data sensor, memberi saran keselamatan yang jelas, dan membantu warga menemukan posko evakuasi. Selalu sebut dirimu "LARAS". Jangan pernah menyebut nama lain.';
        $L[] = '';
        $L[] = $mode === 'warga'
            ? "GAYA BAHASA (WARGA): Pengguna adalah warga awam. Pakai bahasa Indonesia sehari-hari yang hangat dan menenangkan. Hindari istilah teknis (pitch, roll, LSTM, RF, confidence, mm/jam, akselerometer). Terjemahkan angka ke kata: kering, lembap, basah, sangat basah/jenuh, tidak hujan, hujan ringan/sedang/lebat. Kalimat pendek. Fokus pada: aman atau tidak, apa yang harus dilakukan, ke mana mengungsi."
            : "GAYA BAHASA (PETUGAS): Pengguna adalah kader/admin. Boleh memakai istilah teknis dan angka, sebutkan sumber angka (sensor, prediksi RF/LSTM, BMKG). Tetap ringkas dan terstruktur.";
        $L[] = '';

        $L[] = "WAKTU SEKARANG: {$ctx['sekarang']}";
        $L[] = '';
        $L[] = '== DATA SENSOR TERBARU ==';
        if ($c['waktu']) {
            $L[] = "Waktu data: {$c['waktu']} (" . ($c['umur_menit'] !== null ? "{$c['umur_menit']} menit lalu" : 'waktu tidak diketahui') . ')';
            if ($c['umur_menit'] !== null && $c['umur_menit'] > 15) {
                $L[] = "PERHATIAN: data sudah lebih dari 15 menit tidak diperbarui, sensor mungkin offline. Sampaikan ini kepada pengguna dan jangan menyatakan kondisi pasti aman.";
            }
            $L[] = "Status lereng: {$c['status']} (" . $this->statusMeaning($c['status']) . ')';
            $L[] = 'Prediksi 1 jam ke depan: ' . ($c['prediksi'] ?: 'belum tersedia')
                 . ($c['yakin_rf'] !== null ? " (keyakinan Random Forest {$c['yakin_rf']}%)" : '')
                 . ($c['yakin_lstm'] !== null ? ", keyakinan LSTM {$c['yakin_lstm']}%" : '');
            $L[] = 'Kelembapan tanah permukaan (±25 cm): ' . $this->fmt($c['tanah_dangkal'], '%') . ' -> ' . $this->soilWord($c['tanah_dangkal']);
            $L[] = 'Kelembapan tanah dalam (±75 cm): ' . $this->fmt($c['tanah_dalam'], '%') . ' -> ' . $this->soilWord($c['tanah_dalam']);
            $L[] = 'Curah hujan di lokasi sensor: ' . $this->fmt($c['hujan'], ' mm/jam') . ' -> ' . $this->rainWord($c['hujan']);
            $L[] = 'Kemiringan tiang sensor: pitch ' . $this->fmt($c['pitch'], '°') . ', roll ' . $this->fmt($c['roll'], '°') . '; getaran ' . $this->fmt($c['getaran'], ' g');
            if ($c['baterai'] !== null) $L[] = "Baterai node: {$c['baterai']}%";
            if ($c['alasan'])        $L[] = "Alasan status dari AI: {$c['alasan']}";
            if ($c['kualitas_data']) $L[] = "Catatan kualitas data: {$c['kualitas_data']}";
        } else {
            $L[] = 'Belum ada data sensor di sistem. Katakan dengan jujur bahwa data belum tersedia.';
        }

        $t = $ctx['trend'];
        $L[] = '';
        $L[] = '== PERUBAHAN SEKITAR 1 JAM TERAKHIR ==';
        if ($t['ada']) {
            $L[] = "Rentang perbandingan: {$t['rentang_menit']} menit.";
            $L[] = 'Perubahan kelembapan permukaan: ' . $this->fmtDelta($t['delta_dangkal']) . '; tanah dalam: ' . $this->fmtDelta($t['delta_dalam']) . '.';
            $L[] = 'Perubahan kemiringan maksimum: ' . $this->fmt($t['delta_miring'], '°') . ' (>=1° perlu dicek kader, >=3° tanda bergerak).';
            $L[] = 'Hujan tercatat dalam periode ini: ' . ($t['hujan_tercatat'] ? 'ya' : 'tidak') . '.';
            if ($t['status_berubah']) $L[] = "Status berubah dari {$t['status_berubah']} menjadi {$c['status']}.";
        } else {
            $L[] = 'Belum cukup data untuk membandingkan.';
        }
        if ($ctx['estimasi']) $L[] = 'Estimasi: ' . $ctx['estimasi'];

        $L[] = '';
        $L[] = '== STATUS TIAP NODE SENSOR ==';
        foreach ($ctx['nodes'] as $n) {
            $L[] = "- {$n['nama']} ({$n['kode']}" . ($n['desa'] ? ", desa {$n['desa']}" : '') . "): {$n['status']}" . ($n['baterai'] !== null ? ", baterai {$n['baterai']}%" : '');
        }
        if (!$ctx['nodes']) $L[] = '- belum ada node terdaftar';

        $L[] = '';
        $L[] = '== CUACA BMKG ==';
        $w = $ctx['cuaca'];
        if ($w && !in_array($w['condition'] ?? '', ['Tidak tersedia', 'Gagal memuat cuaca', 'N/A', ''], true)) {
            $L[] = "Lokasi: " . ($w['location'] ?? 'Pacet, Mojokerto') . ". Kondisi: {$w['condition']}"
                 . (($w['temp'] ?? null) !== null ? ", suhu {$w['temp']}°C" : '')
                 . (($w['humidity'] ?? null) !== null ? ", kelembapan udara {$w['humidity']}%" : '')
                 . ", perkiraan hujan " . ($w['rainfall'] ?? 0) . ' mm.';
        } else {
            $L[] = 'Data cuaca BMKG sedang tidak tersedia.';
        }

        $L[] = '';
        $L[] = '== POSKO EVAKUASI ==';
        foreach ($ctx['posko'] as $p) $L[] = "- {$p['nama']}: {$p['lokasi']}";
        $L[] = 'Rute dan posko terdekat bisa dilihat di menu "Peta Evakuasi" di website ini. Setiba di posko, warga melapor (check-in) ke kader.';

        $L[] = '';
        $L[] = '== PENGETAHUAN LARAS ==';
        $L[] = '- AMAN: lereng stabil, beraktivitas biasa, tetap kenali rute evakuasi dan siapkan tas siaga.';
        $L[] = '- WASPADA: tanah jenuh air atau mulai bergerak. Siapkan tas siaga, jauhi tebing, siapkan lansia/anak, ikuti arahan kader.';
        $L[] = '- BAHAYA: risiko longsor tinggi. Segera ke posko lewat rute evakuasi, jangan ambil jalan pintas, dahulukan lansia/anak/disabilitas, lalu check-in di posko. Status BAHAYA diverifikasi kader lebih dulu (human in the loop), tetapi warga yang merasa terancam boleh langsung menjauh dan menghubungi 112.';
        $L[] = '- Ambang kelembapan di web: di atas ' . self::MOIST_BASAH . '% basah, di atas ' . self::MOIST_JENUH . '% jenuh.';
        $L[] = '- Tanda alam longsor: retakan baru di tanah/tembok sejajar lereng, rembesan atau mata air baru, air sumur tiba-tiba keruh, pohon/tiang/pagar miring, suara gemuruh dari lereng, pintu/jendela sulit dibuka.';
        $L[] = '- Isi tas siaga: dokumen penting (KTP, KK, surat tanah) dalam plastik, obat-obatan pribadi, senter dan baterai, air minum dan makanan ringan, uang tunai, pakaian ganti, jas hujan, peluit, power bank, nomor penting.';
        $L[] = '- Setelah longsor: jangan kembali ke lokasi sebelum dinyatakan aman oleh BPBD/kader, waspadai longsor susulan, laporkan korban/kerusakan.';
        $L[] = '- Cara kerja: sensor kelembapan tanah 2 kedalaman, penakar hujan, dan sensor gerak (IMU) di lereng mengirim data lewat radio LoRa 920 MHz ke gateway Raspberry Pi di posko; AI (Random Forest + LSTM + aturan fisik) menilai status dan memperkirakan 60 menit ke depan; peringatan dikirim ke kader.';
        $L[] = '- Kontak darurat: 112 (gratis, bisa saat HP terkunci), BNPB 117, Basarnas 115, BPBD setempat (031) 99860466.';

        $L[] = '';
        $L[] = '== ATURAN MENJAWAB ==';
        $L[] = '1. Jawab langsung inti pertanyaan, lalu beri saran tindakan bila relevan. Panjang ideal 2-6 kalimat atau maksimal 5 poin bernomor.';
        $L[] = '2. Hanya gunakan angka dan fakta dari data di atas. Jangan mengarang lokasi, angka, atau kejadian. Jika data tidak ada, katakan jujur dan sarankan bertanya ke kader desa.';
        $L[] = '3. Jika status WASPADA/BAHAYA atau pengguna menyebut tanda bahaya (retakan, gemuruh, tanah bergerak, ada korban), utamakan keselamatan: suruh menjauh dari lereng, menuju posko, dan hubungi 112.';
        $L[] = '4. Jangan pernah menyatakan "pasti aman 100%". LARAS adalah alat bantu, keputusan evakuasi mengikuti arahan kader/BPBD.';
        $L[] = '5. Untuk pertanyaan di luar topik longsor, cuaca, evakuasi, atau sistem LARAS, jawab singkat dengan sopan lalu arahkan kembali ke topik keselamatan.';
        $L[] = '6. Tulis teks biasa tanpa markdown (tanpa **, *, #, tabel). Untuk daftar, tulis setiap nomor di baris baru seperti "1. ...".';

        return implode("\n", $L);
    }

    private function statusMeaning(string $s): string
    {
        return match ($s) {
            'AMAN'    => 'kondisi normal',
            'WASPADA' => 'siaga, tanah jenuh air atau mulai bergerak',
            'BAHAYA'  => 'risiko longsor tinggi, evakuasi',
            'PENDING' => 'data masuk tetapi belum dianalisis AI',
            default   => 'belum diketahui',
        };
    }

    private function soilWord($v): string
    {
        if ($v === null) return 'tidak terbaca';
        if ($v > self::MOIST_JENUH) return 'sangat basah / jenuh';
        if ($v > self::MOIST_BASAH) return 'basah';
        if ($v > 30) return 'lembap normal';
        return 'kering';
    }

    private function rainWord($r): string
    {
        if ($r === null) return 'tidak terbaca';
        if ($r > 50) return 'hujan sangat lebat';
        if ($r > 20) return 'hujan lebat';
        if ($r > 5)  return 'hujan sedang';
        if ($r > 0)  return 'hujan ringan';
        return 'tidak hujan';
    }

    private function fmt($v, string $unit = ''): string
    {
        return $v === null ? 'tidak tersedia' : $v . $unit;
    }

    private function fmtDelta($v): string
    {
        if ($v === null) return 'tidak tersedia';
        return ($v > 0 ? '+' : '') . $v . ' poin';
    }

    // =====================================================================
    // 3. GEMINI
    // =====================================================================

    private function askGemini(string $apiKey, string $system, array $history, string $message): ?string
    {
        $contents = [];
        foreach (array_slice($history, -8) as $h) {
            $contents[] = ['role' => $h['role'] === 'ai' ? 'model' : 'user', 'parts' => [['text' => $h['text']]]];
        }
        // Gemini mensyaratkan giliran pertama dari user
        while ($contents && $contents[0]['role'] !== 'user') array_shift($contents);
        $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

        try {
            $res = Http::timeout(25)->post(
                'https://generativelanguage.googleapis.com/v1beta/models/' . self::MODEL . ':generateContent?key=' . $apiKey,
                [
                    'systemInstruction' => ['parts' => [['text' => $system]]],
                    'contents'          => $contents,
                    'generationConfig'  => [
                        'temperature'     => 0.3,
                        'maxOutputTokens' => 900,
                        // Gemini 2.5: matikan "thinking" supaya jatah token dipakai untuk jawaban
                        'thinkingConfig'  => ['thinkingBudget' => 0],
                    ],
                    'safetySettings' => [
                        ['category' => 'HARM_CATEGORY_DANGEROUS_CONTENT', 'threshold' => 'BLOCK_ONLY_HIGH'],
                    ],
                ]
            );
        } catch (\Throwable $e) {
            report($e);
            return null;
        }

        if (!$res->successful()) {
            logger()->warning('LARAS chatbot: Gemini error', ['status' => $res->status(), 'body' => mb_substr($res->body(), 0, 500)]);
            return null;
        }

        $cand  = $res->json('candidates.0');
        $parts = $cand['content']['parts'] ?? [];
        $text  = trim(implode('', array_map(fn ($p) => $p['text'] ?? '', $parts)));
        if ($text === '') return null;

        if (($cand['finishReason'] ?? '') === 'MAX_TOKENS') {
            // potong rapi di akhir kalimat terakhir
            $cut = max(strrpos($text, '.') ?: 0, strrpos($text, "\n") ?: 0);
            if ($cut > 40) $text = substr($text, 0, $cut + 1);
        }

        return $this->clean($text);
    }

    private function clean(string $t): string
    {
        $t = preg_replace('/\*\*(.*?)\*\*/s', '$1', $t);
        $t = preg_replace('/(?<!\w)\*(?!\s)(.*?)(?<!\s)\*(?!\w)/s', '$1', $t);
        $t = preg_replace('/^\s*[\*\-•]\s+/m', '- ', $t);
        $t = preg_replace('/^#{1,6}\s+/m', '', $t);
        $t = str_replace(['→', '=>'], ':', $t);
        $t = preg_replace("/\n{3,}/", "\n\n", $t);
        return trim($t);
    }

    // =====================================================================
    // 4. CADANGAN TANPA AI (berbasis aturan, dari data yang sama)
    // =====================================================================

    private function localAnswer(string $msg, array $ctx, string $mode): string
    {
        $q = mb_strtolower($msg);
        $c = $ctx['cur'];
        $has = fn (array $words) => collect($words)->contains(fn ($w) => str_contains($q, $w));

        $statusLine = match ($c['status']) {
            'AMAN'    => 'Saat ini status lereng AMAN. Silakan beraktivitas seperti biasa, tetap waspada bila hujan deras dan lama.',
            'WASPADA' => 'Saat ini status lereng WASPADA. Siapkan tas siaga, jauhi tebing, dan ikuti arahan kader desa.',
            'BAHAYA'  => 'Saat ini status lereng BAHAYA. Segera menuju posko evakuasi terdekat dan hubungi 112 bila perlu bantuan.',
            default   => 'Status lereng belum bisa dipastikan karena data sensor belum tersedia. Tetap perhatikan tanda alam dan arahan kader.',
        };
        if ($c['umur_menit'] !== null && $c['umur_menit'] > 15) {
            $statusLine .= " Catatan: data terakhir masuk {$c['umur_menit']} menit lalu, sensor mungkin sedang tidak mengirim.";
        }

        $posko = collect($ctx['posko'])->values()->map(fn ($p, $i) => ($i + 1) . ". {$p['nama']} ({$p['lokasi']})")->implode("\n");

        if ($has(['mengungsi', 'evakuasi', 'posko', 'ke mana', 'kemana', 'rute', 'titik kumpul'])) {
            return "Posko evakuasi LARAS di wilayah Pacet:\n{$posko}\nLihat rute tercepat di menu Peta Evakuasi. Setiba di posko, laporkan diri (check-in) ke kader.";
        }
        if ($has(['tanda', 'ciri', 'gejala'])) {
            return "Tanda-tanda akan terjadi longsor:\n1. Retakan baru di tanah atau tembok, terutama sejajar lereng.\n2. Muncul rembesan atau mata air baru, atau air sumur tiba-tiba keruh.\n3. Pohon, tiang listrik, atau pagar mulai miring.\n4. Suara gemuruh dari arah lereng saat atau setelah hujan deras.\n5. Pintu atau jendela tiba-tiba sulit dibuka.\nJika melihat tanda ini, segera menjauh dari lereng dan beri tahu kader atau hubungi 112.";
        }
        if ($has(['tas siaga', 'siapkan', 'persiapan', 'bawa apa', 'perlengkapan'])) {
            return "Isi tas siaga bencana:\n1. Dokumen penting (KTP, KK, surat tanah) dalam plastik.\n2. Obat-obatan pribadi dan P3K.\n3. Senter, baterai cadangan, dan power bank.\n4. Air minum, makanan ringan, dan uang tunai.\n5. Pakaian ganti, jas hujan, dan peluit.\nLetakkan tas di dekat pintu agar mudah dibawa saat harus mengungsi.";
        }
        if ($has(['nomor', 'telepon', 'kontak', 'hubungi', 'darurat'])) {
            return "Nomor darurat:\n1. 112, panggilan darurat gratis (bisa saat HP terkunci).\n2. BNPB 117.\n3. Basarnas 115 untuk pencarian dan pertolongan.\n4. BPBD setempat (031) 99860466.";
        }
        if ($has(['cuaca', 'bmkg', 'suhu'])) {
            $w = $ctx['cuaca'];
            $cw = ($w && !in_array($w['condition'] ?? '', ['Tidak tersedia', 'Gagal memuat cuaca', 'N/A', ''], true))
                ? "Menurut BMKG, cuaca di " . ($w['location'] ?? 'Pacet') . " saat ini {$w['condition']}" . (($w['temp'] ?? null) !== null ? " dengan suhu {$w['temp']}°C" : '') . '.'
                : 'Data cuaca BMKG sedang tidak tersedia.';
            return $cw . ' Di lokasi sensor: ' . $this->rainWord($c['hujan']) . '. ' . $statusLine;
        }
        if ($has(['hujan'])) {
            return 'Di lokasi sensor saat ini ' . $this->rainWord($c['hujan']) . '. Tanah permukaan ' . $this->soilWord($c['tanah_dangkal']) . '. ' . $statusLine;
        }
        if ($has(['prediksi', 'perkiraan', 'nanti', 'jam ke depan', 'akan'])) {
            $p = $c['prediksi'] ? "Perkiraan 1 jam ke depan: {$c['prediksi']}." : 'Perkiraan 1 jam ke depan belum tersedia.';
            return $p . ($ctx['estimasi'] ? ' ' . $ctx['estimasi'] : '') . ' Perkiraan bisa berubah, tetap ikuti arahan kader.';
        }
        if ($has(['laras', 'cara kerja', 'sistem', 'kamu siapa', 'siapa kamu'])) {
            return 'Saya LARAS, asisten siaga longsor dari tim ITS. Sensor di lereng mengukur kebasahan tanah, hujan, dan gerakan tanah, lalu mengirim datanya lewat radio LoRa ke posko. Komputer di posko menilai status Aman, Waspada, atau Bahaya dan memperkirakan kondisi 1 jam ke depan, lalu kader menerima peringatan.';
        }

        // default: ringkasan kondisi
        $detail = $mode === 'teknis'
            ? sprintf(' Kelembapan dangkal %s, dalam %s, hujan %s, prediksi %s.', $this->fmt($c['tanah_dangkal'], '%'), $this->fmt($c['tanah_dalam'], '%'), $this->fmt($c['hujan'], ' mm/jam'), $c['prediksi'] ?: 'belum ada')
            : ' Tanah permukaan ' . $this->soilWord($c['tanah_dangkal']) . ', ' . $this->rainWord($c['hujan']) . '.';
        return $statusLine . $detail;
    }
}