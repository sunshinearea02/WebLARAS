<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCheckin;
use App\Models\SensorLog;
use App\Models\SensorNode;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EvacuationMapController extends Controller
{
    // Daftar Pilihan Posko Aman di Pacet
    public function getDaftarPosko(): array
    {
        return [
            [
                'id' => 'Posko Cepokolimo',
                'nama' => 'Posko Utama Cepokolimo',
                'lokasi' => 'Dusun Cepokolimo, Desa Cepokolimo, Pacet',
                'lat' => -7.65200,
                'lng' => 112.52900,
            ],
            [
                'id' => 'Posko Kembangbelor',
                'nama' => 'Posko Lapangan Kembangbelor',
                'lokasi' => 'Jl. Raya Kembangbelor No. 12, Pacet',
                'lat' => -7.64400,
                'lng' => 112.54700,
            ],
            [
                'id' => 'Posko Kemiri',
                'nama' => 'Posko Balai Desa Kemiri',
                'lokasi' => 'Kawasan Pemukiman Desa Kemiri, Pacet',
                'lat' => -7.66000,
                'lng' => 112.55200,
            ],
        ];
    }

    public function index(Request $request)
    {
        $nodes = $this->nodesForMap();

        $daftarPosko = $this->getDaftarPosko();

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

        return view('peta-evakuasi', compact('nodes', 'checkins', 'daftarPosko'));
    }

    /**
     * Node sensor dari database + data terakhir tiap node (status risiko & baterai).
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

            // Baris terbaru yang memuat data baterai (bisa lebih lama dari baris terbaru)
            $battery = SensorLog::without('prediction')
                ->whereIn('id', SensorLog::without('prediction')->selectRaw('MAX(id)')->whereNotNull('battery_percent')->groupBy('node_id')->toBase())
                ->get()
                ->keyBy('node_id');
        } catch (\Throwable $e) {
            return [];
        }

        return $nodes->map(function (SensorNode $n) use ($latest, $battery) {
            $last = $latest->get($n->id);
            $bat  = $battery->get($n->id);

            return (object) [
                'id'          => $n->id,
                'node_code'   => $n->node_code,
                'node_name'   => $n->node_name,
                'desa'        => $n->desa,
                'notes'       => $n->notes,
                'lat'         => $n->latitude,
                'lng'         => $n->longitude,
                // UNKNOWN = node belum pernah kirim data; PENDING = data ada tapi belum dianalisis AI
                'status'      => $status = ! $last ? 'UNKNOWN'
                    : (in_array(strtoupper((string) $last->status), ['', 'UNKNOWN'], true) ? 'PENDING' : strtoupper($last->status)),
                'status_label'=> match ($status) {
                    'UNKNOWN' => 'BELUM ADA DATA',
                    'PENDING' => 'MENUNGGU ANALISIS',
                    default   => $status,
                },
                'battery'     => $bat?->battery_percent,
                'voltage'     => $bat?->battery_voltage,
                'battery_at'  => $bat?->timestamp ? \Illuminate\Support\Carbon::parse($bat->timestamp)->locale('id')->diffForHumans() : null,
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