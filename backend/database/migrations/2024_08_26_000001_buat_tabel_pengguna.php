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
        Schema::create('pengguna', function (Blueprint $table) {
            $table->id();
            $table->string('nama_pengguna')->unique();
            $table->string('email')->unique();
            $table->timestamp('email_terverifikasi_pada')->nullable();
            $table->string('kata_sandi');
            $table->enum('peran', ['mahasiswa', 'dosen', 'staf', 'admin'])->default('mahasiswa');
            $table->boolean('aktif')->default(true);
            $table->json('pengaturan_notifikasi')->nullable();
            $table->timestamp('terakhir_masuk')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            // Index untuk performa
            $table->index(['peran', 'aktif']);
            $table->index('terakhir_masuk');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengguna');
    }
};