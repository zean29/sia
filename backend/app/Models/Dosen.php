<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Dosen extends Model
{
    use HasFactory;

    protected $table = 'dosen';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_pengguna',
        'nomor_dosen',
        'nidn',
        'nip',
        'id_fakultas',
        'data_pribadi',
        'data_kontak',
        'kredensial_akademik',
        'status_kepegawaian',
        'jabatan_fungsional',
        'tanggal_mulai_kerja',
        'tanggal_selesai_kerja',
        'beban_mengajar_min',
        'beban_mengajar_max',
        'bidang_keahlian',
        'sertifikasi',
        'status',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'data_pribadi' => 'array',
        'data_kontak' => 'array',
        'kredensial_akademik' => 'array',
        'tanggal_mulai_kerja' => 'date',
        'tanggal_selesai_kerja' => 'date',
        'beban_mengajar_min' => 'integer',
        'beban_mengajar_max' => 'integer',
        'bidang_keahlian' => 'array',
        'sertifikasi' => 'array',
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
     * Relasi ke model JadwalKelas.
     */
    public function jadwalKelas(): HasMany
    {
        return $this->hasMany(JadwalKelas::class, 'id_dosen');
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
     * Scope untuk dosen aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    /**
     * Scope berdasarkan fakultas.
     */
    public function scopeFakultas($query, $idFakultas)
    {
        return $query->where('id_fakultas', $idFakultas);
    }

    /**
     * Scope berdasarkan jabatan fungsional.
     */
    public function scopeJabatanFungsional($query, $jabatan)
    {
        return $query->where('jabatan_fungsional', $jabatan);
    }

    /**
     * Mendapatkan nama lengkap dosen.
     */
    public function getNamaLengkapAttribute(): string
    {
        return $this->data_pribadi['nama_lengkap'] ?? '';
    }

    /**
     * Mendapatkan gelar lengkap dosen.
     */
    public function getGelarLengkapAttribute(): string
    {
        $nama = $this->getNamaLengkapAttribute();
        $gelarDepan = $this->kredensial_akademik['gelar_depan'] ?? '';
        $gelarBelakang = $this->kredensial_akademik['gelar_belakang'] ?? '';

        $namaLengkap = trim($gelarDepan . ' ' . $nama . ' ' . $gelarBelakang);
        return $namaLengkap;
    }

    /**
     * Mendapatkan beban mengajar saat ini.
     */
    public function getBebanMengajarSaatIniAttribute(): int
    {
        $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
        
        if (!$periodeBerjalan) {
            return 0;
        }

        return $this->jadwalKelas()
            ->where('id_periode_akademik', $periodeBerjalan->id)
            ->where('status', '!=', 'dibatalkan')
            ->withCount(['mataKuliah' => function ($query) {
                $query->select(\DB::raw('sum(sks) as total_sks'));
            }])
            ->sum('mata_kuliah_count') ?? 0;
    }

    /**
     * Cek apakah dosen bisa mengajar mata kuliah tambahan.
     */
    public function bisaMengajarTambahan(): bool
    {
        return $this->getBebanMengajarSaatIniAttribute() < $this->beban_mengajar_max;
    }

    /**
     * Mendapatkan mata kuliah yang diajar pada periode tertentu.
     */
    public function mataKuliahPeriode($idPeriode = null)
    {
        $query = $this->jadwalKelas()->with('mataKuliah');

        if ($idPeriode) {
            $query->where('id_periode_akademik', $idPeriode);
        } else {
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            if ($periodeBerjalan) {
                $query->where('id_periode_akademik', $periodeBerjalan->id);
            }
        }

        return $query->get();
    }

    /**
     * Mendapatkan masa kerja dalam tahun.
     */
    public function getMasaKerjaAttribute(): int
    {
        $tanggalSelesai = $this->tanggal_selesai_kerja ?? now();
        return $this->tanggal_mulai_kerja->diffInYears($tanggalSelesai);
    }
}