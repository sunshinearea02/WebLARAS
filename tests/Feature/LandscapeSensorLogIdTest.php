<?php

namespace Tests\Feature;

use App\Models\LandscapeAssessment;
use App\Models\SensorNode;
use App\Models\User;
use Database\Seeders\LandscapePacetSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandscapeSensorLogIdTest extends TestCase
{
    use RefreshDatabase;

    private function form(SensorNode $node): array
    {
        return [
            'node_id' => $node->id,
            'location_name' => 'Lereng uji',
            'dominant_vegetation' => 'Perkebunan/tanaman keras',
            'slope_classification' => 'Curam (25–45%)',
            'drainage_condition' => 'Tidak ada saluran drainase',
            'soil_cracking' => 'Tidak ada',
            'soil_type' => 'Tanah vulkanik (andosol)',
            'landslide_history' => 'Tidak Ada',
            'artificial_slope_cutting' => 'tidak_ada',
            'geosynthetic_reinforcement' => 'Tidak ada',
        ];
    }

    public function test_store_and_update_fill_sensor_log_id_from_node(): void
    {
        $kader = User::factory()->create(['role' => User::ROLE_KADER, 'status' => 'ACTIVE']);
        $node1 = SensorNode::create(['node_code' => 'NODE-01', 'node_name' => 'Node 1', 'status' => 'ACTIVE', 'latitude' => -7.674, 'longitude' => 112.516]);
        $node2 = SensorNode::create(['node_code' => 'NODE-02', 'node_name' => 'Node 2', 'status' => 'ACTIVE', 'latitude' => -7.674, 'longitude' => 112.516]);

        $this->actingAs($kader)->post('/lanskap', $this->form($node1))->assertRedirect();
        $entry = LandscapeAssessment::sole();
        $this->assertSame('NODE-01', $entry->sensor_log_id);

        $this->actingAs($kader)->put("/lanskap/{$entry->id}", $this->form($node2))->assertRedirect();
        $this->assertSame('NODE-02', $entry->fresh()->sensor_log_id);
    }

    public function test_pacet_seeder_replaces_dummy_surveys(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => 'ACTIVE']);
        $nodes = [];
        foreach (['NODE-01' => 12, 'NODE-02' => 24, 'NODE-04' => 5] as $code => $deg) {
            $nodes[$code] = SensorNode::create(['node_code' => $code, 'node_name' => $code, 'desa' => 'Kemiri',
                'status' => 'ACTIVE', 'latitude' => -7.674, 'longitude' => 112.516]);
        }
        LandscapeAssessment::create(['node_id' => $nodes['NODE-01']->id, 'user_id' => $admin->id,
            'location_name' => 'Titik Sensor B - Lereng Gunung Merbabu']);

        $this->seed(LandscapePacetSeeder::class);
        $this->seed(LandscapePacetSeeder::class);   // aman dijalankan ulang

        $this->assertSame(0, LandscapeAssessment::where('location_name', 'like', '%Merbabu%')->count());
        $this->assertSame(3, LandscapeAssessment::count());
        $byCode = LandscapeAssessment::all()->keyBy('sensor_log_id');
        $this->assertSame('Sedang (15–25%)', $byCode['NODE-01']->slope_classification);
        $this->assertSame('Curam (25–45%)', $byCode['NODE-02']->slope_classification);
        $this->assertSame('Landai (8-15%)', $byCode['NODE-04']->slope_classification);
    }
}
