<?php

namespace App\Http\Controllers;

use App\Models\SensorLog;
use App\Models\TelegramAlertLog;
use App\Services\TelegramAlertService;
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
            'location'  => 'Surabaya, Jawa Timur',
        ];

        try {
            $weatherInfo = $this->fetchBmkgSurabaya();
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

        $status = strtoupper(trim($current->status ?? $current->status_ai ?? 'UNKNOWN'));

        if (in_array($status, ['WASPADA', 'BAHAYA'])) {
            $telegram = new TelegramAlertService();
            $data     = $current->toArray();
            $sent     = $telegram->sendIfChanged($status, $data);

            // null = tidak dikirim karena masih cooldown / status sama.
            // Hanya catat log saat benar-benar ada percobaan kirim, supaya tabel
            // telegram_alert_logs tidak bertambah di setiap polling browser.
            if ($sent !== null) TelegramAlertLog::create([
                'status'           => $status,
                'sensor_log_id'    => $current->id,
                'moisture_percent' => $current->moisture_percent,
                'pitch'            => $current->pitch,
                'roll'             => $current->roll,
                'vibration'        => $current->vibration ?? null,
                'rf_confidence'    => $current->rf_confidence ?? null,
                'lstm_confidence'  => $current->lstm_confidence ?? null,
                'sent'             => $sent,
                'sensor_timestamp' => $current->timestamp,
            ]);
        }

        // WhatsApp (GoWA): cadangan bila scheduler belum jalan. Dibatasi 1x per 15 detik.
        try {
            app(WhatsAppAlertService::class)->evaluateThrottled();
        } catch (\Throwable $e) {
            report($e); // notifikasi gagal tidak boleh membuat dashboard error
        }

        return response()->json($current);
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
            ->get(['id', 'timestamp', 'status', 'forecast_status', 'moisture_percent', 'pitch'])
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
            return response()->json($this->fetchBmkgSurabaya());
        } catch (\Exception $e) {
            return response()->json([
                'condition' => 'Gagal memuat cuaca',
                'rainfall'  => 0,
                'temp'      => null,
                'humidity'  => null,
                'location'  => 'Surabaya, Jawa Timur',
            ]);
        }
    }

    private function fetchBmkgSurabaya(): array
    {
        $default = [
            'condition' => 'Tidak tersedia',
            'rainfall'  => 0,
            'temp'      => null,
            'humidity'  => null,
            'location'  => 'Surabaya, Jawa Timur',
        ];

        $res = Http::timeout(8)->get('https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=35.78.04.1001');
        if ($res->successful()) {
            $data = $res->json('data', []);
            if (!empty($data)) {
                $wilayah = $data[0];
                $cuaca   = $wilayah['cuaca'][0][0] ?? null;
                if ($cuaca) {
                    return [
                        'condition' => $cuaca['weather_desc'] ?? 'N/A',
                        'rainfall'  => $cuaca['tp']  ?? 0,
                        'temp'      => $cuaca['t']   ?? null,
                        'humidity'  => $cuaca['hu']  ?? null,
                        'location'  => ($wilayah['lokasi']['kecamatan'] ?? '')
                                     . ', ' . ($wilayah['lokasi']['kotkab'] ?? 'Surabaya'),
                    ];
                }
            }
        }

        $res2 = Http::timeout(8)->get('https://api.bmkg.go.id/publik/prakiraan-cuaca?adm1=35');
        if ($res2->successful()) {
            foreach ($res2->json('data', []) as $wilayah) {
                $kotkab = $wilayah['lokasi']['kotkab'] ?? '';
                if (stripos($kotkab, 'Surabaya') !== false) {
                    $cuaca = $wilayah['cuaca'][0][0] ?? null;
                    if ($cuaca) {
                        return [
                            'condition' => $cuaca['weather_desc'] ?? 'N/A',
                            'rainfall'  => $cuaca['tp']  ?? 0,
                            'temp'      => $cuaca['t']   ?? null,
                            'humidity'  => $cuaca['hu']  ?? null,
                            'location'  => ($wilayah['lokasi']['kecamatan'] ?? '')
                                         . ', ' . $kotkab,
                        ];
                    }
                    break;
                }
            }
        }

        return $default;
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