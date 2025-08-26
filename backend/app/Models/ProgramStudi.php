<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProgramStudi extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'program_studi';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'kode_program_studi',
        'nama_program_studi',
        'id_fakultas',
        'jenjang',
        'akreditasi',
        'tanggal_akreditasi',
        'kepala_program_studi',
        'kapasitas_mahasiswa',
        'visi',
        'misi',
        'kompetensi_lulusan',
        'status_aktif',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'tanggal_akreditasi' => 'date',
        'kompetensi_lulusan' => 'array',
        'kapasitas_mahasiswa' => 'integer',
    ];

    /**
     * Relasi ke model Fakultas.
     */
    public function fakultas(): BelongsTo
    {
        return $this->belongsTo(Fakultas::class, 'id_fakultas');
    }

    /**
     * Relasi ke model Mahasiswa.
     */
    public function mahasiswa(): HasMany
    {
        return $this->hasMany(Mahasiswa::class, 'id_program_studi');
    }

    /**
     * Relasi ke model MataKuliah.
     */
    public function mataKuliah(): HasMany
    {
        return $this->hasMany(MataKuliah::class, 'id_program_studi');
    }

    /**
     * Scope untuk program studi aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status_aktif', 'aktif');
    }

    /**
     * Scope berdasarkan jenjang.
     */
    public function scopeJenjang($query, $jenjang)
    {
        return $query->where('jenjang', $jenjang);
    }

    /**
     * Scope berdasarkan fakultas.
     */
    public function scopeFakultas($query, $idFakultas)
    {
        return $query->where('id_fakultas', $idFakultas);
    }

    /**
     * Mendapatkan jumlah mahasiswa aktif.
     */
    public function getJumlahMahasiswaAktifAttribute(): int
    {
        return $this->mahasiswa()->where('status', 'aktif')->count();
    }

    /**
     * Mendapatkan jumlah mahasiswa yang sudah lulus.
     */
    public function getJumlahMahasiswaLulusAttribute(): int
    {
        return $this->mahasiswa()->where('status', 'lulus')->count();
    }

    /**
     * Mendapatkan kapasitas yang masih tersedia.
     */
    public function getKapasitasTersediaAttribute(): int
    {
        $jumlahMahasiswaAktif = $this->getJumlahMahasiswaAktifAttribute();
        return max(0, $this->kapasitas_mahasiswa - $jumlahMahasiswaAktif);
    }

    /**
     * Cek apakah program studi masih bisa menerima mahasiswa baru.
     */
    public function bisaTerimaMahasiswaBaru(): bool
    {
        return $this->getKapasitasTersediaAttribute() > 0 && $this->status_aktif === 'aktif';
    }

    /**
     * Mendapatkan nama lengkap program studi dengan fakultas.
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->nama_program_studi . ' - ' . $this->fakultas->nama_fakultas;
    }
}