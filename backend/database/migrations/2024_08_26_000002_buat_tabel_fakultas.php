<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('fakultas', function (Blueprint $table) {
            $table->id();
            $table->string('kode_fakultas', 10)->unique();
            $table->string('nama_fakultas');
            $table->text('deskripsi')->nullable();
            $table->string('dekan')->nullable();
            $table->string('wakil_dekan')->nullable();
            $table->json('kontak_fakultas')->nullable();
            $table->enum('status_aktif', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['status_aktif']);
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('fakultas');
    }
};