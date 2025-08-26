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
        Schema::create('mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->string('kode_mata_kuliah', 15)->unique();
            $table->string('nama_mata_kuliah');
            $table->foreignId('id_program_studi')->constrained('program_studi')->onDelete('cascade');
            $table->integer('sks')->default(3);
            $table->enum('jenis', ['wajib', 'pilihan', 'konsentrasi'])->default('wajib');
            $table->integer('semester_rekomendasi')->default(1);
            
            // Prasyarat
            $table->json('prasyarat')->nullable(); // mata kuliah yang harus diambil sebelumnya
            $table->decimal('minimal_ipk', 3, 2)->nullable(); // IPK minimal untuk mengambil
            $table->integer('minimal_semester')->nullable(); // semester minimal
            
            // Deskripsi dan Kurikulum
            $table->text('deskripsi')->nullable();
            $table->json('tujuan_pembelajaran')->nullable();
            $table->json('capaian_pembelajaran')->nullable();
            $table->json('materi_pembelajaran')->nullable();
            $table->json('metode_penilaian')->nullable();
            $table->json('referensi')->nullable();
            
            // Pengaturan Kelas
            $table->integer('kapasitas_kelas')->default(40);
            $table->integer('minimal_peserta')->default(10);
            $table->boolean('tersedia_online')->default(false);
            
            $table->enum('status', ['aktif', 'tidak_aktif', 'arsip'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_program_studi', 'status']);
            $table->index(['jenis', 'semester_rekomendasi']);
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('mata_kuliah');
    }
};