<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\JadwalKelas;
use App\Models\Pembayaran;
use App\Models\PeriodeAkademik;
use App\Models\PengambilanMataKuliah;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard data based on user role.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $pengguna = auth()->user();

            switch ($pengguna->peran) {
                case 'admin':
                    return $this->dashboardAdmin();
                case 'mahasiswa':
                    return $this->dashboardMahasiswa($pengguna->mahasiswa);
                case 'dosen':
                    return $this->dashboardDosen($pengguna->dosen);
                case 'staf':
                    return $this->dashboardStaf($pengguna->staf);
                default:
                    return response()->json([
                        'sukses' => false,
                        'pesan' => 'Peran tidak valid'
                    ], 403);
            }

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat memuat dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dashboard for admin users.
     */
    private function dashboardAdmin(): JsonResponse
    {
        $periodeBerjalan = PeriodeAkademik::sedangBerjalan();

        $statistik = [
            'total_mahasiswa' => Mahasiswa::count(),
            'mahasiswa_aktif' => Mahasiswa::where('status', 'aktif')->count(),
            'total_dosen' => Dosen::count(),
            'dosen_aktif' => Dosen::where('status', 'aktif')->count(),
            'total_mata_kuliah' => MataKuliah::where('status', 'aktif')->count(),
            'periode_berjalan' => $periodeBerjalan ? $periodeBerjalan->nama_periode : 'Tidak ada',
        ];

        if ($periodeBerjalan) {
            $statistik['kelas_periode_ini'] = JadwalKelas::where('id_periode_akademik', $periodeBerjalan->id)->count();
            $statistik['mahasiswa_terdaftar_periode_ini'] = PengambilanMataKuliah::where('id_periode_akademik', $periodeBerjalan->id)
                ->whereIn('status', ['disetujui', 'berjalan'])
                ->distinct('id_mahasiswa')
                ->count();
        }

        // Statistik per fakultas
        $statistikFakultas = DB::table('fakultas')
            ->leftJoin('program_studi', 'fakultas.id', '=', 'program_studi.id_fakultas')
            ->leftJoin('mahasiswa', 'program_studi.id', '=', 'mahasiswa.id_program_studi')
            ->select(
                'fakultas.nama_fakultas',
                'fakultas.kode_fakultas',
                DB::raw('COUNT(DISTINCT program_studi.id) as jumlah_prodi'),
                DB::raw('COUNT(CASE WHEN mahasiswa.status = "aktif" THEN mahasiswa.id END) as jumlah_mahasiswa_aktif')
            )
            ->where('fakultas.status_aktif', 'aktif')
            ->groupBy('fakultas.id', 'fakultas.nama_fakultas', 'fakultas.kode_fakultas')
            ->get();

        // Grafik registrasi mahasiswa per bulan (12 bulan terakhir)
        $registrasiPerBulan = DB::table('mahasiswa')
            ->select(
                DB::raw('MONTH(tanggal_masuk) as bulan'),
                DB::raw('YEAR(tanggal_masuk) as tahun'),
                DB::raw('COUNT(*) as jumlah')
            )
            ->where('tanggal_masuk', '>=', now()->subMonths(12))
            ->groupBy('tahun', 'bulan')
            ->orderBy('tahun')
            ->orderBy('bulan')
            ->get();

        // Pembayaran tertunggak
        $pembayaranTertunggak = Pembayaran::where('status_pembayaran', '!=', 'lunas')
            ->where('tanggal_jatuh_tempo', '<', now())
            ->count();

        return response()->json([
            'sukses' => true,
            'data' => [
                'statistik_umum' => $statistik,
                'statistik_fakultas' => $statistikFakultas,
                'registrasi_per_bulan' => $registrasiPerBulan,
                'pembayaran_tertunggak' => $pembayaranTertunggak,
                'notifikasi' => $this->getNotifikasiAdmin(),
            ]
        ]);
    }

    /**
     * Dashboard for student users.
     */
    private function dashboardMahasiswa($mahasiswa): JsonResponse
    {
        $periodeBerjalan = PeriodeAkademik::sedangBerjalan();

        // Informasi akademik mahasiswa
        $infoAkademik = [
            'nim' => $mahasiswa->nim,
            'nama_lengkap' => $mahasiswa->data_pribadi['nama_lengkap'],
            'program_studi' => $mahasiswa->programStudi->nama_program_studi,
            'fakultas' => $mahasiswa->programStudi->fakultas->nama_fakultas,
            'semester_aktif' => $mahasiswa->semester_aktif,
            'ipk' => $mahasiswa->ipk,
            'total_sks' => $mahasiswa->total_sks,
            'status' => $mahasiswa->status,
        ];

        $jadwalKuliah = [];
        $pengambilanSemesterIni = [];
        
        if ($periodeBerjalan) {
            // Jadwal kuliah semester ini
            $jadwalKuliah = $mahasiswa->pengambilanMataKuliah()
                ->with(['jadwalKelas.mataKuliah', 'jadwalKelas.dosen.pengguna'])
                ->where('id_periode_akademik', $periodeBerjalan->id)
                ->whereIn('status', ['disetujui', 'berjalan'])
                ->get()
                ->map(function ($pengambilan) {
                    $jadwal = $pengambilan->jadwalKelas;
                    return [
                        'mata_kuliah' => $jadwal->mataKuliah->nama_mata_kuliah,
                        'kode' => $jadwal->mataKuliah->kode_mata_kuliah,
                        'sks' => $jadwal->mataKuliah->sks,
                        'kelas' => $jadwal->nama_kelas,
                        'dosen' => $jadwal->dosen->data_pribadi['nama_lengkap'],
                        'ruang' => $jadwal->ruang_kelas,
                        'jadwal' => $jadwal->getJadwalTeksAttribute(),
                    ];
                });

            // Summary pengambilan semester ini
            $pengambilanSemesterIni = [
                'total_sks' => $mahasiswa->totalSksPeriode($periodeBerjalan->id),
                'jumlah_mata_kuliah' => $jadwalKuliah->count(),
                'status_krs' => $periodeBerjalan->dalamMasaPengambilanKrs() ? 'bisa_diubah' : 'terkunci',
            ];
        }

        // Status pembayaran
        $statusPembayaran = [
            'total_tagihan' => $mahasiswa->pembayaran()->sum('jumlah_tagihan'),
            'total_bayar' => $mahasiswa->pembayaran()->sum('jumlah_bayar'),
            'tagihan_tertunggak' => $mahasiswa->pembayaran()
                ->where('status_pembayaran', '!=', 'lunas')
                ->sum('sisa_tagihan'),
        ];

        // Nilai semester terakhir
        $nilaiTerakhir = $mahasiswa->nilai()
            ->with('mataKuliah')
            ->where('status', 'final')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($nilai) {
                return [
                    'mata_kuliah' => $nilai->mataKuliah->nama_mata_kuliah,
                    'sks' => $nilai->mataKuliah->sks,
                    'nilai_huruf' => $nilai->nilai_huruf,
                    'nilai_angka' => $nilai->nilai_angka,
                ];
            });

        return response()->json([
            'sukses' => true,
            'data' => [
                'info_akademik' => $infoAkademik,
                'jadwal_kuliah' => $jadwalKuliah,
                'pengambilan_semester_ini' => $pengambilanSemesterIni,
                'status_pembayaran' => $statusPembayaran,
                'nilai_terakhir' => $nilaiTerakhir,
                'periode_berjalan' => $periodeBerjalan ? $periodeBerjalan->nama_periode : null,
            ]
        ]);
    }

    /**
     * Dashboard for lecturer users.
     */
    private function dashboardDosen($dosen): JsonResponse
    {
        $periodeBerjalan = PeriodeAkademik::sedangBerjalan();

        $infoDosen = [
            'nidn' => $dosen->nidn,
            'nama_lengkap' => $dosen->data_pribadi['nama_lengkap'],
            'fakultas' => $dosen->fakultas->nama_fakultas,
            'jabatan_fungsional' => $dosen->jabatan_fungsional,
        ];

        $jadwalMengajar = [];
        $statistikMengajar = [
            'beban_sks_semester_ini' => 0,
            'jumlah_kelas' => 0,
            'total_mahasiswa' => 0,
        ];

        if ($periodeBerjalan) {
            $kelasYangDiajar = $dosen->jadwalKelas()
                ->with(['mataKuliah'])
                ->where('id_periode_akademik', $periodeBerjalan->id)
                ->where('status', '!=', 'dibatalkan')
                ->get();

            $jadwalMengajar = $kelasYangDiajar->map(function ($jadwal) {
                return [
                    'mata_kuliah' => $jadwal->mataKuliah->nama_mata_kuliah,
                    'kode' => $jadwal->mataKuliah->kode_mata_kuliah,
                    'kelas' => $jadwal->nama_kelas,
                    'sks' => $jadwal->mataKuliah->sks,
                    'ruang' => $jadwal->ruang_kelas,
                    'jadwal' => $jadwal->getJadwalTeksAttribute(),
                    'jumlah_mahasiswa' => $jadwal->jumlah_terdaftar,
                ];
            });

            $statistikMengajar = [
                'beban_sks_semester_ini' => $kelasYangDiajar->sum(function ($jadwal) {
                    return $jadwal->mataKuliah->sks;
                }),
                'jumlah_kelas' => $kelasYangDiajar->count(),
                'total_mahasiswa' => $kelasYangDiajar->sum('jumlah_terdaftar'),
            ];
        }

        // Daftar nilai yang perlu diinput
        $nilaiPendingInput = DB::table('pengambilan_mata_kuliah')
            ->join('jadwal_kelas', 'pengambilan_mata_kuliah.id_jadwal_kelas', '=', 'jadwal_kelas.id')
            ->join('mata_kuliah', 'jadwal_kelas.id_mata_kuliah', '=', 'mata_kuliah.id')
            ->leftJoin('nilai', function ($join) {
                $join->on('pengambilan_mata_kuliah.id_mahasiswa', '=', 'nilai.id_mahasiswa')
                     ->on('pengambilan_mata_kuliah.id_mata_kuliah', '=', 'nilai.id_mata_kuliah')
                     ->on('pengambilan_mata_kuliah.id_periode_akademik', '=', 'nilai.id_periode_akademik');
            })
            ->where('jadwal_kelas.id_dosen', $dosen->id)
            ->whereIn('pengambilan_mata_kuliah.status', ['berjalan', 'selesai'])
            ->whereNull('nilai.id')
            ->count();

        return response()->json([
            'sukses' => true,
            'data' => [
                'info_dosen' => $infoDosen,
                'jadwal_mengajar' => $jadwalMengajar,
                'statistik_mengajar' => $statistikMengajar,
                'nilai_pending_input' => $nilaiPendingInput,
                'periode_berjalan' => $periodeBerjalan ? $periodeBerjalan->nama_periode : null,
            ]
        ]);
    }

    /**
     * Dashboard for staff users.
     */
    private function dashboardStaf($staf): JsonResponse
    {
        $periodeBerjalan = PeriodeAkademik::sedangBerjalan();

        $infoStaf = [
            'nama_lengkap' => $staf->data_pribadi['nama_lengkap'],
            'fakultas' => $staf->fakultas ? $staf->fakultas->nama_fakultas : 'Umum',
            'jabatan' => $staf->jabatan,
        ];

        // Task berdasarkan jabatan staf
        $tugasPending = [];

        switch ($staf->jabatan) {
            case 'staf_akademik':
                $tugasPending = [
                    'pengambilan_mk_pending' => PengambilanMataKuliah::where('status', 'diajukan')->count(),
                    'nilai_perlu_verifikasi' => Nilai::where('status', 'draft')->count(),
                ];
                break;

            case 'staf_keuangan':
                $tugasPending = [
                    'pembayaran_perlu_verifikasi' => Pembayaran::whereNull('diverifikasi_oleh')
                        ->whereNotNull('tanggal_bayar')
                        ->count(),
                    'tagihan_jatuh_tempo' => Pembayaran::where('tanggal_jatuh_tempo', '<', now())
                        ->where('status_pembayaran', '!=', 'lunas')
                        ->count(),
                ];
                break;
        }

        // Statistik umum untuk staf
        $statistikUmum = [
            'mahasiswa_aktif' => Mahasiswa::where('status', 'aktif')->count(),
            'dosen_aktif' => Dosen::where('status', 'aktif')->count(),
        ];

        if ($staf->fakultas) {
            $statistikUmum['mahasiswa_fakultas'] = Mahasiswa::whereHas('programStudi', function ($query) use ($staf) {
                $query->where('id_fakultas', $staf->fakultas->id);
            })->where('status', 'aktif')->count();
        }

        return response()->json([
            'sukses' => true,
            'data' => [
                'info_staf' => $infoStaf,
                'tugas_pending' => $tugasPending,
                'statistik_umum' => $statistikUmum,
                'periode_berjalan' => $periodeBerjalan ? $periodeBerjalan->nama_periode : null,
            ]
        ]);
    }

    /**
     * Get admin notifications.
     */
    private function getNotifikasiAdmin(): array
    {
        $notifikasi = [];

        // Cek mahasiswa baru yang perlu verifikasi
        $mahasiswaBaru = Mahasiswa::whereDate('created_at', today())->count();
        if ($mahasiswaBaru > 0) {
            $notifikasi[] = [
                'jenis' => 'info',
                'pesan' => "Ada {$mahasiswaBaru} mahasiswa baru hari ini",
                'url' => '/mahasiswa',
            ];
        }

        // Cek pembayaran yang perlu verifikasi
        $pembayaranPending = Pembayaran::whereNull('diverifikasi_oleh')
            ->whereNotNull('tanggal_bayar')
            ->count();
        
        if ($pembayaranPending > 0) {
            $notifikasi[] = [
                'jenis' => 'warning',
                'pesan' => "Ada {$pembayaranPending} pembayaran yang perlu diverifikasi",
                'url' => '/pembayaran',
            ];
        }

        return $notifikasi;
    }
}