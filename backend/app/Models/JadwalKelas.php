<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class JadwalKelas extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'jadwal_kelas';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_mata_kuliah',
        'id_dosen',
        'id_periode_akademik',
        'nama_kelas',
        'ruang_kelas',
        'jenis_kelas',
        'waktu_jadwal',
        'kapasitas_maksimal',
        'jumlah_terdaftar',
        'kapasitas_waiting_list',
        'metode_pembelajaran',
        'link_online',
        'peralatan_diperlukan',
        'status',
        'tanggal_buka_pendaftaran',
        'tanggal_tutup_pendaftaran',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'waktu_jadwal' => 'array',
        'kapasitas_maksimal' => 'integer',
        'jumlah_terdaftar' => 'integer',
        'kapasitas_waiting_list' => 'integer',
        'peralatan_diperlukan' => 'array',
        'tanggal_buka_pendaftaran' => 'date',
        'tanggal_tutup_pendaftaran' => 'date',
    ];

    /**
     * Relasi ke model MataKuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah');
    }

    /**
     * Relasi ke model Dosen.
     */
    public function dosen(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'id_dosen');
    }

    /**
     * Relasi ke model PeriodeAkademik.
     */
    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model PengambilanMataKuliah.
     */
    public function pengambilanMataKuliah(): HasMany
    {
        return $this->hasMany(PengambilanMataKuliah::class, 'id_jadwal_kelas');
    }

    /**
     * Relasi ke model Kehadiran.
     */
    public function kehadiran(): HasMany
    {
        return $this->hasMany(Kehadiran::class, 'id_jadwal_kelas');
    }

    /**
     * Scope untuk kelas terbuka.
     */
    public function scopeTerbuka($query)
    {
        return $query->where('status', 'terbuka');
    }

    /**
     * Scope untuk periode tertentu.
     */
    public function scopePeriode($query, $idPeriode)
    {
        return $query->where('id_periode_akademik', $idPeriode);
    }

    /**
     * Scope untuk dosen tertentu.
     */
    public function scopeDosen($query, $idDosen)
    {
        return $query->where('id_dosen', $idDosen);
    }

    /**
     * Mendapatkan sisa kapasitas kelas.
     */
    public function getSisaKapasitasAttribute(): int
    {
        return max(0, $this->kapasitas_maksimal - $this->jumlah_terdaftar);
    }

    /**
     * Cek apakah kelas masih bisa diambil.
     */
    public function masihBisaDiambil(): bool
    {
        return $this->status === 'terbuka' && 
               $this->getSisaKapasitasAttribute() > 0 &&
               ($this->tanggal_tutup_pendaftaran === null || 
                now()->lte($this->tanggal_tutup_pendaftaran));
    }

    /**
     * Mendapatkan hari dan jam kelas.
     */
    public function getJadwalTeksAttribute(): string
    {
        if (empty($this->waktu_jadwal)) {
            return 'Belum dijadwalkan';
        }

        $hari = $this->waktu_jadwal['hari'] ?? '';
        $jamMulai = $this->waktu_jadwal['jam_mulai'] ?? '';
        $jamSelesai = $this->waktu_jadwal['jam_selesai'] ?? '';

        return "{$hari}, {$jamMulai} - {$jamSelesai}";
    }

    /**
     * Mendapatkan informasi ruang lengkap.
     */
    public function getRuangLengkapAttribute(): string
    {
        $ruang = $this->ruang_kelas ?? 'TBA';
        $gedung = $this->waktu_jadwal['gedung'] ?? '';
        $lantai = $this->waktu_jadwal['lantai'] ?? '';

        if ($gedung && $lantai) {
            return "{$ruang} (Gedung {$gedung}, Lantai {$lantai})";
        } elseif ($gedung) {
            return "{$ruang} (Gedung {$gedung})";
        }

        return $ruang;
    }

    /**
     * Update jumlah terdaftar.
     */
    public function updateJumlahTerdaftar(): void
    {
        $jumlah = $this->pengambilanMataKuliah()
            ->whereIn('status', ['disetujui', 'berjalan', 'selesai'])
            ->count();

        $this->update(['jumlah_terdaftar' => $jumlah]);
    }

    /**
     * Mendapatkan mahasiswa yang terdaftar.
     */
    public function mahasiswaTerdaftar()
    {
        return $this->pengambilanMataKuliah()
            ->with(['mahasiswa.pengguna'])
            ->whereIn('status', ['disetujui', 'berjalan', 'selesai'])
            ->get()
            ->pluck('mahasiswa');
    }

    /**
     * Cek konflik jadwal dengan jadwal lain.
     */
    public function cekKonflikJadwal($excludeId = null): bool
    {
        if (empty($this->waktu_jadwal)) {
            return false;
        }

        $query = static::where('id_periode_akademik', $this->id_periode_akademik)
            ->where('id_dosen', $this->id_dosen)
            ->where('status', '!=', 'dibatalkan');

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->whereJsonContains('waktu_jadwal->hari', $this->waktu_jadwal['hari'])
            ->where(function ($q) {
                $jamMulai = $this->waktu_jadwal['jam_mulai'];
                $jamSelesai = $this->waktu_jadwal['jam_selesai'];
                
                $q->whereBetween(\DB::raw("JSON_UNQUOTE(JSON_EXTRACT(waktu_jadwal, '$.jam_mulai'))"), [$jamMulai, $jamSelesai])
                  ->orWhereBetween(\DB::raw("JSON_UNQUOTE(JSON_EXTRACT(waktu_jadwal, '$.jam_selesai'))"), [$jamMulai, $jamSelesai]);
            })
            ->exists();
    }
}