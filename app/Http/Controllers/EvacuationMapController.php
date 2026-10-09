<?php

namespace App\Http\Controllers;

use App\Models\EvacuationCheckin;
use Illuminate\Http\Request;

class EvacuationMapController extends Controller
{
    // Daftar Pilihan Posko Aman di Pacet
    private function getDaftarPosko()
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
        $nodes = [
            (object)[
                'id' => 'Node-01',
                'node_name' => 'Node-01',
                'desa' => 'Padusan',
                'lat' => -7.69100,
                'lng' => 112.54100,
                'status' => 'BAHAYA',
                'notes' => 'Lereng curam dekat permukiman, riwayat longsor & retakan tanah terpantau'
            ],
            (object)[
                'id' => 'Node-02',
                'node_name' => 'Node-02',
                'desa' => 'Sajen',
                'lat' => -7.67500,
                'lng' => 112.53500,
                'status' => 'WASPADA',
                'notes' => 'Perbukitan dengan kelembapan tinggi, titik kumpul evakuasi sementara'
            ],
            (object)[
                'id' => 'Node-03',
                'node_name' => 'Node-03',
                'desa' => 'Claket',
                'lat' => -7.66200,
                'lng' => 112.54000,
                'status' => 'WASPADA',
                'notes' => 'Kelembapan tanah meningkat, titik temu persimpangan jalur atas'
            ],
            (object)[
                'id' => 'Node-04',
                'node_name' => 'Node-04',
                'desa' => 'Cepokolimo',
                'lat' => -7.65200,
                'lng' => 112.52900,
                'status' => 'AMAN',
                'notes' => 'Dataran lebih landai, aman untuk titik kumpul & check-in warga'
            ],
            (object)[
                'id' => 'Node-05',
                'node_name' => 'Node-05',
                'desa' => 'Kembangbelor',
                'lat' => -7.64400,
                'lng' => 112.54700,
                'status' => 'AMAN',
                'notes' => 'Titik kumpul evakuasi utama, fasilitas kesehatan & dapur umum'
            ],
            (object)[
                'id' => 'Node-06',
                'node_name' => 'Node-06',
                'desa' => 'Kemiri',
                'lat' => -7.66000,
                'lng' => 112.55200,
                'status' => 'AMAN',
                'notes' => 'Zona permukiman padat, jalur evakuasi utama ke jalan raya'
            ]
        ];

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

    public function storeCheckin(Request $request)
    {
        $validated = $request->validate([
            'posko_id'     => 'required|string',
            'lokasi_posko' => 'required|string',
            'nama_lengkap' => 'required|string|max:255',
            'asal'         => 'required|string|max:255',
            'usia'         => 'required|integer|min:0|max:120',
            'kondisi'      => 'required|in:Sehat,Luka Ringan,Luka Berat,Lansia / Rentan',
        ]);

        $checkin = EvacuationCheckin::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data warga berhasil di-checkin di ' . $checkin->posko_id,
            'data'    => $checkin
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