<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    
public function up(): void
{
    Schema::create('poskos', function (Blueprint $table) {
        $table->id();
        $table->string('nama');
        $table->string('alamat')->nullable();
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->string('jenis')->default('evakuasi');
        $table->text('keterangan')->nullable();
        $table->boolean('aktif')->default(true);
        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('poskos');
}


};
