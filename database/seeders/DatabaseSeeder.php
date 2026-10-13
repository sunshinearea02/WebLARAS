<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Membuat akun Admin pertama. Setelah login, Admin bisa membuat akun
     * Kader/Admin lain lewat menu "Tambah Petugas" (/register).
     *
     * Kredensial bisa diatur di .env:
     *   ADMIN_EMAIL=admin@laras.id
     *   ADMIN_PASSWORD=rahasia123
     */
    public function run(): void
    {
        User::updateOrCreate(
   
            ['email' => env('ADMIN_EMAIL', 'admin@laras.id')],
            [
                'name' => env('ADMIN_NAME', 'Admin LARAS'),
                'password' => env('ADMIN_PASSWORD', 'miotgemastik'),
                'role' => User::ROLE_ADMIN,
                'status' => 'ACTIVE',
            ]
        );
          $this->call(SensorNodeSeeder::class);
    }
}
