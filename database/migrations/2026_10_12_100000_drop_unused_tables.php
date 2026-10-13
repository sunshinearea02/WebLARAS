<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * OPSIONAL - TIDAK ikut "php artisan migrate" biasa.
 *
 * Menghapus tabel yang tidak dipakai kode web LARAS:
 *  - sos_reports         : tidak ada controller/model; check-in warga sudah pakai evacuation_checkins
 *  - evacuation_routes   : tidak ada controller/model; rute evakuasi ditulis langsung di peta-evakuasi.blade.php
 *  - personal_access_tokens : token API Sanctum, tidak dipakai (login memakai session)
 *
 * SEBELUM MENJALANKAN: cek dulu isinya di Supabase SQL Editor dan pastikan
 * tidak ada aplikasi lain (mis. script Python / app mobile) yang memakainya:
 *   select count(*) from sos_reports;
 *   select count(*) from evacuation_routes;
 *   select count(*) from personal_access_tokens;
 *
 * Cara menjalankan:
 *   php artisan migrate --path=database/migrations/optional/2026_10_12_100000_drop_unused_tables.php
 * Batalkan (membuat ulang tabel kosong):
 *   php artisan migrate:rollback --path=database/migrations/optional/2026_10_12_100000_drop_unused_tables.php
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('sos_reports');        // dihapus duluan karena punya FK ke evacuation_routes
        Schema::dropIfExists('evacuation_routes');
        Schema::dropIfExists('personal_access_tokens');
    }

    public function down(): void
    {
        if (! Schema::hasTable('evacuation_routes')) {
            Schema::create('evacuation_routes', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->json('waypoints')->nullable();
                $table->string('meeting_point_name')->nullable();
                $table->decimal('meeting_point_lat', 10, 7)->nullable();
                $table->decimal('meeting_point_lng', 10, 7)->nullable();
                $table->boolean('validated_by_bpbd')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('sos_reports')) {
            Schema::create('sos_reports', function (Blueprint $table) {
                $table->id();
                $table->string('reporter_name')->nullable();
                $table->string('phone')->nullable();
                $table->enum('type', ['sos', 'check_in'])->default('sos');
                $table->decimal('lat', 10, 7)->nullable();
                $table->decimal('lng', 10, 7)->nullable();
                $table->foreignId('evacuation_route_id')->nullable()->constrained('evacuation_routes')->nullOnDelete();
                $table->text('notes')->nullable();
                $table->timestamp('reported_at')->useCurrent();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }
    }
};
