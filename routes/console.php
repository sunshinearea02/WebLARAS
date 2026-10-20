<?php

use App\Models\SensorLog;
use App\Services\WhatsAppAlertService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
 * Uji notifikasi WhatsApp (GoWA).
 *   php artisan laras:wa-test                       -> pesan uji singkat ke semua WA_ALERT_TARGETS
 *   php artisan laras:wa-test --status=BAHAYA       -> contoh pesan BAHAYA memakai data sensor terbaru
 *   php artisan laras:wa-test --to=081234567890     -> kirim ke satu nomor saja
 */
Artisan::command('laras:wa-test {--status= : AMAN, WASPADA, atau BAHAYA} {--to= : nomor/grup tujuan, menimpa WA_ALERT_TARGETS}', function () {
    $wa = app(WhatsAppAlertService::class);
    $targets = $this->option('to') ? WhatsAppAlertService::parseTargets($this->option('to')) : $wa->targets();

    if (!config('services.gowa.url')) { $this->error('GOWA_URL belum diisi di .env'); return 1; }
    if (!$targets)                     { $this->error('Tujuan kosong. Isi WA_ALERT_TARGETS di .env atau pakai --to=08xxx'); return 1; }

    $status = strtoupper((string) $this->option('status'));
    if ($status !== '' && !in_array($status, ['AMAN', 'WASPADA', 'BAHAYA'], true)) {
        $this->error('--status harus AMAN, WASPADA, atau BAHAYA'); return 1;
    }

    $message = $status
        ? "[UJI COBA, BUKAN KEJADIAN NYATA]\n\n" . $wa->buildMessage($status, SensorLog::latest('timestamp')->first()?->toArray() ?? [])
        : "*Uji koneksi LARAS*\nNotifikasi WhatsApp dari LARAS Control Center sudah tersambung.\nWaktu: " . now('Asia/Jakarta')->format('d/m/Y H:i') . ' WIB';

    $this->info('Server GoWA: ' . config('services.gowa.url'));
    $gagal = 0;
    foreach ($targets as $t) {
        $r = $wa->sendTo($t, $message);
        $r['ok'] ? $this->line("  <fg=green>OK</>    {$t}  (id: {$r['message_id']})")
                 : $this->line("  <fg=red>GAGAL</> {$t}  {$r['error']}");
        $gagal += $r['ok'] ? 0 : 1;
    }
    return $gagal ? 1 : 0;
})->purpose('Kirim pesan uji notifikasi WhatsApp lewat GoWA');

/*
 * Pemeriksaan peringatan otomatis (TIDAK perlu dashboard terbuka):
 *  - baca status terbaru semua node, kelompokkan per desa
 *  - desa WASPADA/BAHAYA -> WA ke semua kader berisi lokasi + link verifikasi web
 *  - BAHAYA tanpa keputusan 10 menit -> otomatis ke warga desa itu
 * Dijalankan scheduler tiap 15 detik:  php artisan schedule:work
 */
Artisan::command('laras:alert-tick', function () {
    $res = app(WhatsAppAlertService::class)->evaluate();
    if (!$res) { $this->line('Tidak ada node dengan data baru yang sudah diproses AI.'); return; }
    foreach ($res as $desa => $r) $this->line("  {$desa}: {$r}");
})->purpose('Periksa status semua desa & antrean verifikasi kader');

Schedule::command('laras:alert-tick')->everyFifteenSeconds()->withoutOverlapping();

/*
 * Contoh pesan verifikasi ke kader (tanpa membuat antrean):
 *   php artisan laras:wa-test-kader --status=BAHAYA
 */
Artisan::command('laras:wa-test-kader {--status=BAHAYA}', function () {
    $wa = app(WhatsAppAlertService::class);
    $status = strtoupper((string) $this->option('status'));
    if (!in_array($status, ['WASPADA', 'BAHAYA'], true)) { $this->error('--status harus WASPADA atau BAHAYA'); return 1; }

    $kader = $wa->kaderContacts();
    if (!$kader) { $this->error('Belum ada nomor kader. Isi kolom phone akun admin/kader (status ACTIVE) atau WA_KADER_TARGETS.'); return 1; }

    $groups = $wa->currentByDesa();
    $desa = array_key_first($groups) ?? 'Contoh';
    $v = new \App\Models\AlertVerification(['level' => $status, 'desa' => $desa,
        'snapshot' => ['desa' => $desa, 'nodes' => $groups[$desa]['nodes'] ?? []]]);
    $v->id = 0;
    $msg = "[UJI COBA, JANGAN DIBALAS]\n\n" . $wa->kaderMessage($v);
    foreach ($kader as $phone => $user) {
        $r = $wa->sendTo($phone, $msg);
        $this->line(($r['ok'] ? '  <fg=green>OK</>    ' : '  <fg=red>GAGAL</> ') . $phone . ($user ? " ({$user->name})" : ' (.env)') . ($r['ok'] ? '' : "  {$r['error']}"));
    }
})->purpose('Kirim contoh pesan verifikasi ke semua kader');