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
        Schema::create('log_sistem', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_pengguna')->nullable()->constrained('pengguna')->onDelete('set null');
            
            // Informasi Aktivitas
            $table->string('aktivitas'); // login, logout, create, update, delete, dll
            $table->string('modul'); // mahasiswa, dosen, nilai, pembayaran, dll
            $table->morphs('entitas'); // entitas yang terkait (mahasiswa, dosen, dll)
            
            // Detail Aktivitas
            $table->text('deskripsi');
            $table->json('data_lama')->nullable(); // data sebelum perubahan
            $table->json('data_baru')->nullable(); // data setelah perubahan
            
            // Informasi Teknis
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->string('metode_http')->nullable(); // GET, POST, PUT, DELETE
            $table->string('url')->nullable();
            
            // Level Log
            $table->enum('level', ['debug', 'info', 'warning', 'error', 'critical'])->default('info');
            
            // Status
            $table->boolean('berhasil')->default(true);
            $table->text('pesan_error')->nullable();
            
            $table->timestamp('waktu_aktivitas');
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_pengguna', 'waktu_aktivitas']);
            $table->index(['modul', 'aktivitas']);
            $table->index(['level', 'berhasil']);
            $table->index('waktu_aktivitas');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('log_sistem');
    }
};