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
        Schema::create('nilai', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('id_mata_kuliah')->constrained('mata_kuliah')->onDelete('cascade');
            $table->foreignId('id_periode_akademik')->constrained('periode_akademik')->onDelete('cascade');
            $table->foreignId('id_jadwal_kelas')->constrained('jadwal_kelas')->onDelete('cascade');
            
            // Komponen Nilai
            $table->decimal('nilai_tugas', 5, 2)->nullable()->comment('Nilai Tugas (0-100)');
            $table->decimal('nilai_uts', 5, 2)->nullable()->comment('Nilai UTS (0-100)');
            $table->decimal('nilai_uas', 5, 2)->nullable()->comment('Nilai UAS (0-100)');
            $table->decimal('nilai_praktek', 5, 2)->nullable()->comment('Nilai Praktek (0-100)');
            $table->decimal('nilai_kehadiran', 5, 2)->nullable()->comment('Nilai Kehadiran (0-100)');
            
            // Nilai Akhir
            $table->decimal('nilai_angka', 5, 2)->nullable()->comment('Nilai Angka (0-100)');
            $table->enum('nilai_huruf', ['A', 'B+', 'B', 'C+', 'C', 'D', 'E'])->nullable();
            $table->decimal('nilai_indeks', 3, 2)->nullable()->comment('Nilai Indeks (0-4)');
            
            // Status Nilai
            $table->enum('status', ['draft', 'final', 'revisi'])->default('draft');
            $table->boolean('lulus')->default(false);
            
            // Metadata
            $table->foreignId('diinput_oleh')->constrained('pengguna');
            $table->timestamp('tanggal_input')->nullable();
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('tanggal_verifikasi')->nullable();
            
            // Catatan
            $table->text('catatan_dosen')->nullable();
            $table->json('riwayat_perubahan')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_mahasiswa', 'id_periode_akademik']);
            $table->index(['id_mata_kuliah', 'id_periode_akademik']);
            $table->index(['status', 'tanggal_input']);
            
            // Unique constraint untuk menghindari duplikasi nilai
            $table->unique(['id_mahasiswa', 'id_mata_kuliah', 'id_periode_akademik'], 'unique_nilai_mahasiswa');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('nilai');
    }
};