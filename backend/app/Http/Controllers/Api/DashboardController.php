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

    /**
     * Get admin dashboard statistics.
     */
    public function adminStats(): JsonResponse
    {
        try {
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            $currentMonth = now()->format('Y-m');
            
            $stats = [
                'totalMahasiswa' => Mahasiswa::count(),
                'mahasiswaAktif' => Mahasiswa::where('status', 'aktif')->count(),
                'mahasiswaBaruBulanIni' => Mahasiswa::whereDate('tanggal_masuk', '>=', now()->startOfMonth())->count(),
                'totalDosen' => Dosen::count(),
                'dosenAktif' => Dosen::where('status', 'aktif')->count(),
                'totalMataKuliah' => MataKuliah::where('status', 'aktif')->count(),
                'mataKuliahAktif' => $periodeBerjalan ? JadwalKelas::where('id_periode_akademik', $periodeBerjalan->id)->where('status', 'terbuka')->count() : 0,
                'totalJadwal' => $periodeBerjalan ? JadwalKelas::where('id_periode_akademik', $periodeBerjalan->id)->count() : 0,
                'pendapatanBulanIni' => Pembayaran::where('status_pembayaran', 'lunas')
                    ->whereRaw('DATE_FORMAT(tanggal_bayar, "%Y-%m") = ?', [$currentMonth])
                    ->sum('jumlah_bayar'),
                'targetPendapatan' => 50000000, // Static target for now
                'pembayaranPending' => Pembayaran::where('status_pembayaran', 'pending')->count(),
                'totalUsers' => \App\Models\Pengguna::count(),
                'currentSemester' => $periodeBerjalan ? $periodeBerjalan->nama_periode : 'N/A'
            ];

            return response()->json([
                'sukses' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading admin stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent students for admin dashboard.
     */
    public function recentStudents(): JsonResponse
    {
        try {
            $students = Mahasiswa::with(['programStudi', 'pengguna'])
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($mahasiswa) {
                    return [
                        'id' => $mahasiswa->id,
                        'nama' => $mahasiswa->data_pribadi['nama_lengkap'] ?? 'N/A',
                        'nim' => $mahasiswa->nim,
                        'program_studi' => $mahasiswa->programStudi->nama_program_studi ?? 'N/A',
                        'tanggal_daftar' => $mahasiswa->created_at->format('d/m/Y H:i'),
                        'status' => $mahasiswa->status
                    ];
                });

            return response()->json([
                'sukses' => true,
                'data' => $students
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading recent students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent payments for admin dashboard.
     */
    public function recentPayments(): JsonResponse
    {
        try {
            $payments = Pembayaran::with(['mahasiswa'])
                ->whereDate('created_at', today())
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($payment) {
                    return [
                        'id' => $payment->id,
                        'mahasiswa' => $payment->mahasiswa->data_pribadi['nama_lengkap'] ?? 'N/A',
                        'nim' => $payment->mahasiswa->nim ?? 'N/A',
                        'jenis_pembayaran' => $payment->jenis_pembayaran,
                        'jumlah' => $payment->jumlah_tagihan,
                        'status' => $payment->status_pembayaran,
                        'tanggal' => $payment->created_at->format('d/m/Y H:i')
                    ];
                });

            return response()->json([
                'sukses' => true,
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading recent payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get admin activities.
     */
    public function adminActivities(): JsonResponse
    {
        try {
            // This would typically come from an activity log table
            // For now, return mock data
            $activities = [
                [
                    'id' => 1,
                    'user' => 'Admin',
                    'action' => 'Mahasiswa baru terdaftar',
                    'description' => 'Ahmad Sutanto (24010001) berhasil terdaftar',
                    'time' => '2 jam yang lalu'
                ],
                [
                    'id' => 2,
                    'user' => 'Staf Keuangan',
                    'action' => 'Pembayaran diverifikasi',
                    'description' => 'SPP September 2024 - Siti Nurhaliza',
                    'time' => '3 jam yang lalu'
                ]
            ];

            return response()->json([
                'sukses' => true,
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading admin activities: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dosen dashboard statistics.
     */
    public function dosenStats(): JsonResponse
    {
        try {
            $dosen = auth()->user()->dosen;
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            
            if (!$dosen) {
                return response()->json(['sukses' => false, 'pesan' => 'Data dosen tidak ditemukan'], 404);
            }

            $kelasYangDiajar = $periodeBerjalan ? 
                $dosen->jadwalKelas()->where('id_periode_akademik', $periodeBerjalan->id)->get() : collect();

            $stats = [
                'totalMataKuliah' => $kelasYangDiajar->pluck('id_mata_kuliah')->unique()->count(),
                'kelasAktif' => $kelasYangDiajar->count(),
                'totalMahasiswa' => $kelasYangDiajar->sum('jumlah_terdaftar'),
                'totalKelas' => $kelasYangDiajar->count(),
                'tugasBelumDinilai' => 15, // Mock data - would calculate from assignments
                'ujianBelumDinilai' => 5, // Mock data
                'kehadiranRate' => 88, // Mock data
                'kelasHariIni' => $kelasYangDiajar->filter(function($kelas) {
                    $hari = strtolower(now()->locale('id')->dayName);
                    return isset($kelas->waktu_jadwal['hari']) && strtolower($kelas->waktu_jadwal['hari']) === $hari;
                })->count(),
                'rataRataNilai' => 78.5 // Mock data
            ];

            return response()->json([
                'sukses' => true,
                'data' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading dosen stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dosen today schedule.
     */
    public function dosenTodaySchedule(): JsonResponse
    {
        try {
            $dosen = auth()->user()->dosen;
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            
            if (!$dosen || !$periodeBerjalan) {
                return response()->json(['sukses' => true, 'data' => []]);
            }

            $hari = strtolower(now()->locale('id')->dayName);
            
            $schedule = $dosen->jadwalKelas()
                ->with(['mataKuliah'])
                ->where('id_periode_akademik', $periodeBerjalan->id)
                ->get()
                ->filter(function($kelas) use ($hari) {
                    return isset($kelas->waktu_jadwal['hari']) && strtolower($kelas->waktu_jadwal['hari']) === $hari;
                })
                ->map(function($kelas) {
                    return [
                        'id' => $kelas->id,
                        'mata_kuliah' => $kelas->mataKuliah->nama_mata_kuliah,
                        'kelas' => $kelas->nama_kelas,
                        'waktu' => ($kelas->waktu_jadwal['jam_mulai'] ?? '') . ' - ' . ($kelas->waktu_jadwal['jam_selesai'] ?? ''),
                        'ruang' => $kelas->ruang_kelas,
                        'jumlah_mahasiswa' => $kelas->jumlah_terdaftar
                    ];
                })->values();

            return response()->json([
                'sukses' => true,
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading dosen schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dosen recent submissions.
     */
    public function dosenRecentSubmissions(): JsonResponse
    {
        try {
            // Mock data - in real implementation, this would come from assignments table
            $submissions = [
                [
                    'id' => 1,
                    'mahasiswa' => 'Ahmad Sutanto',
                    'mata_kuliah' => 'Algoritma dan Pemrograman',
                    'tugas' => 'Tugas 3 - Sorting Algorithm',
                    'waktu_submit' => '2 jam yang lalu',
                    'status' => 'Baru'
                ],
                [
                    'id' => 2,
                    'mahasiswa' => 'Siti Nurhaliza',
                    'mata_kuliah' => 'Basis Data',
                    'tugas' => 'Tugas 2 - ERD Design',
                    'waktu_submit' => '4 jam yang lalu',
                    'status' => 'Baru'
                ]
            ];

            return response()->json([
                'sukses' => true,
                'data' => $submissions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading submissions: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get dosen late students.
     */
    public function dosenLateStudents(): JsonResponse
    {
        try {
            // Mock data - in real implementation, this would come from attendance table
            $lateStudents = [
                [
                    'id' => 1,
                    'mahasiswa' => 'Budi Santoso',
                    'mata_kuliah' => 'Algoritma dan Pemrograman',
                    'waktu_terlambat' => '15 menit',
                    'keterangan' => 'Macet di jalan'
                ]
            ];

            return response()->json([
                'sukses' => true,
                'data' => $lateStudents
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading late students: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mahasiswa dashboard statistics.
     */
    public function mahasiswaStats(): JsonResponse
    {
        try {
            $mahasiswa = auth()->user()->mahasiswa;
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            
            if (!$mahasiswa) {
                return response()->json(['sukses' => false, 'pesan' => 'Data mahasiswa tidak ditemukan'], 404);
            }

            $pengambilanSemesterIni = $periodeBerjalan ? 
                $mahasiswa->pengambilanMataKuliah()->where('id_periode_akademik', $periodeBerjalan->id)->get() : collect();

            $stats = [
                'ipk' => $mahasiswa->ipk ?: 0.00,
                'totalSks' => $mahasiswa->total_sks ?: 0,
                'sksSekarang' => $pengambilanSemesterIni->sum(function($p) { return $p->jadwalKelas->mataKuliah->sks ?? 0; }),
                'mataKuliahSekarang' => $pengambilanSemesterIni->count(),
                'tingkatKehadiran' => 85, // Mock data
                'totalPertemuan' => 24 // Mock data
            ];

            // Payment stats
            $latestPayment = $mahasiswa->pembayaran()->latest()->first();
            $paymentStats = [
                'tagihanAktif' => $latestPayment ? $latestPayment->sisa_tagihan : 12500000,
                'jatuhTempo' => $latestPayment ? $latestPayment->tanggal_jatuh_tempo->format('d F Y') : '30 September 2024',
                'status' => $latestPayment ? $latestPayment->status_pembayaran : 'Belum Bayar'
            ];

            return response()->json([
                'sukses' => true,
                'data' => [
                    'academic' => $stats,
                    'payment' => $paymentStats,
                    'documents' => ['available' => 3],
                    'profile' => ['completeness' => 85, 'status' => 'Lengkap']
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading mahasiswa stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mahasiswa today schedule.
     */
    public function mahasiswaTodaySchedule(): JsonResponse
    {
        try {
            $mahasiswa = auth()->user()->mahasiswa;
            $periodeBerjalan = PeriodeAkademik::sedangBerjalan();
            
            if (!$mahasiswa || !$periodeBerjalan) {
                return response()->json(['sukses' => true, 'data' => []]);
            }

            $hari = strtolower(now()->locale('id')->dayName);
            
            $schedule = $mahasiswa->pengambilanMataKuliah()
                ->with(['jadwalKelas.mataKuliah', 'jadwalKelas.dosen'])
                ->where('id_periode_akademik', $periodeBerjalan->id)
                ->whereIn('status', ['disetujui', 'berjalan'])
                ->get()
                ->filter(function($pengambilan) use ($hari) {
                    $jadwal = $pengambilan->jadwalKelas;
                    return isset($jadwal->waktu_jadwal['hari']) && strtolower($jadwal->waktu_jadwal['hari']) === $hari;
                })
                ->map(function($pengambilan) {
                    $jadwal = $pengambilan->jadwalKelas;
                    return [
                        'id' => $jadwal->id,
                        'mata_kuliah' => $jadwal->mataKuliah->nama_mata_kuliah,
                        'dosen' => $jadwal->dosen->data_pribadi['nama_lengkap'] ?? 'N/A',
                        'waktu' => ($jadwal->waktu_jadwal['jam_mulai'] ?? '') . ' - ' . ($jadwal->waktu_jadwal['jam_selesai'] ?? ''),
                        'ruang' => $jadwal->ruang_kelas,
                        'kelas' => $jadwal->nama_kelas
                    ];
                })->values();

            return response()->json([
                'sukses' => true,
                'data' => $schedule
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading mahasiswa schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mahasiswa recent grades.
     */
    public function mahasiswaRecentGrades(): JsonResponse
    {
        try {
            $mahasiswa = auth()->user()->mahasiswa;
            
            if (!$mahasiswa) {
                return response()->json(['sukses' => false, 'pesan' => 'Data mahasiswa tidak ditemukan'], 404);
            }

            $grades = $mahasiswa->nilai()
                ->with('mataKuliah')
                ->where('status', 'final')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($nilai) {
                    return [
                        'id' => $nilai->id,
                        'mata_kuliah' => $nilai->mataKuliah->nama_mata_kuliah,
                        'jenis' => 'Final', // Could be expanded to include assignment types
                        'nilai' => $nilai->nilai_angka,
                        'tanggal' => $nilai->created_at->format('Y-m-d')
                    ];
                });

            return response()->json([
                'sukses' => true,
                'data' => $grades
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading recent grades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mahasiswa recent payments.
     */
    public function mahasiswaRecentPayments(): JsonResponse
    {
        try {
            $mahasiswa = auth()->user()->mahasiswa;
            
            if (!$mahasiswa) {
                return response()->json(['sukses' => false, 'pesan' => 'Data mahasiswa tidak ditemukan'], 404);
            }

            $payments = $mahasiswa->pembayaran()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function($payment) {
                    return [
                        'id' => $payment->id,
                        'jenis' => $payment->jenis_pembayaran,
                        'periode' => $payment->periode_tagihan,
                        'jumlah' => $payment->jumlah_tagihan,
                        'tanggal' => $payment->tanggal_bayar ? $payment->tanggal_bayar->format('Y-m-d') : null,
                        'status' => $payment->status_pembayaran
                    ];
                });

            return response()->json([
                'sukses' => true,
                'data' => $payments
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading recent payments: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get mahasiswa announcements.
     */
    public function mahasiswaAnnouncements(): JsonResponse
    {
        try {
            // Mock data - in real implementation, this would come from announcements table
            $announcements = [
                [
                    'id' => 1,
                    'title' => 'Pengumuman Libur Nasional',
                    'excerpt' => 'Kampus akan libur pada tanggal 17 Agustus 2024 dalam rangka HUT RI ke-79',
                    'date' => '2024-08-15',
                    'priority' => 'high'
                ],
                [
                    'id' => 2,
                    'title' => 'Pendaftaran Beasiswa',
                    'excerpt' => 'Pendaftaran beasiswa prestasi akademik telah dibuka untuk semester ganjil 2024/2025',
                    'date' => '2024-08-20',
                    'priority' => 'medium'
                ]
            ];

            return response()->json([
                'sukses' => true,
                'data' => $announcements
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Error loading announcements: ' . $e->getMessage()
            ], 500);
        }
}