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
        Schema::create('dokumen', function (Blueprint $table) {
            $table->id();
            $table->morphs('pemilik'); // bisa mahasiswa, dosen, staf
            
            // Informasi Dokumen
            $table->string('nama_dokumen');
            $table->enum('jenis_dokumen', [
                'ktp',
                'ijazah',
                'transkrip',
                'sertifikat',
                'foto',
                'cv',
                'surat_keterangan',
                'kartu_keluarga',
                'akta_lahir',
                'surat_sehat',
                'lain_lain'
            ]);
            
            // File Information
            $table->string('nama_file_asli');
            $table->string('nama_file_sistem');
            $table->string('path_file');
            $table->string('mime_type');
            $table->bigInteger('ukuran_file'); // dalam bytes
            $table->string('hash_file'); // untuk verifikasi integritas
            
            // Status dan Verifikasi
            $table->enum('status_verifikasi', ['belum_verifikasi', 'terverifikasi', 'ditolak'])->default('belum_verifikasi');
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            
            // Metadata
            $table->date('tanggal_dokumen')->nullable();
            $table->date('tanggal_kadaluarsa')->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('wajib')->default(false);
            $table->boolean('publik')->default(false);
            
            // Upload Info
            $table->foreignId('diupload_oleh')->constrained('pengguna');
            $table->timestamp('tanggal_upload');
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['jenis_dokumen', 'status_verifikasi']);
            $table->index('tanggal_kadaluarsa');
            $table->index('hash_file');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen');
    }
};