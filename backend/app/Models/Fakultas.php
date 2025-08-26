<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fakultas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fakultas';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'kode_fakultas',
        'nama_fakultas',
        'deskripsi',
        'dekan',
        'wakil_dekan',
        'kontak_fakultas',
        'status_aktif',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'kontak_fakultas' => 'array',
    ];

    /**
     * Relasi ke model ProgramStudi.
     */
    public function programStudi(): HasMany
    {
        return $this->hasMany(ProgramStudi::class, 'id_fakultas');
    }

    /**
     * Relasi ke model Dosen.
     */
    public function dosen(): HasMany
    {
        return $this->hasMany(Dosen::class, 'id_fakultas');
    }

    /**
     * Relasi ke model Staf.
     */
    public function staf(): HasMany
    {
        return $this->hasMany(Staf::class, 'id_fakultas');
    }

    /**
     * Scope untuk fakultas aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', 'aktif');
    }

    /**
     * Mendapatkan jumlah program studi aktif.
     */
    public function getJumlahProgramStudiAktifAttribute(): int
    {
        return $this->programStudi()->where('status_aktif', 'aktif')->count();
    }

    /**
     * Mendapatkan jumlah mahasiswa di fakultas ini.
     */
    public function getJumlahMahasiswaAttribute(): int
    {
        return Mahasiswa::whereHas('programStudi', function ($query) {
            $query->where('id_fakultas', $this->id);
        })->count();
    }

    /**
     * Mendapatkan jumlah dosen aktif.
     */
    public function getJumlahDosenAktifAttribute(): int
    {
        return $this->dosen()->where('status', 'aktif')->count();
    }
}