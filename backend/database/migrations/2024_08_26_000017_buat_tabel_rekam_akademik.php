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
        Schema::create('rekam_akademik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswa')->onDelete('cascade');
            $table->foreignId('id_periode_akademik')->constrained('periode_akademik')->onDelete('cascade');
            
            // Status Akademik
            $table->enum('status_akademik', ['aktif', 'cuti', 'tidak_aktif', 'lulus', 'do', 'skorsing'])->default('aktif');
            $table->integer('semester');
            
            // Beban SKS
            $table->integer('sks_diambil')->default(0);
            $table->integer('sks_lulus')->default(0);
            $table->integer('sks_mengulang')->default(0);
            $table->integer('total_sks_kumulatif')->default(0);
            
            // Prestasi Akademik
            $table->decimal('ips', 3, 2)->default(0)->comment('Indeks Prestasi Semester');
            $table->decimal('ipk', 3, 2)->default(0)->comment('Indeks Prestasi Kumulatif');
            
            // Statistik
            $table->integer('mata_kuliah_a')->default(0);
            $table->integer('mata_kuliah_b_plus')->default(0);
            $table->integer('mata_kuliah_b')->default(0);
            $table->integer('mata_kuliah_c_plus')->default(0);
            $table->integer('mata_kuliah_c')->default(0);
            $table->integer('mata_kuliah_d')->default(0);
            $table->integer('mata_kuliah_e')->default(0);
            
            // Status Pembayaran
            $table->boolean('lunas_pembayaran')->default(false);
            $table->decimal('total_tagihan', 15, 2)->default(0);
            $table->decimal('total_bayar', 15, 2)->default(0);
            
            // Catatan
            $table->text('catatan_akademik')->nullable();
            $table->text('catatan_pembayaran')->nullable();
            
            // Validasi
            $table->foreignId('divalidasi_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('tanggal_validasi')->nullable();
            $table->enum('status_validasi', ['draft', 'tervalidasi', 'perlu_revisi'])->default('draft');
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_mahasiswa', 'semester']);
            $table->index(['id_periode_akademik', 'status_akademik']);
            $table->index(['ipk', 'semester']);
            
            // Unique constraint
            $table->unique(['id_mahasiswa', 'id_periode_akademik'], 'unique_rekam_periode');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekam_akademik');
    }
};