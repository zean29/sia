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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_mahasiswa')->constrained('mahasiswa')->onDelete('cascade');
            $table->string('nomor_pembayaran', 30)->unique();
            $table->foreignId('id_periode_akademik')->nullable()->constrained('periode_akademik')->onDelete('set null');
            
            // Jenis Pembayaran
            $table->enum('jenis_pembayaran', [
                'spp',
                'uang_masuk',
                'praktikum',
                'skripsi',
                'wisuda',
                'denda',
                'lain_lain'
            ])->default('spp');
            
            // Detail Pembayaran
            $table->decimal('jumlah_tagihan', 15, 2);
            $table->decimal('jumlah_bayar', 15, 2)->default(0);
            $table->decimal('sisa_tagihan', 15, 2)->default(0);
            $table->decimal('denda', 15, 2)->default(0);
            
            // Tanggal Penting
            $table->date('tanggal_tagihan');
            $table->date('tanggal_jatuh_tempo');
            $table->timestamp('tanggal_bayar')->nullable();
            
            // Status Pembayaran
            $table->enum('status_pembayaran', [
                'belum_bayar',
                'sebagian',
                'lunas',
                'kelebihan',
                'batal'
            ])->default('belum_bayar');
            
            // Metode Pembayaran
            $table->enum('metode_pembayaran', [
                'tunai',
                'transfer',
                'va_bank',
                'e_wallet',
                'kartu_kredit',
                'cicilan'
            ])->nullable();
            
            // Detail Transaksi
            $table->json('detail_pembayaran')->nullable(); // bank, no_rekening, referensi, dll
            $table->string('nomor_referensi')->nullable();
            $table->text('keterangan')->nullable();
            
            // Approval dan Verifikasi
            $table->foreignId('diverifikasi_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('tanggal_verifikasi')->nullable();
            $table->text('catatan_verifikasi')->nullable();
            
            // Bukti Pembayaran
            $table->string('file_bukti_bayar')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_mahasiswa', 'status_pembayaran']);
            $table->index(['jenis_pembayaran', 'id_periode_akademik']);
            $table->index(['tanggal_jatuh_tempo', 'status_pembayaran']);
            $table->index('nomor_referensi');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};