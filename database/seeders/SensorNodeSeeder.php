<?php

namespace Database\Seeders;

use App\Models\SensorNode;
use Illuminate\Database\Seeder;

/**
 * Mengisi 6 node awal LARAS (sebelumnya ditulis langsung di EvacuationMapController).
 * Aman dijalankan berulang: node yang sudah ada (berdasarkan node_code) tidak diubah koordinatnya.
 */
class SensorNodeSeeder extends Seeder
{
    public function run(): void
    {
        $nodes = [
            ['Node-01', 'Padusan',      -7.69100, 112.54100, 'Lereng curam dekat permukiman, riwayat longsor & retakan tanah terpantau'],
            ['Node-02', 'Sajen',        -7.67500, 112.53500, 'Perbukitan dengan kelembapan tinggi, titik kumpul evakuasi sementara'],
            ['Node-03', 'Claket',       -7.66200, 112.54000, 'Kelembapan tanah meningkat, titik temu persimpangan jalur atas'],
            ['Node-04', 'Cepokolimo',   -7.65200, 112.52900, 'Dataran lebih landai, aman untuk titik kumpul & check-in warga'],
            ['Node-05', 'Kembangbelor', -7.64400, 112.54700, 'Titik kumpul evakuasi utama, fasilitas kesehatan & dapur umum'],
            ['Node-06', 'Kemiri',       -7.66000, 112.55200, 'Zona permukiman padat, jalur evakuasi utama ke jalan raya'],
        ];

        foreach ($nodes as [$code, $desa, $lat, $lng, $notes]) {
            // Node yang sudah ada (mis. "NODE-01") tidak diduplikasi; hanya desa/catatan yang kosong diisi.
            $existing = SensorNode::whereRaw('UPPER(node_code) = ?', [strtoupper($code)])->first();

            if ($existing) {
                $existing->update(array_filter([
                    'desa'  => $existing->desa ?: $desa,
                    'notes' => $existing->notes ?: $notes,
                ]));
                continue;
            }

            SensorNode::create(['node_code' => $code, 'node_name' => $code, 'desa' => $desa,
                'latitude' => $lat, 'longitude' => $lng, 'notes' => $notes, 'status' => 'ACTIVE']);
        }
    }
}
