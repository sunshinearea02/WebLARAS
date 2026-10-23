<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCheckin;
use App\Models\SensorLog;
use App\Models\SensorNode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvacuationMapController extends Controller
{
    /** Posko evakuasi desa yang dipantau (config/laras.php). */
    public function getDaftarPosko(): array
    {
        return config('laras.posko', []);
    }

    public function index(Request $request)
    {
        $desa  = (string) config('laras.desa');
        // Semua node di wilayah kecamatan (lintas desa) ditampilkan di peta
        $nodes = $this->nodesForMap();

        $daftarPosko = $this->getDaftarPosko();

        $ringkasan = ['BAHAYA' => 0, 'WASPADA' => 0, 'AMAN' => 0, 'LAINNYA' => 0];
        foreach ($nodes as $n) {
            $ringkasan[isset($ringkasan[$n->status]) ? $n->status : 'LAINNYA']++;
        }

        try {
            $query = EvacuationCheckin::query();

            // Filter berdasarkan posko jika ada request filter
            if ($request->has('filter_posko') && $request->filter_posko != 'SEMUA') {
                $query->where('posko_id', $request->filter_posko);
            }

            $checkins = $query->latest()->get();
        } catch (\Exception $e) {
            $checkins = collect();
        }

        $wilayah = [
            'desa'      => $desa,
            'daftarDesa'=> collect($nodes)->where('status', '!=', 'UNKNOWN')->pluck('desa')->filter()->unique()->sort()->values()->all(),
            'kecamatan' => config('laras.kecamatan'),
            'kabupaten' => config('laras.kabupaten'),
            'center'    => config('laras.center'),
            'zoom'      => config('laras.zoom'),
            'stasiun'   => config('laras.stasiun'),
        ];

        // Menentukan hak akses kelola (ubah sesuai kebutuhan otorisasi aplikasi Anda)
        $canManage = auth()->check();

        // Laporan hambatan jalan (warga & petugas); kader/admin bisa memverifikasi & menghapus
        $laporanJalan = \App\Models\HazardReport::untukPeta();
        $canVerify    = \Illuminate\Support\Facades\Gate::allows('kelola-data');

        return view('peta-evakuasi', compact('nodes', 'checkins', 'daftarPosko', 'ringkasan', 'wilayah', 'canManage', 'laporanJalan', 'canVerify'));
    }

    /**
     * Node sensor dari database + data terakhir tiap node (status risiko, waktu & nilai sensor).
     */
    public function nodesForMap(): array
    {
        try {
            $nodes = SensorNode::orderBy('node_code')->get();

            // Baris sensor_data terbaru per node (satu query)
            $latest = SensorLog::without('prediction')
                ->whereIn('id', SensorLog::without('prediction')->selectRaw('MAX(id)')->groupBy('node_id')->toBase())
                ->get()
                ->keyBy('node_id');
        } catch (\Throwable $e) {
            return [];
        }

        return $nodes->map(function (SensorNode $n) use ($latest) {
            $last = $latest->get($n->id);
            $tilt = ($last && ($last->pitch !== null || $last->roll !== null))
                ? round(max(abs((float) $last->pitch), abs((float) $last->roll)), 1) : null;

            return (object) [
                'id'            => $n->id,
                'node_code'     => $n->node_code,
                'node_name'     => $n->node_name,
                'desa'          => $n->desa,
                'notes'         => $n->notes,
                'lat'           => $n->latitude,
                'lng'           => $n->longitude,
                // UNKNOWN = node belum pernah kirim data; PENDING = data ada tapi belum dianalisis AI
                'status'      => $status = ! $last ? 'UNKNOWN'
                    : (in_array(strtoupper((string) $last->status), ['', 'UNKNOWN'], true) ? 'PENDING' : strtoupper($last->status)),
                'status_label'=> match ($status) {
                    'UNKNOWN' => 'BELUM ADA DATA',
                    'PENDING' => 'MENUNGGU ANALISIS',
                    default   => $status,
                },
                'last_at'     => $last?->timestamp ? \Illuminate\Support\Carbon::parse($last->timestamp)->locale('id')->diffForHumans() : null,
                // accessor SensorLog: membaca kelembapan_dangkal/_dalam (nama kolom baru) maupun nama lama
                'moisture'      => $last?->moisture_percent !== null ? round($last->moisture_percent, 1) : null,
                'moisture_deep' => $last?->moisture_percent_deep !== null ? round($last->moisture_percent_deep, 1) : null,
                'rain'          => $last?->rainfall_mm !== null ? round((float) $last->rainfall_mm, 1) : null,
                'tilt'          => $tilt,
            ];
        })->all();
    }

    public function storeCheckin(Request $request)
    {
        // Hanya Admin / Kader (dijaga middleware role:admin,kader di routes/web.php)
        $daftarPosko = collect($this->getDaftarPosko())->keyBy('id');

        $validated = $request->validate([
            'posko_id'     => ['required', 'string', Rule::in($daftarPosko->keys()->all())],
            'nama_lengkap' => 'required|string|max:255',
            'asal'         => 'required|string|max:255',
            'usia'         => 'required|integer|min:0|max:120',
            'kondisi'      => 'required|in:Sehat,Luka Ringan,Luka Berat,Lansia / Rentan',
        ]);

        // Lokasi posko diambil dari server, bukan dari input browser
        $validated['lokasi_posko'] = $daftarPosko[$validated['posko_id']]['lokasi'];

        $checkin = EvacuationCheckin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data warga berhasil di-checkin di ' . $checkin->posko_id,
            'data'    => $checkin
        ]);
    }

    public function destroyCheckin($id)
    {
        // Hanya Admin / Kader (dijaga middleware role:admin,kader di routes/web.php)
        EvacuationCheckin::findOrFail($id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data check-in berhasil dihapus.',
        ]);
    }

    public function getCheckins(Request $request)
    {
        try {
            $query = EvacuationCheckin::query();

            if ($request->has('posko_id') && $request->posko_id != 'SEMUA') {
                $query->where('posko_id', $request->posko_id);
            }

            return response()->json($query->latest()->get());
        } catch (\Exception $e) {
            return response()->json([]);
        }
    }
}