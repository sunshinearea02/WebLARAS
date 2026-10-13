<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'phone', 'role', 'status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_KADER = 'kader';

    /** Role yang boleh mengelola data (edit lanskap & check-in warga). */
    public const STAFF_ROLES = [self::ROLE_ADMIN, self::ROLE_KADER];

    protected $table = 'users'; // Menyesuaikan nama tabel di Supabase

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isKader(): bool
    {
        return $this->role === self::ROLE_KADER;
    }

    public const STATUS_ACTIVE = 'ACTIVE';
    public const STATUS_PENDING = 'PENDING';

    public function isPending(): bool
    {
        return strtoupper((string) $this->status) === self::STATUS_PENDING;
    }

    /** Akun dianggap aktif jika status kosong (data lama) atau 'ACTIVE'. */
    public function isActive(): bool
    {
        return $this->status === null || strtoupper($this->status) === 'ACTIVE';
    }

    /** Admin / Kader yang aktif = boleh edit data lanskap & check-in. */
    public function isStaff(): bool
    {
        return $this->isActive() && in_array($this->role, self::STAFF_ROLES, true);
    }
}