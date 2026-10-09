<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class TelegramAlertService
{
    protected string $botToken;
    protected string $chatId;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token', '');
        $this->chatId   = config('services.telegram.chat_id', '');
    }

    public function send(string $message): bool
    {
        if (empty($this->botToken) || empty($this->chatId)) {
            Log::warning('[ILMAS Telegram] Bot token atau Chat ID belum dikonfigurasi.');
            return false;
        }

        try {
            $response = Http::timeout(10)->post("https://api.telegram.org/bot{$this->botToken}/sendMessage", [
                'chat_id'    => $this->chatId,
                'text'       => $message,
                'parse_mode' => 'HTML',
            ]);

            if ($response->successful()) {
                Log::info('[ILMAS Telegram] Alert berhasil dikirim.');
                return true;
            }

            Log::error('[ILMAS Telegram] Gagal kirim alert. Response: ' . $response->body());
            return false;

        } catch (\Exception $e) {
            Log::error('[ILMAS Telegram] Exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendIfChanged(string $status, array $data): bool
    {
        $status  = strtoupper(trim($status));
        $cacheKey = 'telegram_last_alert_status';

        $lastSentStatus = Cache::get($cacheKey, null);

        $cooldown = match ($status) {
            'BAHAYA'  => 60,   
            'WASPADA' => 180,  
            default   => 600, 
        };

        $cooldownKey = "telegram_cooldown_{$status}";
        $inCooldown  = Cache::has($cooldownKey);

        
        if ($lastSentStatus === $status && $inCooldown) {
            return false; 
        }

        $message = $this->buildMessage($status, $data);
        $sent    = $this->send($message);

        if ($sent) {
            Cache::put($cacheKey, $status, now()->addSeconds($cooldown * 5));
            Cache::put($cooldownKey, true, now()->addSeconds($cooldown));
        }

        return $sent;
    }

    
    protected function buildMessage(string $status, array $data): string
    {
        $tz        = new \DateTimeZone('Asia/Jakarta');
        $now       = (new \DateTime('now', $tz))->format('d/m/Y H:i:s') . ' WIB';
        $timestamp = $data['timestamp'] ?? $now;
        $moisture  = isset($data['moisture_percent']) ? number_format((float)$data['moisture_percent'], 1) . '%' : '-';
        $pitch     = isset($data['pitch'])     ? number_format((float)$data['pitch'], 2) . '°'   : '-';
        $roll      = isset($data['roll'])      ? number_format((float)$data['roll'], 2) . '°'    : '-';
        $vibration = isset($data['vibration']) ? number_format((float)$data['vibration'], 3)     : '-';
        $rfConf    = isset($data['rf_confidence'])   ? number_format((float)$data['rf_confidence'], 1) . '%' : '-';
        $lstmConf  = isset($data['lstm_confidence']) ? number_format((float)$data['lstm_confidence'], 1) . '%' : '-';
        $fcastSt   = isset($data['forecast_status']) ? strtoupper($data['forecast_status']) : '-';
        $fcastConf = isset($data['forecast_confidence']) ? number_format((float)$data['forecast_confidence'], 1) . '%' : '-';

        $weatherLine = '';
        try {
            $res = Http::timeout(5)->get(config('app.url') . '/api/weather-bmkg');
            if ($res->successful()) {
                $w = $res->json();
                $wParts = array_filter([
                    $w['condition'] ?? null,
                    isset($w['temp'])     ? "Suhu {$w['temp']}°C"         : null,
                    isset($w['humidity']) ? "RH {$w['humidity']}%"        : null,
                    (isset($w['rainfall']) && $w['rainfall'] > 0) ? "Hujan {$w['rainfall']} mm/jam" : null,
                ]);
                $weatherLine = implode(' · ', $wParts);
            }
        } catch (\Exception $e) {  }
        $weatherStr  = $weatherLine ?: 'Data cuaca tidak tersedia';
        $weatherNote = $weatherLine && stripos($weatherLine, 'hujan') !== false
            ? "\nCatatan Cuaca          : Curah hujan terdeteksi. Risiko longsor meningkat."
            : '';

        $footer = implode("\n", [
            '',
            '==================================================',
            'Pesan ini dihasilkan secara otomatis oleh Sistem ILMAS.',
            'Informasi ini bersifat peringatan dini dan/atau darurat.',
            'Tindak lanjut wajib mengikuti arahan petugas berwenang.',
        ]);

        return match ($status) {

            'BAHAYA' => implode("\n", [
                '<b>PERINGATAN DARURAT BENCANA TANAH LONGSOR</b>',
                '<b>SISTEM PEMANTAUAN ILMAS</b>',
                '==================================================',
                'STATUS     : BAHAYA',
                'LEVEL      : DARURAT',
                'TINDAKAN   : EVAKUASI SEGERA',
                '',
                'Waktu Pengamatan      : ' . $timestamp,
                'Kelembaban Tanah      : ' . $moisture,
                'Kemiringan (Pitch)    : ' . $pitch,
                'Kemiringan (Roll)     : ' . $roll,
                'Getaran Tanah         : ' . $vibration,
                'Prediksi Sistem       : ' . $fcastSt . ' (' . $fcastConf . ')',
                'Keyakinan Model RF    : ' . $rfConf,
                'Keyakinan Model LSTM  : ' . $lstmConf,
                '',
                'Informasi Cuaca       : ' . $weatherStr . $weatherNote,
                '',
                '<b>INSTRUKSI DARURAT:</b>',
                '1. Segera lakukan evakuasi seluruh warga ke titik aman terdekat.',
                '2. Hentikan seluruh aktivitas di wilayah rawan longsor.',
                '3. Lakukan koordinasi dengan layanan darurat setempat:',
                '   - BPBD',
                '   - Pemadam Kebakaran',
                '   - Nomor Darurat Nasional: 112',
                '4. Patuhi seluruh arahan petugas berwenang di lapangan.',
                '',
                '<b>PERINGATAN:</b>',
                'Potensi longsor berada pada tingkat sangat tinggi dan dapat terjadi sewaktu-waktu.',
                'Keterlambatan evakuasi berisiko menimbulkan korban jiwa.',
            ]) . $footer,

            'WASPADA' => implode("\n", [
                '<b>PERINGATAN DINI POTENSI BENCANA TANAH LONGSOR</b>',
                '<b>SISTEM PEMANTAUAN ILMAS</b>',
                '==================================================',
                'STATUS   : WASPADA',
                'LEVEL    : SIAGA',
                'TINDAKAN : PEMANTAUAN INTENSIF',
                '',
                'Waktu Pengamatan      : ' . $timestamp,
                'Kelembaban Tanah      : ' . $moisture,
                'Kemiringan (Pitch)    : ' . $pitch,
                'Kemiringan (Roll)     : ' . $roll,
                'Getaran Tanah         : ' . $vibration,
                'Prediksi Sistem       : ' . $fcastSt . ' (' . $fcastConf . ')',
                'Keyakinan Model RF    : ' . $rfConf,
                'Keyakinan Model LSTM  : ' . $lstmConf,
                '',
                'Informasi Cuaca       : ' . $weatherStr . $weatherNote,
                '',
                '<b>INSTRUKSI KEWASPADAAN:</b>',
                '1. Tingkatkan kewaspadaan di seluruh wilayah rawan longsor.',
                '2. Siapkan jalur dan rencana evakuasi secara dini.',
                '3. Koordinasikan kesiapsiagaan dengan BPBD atau perangkat desa.',
                '4. Pantau perkembangan informasi dan kondisi lapangan secara berkala.',
                '',
                '<b>CATATAN:</b>',
                'Perubahan kondisi lingkungan dapat meningkatkan status menjadi DARURAT.',
            ]) . $footer,

            default => implode("\n", [
                '<b>INFORMASI STATUS KEAMANAN LINGKUNGAN</b>',
                '<b>SISTEM PEMANTAUAN ILMAS</b>',
                '==================================================',
                'STATUS     : AMAN',
                'LEVEL      : NORMAL',
                'TINDAKAN   : PEMANTAUAN RUTIN',
                '',
                'Waktu Pengamatan      : ' . $timestamp,
                'Kelembaban Tanah      : ' . $moisture,
                'Kemiringan (Pitch)    : ' . $pitch,
                'Kemiringan (Roll)     : ' . $roll,
                'Getaran Tanah         : ' . $vibration,
                'Prediksi Sistem       : ' . $fcastSt . ' (' . $fcastConf . ')',
                '',
                'Informasi Cuaca       : ' . $weatherStr,
                '',
                '<b>KETERANGAN:</b>',
                'Tidak terdeteksi indikasi potensi longsor.',
                'Sistem beroperasi normal dan melakukan pemantauan rutin.',
            ]) . $footer,
        };
    }
}