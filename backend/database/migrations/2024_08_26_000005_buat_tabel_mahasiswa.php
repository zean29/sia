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
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->constrained('pengguna')->onDelete('cascade');
            $table->string('nomor_mahasiswa', 20)->unique();
            $table->string('nim', 15)->unique();
            $table->foreignId('id_program_studi')->constrained('program_studi')->onDelete('cascade');
            $table->enum('status', ['aktif', 'tidak_aktif', 'lulus', 'do', 'skorsing', 'cuti'])->default('aktif');
            $table->date('tanggal_masuk');
            $table->date('tanggal_lulus')->nullable();
            $table->string('jenis_masuk')->default('reguler'); // reguler, transfer, alih_kredit
            
            // Data Pribadi
            $table->json('data_pribadi'); // nama_lengkap, tanggal_lahir, tempat_lahir, jenis_kelamin, agama, kewarganegaraan, dll
            
            // Data Kontak
            $table->json('data_kontak'); // alamat, telepon, email_pribadi, kontak_darurat, dll
            
            // Data Akademik
            $table->json('data_akademik'); // asal_sekolah, tahun_lulus, nilai_masuk, jalur_masuk, dll
            
            // Data Orang Tua/Wali
            $table->json('data_orangtua')->nullable();
            
            // Data Finansial
            $table->decimal('total_biaya_kuliah', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            
            // Tracking akademik
            $table->decimal('ipk', 3, 2)->default(0);
            $table->integer('total_sks')->default(0);
            $table->integer('semester_aktif')->default(1);
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_program_studi', 'status']);
            $table->index(['status', 'semester_aktif']);
            $table->index('tanggal_masuk');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};