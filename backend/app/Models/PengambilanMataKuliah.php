<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengambilanMataKuliah extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pengambilan_mata_kuliah';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_mahasiswa',
        'id_jadwal_kelas',
        'id_periode_akademik',
        'status',
        'tanggal_pengajuan',
        'tanggal_persetujuan',
        'tanggal_pembatalan',
        'disetujui_oleh',
        'catatan_persetujuan',
        'alasan_penolakan',
        'jumlah_hadir',
        'jumlah_tidak_hadir',
        'persentase_kehadiran',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_praktek',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'tanggal_pengajuan' => 'datetime',
        'tanggal_persetujuan' => 'datetime',
        'tanggal_pembatalan' => 'datetime',
        'jumlah_hadir' => 'integer',
        'jumlah_tidak_hadir' => 'integer',
        'persentase_kehadiran' => 'decimal:2',
        'nilai_tugas' => 'decimal:2',
        'nilai_uts' => 'decimal:2',
        'nilai_uas' => 'decimal:2',
        'nilai_praktek' => 'decimal:2',
    ];

    /**
     * Relasi ke model Mahasiswa.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model JadwalKelas.
     */
    public function jadwalKelas(): BelongsTo
    {
        return $this->belongsTo(JadwalKelas::class, 'id_jadwal_kelas');
    }

    /**
     * Relasi ke model PeriodeAkademik.
     */
    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model Pengguna yang menyetujui.
     */
    public function penyetuju(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'disetujui_oleh');
    }

    /**
     * Scope untuk status tertentu.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope untuk mahasiswa tertentu.
     */
    public function scopeMahasiswa($query, $idMahasiswa)
    {
        return $query->where('id_mahasiswa', $idMahasiswa);
    }

    /**
     * Scope untuk periode tertentu.
     */
    public function scopePeriode($query, $idPeriode)
    {
        return $query->where('id_periode_akademik', $idPeriode);
    }

    /**
     * Scope untuk pengambilan yang disetujui.
     */
    public function scopeDisetujui($query)
    {
        return $query->where('status', 'disetujui');
    }

    /**
     * Scope untuk pengambilan yang berjalan.
     */
    public function scopeBerjalan($query)
    {
        return $query->where('status', 'berjalan');
    }

    /**
     * Mendapatkan mata kuliah melalui jadwal kelas.
     */
    public function getMataKuliahAttribute()
    {
        return $this->jadwalKelas ? $this->jadwalKelas->mataKuliah : null;
    }

    /**
     * Setujui pengambilan mata kuliah.
     */
    public function setujui(Pengguna $penyetuju, string $catatan = null): void
    {
        $this->update([
            'status' => 'disetujui',
            'tanggal_persetujuan' => now(),
            'disetujui_oleh' => $penyetuju->id,
            'catatan_persetujuan' => $catatan,
        ]);

        // Update jumlah terdaftar di jadwal kelas
        $this->jadwalKelas->updateJumlahTerdaftar();
    }

    /**
     * Tolak pengambilan mata kuliah.
     */
    public function tolak(string $alasanPenolakan): void
    {
        $this->update([
            'status' => 'ditolak',
            'alasan_penolakan' => $alasanPenolakan,
        ]);
    }

    /**
     * Batalkan pengambilan mata kuliah.
     */
    public function batalkan(string $alasan = null): void
    {
        $this->update([
            'status' => 'dibatalkan',
            'tanggal_pembatalan' => now(),
            'alasan_penolakan' => $alasan,
        ]);

        // Update jumlah terdaftar di jadwal kelas
        $this->jadwalKelas->updateJumlahTerdaftar();
    }

    /**
     * Update kehadiran.
     */
    public function updateKehadiran(): void
    {
        $totalPertemuan = $this->jadwalKelas->kehadiran()
            ->where('id_mahasiswa', $this->id_mahasiswa)
            ->count();

        $hadir = $this->jadwalKelas->kehadiran()
            ->where('id_mahasiswa', $this->id_mahasiswa)
            ->where('status_kehadiran', 'hadir')
            ->count();

        $tidakHadir = $totalPertemuan - $hadir;
        $persentase = $totalPertemuan > 0 ? ($hadir / $totalPertemuan) * 100 : 0;

        $this->update([
            'jumlah_hadir' => $hadir,
            'jumlah_tidak_hadir' => $tidakHadir,
            'persentase_kehadiran' => $persentase,
        ]);
    }

    /**
     * Cek apakah memenuhi syarat kehadiran minimum.
     */
    public function memenuhinSyaratKehadiran(float $minimumPersentase = 75): bool
    {
        return $this->persentase_kehadiran >= $minimumPersentase;
    }

    /**
     * Hitung nilai akhir berdasarkan komponen nilai.
     */
    public function hitungNilaiAkhir(array $bobot = null): float
    {
        $bobotDefault = [
            'tugas' => 30,
            'uts' => 30,
            'uas' => 30,
            'praktek' => 10,
        ];

        $bobot = $bobot ?? $bobotDefault;

        $nilaiAkhir = 0;
        $totalBobot = 0;

        if ($this->nilai_tugas !== null && isset($bobot['tugas'])) {
            $nilaiAkhir += ($this->nilai_tugas * $bobot['tugas']);
            $totalBobot += $bobot['tugas'];
        }

        if ($this->nilai_uts !== null && isset($bobot['uts'])) {
            $nilaiAkhir += ($this->nilai_uts * $bobot['uts']);
            $totalBobot += $bobot['uts'];
        }

        if ($this->nilai_uas !== null && isset($bobot['uas'])) {
            $nilaiAkhir += ($this->nilai_uas * $bobot['uas']);
            $totalBobot += $bobot['uas'];
        }

        if ($this->nilai_praktek !== null && isset($bobot['praktek'])) {
            $nilaiAkhir += ($this->nilai_praktek * $bobot['praktek']);
            $totalBobot += $bobot['praktek'];
        }

        return $totalBobot > 0 ? $nilaiAkhir / $totalBobot : 0;
    }

    /**
     * Finalisasi ke tabel nilai.
     */
    public function finalisasiKeNilai(Pengguna $penginput): Nilai
    {
        $nilaiAkhir = $this->hitungNilaiAkhir();
        
        // Konversi ke nilai huruf dan indeks
        $nilaiHuruf = $this->konversiKeNilaiHuruf($nilaiAkhir);
        $nilaiIndeks = $this->konversiKeNilaiIndeks($nilaiHuruf);

        return Nilai::create([
            'id_mahasiswa' => $this->id_mahasiswa,
            'id_mata_kuliah' => $this->jadwalKelas->id_mata_kuliah,
            'id_periode_akademik' => $this->id_periode_akademik,
            'id_jadwal_kelas' => $this->id_jadwal_kelas,
            'nilai_tugas' => $this->nilai_tugas,
            'nilai_uts' => $this->nilai_uts,
            'nilai_uas' => $this->nilai_uas,
            'nilai_praktek' => $this->nilai_praktek,
            'nilai_kehadiran' => $this->persentase_kehadiran,
            'nilai_angka' => $nilaiAkhir,
            'nilai_huruf' => $nilaiHuruf,
            'nilai_indeks' => $nilaiIndeks,
            'lulus' => $nilaiHuruf !== 'E',
            'status' => 'final',
            'diinput_oleh' => $penginput->id,
            'tanggal_input' => now(),
        ]);
    }

    /**
     * Konversi nilai angka ke nilai huruf.
     */
    private function konversiKeNilaiHuruf(float $nilaiAngka): string
    {
        if ($nilaiAngka >= 80) return 'A';
        if ($nilaiAngka >= 75) return 'B+';
        if ($nilaiAngka >= 70) return 'B';
        if ($nilaiAngka >= 65) return 'C+';
        if ($nilaiAngka >= 60) return 'C';
        if ($nilaiAngka >= 55) return 'D';
        return 'E';
    }

    /**
     * Konversi nilai huruf ke nilai indeks.
     */
    private function konversiKeNilaiIndeks(string $nilaiHuruf): float
    {
        $mapping = [
            'A' => 4.0,
            'B+' => 3.5,
            'B' => 3.0,
            'C+' => 2.5,
            'C' => 2.0,
            'D' => 1.0,
            'E' => 0.0,
        ];

        return $mapping[$nilaiHuruf] ?? 0.0;
    }
}