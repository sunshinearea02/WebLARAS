<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('landscape_assessments', 'upstream_land_use')) {
            Schema::table('landscape_assessments', function (Blueprint $table) {
                $table->dropColumn('upstream_land_use');
            });
        }
    }

    public function down(): void
    {
        // Kolom sudah tidak dipakai, tidak dikembalikan.
    }
};
