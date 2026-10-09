<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landscape_assessments', function (Blueprint $table) {
            $table->dropColumn('upstream_land_use');
        });
    }

    public function down(): void
    {
        Schema::table('landscape_assessments', function (Blueprint $table) {
            $table->string('upstream_land_use')->nullable();
        });
    }
};
