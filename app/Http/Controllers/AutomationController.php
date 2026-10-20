<?php

namespace App\Http\Controllers;

use App\Models\AlertVerification;
use App\Services\WhatsAppAlertService;
use Illuminate\Http\Request;

/**
 * Endpoint untuk otomasi n8n.
 *
 * n8n memanggil POST /api/n8n/alert-tick tiap 15 detik (menggantikan `php artisan schedule:work`).
 * Isinya sama dengan `php artisan laras:alert-tick`:
 *  - baca status terbaru semua node per desa
 *  - WASPADA/BAHAYA -> WA verifikasi ke semua kader
 *  - BAHAYA tanpa keputusan 10 menit -> otomatis ke warga
 * Pengiriman ke warga setelah kader klik "Setujui" tetap terjadi langsung dari web.
 *
 * Keamanan: header X-LARAS-Token harus sama dengan N8N_TOKEN di .env.
 */
class AutomationController extends Controller
{
    public function alertTick(Request $request, WhatsAppAlertService $wa)
    {
        $token = (string) config('services.n8n.token');
        if ($token === '' || !hash_equals($token, (string) $request->header('X-LARAS-Token'))) {
            return response()->json(['ok' => false, 'error' => 'token tidak valid'], 401);
        }

        $hasil = $wa->evaluate();

        $pending = AlertVerification::where('state', AlertVerification::PENDING)->latest('id')->get()
            ->map(fn ($v) => [
                'id'          => $v->id,
                'desa'        => $v->desa,
                'level'       => $v->level,
                'sejak'       => $v->created_at?->toIso8601String(),
                'url'         => rtrim((string) config('app.url'), '/') . '/verifikasi-peringatan?v=' . $v->id,
            ])->values();

        $ringkas = collect($hasil)->map(fn ($r, $desa) => "{$desa}: {$r}")->values();

        return response()->json([
            'ok'          => true,
            'waktu'       => now('Asia/Jakarta')->format('Y-m-d H:i:s'),
            'desa'        => $hasil,
            'ringkasan'   => $ringkas,
            // true bila ada kiriman WA baru di putaran ini (verifikasi baru, pengingat, kabar aman)
            'ada_kiriman' => $ringkas->contains(fn ($s) => preg_match('/dikirim|pengingat|kabar aman|langsung ke/', $s)),
            'ada_gagal'   => $ringkas->contains(fn ($s) => str_contains($s, 'GAGAL')),
            'pending'     => $pending,
        ]);
    }
}