<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pembayaran extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pembayaran';

    protected $fillable = [
        'id_mahasiswa', 'nomor_pembayaran', 'id_periode_akademik', 'jenis_pembayaran',
        'jumlah_tagihan', 'jumlah_bayar', 'sisa_tagihan', 'denda', 'tanggal_tagihan',
        'tanggal_jatuh_tempo', 'tanggal_bayar', 'status_pembayaran', 'metode_pembayaran',
        'detail_pembayaran', 'nomor_referensi', 'keterangan', 'diverifikasi_oleh',
        'tanggal_verifikasi', 'catatan_verifikasi', 'file_bukti_bayar'
    ];

    protected $casts = [
        'jumlah_tagihan' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'sisa_tagihan' => 'decimal:2',
        'denda' => 'decimal:2',
        'tanggal_tagihan' => 'date',
        'tanggal_jatuh_tempo' => 'date',
        'tanggal_bayar' => 'datetime',
        'tanggal_verifikasi' => 'datetime',
        'detail_pembayaran' => 'array',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class, 'id_mahasiswa');
    }

    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class, 'id_periode_akademik');
    }

    public function penerifikasi(): BelongsTo
    {
        return $this->belongsTo(Pengguna::class, 'diverifikasi_oleh');
    }

    /**
     * Scope untuk pembayaran belum lunas.
     */
    public function scopeBelumLunas($query)
    {
        return $query->whereIn('status_pembayaran', ['belum_bayar', 'sebagian']);
    }

    /**
     * Scope untuk pembayaran lunas.
     */
    public function scopeLunas($query)
    {
        return $query->where('status_pembayaran', 'lunas');
    }

    /**
     * Scope untuk pembayaran jatuh tempo.
     */
    public function scopeJatuhTempo($query)
    {
        return $query->where('tanggal_jatuh_tempo', '<', now())
                    ->whereIn('status_pembayaran', ['belum_bayar', 'sebagian']);
    }

    /**
     * Proses pembayaran.
     */
    public function prosesPembayaran(float $jumlahBayar, array $detailPembayaran = []): void
    {
        $sisaTagihanBaru = max(0, $this->sisa_tagihan - $jumlahBayar);
        $totalBayarBaru = $this->jumlah_bayar + $jumlahBayar;

        $statusBaru = 'sebagian';
        if ($sisaTagihanBaru == 0) {
            $statusBaru = 'lunas';
        } elseif ($totalBayarBaru == 0) {
            $statusBaru = 'belum_bayar';
        }

        $this->update([
            'jumlah_bayar' => $totalBayarBaru,
            'sisa_tagihan' => $sisaTagihanBaru,
            'status_pembayaran' => $statusBaru,
            'tanggal_bayar' => now(),
            'detail_pembayaran' => array_merge($this->detail_pembayaran ?? [], $detailPembayaran),
        ]);
    }

    /**
     * Hitung denda keterlambatan.
     */
    public function hitungDenda(float $persentaseDenda = 2): float
    {
        if ($this->tanggal_jatuh_tempo >= now() || $this->status_pembayaran === 'lunas') {
            return 0;
        }

        $hariTerlambat = now()->diffInDays($this->tanggal_jatuh_tempo);
        $dendaPerHari = ($this->sisa_tagihan * $persentaseDenda) / 100;
        
        return $hariTerlambat * $dendaPerHari;
    }
}