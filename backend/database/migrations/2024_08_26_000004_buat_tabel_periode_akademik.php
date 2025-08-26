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
        Schema::create('periode_akademik', function (Blueprint $table) {
            $table->id();
            $table->string('kode_periode', 10)->unique();
            $table->string('nama_periode');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('jenis_periode', ['ganjil', 'genap', 'pendek'])->default('ganjil');
            $table->enum('status', ['rencana', 'berjalan', 'selesai'])->default('rencana');
            $table->date('batas_pengambilan_krs')->nullable();
            $table->date('batas_pembayaran')->nullable();
            $table->date('mulai_perkuliahan')->nullable();
            $table->date('selesai_perkuliahan')->nullable();
            $table->date('mulai_uts')->nullable();
            $table->date('selesai_uts')->nullable();
            $table->date('mulai_uas')->nullable();
            $table->date('selesai_uas')->nullable();
            $table->json('kalender_akademik')->nullable();
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['status', 'jenis_periode']);
            $table->index(['tanggal_mulai', 'tanggal_selesai']);
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('periode_akademik');
    }
};