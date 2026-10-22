<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Laporan hambatan jalan di Peta Evakuasi (tabel hazard_reports). */
class HazardReport extends Model
{
    public const JENIS = [
        'Jalan Ditutup / Longsor',
        'Perbaikan Jalan',
        'Genangan Air / Banjir',
        'Akses Alternatif Aman',
    ];

    /** Laporan yang lebih lama dari ini tidak ditampilkan lagi di peta. */
    public const TAMPIL_HARI = 14;

    protected $fillable = [
        'type', 'latitude', 'longitude', 'description', 'reporter_name',
        'source', 'status', 'user_id', 'verified_by', 'verified_at',
    ];

    protected $casts = [
        'latitude'    => 'float',
        'longitude'   => 'float',
        'verified_at' => 'datetime',
    ];

    /** Data untuk peta: tanpa user_id / verified_by. */
    public function toMap(): array
    {
        return [
            'id'       => $this->id,
            'type'     => $this->type,
            'lat'      => $this->latitude,
            'lng'      => $this->longitude,
            'desc'     => $this->description,
            'pelapor'  => $this->source === 'petugas' ? 'Petugas' : ($this->reporter_name ?: 'Warga'),
            'source'   => $this->source,
            'status'   => $this->status,
            'waktu'    => $this->created_at?->locale('id')->diffForHumans(),
        ];
    }

    public static function untukPeta(): array
    {
        try {
            return static::whereIn('status', ['menunggu', 'terverifikasi'])
                ->where('created_at', '>=', now()->subDays(self::TAMPIL_HARI))
                ->latest()->limit(200)->get()
                ->map->toMap()->values()->all();
        } catch (\Throwable $e) {
            return [];   // tabel belum ada: peta tetap tampil
        }
    }
}
