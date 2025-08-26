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
        Schema::create('jadwal_kelas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mata_kuliah')->constrained('mata_kuliah')->onDelete('cascade');
            $table->foreignId('id_dosen')->constrained('dosen')->onDelete('cascade');
            $table->foreignId('id_periode_akademik')->constrained('periode_akademik')->onDelete('cascade');
            
            $table->string('nama_kelas', 10); // A, B, C, dll
            $table->string('ruang_kelas')->nullable();
            $table->enum('jenis_kelas', ['teori', 'praktikum', 'gabungan'])->default('teori');
            
            // Jadwal Waktu
            $table->json('waktu_jadwal'); // hari, jam_mulai, jam_selesai, gedung, lantai
            
            // Kapasitas
            $table->integer('kapasitas_maksimal')->default(40);
            $table->integer('jumlah_terdaftar')->default(0);
            $table->integer('kapasitas_waiting_list')->default(10);
            
            // Pengaturan Kelas
            $table->enum('metode_pembelajaran', ['offline', 'online', 'hybrid'])->default('offline');
            $table->string('link_online')->nullable();
            $table->json('peralatan_diperlukan')->nullable();
            
            // Status
            $table->enum('status', ['draft', 'terbuka', 'tutup', 'berjalan', 'selesai', 'dibatalkan'])->default('draft');
            $table->date('tanggal_buka_pendaftaran')->nullable();
            $table->date('tanggal_tutup_pendaftaran')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_periode_akademik', 'status']);
            $table->index(['id_mata_kuliah', 'id_periode_akademik']);
            $table->index(['id_dosen', 'id_periode_akademik']);
            
            // Unique constraint untuk menghindari kelas duplikat
            $table->unique(['id_mata_kuliah', 'nama_kelas', 'id_periode_akademik'], 'unique_kelas_periode');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_kelas');
    }
};