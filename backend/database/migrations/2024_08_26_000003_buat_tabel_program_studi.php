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
        Schema::create('program_studi', function (Blueprint $table) {
            $table->id();
            $table->string('kode_program_studi', 10)->unique();
            $table->string('nama_program_studi');
            $table->foreignId('id_fakultas')->constrained('fakultas')->onDelete('cascade');
            $table->enum('jenjang', ['D3', 'D4', 'S1', 'S2', 'S3'])->default('S1');
            $table->string('akreditasi', 5)->nullable();
            $table->date('tanggal_akreditasi')->nullable();
            $table->string('kepala_program_studi')->nullable();
            $table->integer('kapasitas_mahasiswa')->default(100);
            $table->text('visi')->nullable();
            $table->text('misi')->nullable();
            $table->json('kompetensi_lulusan')->nullable();
            $table->enum('status_aktif', ['aktif', 'tidak_aktif'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_fakultas', 'status_aktif']);
            $table->index('jenjang');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_studi');
    }
};