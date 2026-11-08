<?php

namespace Database\Seeders;

use App\Models\LandscapeAssessment;
use App\Models\SensorNode;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Mengganti survei lanskap dummy dengan survei awal untuk node LARAS di Desa Kemiri, Pacet.
 *
 * Jalankan: php artisan db:seed --class=LandscapePacetSeeder
 *
 * - Menghapus survei dummy (lokasi Gunung Merbabu, Gunung Salak, Bukit Campuhan).
 * - Mengisi sensor_log_id survei lama yang masih kosong dengan kode node-nya.
 * - Membuat satu survei untuk setiap node yang belum punya survei.
 *
 * Kemiringan diturunkan dari catatan peta node (derajat -> persen). Parameter lain adalah
 * isian awal yang wajib diverifikasi kader lewat halaman Data Lanskap.
 * Aman dijalankan berulang: node yang sudah punya survei tidak diubah.
 */
class LandscapePacetSeeder extends Seeder
{
    private const DUMMY_LOCATIONS = ['Gunung Merbabu', 'Gunung Salak', 'Bukit Campuhan'];

    // kode node => [kemiringan (derajat), ketinggian (mdpl), vegetasi]
    private const NODES = [
        'NODE-01' => [12, 557, 'Perkebunan/tanaman keras'],
        'NODE-02' => [24, 521, 'Perkebunan/tanaman keras'],
        'NODE-03' => [10, 580, 'Perkebunan/tanaman keras'],
        'NODE-04' => [5, 597, 'Perkebunan/tanaman keras'],
        'NODE-06' => [24, 563, 'Perkebunan/tanaman keras'],
    ];

    public function run(): void
    {
        $deleted = LandscapeAssessment::query()
            ->where(function ($q) {
                foreach (self::DUMMY_LOCATIONS as $name) {
                    $q->orWhere('location_name', 'like', "%{$name}%");
                }
            })
            ->delete();
        $this->command?->info("Survei dummy dihapus: {$deleted}");

        foreach (LandscapeAssessment::whereNull('sensor_log_id')->orWhere('sensor_log_id', '')->get() as $entry) {
            $entry->update(['sensor_log_id' => $entry->node?->node_code]);
        }

        $userId = User::where('role', User::ROLE_ADMIN)->orderBy('id')->value('id')
            ?? User::orderBy('id')->value('id');

        foreach (self::NODES as $code => [$degrees, $elevation, $vegetation]) {
            $node = SensorNode::whereRaw('UPPER(node_code) = ?', [$code])->first();
            if (! $node) {
                $this->command?->warn("{$code} tidak ada di sensor_nodes, dilewati");
                continue;
            }
            if (LandscapeAssessment::where('node_id', $node->id)->exists()) {
                $this->command?->line("{$code} sudah punya survei, tidak diubah");
                continue;
            }

            $percent = tan(deg2rad($degrees)) * 100;

            LandscapeAssessment::create([
                'node_id' => $node->id,
                'user_id' => $userId,
                'sensor_log_id' => $node->node_code,
                'location_name' => trim("{$node->node_name}, Desa {$node->desa}, Pacet"),
                'dominant_vegetation' => $vegetation,
                'slope_classification' => self::slopeClass($percent),
                'drainage_condition' => 'Tidak ada saluran drainase',
                'soil_cracking' => 'Tidak ada',
                'soil_type' => 'Tanah vulkanik (andosol)',
                'landslide_history' => 'Tidak Ada',
                'artificial_slope_cutting' => false,
                'geosynthetic_reinforcement' => 'Tidak ada',
                'field_notes' => sprintf(
                    'Kemiringan ±%d° (±%.0f%%) dan ketinggian %d mdpl dari peta node. Vegetasi, drainase, '
                    . 'retakan, jenis tanah, dan riwayat longsor adalah isian awal, wajib diverifikasi survei lapangan.',
                    $degrees, $percent, $elevation
                ),
            ]);
            $this->command?->info("{$code}: survei dibuat ({$degrees}° = " . self::slopeClass($percent) . ')');
        }
    }

    private static function slopeClass(float $percent): string
    {
        return match (true) {
            $percent < 8 => 'Datar (0-8%)',
            $percent < 15 => 'Landai (8-15%)',
            $percent < 25 => 'Sedang (15–25%)',
            $percent <= 45 => 'Curam (25–45%)',
            default => 'Sangat curam (> 45%)',
        };
    }
}
