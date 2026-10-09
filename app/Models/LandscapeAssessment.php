<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LandscapeAssessment extends Model
{
    protected $table = 'landscape_assessments';

    protected $fillable = [
        'location_name',
        'field_notes',
        'dominant_vegetation',
        'slope_classification',
        'drainage_condition',
        'soil_cracking',
        'soil_type',
        'landslide_history',
        'artificial_slope_cutting',
        'geosynthetic_reinforcement',
        'sensor_log_id',
    ];

    protected $casts = [
        'artificial_slope_cutting' => 'boolean',
    ];

    public static function options(): array
    {
        return [
            'dominant_vegetation' => [
                'Hutan lebat/primer',
                'Hutan sekunder/semak belukar',
                'Perkebunan/tanaman keras',
                'Lahan pertanian/tanaman semusim',
                'Rumput/lahan terbuka',
                'Tanpa vegetasi/gundul',
            ],
            'slope_classification' => [
                'Datar (0-8%)',
                'Landai (8-15%)',
                'Sedang (15–25%)',
                'Curam (25–45%)',
                'Sangat curam (> 45%)',
            ],
            'drainage_condition' => [
                'Baik, berfungsi lancar',
                'Tidak ada saluran drainase',
                'Ada tapi tersumbat/rusak',
            ],
            'soil_cracking' => [
                'Tidak ada',
                'Ringan (< 2 cm)',
                'Sedang (2 - 5 cm)',
                'Parah (> 5 cm)',
            ],
            'soil_type' => [
                'Tanah berbatu/berkerikil',
                'Tanah berpasir',
                'Tanah vulkanik (andosol)',
                'Tanah lanau',
                'Tanah liat/lempung',
                'Tidak diketahui',
            ],
            'landslide_history' => [
                'Ada',
                'Tidak Ada',
            ],
            'geosynthetic_reinforcement' => [
                'Tidak ada',
                'Geotekstil',
                'Geogrid',
                'Geocell',
                'Soil nailing/angkur tanah',
                'Kombinasi lebih dari satu',
            ],
        ];
    }
}
