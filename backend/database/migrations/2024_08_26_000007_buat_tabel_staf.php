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
        Schema::create('staf', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->string('nomor_staf', 20)->unique();
            $table->string('nip', 20)->unique()->nullable();
            $table->foreignId('id_fakultas')->nullable()->constrained('fakultas')->onDelete('set null');
            
            // Data Pribadi
            $table->json('data_pribadi'); // nama_lengkap, tanggal_lahir, tempat_lahir, jenis_kelamin, agama, kewarganegaraan, dll
            
            // Data Kontak
            $table->json('data_kontak'); // alamat, telepon, email_pribadi, dll
            
            // Data Kepegawaian
            $table->enum('jabatan', [
                'kepala_bagian_akademik',
                'kepala_bagian_keuangan', 
                'kepala_bagian_kemahasiswaan',
                'kepala_bagian_sdm',
                'staf_akademik',
                'staf_keuangan',
                'staf_kemahasiswaan',
                'staf_it',
                'staf_umum'
            ])->default('staf_umum');
            
            $table->enum('status_kepegawaian', ['tetap', 'tidak_tetap', 'honorer'])->default('tetap');
            $table->date('tanggal_mulai_kerja');
            $table->date('tanggal_selesai_kerja')->nullable();
            
            // Hak Akses Sistem
            $table->json('hak_akses')->nullable(); // modul apa saja yang bisa diakses
            
            $table->enum('status', ['aktif', 'tidak_aktif', 'pensiun', 'resign'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['jabatan', 'status']);
            $table->index(['id_fakultas', 'status']);
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('staf');
    }
};