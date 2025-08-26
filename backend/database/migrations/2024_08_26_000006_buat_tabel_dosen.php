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
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->string('nomor_dosen', 20)->unique();
            $table->string('nidn', 20)->unique()->nullable();
            $table->string('nip', 20)->unique()->nullable();
            $table->foreignId('id_fakultas')->constrained('fakultas')->onDelete('cascade');
            
            // Data Pribadi
            $table->json('data_pribadi'); // nama_lengkap, tanggal_lahir, tempat_lahir, jenis_kelamin, agama, kewarganegaraan, dll
            
            // Data Kontak
            $table->json('data_kontak'); // alamat, telepon, email_pribadi, dll
            
            // Kredensial Akademik
            $table->json('kredensial_akademik'); // pendidikan_terakhir, gelar, universitas, bidang_keahlian, dll
            
            // Status Kepegawaian
            $table->enum('status_kepegawaian', ['tetap', 'tidak_tetap', 'honorer'])->default('tetap');
            $table->enum('jabatan_fungsional', ['asisten_ahli', 'lektor', 'lektor_kepala', 'guru_besar', 'tidak_ada'])->default('tidak_ada');
            $table->date('tanggal_mulai_kerja');
            $table->date('tanggal_selesai_kerja')->nullable();
            
            // Data Akademik
            $table->integer('beban_mengajar_min')->default(12); // SKS minimum
            $table->integer('beban_mengajar_max')->default(16); // SKS maksimum
            $table->json('bidang_keahlian')->nullable();
            $table->json('sertifikasi')->nullable();
            
            $table->enum('status', ['aktif', 'tidak_aktif', 'pensiun', 'resign'])->default('aktif');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_fakultas', 'status']);
            $table->index(['status_kepegawaian', 'jabatan_fungsional']);
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};