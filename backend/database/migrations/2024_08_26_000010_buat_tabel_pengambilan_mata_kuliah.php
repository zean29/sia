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
        Schema::create('pengambilan_mata_kuliah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('id_jadwal_kelas')->constrained('jadwal_kelas')->onDelete('cascade');
            $table->foreignId('id_periode_akademik')->constrained('periode_akademik')->onDelete('cascade');
            
            // Status Pengambilan
            $table->enum('status', [
                'draft', 
                'diajukan', 
                'disetujui', 
                'ditolak', 
                'berjalan', 
                'selesai', 
                'mengulang',
                'dibatalkan'
            ])->default('draft');
            
            // Tanggal Penting
            $table->timestamp('tanggal_pengajuan')->nullable();
            $table->timestamp('tanggal_persetujuan')->nullable();
            $table->timestamp('tanggal_pembatalan')->nullable();
            
            // Approval
            $table->foreignId('disetujui_oleh')->nullable()->constrained('pengguna');
            $table->text('catatan_persetujuan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            
            // Tracking Kehadiran
            $table->integer('jumlah_hadir')->default(0);
            $table->integer('jumlah_tidak_hadir')->default(0);
            $table->decimal('persentase_kehadiran', 5, 2)->default(0);
            
            // Nilai Sementara (sebelum masuk ke tabel nilai resmi)
            $table->decimal('nilai_tugas', 5, 2)->nullable();
            $table->decimal('nilai_uts', 5, 2)->nullable();
            $table->decimal('nilai_uas', 5, 2)->nullable();
            $table->decimal('nilai_praktek', 5, 2)->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_mahasiswa', 'id_periode_akademik']);
            $table->index(['id_jadwal_kelas', 'status']);
            $table->index(['status', 'tanggal_pengajuan']);
            
            // Unique constraint untuk menghindari double enrollment
            $table->unique(['id_mahasiswa', 'id_jadwal_kelas'], 'unique_mahasiswa_kelas');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengambilan_mata_kuliah');
    }
};