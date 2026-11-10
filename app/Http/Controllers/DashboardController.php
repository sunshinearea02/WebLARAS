<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use App\Services\WhatsAppAlertService;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        $logs    = SensorLog::orderBy('timestamp', 'asc')->get();
        $current = SensorLog::latest('timestamp')->first();

        foreach ($logs as $log) { $this->mapFields($log); }
        $this->mapFields($current);

        $weatherInfo = [
            'condition' => 'Memuat...',
            'rainfall'  => 0,
            'temp'      => null,
            'humidity'  => null,
            'location'  => self::LOKASI_DEFAULT,
        ];

        try {
            $weatherInfo = $this->fetchBmkgPacet();
        } catch (\Exception $e) {
            $weatherInfo['condition'] = 'Gagal memuat cuaca';
        }

        // Ringkasan node & posko untuk Beranda (sama dengan halaman Peta Evakuasi)
        $map    = app(EvacuationMapController::class);
        $nodes  = $map->nodesForMap();
        $posko  = $map->getDaftarPosko();

        return view('dashboard', compact('logs', 'current', 'weatherInfo', 'nodes', 'posko'));
    }

    public function getLatestData()
    {
        $current = SensorLog::latest('timestamp')->first();

        if (!$current) {
            return response()->json(['error' => 'Tidak ada data sensor.'], 404);
        }

        $this->mapFields($current);

        // WhatsApp (GoWA): cadangan bila scheduler belum jalan. Dibatasi 1x per 15 detik.
        try {
            app(WhatsAppAlertService::class)->evaluateThrottled();
        } catch (\Throwable $e) {
            report($e); // notifikasi gagal tidak boleh membuat dashboard error
        }

        return response()->json($current);
    }

    /** Halaman tabel data sensor yang bertambah otomatis (tanpa refresh), mis. untuk rekaman video. */
    public function realtime()
    {
        $nodes = \App\Models\SensorNode::query()->get(['id', 'node_code', 'node_name', 'desa'])
            ->mapWithKeys(fn ($n) => [$n->id => ['kode' => $n->node_code, 'nama' => $n->node_name, 'desa' => $n->desa]]);

        return view('data-realtime', ['nodes' => $nodes]);
    }

    /** N baris sensor_data terbaru (default 40) untuk halaman realtime; status ikut terbaru (diisi AI). */
    public function getTerbaru(\Illuminate\Http\Request $request)
    {
        $limit = max(5, min(200, (int) $request->query('limit', 40)));

        $rows = SensorLog::without('prediction')->orderByDesc('id')->limit($limit)->get()
            ->map(fn ($r) => [
                'id'        => $r->id,
                'node_id'   => $r->node_id,
                'timestamp' => $r->timestamp,
                'log'       => $r->sensor_log_id,
                'dangkal'   => $r->moisture_percent,
                'dalam'     => $r->moisture_percent_deep,
                'hujan'     => $r->rainfall_mm,
                'ax' => $r->ax, 'ay' => $r->ay, 'az' => $r->az,
                'gx' => $r->gx, 'gy' => $r->gy, 'gz' => $r->gz,
                'pitch'     => $r->pitch,
                'roll'      => $r->roll,
                'status'    => $r->getAttributes()['status'] ?? null,
                'forecast'  => $r->getAttributes()['forecast_status'] ?? null,
            ]);

        return response()->json($rows)->header('Cache-Control', 'no-store');
    }

    public function getSince(\Illuminate\Http\Request $request)
    {
        $lastId = (int) $request->query('last_id', 0);

        $rows = SensorLog::where('id', '>', $lastId)
            ->orderBy('id', 'asc')
            ->get();

        foreach ($rows as $row) { $this->mapFields($row); }

        return response()->json($rows);
    }

    public function getStatusHistory()
    {
        $history = SensorLog::without('prediction')
            ->orderBy('timestamp', 'asc')
            ->get(['id', 'timestamp', 'status'])
            ->map(function ($log) {
                return [
                    'timestamp' => $log->timestamp,
                    'status_ai' => strtoupper($log->status ?? 'UNKNOWN'),
                ];
            });

        return response()->json($history);
    }

    public function getForecastHistory()
    {
        $history = SensorLog::orderBy('timestamp', 'asc')
            // vibration & confidence diambil dari relasi ai_predictions (lihat model SensorLog)
            ->get() // semua kolom: nama kolom kelembapan bisa lama/baru (lihat accessor di SensorLog)
            ->map(function ($log) {
                return [
                    'timestamp'           => $log->timestamp,
                    'status'              => strtoupper($log->status ?? 'UNKNOWN'),
                    'forecast_status'     => strtoupper($log->forecast_status ?? 'UNKNOWN'),
                    'forecast_confidence' => $log->forecast_confidence,
                    'rf_confidence'       => $log->rf_confidence,
                    'lstm_confidence'     => $log->lstm_confidence,
                    'moisture_percent'    => $log->moisture_percent,
                    'vibration'           => $log->vibration,
                    'pitch'               => $log->pitch,
                ];
            });

        return response()->json($history);
    }

    public function getWeather()
    {
        try {
            return response()->json($this->fetchBmkgPacet());
        } catch (\Exception $e) {
            return response()->json([
                'condition' => 'Gagal memuat cuaca',
                'rainfall'  => 0,
                'temp'      => null,
                'humidity'  => null,
                'location'  => self::LOKASI_DEFAULT,
            ]);
        }
    }

    // Kode wilayah BMKG (adm4) lokasi node LARAS: Desa Kemiri, Kec. Pacet, Kab. Mojokerto.
    // Cadangan: Desa Pacet (kecamatan yang sama) bila data Kemiri kosong.
    private const BMKG_ADM4          = '35.16.03.2001';
    private const BMKG_ADM4_CADANGAN = '35.16.03.2005';
    private const LOKASI_DEFAULT     = 'Kemiri, Pacet, Mojokerto';

    private function fetchBmkgPacet(): array
    {
        foreach ([self::BMKG_ADM4, self::BMKG_ADM4_CADANGAN] as $adm4) {
            $res = Http::timeout(8)->get('https://api.bmkg.go.id/publik/prakiraan-cuaca', ['adm4' => $adm4]);
            if (!$res->successful()) continue;

            $wilayah = $res->json('data.0');
            $cuaca   = $this->slotTerdekat($wilayah['cuaca'] ?? []);
            if (!$cuaca) continue;

            $lokasi = $wilayah['lokasi'] ?? [];
            return [
                'condition' => $cuaca['weather_desc'] ?? 'N/A',
                'rainfall'  => $cuaca['tp']  ?? 0,     // mm per slot prakiraan 3 jam
                'temp'      => $cuaca['t']   ?? null,
                'humidity'  => $cuaca['hu']  ?? null,
                'location'  => implode(', ', array_filter([
                    $lokasi['desa'] ?? null, $lokasi['kecamatan'] ?? null, $lokasi['kotkab'] ?? null,
                ])) ?: self::LOKASI_DEFAULT,
                'time'      => $cuaca['local_datetime'] ?? null,
            ];
        }

        return [
            'condition' => 'Tidak tersedia',
            'rainfall'  => 0,
            'temp'      => null,
            'humidity'  => null,
            'location'  => self::LOKASI_DEFAULT,
        ];
    }

    /** Slot prakiraan 3 jam yang paling dekat dengan jam sekarang (WIB), bukan sekadar slot pertama. */
    private function slotTerdekat(array $hari): ?array
    {
        $slots = [];
        foreach ($hari as $slotHarian) {
            if (is_array($slotHarian)) array_push($slots, ...$slotHarian);
        }

        $now     = now('Asia/Jakarta')->timestamp;
        $pilihan = null;
        $selisih = PHP_INT_MAX;
        foreach ($slots as $slot) {
            $t = strtotime(($slot['local_datetime'] ?? '') . ' Asia/Jakarta');
            if ($t !== false && abs($t - $now) < $selisih) {
                [$pilihan, $selisih] = [$slot, abs($t - $now)];
            }
        }
        return $pilihan ?? ($slots[0] ?? null);
    }

    protected function mapFields($model): void
    {
        if (!$model) return;

        $model->soil_moisture = $model->moisture_percent;
        $model->accel_x       = $model->ax;
        $model->accel_y       = $model->ay;
        $model->accel_z       = $model->az;
        $model->gyro_x        = $model->gx;
        $model->gyro_y        = $model->gy;
        $model->gyro_z        = $model->gz;
        $model->status_ai     = strtoupper($model->status ?? 'UNKNOWN');
    }
}