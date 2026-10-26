<?php

namespace App\Console\Commands;

use App\Models\SensorNode;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Simulasi data sensor masuk SATU PER SATU (seperti node sungguhan), untuk uji alur & rekaman video.
 *
 *   php artisan laras:simulasi                                   # NODE-01..03, BAHAYA, tiap 5 dtk, terus
 *   php artisan laras:simulasi --node=NODE-03 --status=WASPADA --interval=3 --jumlah=20
 *   php artisan laras:simulasi --status=campur                   # NODE pertama BAHAYA, sisanya WASPADA
 *   php artisan laras:simulasi --status=AI                       # status dikosongkan -> diisi AI di Raspy
 *   php artisan laras:simulasi --jumlah=10 --pratinjau          # lihat contoh data tanpa menyimpan
 *
 * Nilai tiap node bergeser halus dari data sebelumnya (tidak melompat acak), dengan tren pelan memburuk.
 * Kolom lengkap: kelembapan dangkal & dalam (+ nilai mentah ADC), hujan, MPU6050 RAW LSB
 * (ax..gz, +-2g = 16384/g, +-250 dps = 131/dps, sama dengan firmware Node 1), pitch & roll.
 * sensor_log_id 'UJI-SIM' -> dilewati AI (status uji tidak ditimpa). Mode AI memakai kode node.
 * Berhenti: Ctrl+C. Hapus data uji: DELETE FROM sensor_data WHERE sensor_log_id = 'UJI-SIM';
 */
class SimulasiSensor extends Command
{
    protected $signature = 'laras:simulasi
        {--node=NODE-01,NODE-02,NODE-03 : Kode node, pisahkan dengan koma}
        {--status=BAHAYA : AMAN | WASPADA | BAHAYA | campur | AI}
        {--interval=5 : Jeda antar data (detik)}
        {--jumlah=0 : Jumlah data yang dikirim (0 = terus sampai Ctrl+C)}
        {--pratinjau : Tampilkan contoh data saja, TIDAK dimasukkan ke database}';

    protected $description = 'Kirim data sensor simulasi ke Supabase satu per satu (realtime), lengkap dengan MPU6050';

    /** Rentang nilai per status: [kelembapan dangkal, dalam, hujan mm, kemiringan derajat, getaran LSB] */
    private const PROFIL = [
        'AMAN'    => [[38, 48], [42, 52], [0, 2],   [0.2, 1.0], 60],
        'WASPADA' => [[66, 76], [60, 70], [10, 20], [1.5, 3.5], 250],
        'BAHAYA'  => [[87, 95], [83, 92], [28, 45], [5.0, 9.0], 900],
    ];

    public function handle(): int
    {
        $kode = array_values(array_filter(array_map(fn ($k) => strtoupper(trim($k)), explode(',', (string) $this->option('node')))));
        $nodes = SensorNode::query()->get()->filter(fn ($n) => in_array(strtoupper($n->node_code), $kode, true))
            ->sortBy(fn ($n) => array_search(strtoupper($n->node_code), $kode, true))->values();
        if ($nodes->isEmpty()) {
            $this->error('Node tidak ditemukan: ' . implode(', ', $kode) . '. Cek kode node di tabel sensor_nodes.');
            return self::FAILURE;
        }
        $hilang = array_diff($kode, $nodes->map(fn ($n) => strtoupper($n->node_code))->all());
        if ($hilang) $this->warn('Dilewati (tidak ada di sensor_nodes): ' . implode(', ', $hilang));

        $mode = strtoupper((string) $this->option('status'));
        if (! in_array($mode, ['AMAN', 'WASPADA', 'BAHAYA', 'CAMPUR', 'AI'], true)) {
            $this->error('--status harus AMAN, WASPADA, BAHAYA, campur, atau AI');
            return self::FAILURE;
        }
        $interval = max(1, (float) $this->option('interval'));
        $jumlah = (int) $this->option('jumlah');

        $this->info(sprintf('Simulasi %s | node: %s | tiap %s dtk | %s. Ctrl+C untuk berhenti.',
            $mode, $nodes->map(fn ($n) => "{$n->node_code} ({$n->node_name})")->implode(', '), $interval,
            $jumlah ? "{$jumlah} data" : 'terus-menerus'));

        $i = 0;
        while ($jumlah === 0 || $i < $jumlah) {
            $node = $nodes[$i % $nodes->count()];
            $status = match ($mode) {
                'CAMPUR' => $i % $nodes->count() === 0 ? 'BAHAYA' : 'WASPADA',
                'AI'     => 'BAHAYA',       // nilai sensor dibuat seperti BAHAYA, status diisi AI
                default  => $mode,
            };
            $row = $this->bacaan($node, $status, $mode === 'AI');
            $pratinjau = (bool) $this->option('pratinjau');
            $id = $pratinjau ? 0 : DB::table('sensor_data')->insertGetId($row);
            $this->line(sprintf('%s  #%d  %-8s %-30s %-8s tanah %5.1f%% / %5.1f%%  hujan %5.1f mm  az %5d  pitch %5.2f  roll %5.2f',
                now()->format('H:i:s'), $id, $node->node_code, $node->node_name, $row['status'] ?? '(AI)',
                $row['kelembapan_dangkal'], $row['kelembapan_dalam'], $row['rainfall_mm'], $row['az'], $row['pitch'], $row['roll']));
            $i++;
            if (! $pratinjau && ($jumlah === 0 || $i < $jumlah)) usleep((int) ($interval * 1_000_000));
        }

        $this->info($this->option('pratinjau') ? "Selesai: {$i} data (pratinjau, TIDAK disimpan)." : "Selesai: {$i} data terkirim.");
        return self::SUCCESS;
    }

    /** Nilai terakhir tiap node, supaya data berikutnya hanya bergeser sedikit (tidak melompat). */
    private array $jejak = [];

    private function bacaan(SensorNode $node, string $status, bool $olehAi): array
    {
        [$dk, $dl, $hujan, $miring, $getar] = self::PROFIL[$status];
        $acak = fn (float $a, float $b) => $a + (mt_rand() / mt_getrandmax()) * ($b - $a);
        $batas = fn (float $v, array $r) => max($r[0], min($r[1], $v));

        // Titik awal per node (sepertiga bawah rentang status), lalu tiap data bergeser sedikit
        // (random walk) dengan tren pelan memburuk: tanah makin basah, kemiringan makin besar.
        $s = $this->jejak[$node->id] ?? null;
        if (! $s || $s['status'] !== $status) {
            $arah = $node->id % 2 ? 1 : -1;   // arah miring tetap per node
            $s = [
                'status' => $status,
                'dk'     => $acak($dk[0], $dk[0] + ($dk[1] - $dk[0]) * 0.35),
                'dl'     => $acak($dl[0], $dl[0] + ($dl[1] - $dl[0]) * 0.35),
                'hujan'  => $acak($hujan[0], ($hujan[0] + $hujan[1]) / 2),
                'pitch'  => $arah * $acak($miring[0], $miring[0] + ($miring[1] - $miring[0]) * 0.3),
                'roll'   => -$arah * $acak($miring[0] * 0.4, $miring[0] * 0.6),
            ];
        } else {
            $s['dk']    = $batas($s['dk'] + 0.06 + $acak(-0.25, 0.25), $dk);
            $s['dl']    = $batas($s['dl'] + 0.05 + $acak(-0.18, 0.18), $dl);
            $tengah     = ($hujan[0] + $hujan[1]) / 2;
            $s['hujan'] = $batas($s['hujan'] + ($tengah - $s['hujan']) * 0.08 + $acak(-1.2, 1.2), $hujan);
            $tanda      = $s['pitch'] >= 0 ? 1 : -1;
            $s['pitch'] = $tanda * $batas(abs($s['pitch']) + 0.02 + $acak(-0.05, 0.05), $miring);
            $s['roll']  = $s['roll'] + $acak(-0.04, 0.04);
        }
        $this->jejak[$node->id] = $s;

        $kel_dk = round($s['dk'], 2);
        $kel_dl = round($s['dl'], 2);

        // MPU6050: gravitasi diputar sesuai kemiringan + derau kecil (RAW LSB, 1 g = 16384)
        $p = deg2rad($s['pitch']); $r = deg2rad($s['roll']); $g = 16384;
        $noise = fn (int $amp) => mt_rand(-$amp, $amp);
        $amp = max(4, (int) round($getar / 15));
        $ax = (int) round(-sin($p) * $g) + $noise($amp);
        $ay = (int) round(sin($r) * cos($p) * $g) + $noise($amp);
        $az = (int) round(cos($r) * cos($p) * $g) + $noise($amp);
        $gyro = max(3, (int) round($getar / 30));

        return [
            'node_id'              => $node->id,
            'timestamp'            => now()->format('Y-m-d H:i:s.u'),   // WIB (timezone aplikasi)
            'sensor_log_id'        => $olehAi ? strtoupper($node->node_code) : 'UJI-SIM',
            'moisture_raw_dangkal' => (int) round(3200 - $kel_dk * 19),
            'moisture_raw_dalam'   => (int) round(3200 - $kel_dl * 19),
            'kelembapan_dangkal'   => $kel_dk,
            'kelembapan_dalam'     => $kel_dl,
            'rainfall_mm'          => round($s['hujan'], 2),
            'ax' => $ax, 'ay' => $ay, 'az' => $az,
            'gx' => $noise($gyro), 'gy' => $noise($gyro), 'gz' => $noise($gyro),
            // pitch & roll dihitung dari akselerometer, sama seperti firmware node
            'pitch'                => round(rad2deg(atan2(-$ax, sqrt($ay * $ay + $az * $az))), 2),
            'roll'                 => round(rad2deg(atan2($ay, $az)), 2),
            'status'               => $olehAi ? null : $status,
            'forecast_status'      => $olehAi ? null : $status,
        ];
    }
}
