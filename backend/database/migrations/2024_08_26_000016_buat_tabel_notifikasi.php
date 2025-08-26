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
        Schema::create('notifikasi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_penerima')->constrained('pengguna')->onDelete('cascade');
            $table->foreignId('id_pengirim')->nullable()->constrained('pengguna')->onDelete('set null');
            
            // Content Notifikasi
            $table->string('judul');
            $table->text('pesan');
            $table->enum('jenis', [
                'info',
                'peringatan',
                'sukses',
                'error',
                'pengingat',
                'announcement'
            ])->default('info');
            
            // Prioritas
            $table->enum('prioritas', ['rendah', 'normal', 'tinggi', 'darurat'])->default('normal');
            
            // Status
            $table->boolean('dibaca')->default(false);
            $table->timestamp('dibaca_pada')->nullable();
            $table->boolean('ditampilkan')->default(true);
            
            // Channel Notifikasi
            $table->json('channel')->nullable(); // web, email, sms, push
            $table->boolean('terkirim_email')->default(false);
            $table->boolean('terkirim_sms')->default(false);
            
            // Metadata
            $table->json('data_tambahan')->nullable(); // link, action, dll
            $table->string('kategori')->nullable(); // akademik, keuangan, umum
            $table->date('kadaluarsa')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['id_penerima', 'dibaca']);
            $table->index(['jenis', 'prioritas']);
            $table->index(['kategori', 'created_at']);
            $table->index('kadaluarsa');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifikasi');
    }
};