<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MataKuliah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mata_kuliah';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'kode_mata_kuliah',
        'nama_mata_kuliah',
        'id_program_studi',
        'sks',
        'jenis',
        'semester_rekomendasi',
        'prasyarat',
        'minimal_ipk',
        'minimal_semester',
        'deskripsi',
        'tujuan_pembelajaran',
        'capaian_pembelajaran',
        'materi_pembelajaran',
        'metode_penilaian',
        'referensi',
        'kapasitas_kelas',
        'minimal_peserta',
        'tersedia_online',
        'status',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'sks' => 'integer',
        'semester_rekomendasi' => 'integer',
        'prasyarat' => 'array',
        'minimal_ipk' => 'decimal:2',
        'minimal_semester' => 'integer',
        'tujuan_pembelajaran' => 'array',
        'capaian_pembelajaran' => 'array',
        'materi_pembelajaran' => 'array',
        'metode_penilaian' => 'array',
        'referensi' => 'array',
        'kapasitas_kelas' => 'integer',
        'minimal_peserta' => 'integer',
        'tersedia_online' => 'boolean',
    ];

    /**
     * Relasi ke model ProgramStudi.
     */
    public function programStudi(): BelongsTo
    {
        return $this->belongsTo(ProgramStudi::class, 'id_program_studi');
    }

    /**
     * Relasi ke model JadwalKelas.
     */
    public function jadwalKelas(): HasMany
    {
        return $this->hasMany(JadwalKelas::class, 'id_mata_kuliah');
    }

    /**
     * Relasi ke model PengambilanMataKuliah.
     */
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_mata_kuliah');
    }

    /**
     * Relasi ke model Nilai.
     */
    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'id_mata_kuliah');
    }

    /**
     * Relasi polimorfik ke model IntegrasiPddikti.
     */
    public function integrasiPddikti(): MorphMany
    {
        return $this->morphMany(IntegrasiPddikti::class, 'entitas');
    }

    /**
     * Scope untuk mata kuliah aktif.
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
     * Scope berdasarkan jenis mata kuliah.
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }

    /**
     * Scope berdasarkan semester.
     */
    public function scopeSemester($query, $semester)
    {
        return $query->where('semester_rekomendasi', $semester);
    }

    /**
     * Scope untuk mata kuliah wajib.
     */
    public function scopeWajib($query)
    {
        return $query->where('jenis', 'wajib');
    }

    /**
     * Scope untuk mata kuliah pilihan.
     */
    public function scopePilihan($query)
    {
        return $query->where('jenis', 'pilihan');
    }

    /**
     * Mendapatkan mata kuliah prasyarat.
     */
    public function getMataKuliahPrasyaratAttribute()
    {
        if (empty($this->prasyarat) || !isset($this->prasyarat['mata_kuliah'])) {
            return collect();
        }

        $kodeMataKuliah = $this->prasyarat['mata_kuliah'];
        
        return static::whereIn('kode_mata_kuliah', $kodeMataKuliah)->get();
    }

    /**
     * Cek apakah mahasiswa memenuhi prasyarat.
     */
    public function memenuhinPrasyarat(Mahasiswa $mahasiswa): bool
    {
        // Cek prasyarat IPK
        if ($this->minimal_ipk && $mahasiswa->ipk < $this->minimal_ipk) {
            return false;
        }

        // Cek prasyarat semester
        if ($this->minimal_semester && $mahasiswa->semester_aktif < $this->minimal_semester) {
            return false;
        }

        // Cek prasyarat mata kuliah
        if (!empty($this->prasyarat) && isset($this->prasyarat['mata_kuliah'])) {
            $mataKuliahPrasyarat = $this->prasyarat['mata_kuliah'];
            
            $mataKuliahLulus = $mahasiswa->nilai()
                ->where('lulus', true)
                ->where('status', 'final')
                ->whereHas('mataKuliah', function ($query) use ($mataKuliahPrasyarat) {
                    $query->whereIn('kode_mata_kuliah', $mataKuliahPrasyarat);
                })
                ->count();

            if ($mataKuliahLulus < count($mataKuliahPrasyarat)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Mendapatkan jumlah mahasiswa yang mengambil pada periode tertentu.
     */
    public function jumlahPesertaPeriode($idPeriode = null): int
    {
        $query = $this->jadwalKelas()
            ->withCount(['pengambilanMataKuliah' => function ($q) {
                $q->where('status', '!=', 'dibatalkan');
            }]);

        if ($idPeriode) {
            $query->where('id_periode_akademik', $idPeriode);
        } else {
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            if ($periodeBerjalan) {
                $query->where('id_periode_akademik', $periodeBerjalan->id);
            }
        }

        return $query->sum('pengambilan_mata_kuliah_count') ?? 0;
    }

    /**
     * Cek apakah mata kuliah tersedia untuk diambil pada periode tertentu.
     */
    public function tersediaPadaPeriode($idPeriode = null): bool
    {
        $query = $this->jadwalKelas()
            ->where('status', 'terbuka');

        if ($idPeriode) {
            $query->where('id_periode_akademik', $idPeriode);
        } else {
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            if ($periodeBerjalan) {
                $query->where('id_periode_akademik', $periodeBerjalan->id);
            }
        }

        return $query->exists();
    }

    /**
     * Mendapatkan kelas yang tersedia dengan sisa kapasitas.
     */
    public function kelasYangTersedia($idPeriode = null)
    {
        $query = $this->jadwalKelas()
            ->where('status', 'terbuka')
            ->whereColumn('jumlah_terdaftar', '<', 'kapasitas_maksimal');

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
}