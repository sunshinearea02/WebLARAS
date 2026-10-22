<?php

namespace App\Http\Controllers;

use App\Models\HazardReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

/**
 * Laporan hambatan jalan di Peta Evakuasi.
 * - Semua orang (warga tanpa login) boleh melihat & mengirim laporan.
 * - Laporan warga berstatus "menunggu" sampai diverifikasi kader/admin.
 * - Kader/admin: laporan langsung terverifikasi, bisa memverifikasi & menghapus laporan.
 */
class HazardReportController extends Controller
{
    public function index()
    {
        return response()->json(HazardReport::untukPeta());
    }

    public function store(Request $request)
    {
        // Titik harus di sekitar wilayah pantau (mencegah laporan iseng di luar wilayah)
        [$clat, $clng] = config('laras.center');

        $data = $request->validate([
            'type'          => ['required', 'string', Rule::in(HazardReport::JENIS)],
            'lat'           => ['required', 'numeric', 'between:' . ($clat - 0.2) . ',' . ($clat + 0.2)],
            'lng'           => ['required', 'numeric', 'between:' . ($clng - 0.2) . ',' . ($clng + 0.2)],
            'desc'          => ['nullable', 'string', 'max:300'],
            'reporter_name' => ['nullable', 'string', 'max:60'],
        ], [
            'lat.between' => 'Titik laporan berada di luar wilayah pantau LARAS.',
            'lng.between' => 'Titik laporan berada di luar wilayah pantau LARAS.',
        ]);

        $petugas = $request->user() && Gate::forUser($request->user())->allows('kelola-data');

        $report = HazardReport::create([
            'type'          => $data['type'],
            'latitude'      => $data['lat'],
            'longitude'     => $data['lng'],
            'description'   => $data['desc'] ?? null,
            'reporter_name' => $petugas ? $request->user()->name : ($data['reporter_name'] ?? null),
            'source'        => $petugas ? 'petugas' : 'warga',
            'status'        => $petugas ? 'terverifikasi' : 'menunggu',
            'user_id'       => $petugas ? $request->user()->id : null,
            'verified_by'   => $petugas ? $request->user()->id : null,
            'verified_at'   => $petugas ? now() : null,
        ]);

        return response()->json([
            'message' => $petugas
                ? 'Laporan tersimpan dan langsung tampil di peta.'
                : 'Terima kasih. Laporan tampil di peta dengan label "belum diverifikasi" sampai dicek kader.',
            'report'  => $report->toMap(),
        ], 201);
    }

    public function verify(Request $request, HazardReport $report)
    {
        $report->update(['status' => 'terverifikasi', 'verified_by' => $request->user()->id, 'verified_at' => now()]);

        return response()->json(['message' => 'Laporan diverifikasi.', 'report' => $report->toMap()]);
    }

    public function destroy(HazardReport $report)
    {
        $report->delete();

        return response()->json(['message' => 'Laporan dihapus dari peta.']);
    }
}
