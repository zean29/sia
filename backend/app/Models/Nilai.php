<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nilai extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nilai';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'id_mahasiswa',
        'id_mata_kuliah',
        'id_periode_akademik',
        'id_jadwal_kelas',
        'nilai_tugas',
        'nilai_uts',
        'nilai_uas',
        'nilai_praktek',
        'nilai_kehadiran',
        'nilai_angka',
        'nilai_huruf',
        'nilai_indeks',
        'status',
        'lulus',
        'diinput_oleh',
        'tanggal_input',
        'diverifikasi_oleh',
        'tanggal_verifikasi',
        'catatan_dosen',
        'riwayat_perubahan',
    ];

    /**
     * Atribut yang harus di-cast.
     */
    protected $casts = [
        'nilai_tugas' => 'decimal:2',
        'nilai_uts' => 'decimal:2',
        'nilai_uas' => 'decimal:2',
        'nilai_praktek' => 'decimal:2',
        'nilai_kehadiran' => 'decimal:2',
        'nilai_angka' => 'decimal:2',
        'nilai_indeks' => 'decimal:2',
        'lulus' => 'boolean',
        'tanggal_input' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'riwayat_perubahan' => 'array',
    ];

    /**
     * Relasi ke model Mahasiswa.
     */
    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    /**
     * Relasi ke model MataKuliah.
     */
    public function mataKuliah(): BelongsTo
    {
        return $this->belongsTo(MataKuliah::class, 'id_mata_kuliah');
    }

    /**
     * Relasi ke model PeriodeAkademik.
     */
    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'id_periode_akademik');
    }

    /**
     * Relasi ke model JadwalKelas.
     */
    public function jadwalKelas(): BelongsTo
    {
        return $this->belongsTo(JadwalKelas::class, 'id_jadwal_kelas');
    }

    /**
     * Relasi ke model Pengguna yang menginput.
     */
    public function penginput(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diinput_oleh');
    }

    /**
     * Relasi ke model Pengguna yang memverifikasi.
     */
    public function penerifikasi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh');
    }

    /**
     * Scope untuk nilai final.
     */
    public function scopeFinal($query)
    {
        return $query->where('status', 'final');
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
     * Scope untuk mata kuliah tertentu.
     */
    public function scopeMataKuliah($query, $idMataKuliah)
    {
        return $query->where('id_mata_kuliah', $idMataKuliah);
    }

    /**
     * Scope untuk nilai lulus.
     */
    public function scopeLulus($query)
    {
        return $query->where('lulus', true);
    }

    /**
     * Scope untuk nilai tidak lulus.
     */
    public function scopeTidakLulus($query)
    {
        return $query->where('lulus', false);
    }

    /**
     * Finalisasi nilai.
     */
    public function finalisasi(Pengguna $penerifikasi): void
    {
        $riwayatBaru = $this->riwayat_perubahan ?? [];
        $riwayatBaru[] = [
            'aksi' => 'finalisasi',
            'oleh' => $penerifikasi->nama_pengguna,
            'tanggal' => now()->toISOString(),
            'status_lama' => $this->status,
            'status_baru' => 'final',
        ];

        $this->update([
            'status' => 'final',
            'diverifikasi_oleh' => $penerifikasi->id,
            'tanggal_verifikasi' => now(),
            'riwayat_perubahan' => $riwayatBaru,
        ]);

        // Update IPK mahasiswa
        $this->mahasiswa->updateIpk();
    }

    /**
     * Revisi nilai.
     */
    public function revisi(array $nilaiBaru, string $alasan, Pengguna $pengubah): void
    {
        $riwayatBaru = $this->riwayat_perubahan ?? [];
        $riwayatBaru[] = [
            'aksi' => 'revisi',
            'oleh' => $pengubah->nama_pengguna,
            'tanggal' => now()->toISOString(),
            'alasan' => $alasan,
            'nilai_lama' => [
                'nilai_angka' => $this->nilai_angka,
                'nilai_huruf' => $this->nilai_huruf,
                'nilai_indeks' => $this->nilai_indeks,
            ],
            'nilai_baru' => $nilaiBaru,
        ];

        $nilaiUpdate = array_merge($nilaiBaru, [
            'status' => 'revisi',
            'riwayat_perubahan' => $riwayatBaru,
        ]);

        $this->update($nilaiUpdate);

        // Update IPK mahasiswa jika nilai sudah final
        if ($this->status === 'final') {
            $this->mahasiswa->updateIpk();
        }
    }

    /**
     * Mendapatkan bobot SKS untuk perhitungan IPK.
     */
    public function getBobotSksAttribute(): float
    {
        $sks = $this->mataKuliah->sks ?? 0;
        return $sks * $this->nilai_indeks;
    }

    /**
     * Mendapatkan keterangan lulus/tidak lulus.
     */
    public function getKeteranganLulusAttribute(): string
    {
        return $this->lulus ? 'Lulus' : 'Tidak Lulus';
    }

    /**
     * Mendapatkan grade point untuk mata kuliah ini.
     */
    public function getGradePointAttribute(): float
    {
        return $this->getBobotSksAttribute();
    }

    /**
     * Konversi nilai angka ke huruf (static method).
     */
    public static function konversiAngkaKeHuruf(float $nilaiAngka): string
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
     * Konversi nilai huruf ke indeks (static method).
     */
    public static function konversiHurufKeIndeks(string $nilaiHuruf): float
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

    /**
     * Cek apakah nilai dapat direvisi.
     */
    public function bisaDirevisi(): bool
    {
        // Nilai bisa direvisi jika statusnya final dan belum lewat batas waktu revisi
        // Implementasi batas waktu bisa disesuaikan dengan kebijakan
        return $this->status === 'final';
    }

    /**
     * Boot method untuk handle events.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($nilai) {
            // Auto calculate nilai huruf dan indeks jika nilai angka berubah
            if ($nilai->isDirty('nilai_angka') && $nilai->nilai_angka !== null) {
                $nilai->nilai_huruf = static::konversiAngkaKeHuruf($nilai->nilai_angka);
                $nilai->nilai_indeks = static::konversiHurufKeIndeks($nilai->nilai_huruf);
                $nilai->lulus = $nilai->nilai_huruf !== 'E';
            }
        });

        static::saved(function ($nilai) {
            // Update IPK mahasiswa setelah nilai disimpan (jika final)
            if ($nilai->status === 'final') {
                $nilai->mahasiswa->updateIpk();
            }
        });
    }
}