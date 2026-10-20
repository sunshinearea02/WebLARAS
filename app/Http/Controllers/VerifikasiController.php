<?php

namespace App\Http\Controllers;

use App\Models\AlertVerification;
use App\Models\SensorNode;
use App\Models\WargaContact;
use App\Services\WhatsAppAlertService;
use Illuminate\Http\Request;

/** Halaman kader: verifikasi peringatan per desa + kelola nomor WA warga per desa. */
class VerifikasiController extends Controller
{
    public function index(Request $request, WhatsAppAlertService $wa)
    {
        $wa->tick(); // BAHAYA yang lewat batas waktu langsung diproses

        $pending = AlertVerification::where('state', AlertVerification::PENDING)
            ->orderByRaw("CASE WHEN level = 'BAHAYA' THEN 0 ELSE 1 END")->latest('id')->get();
        $history = AlertVerification::with('decider')->where('state', '!=', AlertVerification::PENDING)
            ->latest('id')->limit(30)->get();

        $desaList = SensorNode::query()->pluck('desa')->filter()->map(fn ($d) => trim($d))->unique()->sort()->values();
        $contacts = WargaContact::orderBy('desa')->orderBy('name')->get()->groupBy(fn ($c) => $c->desa ?? '');

        // Sesuaikan dengan lokasi file blade:
        //   resources/views/verifikasi.blade.php        -> view('verifikasi', ...)
        //   resources/views/verifikasi/index.blade.php  -> view('verifikasi.index', ...)
        return view('verifikasi', [
            'pending'        => $pending,
            'history'        => $history,
            'highlight'      => (int) $request->query('v'),
            'autoMinutes'    => $wa->autoSendMinutes(),
            'waspadaMinutes' => $wa->waspadaExpireMinutes(),
            'kaderCount'     => count($wa->kaderContacts()),
            'globalCount'    => count($wa->targets()),
            'desaList'       => $desaList,
            'contacts'       => $contacts,
            'wa'             => $wa,
        ]);
    }

    /** Tombol "Setujui & kirim ke warga": kirim WA ke semua warga desa itu. */
    public function approve(Request $request, AlertVerification $verification, WhatsAppAlertService $wa)
    {
        $res = $wa->approve($verification, $request->user());
        return back()->with($res['ok'] ? 'success' : 'error', $res['message']);
    }

    /** Tombol "Tolak": alarm palsu, tidak dikirim ke warga. */
    public function reject(Request $request, AlertVerification $verification, WhatsAppAlertService $wa)
    {
        $data = $request->validate(['note' => ['nullable', 'string', 'max:300']]);
        $res = $wa->reject($verification, $request->user(), $data['note'] ?? null);
        return back()->with($res['ok'] ? 'success' : 'error', $res['message']);
    }

    /** Tambah satu atau banyak nomor warga sekaligus (pisahkan koma / baris baru). */
    public function storeWarga(Request $request)
    {
        $data = $request->validate([
            'desa'   => ['nullable', 'string', 'max:100'],
            'name'   => ['nullable', 'string', 'max:100'],
            'phones' => ['required', 'string', 'max:5000'],
        ]);
        $desa = trim((string) ($data['desa'] ?? '')) ?: null;
        $phones = WhatsAppAlertService::parseTargets($data['phones']);
        if (!$phones) {
            return back()->with('error', 'Nomor tidak valid. Contoh: 081234567890 atau 1203630xxxx@g.us');
        }
        foreach ($phones as $p) {
            WargaContact::updateOrCreate(['desa' => $desa, 'phone' => $p],
                ['name' => count($phones) === 1 ? ($data['name'] ?? null) : null, 'active' => true]);
        }
        return back()->with('success', count($phones) . ' nomor ditambahkan untuk ' . ($desa ? "Desa {$desa}" : 'semua desa') . '.');
    }

    public function destroyWarga(WargaContact $contact)
    {
        $contact->delete();
        return back()->with('success', "Nomor {$contact->phone} dihapus.");
    }
}