<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\SensorLog;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $userMessage = $request->input('message');
        if (!$userMessage) {
            return response()->json(['reply' => 'Pesan kosong.']);
        }

        $row = SensorLog::orderBy('timestamp', 'desc')->first();

        $moisture  = $row ? round($row->moisture_percent ?? 0, 1) : 0;
        $ax        = $row ? round($row->ax ?? 0, 3) : 0;
        $ay        = $row ? round($row->ay ?? 0, 3) : 0;
        $az        = $row ? round($row->az ?? 0, 3) : 0;
        $pitch     = $row ? round($row->pitch ?? 0, 1) : 0;
        $roll      = $row ? round($row->roll ?? 0, 1) : 0;
        $vibration = $row ? round($row->vibration ?? 0, 3) : 0;
        $status    = $row ? strtoupper($row->status ?? 'UNKNOWN') : 'UNKNOWN';
        $timestamp = $row ? ($row->timestamp ?? '-') : '-';
        $rfConf    = $row && $row->rf_confidence   !== null ? round($row->rf_confidence, 1)   : null;
        $lstmConf  = $row && $row->lstm_confidence !== null ? round($row->lstm_confidence, 1) : null;
        $fcastSt   = $row ? strtoupper($row->forecast_status ?? 'UNKNOWN') : 'UNKNOWN';
        $fcastConf = $row && $row->forecast_confidence !== null ? round($row->forecast_confidence, 1) : null;

        $recentLogs = SensorLog::orderBy('timestamp', 'desc')->limit(10)->get();
        $trendLines = [];
        foreach ($recentLogs as $i => $log) {
            $trendLines[] = "  [" . ($i + 1) . "] {$log->timestamp}"
                . "  moisture=" . round($log->moisture_percent ?? 0, 1) . " persen"
                . "  vib=" . round($log->vibration ?? 0, 3)
                . "  pitch=" . round($log->pitch ?? 0, 1) . " derajat"
                . "  status=" . strtoupper($log->status ?? 'UNKNOWN')
                . ($log->forecast_status ? "  forecast=" . strtoupper($log->forecast_status) : '');
        }
        $trendText = implode("\n", $trendLines);

        $moistureValues = $recentLogs->pluck('moisture_percent')->map(fn($v) => (float)($v ?? 0))->toArray();
        $moistureTrend  = 'stabil';
        if (count($moistureValues) >= 2) {
            $delta = $moistureValues[0] - end($moistureValues);
            if ($delta >  2)  $moistureTrend = 'menurun';
            if ($delta < -2)  $moistureTrend = 'meningkat';
        }

        $minuteEstimate = null;
        if (count($moistureValues) >= 3) {
            $n    = count($moistureValues);
            $avgX = ($n - 1) / 2;
            $avgY = array_sum($moistureValues) / $n;
            $num  = 0;
            $den  = 0;
            foreach ($moistureValues as $xi => $yi) {
                $num += ($xi - $avgX) * ($yi - $avgY);
                $den += ($xi - $avgX) ** 2;
            }
            $slope = $den != 0 ? $num / $den : 0;

            $timestamps = $recentLogs->pluck('timestamp')->toArray();
            $intervals  = [];
            for ($i = 0; $i < count($timestamps) - 1; $i++) {
                $diff = abs(strtotime($timestamps[$i]) - strtotime($timestamps[$i + 1]));
                if ($diff > 0) $intervals[] = $diff;
            }
            $avgIntervalSec  = count($intervals) ? array_sum($intervals) / count($intervals) : 60;
            $currentMoisture = $moistureValues[0];

            if ($slope > 0.01 && $currentMoisture < 80) {
                $stepsNeeded = ($currentMoisture > 0) ? (80 - $currentMoisture) / $slope : null;
                if ($stepsNeeded !== null && $stepsNeeded > 0) {
                    $minuteEst      = round($stepsNeeded * $avgIntervalSec / 60);
                    $minuteEstimate = "Jika tren berlanjut, moisture dapat mencapai ambang BAHAYA sekitar 80 persen dalam sekitar {$minuteEst} menit.";
                }
            } elseif ($slope < -0.01 && $currentMoisture > 50) {
                $stepsNeeded = ($currentMoisture > 0) ? ($currentMoisture - 50) / abs($slope) : null;
                if ($stepsNeeded !== null && $stepsNeeded > 0) {
                    $minuteEst      = round($stepsNeeded * $avgIntervalSec / 60);
                    $minuteEstimate = "Jika tren penurunan berlanjut, moisture dapat kembali ke zona aman sekitar 50 persen dalam sekitar {$minuteEst} menit.";
                }
            }
        }

        $weatherText = 'Data cuaca tidak tersedia.';
        try {
            $res = Http::timeout(5)->get(url('/api/weather-bmkg'));
            if ($res->successful()) {
                $w           = $res->json();
                $weatherText = "Kondisi cuaca saat ini di {$w['location']}: {$w['condition']}."
                    . ($w['temp']     ? " Suhu udara: {$w['temp']} derajat Celsius." : '')
                    . ($w['humidity'] ? " Kelembaban udara: {$w['humidity']} persen." : '')
                    . (isset($w['rainfall']) && $w['rainfall'] > 0 ? " Curah hujan: {$w['rainfall']} mm per jam." : '');
            }
        } catch (\Exception $e) { }

        $statusDesc = match ($status) {
            'BAHAYA'  => 'RISIKO TINGGI, diperlukan tindakan evakuasi segera',
            'WASPADA' => 'SIAGA, diperlukan pemantauan intensif dan kesiapsiagaan',
            'AMAN'    => 'KONDISI NORMAL, tidak terdeteksi ancaman signifikan',
            default   => 'STATUS TIDAK DIKETAHUI, data mungkin belum masuk ke sistem',
        };

        $rfStr    = $rfConf    !== null ? "{$rfConf} persen"    : 'N/A';
        $lstmStr  = $lstmConf  !== null ? "{$lstmConf} persen"  : 'N/A';
        $fconfStr = $fcastConf !== null ? "{$fcastConf} persen" : 'N/A';

        $systemPrompt  = "Kamu adalah ILMAS AI Assistant, sistem kecerdasan buatan peringatan dini bencana tanah longsor.\n";
        $systemPrompt .= "Berikan jawaban yang profesional, terstruktur, dan mudah dipahami oleh petugas lapangan maupun masyarakat umum.\n\n";

        $systemPrompt .= "DATA SENSOR REAL-TIME (timestamp: {$timestamp})\n";
        $systemPrompt .= "Kelembaban Tanah: {$moisture} persen (tren: {$moistureTrend})\n";
        $systemPrompt .= "Akselerometer: X={$ax}  Y={$ay}  Z={$az} m/s2\n";
        $systemPrompt .= "Kemiringan: Pitch={$pitch} derajat  Roll={$roll} derajat\n";
        $systemPrompt .= "Getaran Tanah: {$vibration}\n";
        $systemPrompt .= "Status Sistem: {$status}, {$statusDesc}\n";
        $systemPrompt .= "Forecast Status: {$fcastSt} (konfidensi: {$fconfStr})\n";
        $systemPrompt .= "Keyakinan Model RF: {$rfStr}\n";
        $systemPrompt .= "Keyakinan Model LSTM: {$lstmStr}\n";

        if ($minuteEstimate) {
            $systemPrompt .= "\nESTIMASI WAKTU: {$minuteEstimate}\n";
        }

        $systemPrompt .= "\nDATA CUACA BMKG (Surabaya, Jawa Timur)\n";
        $systemPrompt .= "{$weatherText}\n";
        $systemPrompt .= "Catatan: Hujan deras meningkatkan risiko longsor secara signifikan.\n";

        $systemPrompt .= "\nHISTORI TREN 10 DATA TERAKHIR\n";
        $systemPrompt .= "{$trendText}\n";

        $systemPrompt .= "\nATURAN RESPONS\n";
        $systemPrompt .= "1. Gunakan Bahasa Indonesia yang profesional, jelas, dan lugas.\n";
        $systemPrompt .= "2. Jika ditanya kondisi saat ini, gunakan DATA SENSOR REAL-TIME.\n";
        $systemPrompt .= "3. Jika ditanya prediksi atau berapa menit, gunakan ESTIMASI WAKTU dan analisis tren. Sebutkan estimasi dalam satuan menit secara eksplisit.\n";
        $systemPrompt .= "4. Jika ditanya cuaca, integrasikan DATA CUACA BMKG dengan kondisi sensor.\n";
        $systemPrompt .= "5. Jangan mengarang angka. Hanya gunakan data yang tersedia di atas.\n";
        $systemPrompt .= "6. Jawab langsung ke inti pertanyaan. Boleh gunakan poin-poin bernomor jika diperlukan, setiap poin di baris baru.\n";
        $systemPrompt .= "7. Batas jawaban: maksimal 5 kalimat atau 5 poin singkat.\n";
        $systemPrompt .= "8. Jika status BAHAYA, sertakan rekomendasi tindakan darurat di akhir jawaban.\n";
        $systemPrompt .= "9. PENTING: Jangan gunakan simbol markdown seperti **, *, atau #. Jangan gunakan tanda hubung panjang atau panah. Tulis teks biasa saja.\n";
        $systemPrompt .= "10. Jika menjawab dengan poin bernomor, tulis setiap nomor di awal baris baru. Contoh format:\nKondisi saat ini aman.\n1. Poin pertama.\n2. Poin kedua.\n3. Poin ketiga.\n\n";
        $systemPrompt .= "PERTANYAAN PENGGUNA: {$userMessage}";

        try {
            $apiKey = config('services.gemini.api_key');
            if (!$apiKey) {
                return response()->json(['reply' => 'Error: GEMINI_API_KEY belum diisi di file .env']);
            }

            $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key={$apiKey}";

            $response = Http::timeout(20)->post($url, [
                'contents' => [[
                    'parts' => [['text' => $systemPrompt]]
                ]],
                'generationConfig' => [
                    'temperature'     => 0.25,
                    'maxOutputTokens' => 1024,
                ]
            ]);

            if ($response->successful()) {
                $data         = $response->json();
                $candidate    = $data['candidates'][0] ?? null;
                $finishReason = $candidate['finishReason'] ?? 'UNKNOWN';
                $aiReply      = $candidate['content']['parts'][0]['text'] ?? '';

                if ($finishReason === 'MAX_TOKENS' || trim($aiReply) === '') {
                    $aiReply = '[Jawaban terpotong] ' . $aiReply;
                }

                $aiReply = preg_replace('/\*\*(.*?)\*\*/s', '$1', $aiReply);
                $aiReply = preg_replace('/\*(.*?)\*/s', '$1', $aiReply);
                $aiReply = preg_replace('/#{1,6}\s+/', '', $aiReply);
                $aiReply = str_replace(['—', '–', '->', '=>', '→'], ',', $aiReply);

                $aiReply = preg_replace('/\s+(\d+)\.\s+/', "\n$1. ", $aiReply);
                $aiReply = ltrim($aiReply);

                return response()->json(['reply' => trim($aiReply)]);
            } else {
                $errorData = $response->json();
                $errorMsg  = $errorData['error']['message'] ?? ('HTTP ' . $response->status());
                return response()->json(['reply' => 'Gemini Error: ' . $errorMsg]);
            }

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json(['reply' => 'Timeout: tidak bisa terhubung ke Gemini. Periksa koneksi server.']);
        } catch (\Exception $e) {
            return response()->json(['reply' => 'Exception: ' . $e->getMessage()]);
        }
    }
}