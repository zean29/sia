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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('id_jadwal_kelas')->constrained('jadwal_kelas')->onDelete('cascade');
            $table->foreignId('id_periode_akademik')->constrained('periode_akademik')->onDelete('cascade');
            
            // Detail Pertemuan
            $table->integer('pertemuan_ke');
            $table->date('tanggal_pertemuan');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            
            // Status Kehadiran
            $table->enum('status_kehadiran', ['hadir', 'tidak_hadir', 'izin', 'sakit', 'alpa'])->default('tidak_hadir');
            $table->timestamp('waktu_absen')->nullable();
            $table->text('keterangan')->nullable();
            
            // Metode Absensi
            $table->enum('metode_absensi', ['manual', 'qr_code', 'biometric', 'gps'])->default('manual');
            $table->json('data_absensi')->nullable(); // koordinat GPS, foto, dll
            
            // Materi Pertemuan
            $table->text('materi_pertemuan')->nullable();
            $table->json('dokumen_materi')->nullable();
            
            // Approval
            $table->foreignId('diinput_oleh')->constrained('pengguna');
            $table->timestamp('tanggal_input');
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_mahasiswa', 'id_jadwal_kelas']);
            $table->index(['id_jadwal_kelas', 'tanggal_pertemuan']);
            $table->index(['tanggal_pertemuan', 'status_kehadiran']);
            
            // Unique constraint untuk menghindari duplikasi kehadiran
            $table->unique(['id_mahasiswa', 'id_jadwal_kelas', 'pertemuan_ke'], 'unique_kehadiran_pertemuan');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};