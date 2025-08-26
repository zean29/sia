<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Pengguna extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'pengguna';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'nama_pengguna',
        'email',
        'kata_sandi',
        'peran',
        'aktif',
        'pengaturan_notifikasi',
        'terakhir_masuk',
    ];

    /**
     * Atribut yang harus disembunyikan untuk serialisasi.
     */
    protected $hidden = [
        'kata_sandi',
        'remember_token',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'email_terverifikasi_pada' => 'datetime',
        'kata_sandi' => 'hashed',
        'pengaturan_notifikasi' => 'array',
        'terakhir_masuk' => 'datetime',
        'aktif' => 'boolean',
    ];

    /**
     * Accessor untuk nama password yang sesuai dengan Laravel.
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->kata_sandi,
            set: fn ($value) => $this->kata_sandi = $value,
        );
    }

    /**
     * Relasi ke model Mahasiswa.
     */
    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class, 'id_pengguna');
    }

    /**
     * Relasi ke model Dosen.
     */
    public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class, 'id_pengguna');
    }

    /**
     * Relasi ke model Staf.
     */
    public function staf(): HasOne
    {
        return $this->hasOne(Staf::class, 'id_pengguna');
    }

    /**
     * Relasi ke model Notifikasi sebagai penerima.
     */
    public function notifikasiDiterima(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_penerima');
    }

    /**
     * Relasi ke model Notifikasi sebagai pengirim.
     */
    public function notifikasiDikirim(): HasMany
    {
        return $this->hasMany(Notifikasi::class, 'id_pengirim');
    }

    /**
     * Relasi ke model LogSistem.
     */
    public function logSistem(): HasMany
    {
        return $this->hasMany(LogSistem::class, 'id_pengguna');
    }

    /**
     * Scope untuk pengguna aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    /**
     * Scope berdasarkan peran.
     */
    public function scopePeran($query, $peran)
    {
        return $query->where('peran', $peran);
    }

    /**
     * Cek apakah pengguna memiliki peran tertentu.
     */
    public function memilikiPeran($peran): bool
    {
        return $this->peran === $peran;
    }

    /**
     * Cek apakah pengguna adalah mahasiswa.
     */
    public function adalahMahasiswa(): bool
    {
        return $this->peran === 'mahasiswa';
    }

    /**
     * Cek apakah pengguna adalah dosen.
     */
    public function adalahDosen(): bool
    {
        return $this->peran === 'dosen';
    }

    /**
     * Cek apakah pengguna adalah staf.
     */
    public function adalahStaf(): bool
    {
        return $this->peran === 'staf';
    }

    /**
     * Cek apakah pengguna adalah admin.
     */
    public function adalahAdmin(): bool
    {
        return $this->peran === 'admin';
    }

    /**
     * Update waktu terakhir masuk.
     */
    public function updateTerakhirMasuk(): void
    {
        $this->update(['terakhir_masuk' => now()]);
    }
}