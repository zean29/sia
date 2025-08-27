<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_pengguna',
        'nomor_mahasiswa',
        'nim',
        'id_program_studi',
        'status',
        'tanggal_masuk',
        'tanggal_lulus',
        'jenis_masuk',
        'data_pribadi',
        'data_kontak',
        'data_akademik',
        'data_orangtua',
        'total_biaya_kuliah',
        'total_bayar',
        'ipk',
        'total_sks',
        'semester_aktif',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'tanggal_masuk' => 'date',
        'tanggal_lulus' => 'date',
        'data_pribadi' => 'array',
        'data_kontak' => 'array',
        'data_akademik' => 'array',
        'data_orangtua' => 'array',
        'total_biaya_kuliah' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'ipk' => 'decimal:2',
        'total_sks' => 'integer',
        'semester_aktif' => 'integer',
    ];

    /**
     * Relasi ke model Pengguna.
     */
    public function pengguna(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'id_pengguna');
    }

    /**
     * Relasi ke model ProgramStudi.
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi');
    }

    /**
     * Relasi ke model PengambilanMataKuliah.
     */
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model Nilai.
     */
    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model Pembayaran.
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model RekamAkademik.
     */
    public function rekamAkademik(): HasMany
    {
        return $this->hasMany(RekamAkademik::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model Kehadiran.
     */
    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'id_mahasiswa');
    }

    /**
     * Relasi polimorfik ke model Dokumen.
     */
    public function dokumen(): MorphMany
    {
        return $this->morphMany(Dokumen::class, 'pemilik');
    }

    /**
     * Relasi polimorfik ke model IntegrasiPddikti.
     */
    public function integrasiPddikti(): MorphMany
    {
        return $this->morphMany(IntegrasiPddikti::class, 'entitas');
    }

    /**
     * Scope untuk mahasiswa aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope berdasarkan program studi.
     */
    public function scopeProgramStudi($query, $idProgramStudi)
    {
        return $query->where('id_program_studi', $idProgramStudi);
    }

    /**
     * Scope berdasarkan angkatan.
     */
    public function scopeAngkatan($query, $tahun)
    {
        return $query->whereYear('tanggal_masuk', $tahun);
    }

    /**
     * Scope berdasarkan semester.
     */
    public function scopeSemester($query, $semester)
    {
        return $query->where('semester_aktif', $semester);
    }

    /**
     * Mendapatkan nama lengkap mahasiswa.
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->data_pribadi['nama_lengkap'] ?? '';
    }

    /**
     * Mendapatkan angkatan mahasiswa.
     */
    public function getAngkatanAttribute(): int
    {
        return $this->tanggal_masuk->year;
    }

    /**
     * Mendapatkan sisa tagihan.
     */
    public function getSisaTagihanAttribute(): float
    {
        return $this->total_biaya_kuliah - $this->total_bayar;
    }

    /**
     * Cek apakah mahasiswa sudah lulus.
     */
    public function sudahLulus(): bool
    {
        return $this->status === 'lulus' && $this->tanggal_lulus !== null;
    }

    /**
     * Cek apakah mahasiswa aktif.
     */
    public function sedangAktif(): bool
    {
        return $this->status === 'aktif';
    }

    /**
     * Mendapatkan mata kuliah yang sedang diambil pada periode tertentu.
     */
    public function mataKuliahPeriode($idPeriode = null)
    {
        $query = $this->pengambilanMataKuliah()
            ->with(['jadwalKelas.mataKuliah'])
            ->where('status', '!=', 'dibatalkan');

        if ($idPeriode) {
            $query->where('id_periode_akademik', $idPeriode);
        } else {
            // Ambil periode yang sedang berjalan
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            if ($periodeBerjalan) {
                $query->where('id_periode_akademik', $periodeBerjalan->id);
            }
        }

        return $query->get();
    }

    /**
     * Menghitung total SKS yang diambil pada periode tertentu.
     */
    public function totalSksPeriode($idPeriode = null): int
    {
        return $this->mataKuliahPeriode($idPeriode)
            ->sum(function ($pengambilan) {
                return $pengambilan->jadwalKelas->mataKuliah->sks ?? 0;
            });
    }

    /**
     * Update IPK mahasiswa.
     */
    public function updateIpk(): void
    {
        $nilaiLulus = $this->nilai()
            ->where('lulus', true)
            ->where('status', 'final')
            ->get();

        if ($nilaiLulus->count() > 0) {
            $totalBobot = $nilaiLulus->sum(function ($nilai) {
                $sks = $nilai->mataKuliah->sks ?? 0;
                return $sks * ($nilai->nilai_indeks ?? 0);
            });

            $totalSks = $nilaiLulus->sum(function ($nilai) {
                return $nilai->mataKuliah->sks ?? 0;
            });

            $ipkBaru = $totalSks > 0 ? $totalBobot / $totalSks : 0;

            $this->update([
                'ipk' => $ipkBaru,
                'total_sks' => $totalSks,
            ]);
        }
    }
}