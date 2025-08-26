<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class PeriodeAkademik extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'periode_akademik';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'kode_periode',
        'nama_periode',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_periode',
        'status',
        'batas_pengambilan_krs',
        'batas_pembayaran',
        'mulai_perkuliahan',
        'selesai_perkuliahan',
        'mulai_uts',
        'selesai_uts',
        'mulai_uas',
        'selesai_uas',
        'kalender_akademik',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'batas_pengambilan_krs' => 'date',
        'batas_pembayaran' => 'date',
        'mulai_perkuliahan' => 'date',
        'selesai_perkuliahan' => 'date',
        'mulai_uts' => 'date',
        'selesai_uts' => 'date',
        'mulai_uas' => 'date',
        'selesai_uas' => 'date',
        'kalender_akademik' => 'array',
    ];

    /**
     * Relasi ke model JadwalKelas.
     */
    public function jadwalKelas(): HasMany
    {
        return $this->hasMany(JadwalKelas::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model PengambilanMataKuliah.
     */
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model Nilai.
     */
    public function nilai(): HasMany
    {
        return $this->hasMany(Nilai::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model RekamAkademik.
     */
    public function rekamAkademik(): HasMany
    {
        return $this->hasMany(RekamAkademik::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model Pembayaran.
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'id_periode_akademik');
    }

    /**
     * Scope untuk periode aktif.
     */
    public function scopeAktif($query)
    {
        return $query->where('status', 'berjalan');
    }

    /**
     * Scope untuk periode berdasarkan jenis.
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis_periode', $jenis);
    }

    /**
     * Scope untuk periode berdasarkan tahun.
     */
    public function scopeTahun($query, $tahun)
    {
        return $query->whereYear('tanggal_mulai', $tahun);
    }

    /**
     * Mendapatkan periode akademik yang sedang berjalan.
     */
    public static function sedangBerjalan()
    {
        return static::where('status', 'berjalan')->first();
    }

    /**
     * Cek apakah periode masih dalam masa pengambilan KRS.
     */
    public function dalamMasaPengambilanKrs(): bool
    {
        return $this->batas_pengambilan_krs && 
               Carbon::now()->lte($this->batas_pengambilan_krs) &&
               $this->status === 'berjalan';
    }

    /**
     * Cek apakah periode masih dalam masa pembayaran.
     */
    public function dalamMasaPembayaran(): bool
    {
        return $this->batas_pembayaran && 
               Carbon::now()->lte($this->batas_pembayaran) &&
               $this->status === 'berjalan';
    }

    /**
     * Cek apakah periode sedang dalam masa perkuliahan.
     */
    public function dalamMasaPerkuliahan(): bool
    {
        $sekarang = Carbon::now();
        return $this->mulai_perkuliahan && 
               $this->selesai_perkuliahan &&
               $sekarang->gte($this->mulai_perkuliahan) &&
               $sekarang->lte($this->selesai_perkuliahan) &&
               $this->status === 'berjalan';
    }

    /**
     * Mendapatkan durasi periode dalam hari.
     */
    public function getDurasiHariAttribute(): int
    {
        return $this->tanggal_mulai->diffInDays($this->tanggal_selesai);
    }

    /**
     * Mendapatkan tahun akademik.
     */
    public function getTahunAkademikAttribute(): string
    {
        $tahunMulai = $this->tanggal_mulai->year;
        $tahunSelesai = $this->tanggal_selesai->year;
        
        if ($tahunMulai === $tahunSelesai) {
            return (string) $tahunMulai;
        }
        
        return $tahunMulai . '/' . $tahunSelesai;
    }
}