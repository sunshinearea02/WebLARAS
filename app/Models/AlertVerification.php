<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertVerification extends Model
{
    public const PENDING    = 'pending';
    public const SENDING    = 'sending';
    public const APPROVED   = 'approved';
    public const AUTO_SENT  = 'auto_sent';
    public const REJECTED   = 'rejected';
    public const EXPIRED    = 'expired';
    public const SUPERSEDED = 'superseded';
    public const CANCELLED  = 'cancelled';

    public const STATE_LABEL = [
        self::PENDING    => 'Menunggu kader',
        self::SENDING    => 'Sedang dikirim',
        self::APPROVED   => 'Disetujui & terkirim',
        self::AUTO_SENT  => 'Terkirim otomatis',
        self::REJECTED   => 'Ditolak (alarm palsu)',
        self::EXPIRED    => 'Kedaluwarsa',
        self::SUPERSEDED => 'Diganti status baru',
        self::CANCELLED  => 'Batal (kembali aman)',
    ];

    protected $fillable = [
        'level', 'desa', 'state', 'sensor_data_id', 'snapshot', 'decided_by', 'decided_by_phone', 'decided_via',
        'decided_at', 'kader_notified_at', 'kader_sent', 'sent_to_warga_at', 'warga_sent', 'warga_total', 'note',
    ];

    protected function casts(): array
    {
        return [
            'snapshot'          => 'array',
            'decided_at'        => 'datetime',
            'kader_notified_at' => 'datetime',
            'sent_to_warga_at'  => 'datetime',
        ];
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function isPending(): bool
    {
        return $this->state === self::PENDING;
    }

    public function stateLabel(): string
    {
        return self::STATE_LABEL[$this->state] ?? $this->state;
    }

    /** Nama yang memutuskan: nama kader, nomor WA, atau "Sistem (otomatis)". */
    public function deciderName(): ?string
    {
        if ($this->decided_via === 'auto') return 'Sistem (otomatis)';
        return $this->decider?->name ?? $this->decided_by_phone;
    }
}