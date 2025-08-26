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
        Schema::create('integrasi_pddikti', function (Blueprint $table) {
            $table->id();
            $table->morphs('entitas'); // bisa mahasiswa, dosen, mata_kuliah, dll
            
            // Identitas PDDIKTI
            $table->string('id_pddikti')->nullable();
            $table->string('nomor_unik_pddikti')->nullable();
            
            // Jenis Data
            $table->enum('jenis_data', [
                'mahasiswa',
                'dosen',
                'mata_kuliah',
                'kelas_kuliah',
                'nilai',
                'aktivitas_mahasiswa',
                'program_studi',
                'perguruan_tinggi'
            ]);
            
            // Status Sinkronisasi
            $table->enum('status_sinkronisasi', [
                'belum_sync',
                'sync_berhasil',
                'sync_gagal',
                'perlu_update',
                'konflik_data'
            ])->default('belum_sync');
            
            // Detail Sinkronisasi
            $table->timestamp('terakhir_sync')->nullable();
            $table->json('data_terkirim')->nullable();
            $table->json('respon_pddikti')->nullable();
            $table->text('pesan_error')->nullable();
            $table->integer('percobaan_sync')->default(0);
            
            // Mapping Data
            $table->json('mapping_field')->nullable(); // mapping field lokal ke PDDIKTI
            $table->json('data_konflik')->nullable(); // data yang konflik
            
            // Audit
            $table->foreignId('disync_oleh')->nullable()->constrained('pengguna');
            $table->timestamp('tanggal_sync')->nullable();
            
            $table->timestamps();
            
            // Index untuk performa
            $table->index(['jenis_data', 'status_sinkronisasi']);
            $table->index('id_pddikti');
            $table->index('terakhir_sync');
        });
    }

    /**
     * Kembalikan migrasi.
     */
    public function down(): void
    {
        Schema::dropIfExists('integrasi_pddikti');
    }
};