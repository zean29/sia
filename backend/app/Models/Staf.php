<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staf extends Model
{
    use HasFactory;

    protected $table = 'staf';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_pengguna',
        'nomor_staf',
        'nip',
        'id_fakultas',
        'data_pribadi',
        'data_kontak',
        'jabatan',
        'status_kepegawaian',
        'tanggal_mulai_kerja',
        'tanggal_selesai_kerja',
        'hak_akses',
        'status',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'data_pribadi' => 'array',
        'data_kontak' => 'array',
        'hak_akses' => 'array',
        'tanggal_mulai_kerja' => 'date',
        'tanggal_selesai_kerja' => 'date',
    ];

    /**
     * Relasi ke model Pengguna.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    /**
     * Relasi ke model Fakultas.
     */
    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas');
    }

    /**
     * Scope untuk staf aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope berdasarkan jabatan.
     */
    public function scopeJabatan($query, $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    /**
     * Scope berdasarkan fakultas.
     */
    public function scopeFakultas($query, $fakultasId)
    {
        return $query->where('id_fakultas', $fakultasId);
    }

    /**
     * Cek apakah staf aktif.
     */
    public function aktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Dapatkan nama lengkap staf.
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->data_pribadi['nama_lengkap'] ?? '';
    }

    /**
     * Dapatkan email staf.
     */
    public function getEmailAttribute(): string
    {
        return $this->pengguna->email ?? '';
    }

    /**
     * Dapatkan telepon staf.
     */
    public function getTeleponAttribute(): string
    {
        return $this->data_kontak['telepon'] ?? '';
    }
}