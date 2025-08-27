<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AutentikasiController;
use App\Http\Controllers\Api\MahasiswaController;
use App\Http\Controllers\Api\DosenController;
use App\Http\Controllers\Api\StafController;
use App\Http\Controllers\Api\MataKuliahController;
use App\Http\Controllers\Api\JadwalKelasController;
use App\Http\Controllers\Api\NilaiController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\IntegrasiPddiktiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Test route
Route::get('/', function () {
    return response()->json([
        'message' => 'SIA API is working',
        'version' => '1.0.0',
        'timestamp' => now()
    ]);
});

// Routes untuk autentikasi (tidak perlu login)
Route::prefix('autentikasi')->name('auth.')->group(function () {
    Route::post('/masuk', [AutentikasiController::class, 'masuk'])->name('masuk');
    Route::post('/lupa-kata-sandi', [AutentikasiController::class, 'lupaKataSandi'])->name('lupa-kata-sandi');
    Route::post('/reset-kata-sandi', [AutentikasiController::class, 'resetKataSandi'])->name('reset-kata-sandi');
});

// Routes yang memerlukan autentikasi
Route::middleware(['auth:sanctum'])->group(function () {
    
    // Routes autentikasi untuk pengguna yang sudah login
    Route::prefix('autentikasi')->name('auth.')->group(function () {
        Route::post('/keluar', [AutentikasiController::class, 'keluar'])->name('keluar');
        Route::post('/refresh', [AutentikasiController::class, 'refreshToken'])->name('refresh');
        Route::get('/saya', [AutentikasiController::class, 'saya'])->name('saya');
        Route::put('/ubah-kata-sandi', [AutentikasiController::class, 'ubahKataSandi'])->name('ubah-kata-sandi');
    });

    // Dashboard - semua peran bisa akses
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Dashboard specific endpoints
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        // Admin dashboard data
        Route::middleware(['cek.peran:admin'])->group(function () {
            Route::get('/admin/stats', [DashboardController::class, 'adminStats'])->name('admin.stats');
            Route::get('/admin/recent-students', [DashboardController::class, 'recentStudents'])->name('admin.recent-students');
            Route::get('/admin/recent-payments', [DashboardController::class, 'recentPayments'])->name('admin.recent-payments');
            Route::get('/admin/activities', [DashboardController::class, 'adminActivities'])->name('admin.activities');
        });
        
        // Dosen dashboard data
        Route::middleware(['cek.peran:dosen'])->group(function () {
            Route::get('/dosen/stats', [DashboardController::class, 'dosenStats'])->name('dosen.stats');
            Route::get('/dosen/today-schedule', [DashboardController::class, 'dosenTodaySchedule'])->name('dosen.today-schedule');
            Route::get('/dosen/recent-submissions', [DashboardController::class, 'dosenRecentSubmissions'])->name('dosen.recent-submissions');
            Route::get('/dosen/late-students', [DashboardController::class, 'dosenLateStudents'])->name('dosen.late-students');
        });
        
        // Mahasiswa dashboard data
        Route::middleware(['cek.peran:mahasiswa'])->group(function () {
            Route::get('/mahasiswa/stats', [DashboardController::class, 'mahasiswaStats'])->name('mahasiswa.stats');
            Route::get('/mahasiswa/today-schedule', [DashboardController::class, 'mahasiswaTodaySchedule'])->name('mahasiswa.today-schedule');
            Route::get('/mahasiswa/recent-grades', [DashboardController::class, 'mahasiswaRecentGrades'])->name('mahasiswa.recent-grades');
            Route::get('/mahasiswa/recent-payments', [DashboardController::class, 'mahasiswaRecentPayments'])->name('mahasiswa.recent-payments');
            Route::get('/mahasiswa/announcements', [DashboardController::class, 'mahasiswaAnnouncements'])->name('mahasiswa.announcements');
        });
    });

    // Routes untuk Mahasiswa
    Route::prefix('mahasiswa')->name('mahasiswa.')->group(function () {
        // Mahasiswa bisa lihat dan edit profil sendiri
        Route::middleware(['cek.peran:mahasiswa,staf,admin'])->group(function () {
            Route::get('/', [MahasiswaController::class, 'index'])->name('index');
            Route::get('/{id}', [MahasiswaController::class, 'show'])->name('show');
        });
        
        // Hanya admin dan staf yang bisa buat mahasiswa baru
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::post('/', [MahasiswaController::class, 'store'])->name('store');
            Route::put('/{id}', [MahasiswaController::class, 'update'])->name('update');
            Route::delete('/{id}', [MahasiswaController::class, 'destroy'])->name('destroy');
        });

        // Fitur khusus mahasiswa
        Route::middleware(['cek.peran:mahasiswa'])->group(function () {
            Route::get('/{id}/transkrip', [MahasiswaController::class, 'transkrip'])->name('transkrip');
            Route::get('/{id}/kartu-hasil-studi', [MahasiswaController::class, 'kartuHasilStudi'])->name('khs');
            Route::post('/{id}/upload-dokumen', [MahasiswaController::class, 'uploadDokumen'])->name('upload-dokumen');
        });
    });

    // Routes untuk Dosen
    Route::prefix('dosen')->name('dosen.')->group(function () {
        Route::middleware(['cek.peran:dosen,admin,staf'])->group(function () {
            Route::get('/', [DosenController::class, 'index'])->name('index');
            Route::get('/{id}', [DosenController::class, 'show'])->name('show');
        });
        
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::post('/', [DosenController::class, 'store'])->name('store');
            Route::put('/{id}', [DosenController::class, 'update'])->name('update');
            Route::delete('/{id}', [DosenController::class, 'destroy'])->name('destroy');
        });

        // Fitur khusus dosen
        Route::middleware(['cek.peran:dosen'])->group(function () {
            Route::get('/{id}/jadwal-mengajar', [DosenController::class, 'jadwalMengajar'])->name('jadwal-mengajar');
            Route::get('/{id}/mahasiswa-bimbingan', [DosenController::class, 'mahasiswaBimbingan'])->name('mahasiswa-bimbingan');
        });
    });

    // Routes untuk Staf
    Route::prefix('staf')->name('staf.')->middleware(['cek.peran:admin,staf'])->group(function () {
        Route::get('/', [StafController::class, 'index'])->name('index');
        Route::get('/{id}', [StafController::class, 'show'])->name('show');
        Route::post('/', [StafController::class, 'store'])->name('store');
        Route::put('/{id}', [StafController::class, 'update'])->name('update');
        Route::delete('/{id}', [StafController::class, 'destroy'])->name('destroy');
    });

    // Routes untuk Mata Kuliah
    Route::prefix('mata-kuliah')->name('mata-kuliah.')->group(function () {
        // Semua bisa lihat mata kuliah
        Route::get('/', [MataKuliahController::class, 'index'])->name('index');
        Route::get('/{id}', [MataKuliahController::class, 'show'])->name('show');
        
        // Hanya admin dan staf akademik yang bisa kelola mata kuliah
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::post('/', [MataKuliahController::class, 'store'])->name('store');
            Route::put('/{id}', [MataKuliahController::class, 'update'])->name('update');
            Route::delete('/{id}', [MataKuliahController::class, 'destroy'])->name('destroy');
        });

        // Cek prasyarat untuk mahasiswa
        Route::middleware(['cek.peran:mahasiswa'])->group(function () {
            Route::get('/{id}/cek-prasyarat', [MataKuliahController::class, 'cekPrasyarat'])->name('cek-prasyarat');
        });
    });

    // Routes untuk Jadwal Kelas
    Route::prefix('jadwal-kelas')->name('jadwal-kelas.')->group(function () {
        // Semua bisa lihat jadwal
        Route::get('/', [JadwalKelasController::class, 'index'])->name('index');
        Route::get('/{id}', [JadwalKelasController::class, 'show'])->name('show');
        
        // Hanya admin dan staf yang bisa kelola jadwal
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::post('/', [JadwalKelasController::class, 'store'])->name('store');
            Route::put('/{id}', [JadwalKelasController::class, 'update'])->name('update');
            Route::delete('/{id}', [JadwalKelasController::class, 'destroy'])->name('destroy');
        });

        // Pengambilan mata kuliah untuk mahasiswa
        Route::middleware(['cek.peran:mahasiswa'])->group(function () {
            Route::post('/{id}/ambil', [JadwalKelasController::class, 'ambilMataKuliah'])->name('ambil');
            Route::delete('/{id}/batalkan', [JadwalKelasController::class, 'batalkanPengambilan'])->name('batalkan');
        });

        // Persetujuan untuk staf akademik
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::put('/{id}/setujui', [JadwalKelasController::class, 'setujuiPengambilan'])->name('setujui');
            Route::put('/{id}/tolak', [JadwalKelasController::class, 'tolakPengambilan'])->name('tolak');
        });
    });

    // Routes untuk Nilai
    Route::prefix('nilai')->name('nilai.')->group(function () {
        // Mahasiswa bisa lihat nilai sendiri
        Route::middleware(['cek.peran:mahasiswa,dosen,admin,staf'])->group(function () {
            Route::get('/', [NilaiController::class, 'index'])->name('index');
            Route::get('/{id}', [NilaiController::class, 'show'])->name('show');
        });
        
        // Dosen bisa input nilai untuk mata kuliah yang diajar
        Route::middleware(['cek.peran:dosen,admin'])->group(function () {
            Route::post('/', [NilaiController::class, 'store'])->name('store');
            Route::put('/{id}', [NilaiController::class, 'update'])->name('update');
            Route::put('/{id}/finalisasi', [NilaiController::class, 'finalisasi'])->name('finalisasi');
        });

        // Admin bisa revisi nilai
        Route::middleware(['cek.peran:admin'])->group(function () {
            Route::put('/{id}/revisi', [NilaiController::class, 'revisi'])->name('revisi');
        });
    });

    // Routes untuk Pembayaran
    Route::prefix('pembayaran')->name('pembayaran.')->group(function () {
        // Mahasiswa bisa lihat pembayaran sendiri
        Route::middleware(['cek.peran:mahasiswa,admin,staf'])->group(function () {
            Route::get('/', [PembayaranController::class, 'index'])->name('index');
            Route::get('/{id}', [PembayaranController::class, 'show'])->name('show');
        });
        
        // Staf keuangan bisa kelola pembayaran
        Route::middleware(['cek.peran:admin,staf'])->group(function () {
            Route::post('/', [PembayaranController::class, 'store'])->name('store');
            Route::put('/{id}', [PembayaranController::class, 'update'])->name('update');
            Route::put('/{id}/verifikasi', [PembayaranController::class, 'verifikasi'])->name('verifikasi');
        });

        // Mahasiswa bisa upload bukti bayar
        Route::middleware(['cek.peran:mahasiswa'])->group(function () {
            Route::post('/{id}/upload-bukti', [PembayaranController::class, 'uploadBuktiBayar'])->name('upload-bukti');
        });
    });

    // Routes untuk Laporan - hanya admin dan staf tertentu
    Route::prefix('laporan')->name('laporan.')->middleware(['cek.peran:admin,staf'])->group(function () {
        Route::get('/mahasiswa', [LaporanController::class, 'laporanMahasiswa'])->name('mahasiswa');
        Route::get('/akademik', [LaporanController::class, 'laporanAkademik'])->name('akademik');
        Route::get('/keuangan', [LaporanController::class, 'laporanKeuangan'])->name('keuangan');
        Route::get('/statistik', [LaporanController::class, 'statistik'])->name('statistik');
    });

    // Routes untuk Integrasi PDDIKTI - hanya admin
    Route::prefix('pddikti')->name('pddikti.')->middleware(['cek.peran:admin'])->group(function () {
        Route::post('/sync-mahasiswa', [IntegrasiPddiktiController::class, 'syncMahasiswa'])->name('sync-mahasiswa');
        Route::post('/sync-dosen', [IntegrasiPddiktiController::class, 'syncDosen'])->name('sync-dosen');
        Route::post('/sync-mata-kuliah', [IntegrasiPddiktiController::class, 'syncMataKuliah'])->name('sync-mata-kuliah');
        Route::post('/sync-nilai', [IntegrasiPddiktiController::class, 'syncNilai'])->name('sync-nilai');
        Route::get('/status-sync', [IntegrasiPddiktiController::class, 'statusSync'])->name('status-sync');
    });
});

// Route fallback untuk API yang tidak ditemukan
Route::fallback(function () {
    return response()->json([
        'sukses' => false,
        'pesan' => 'Endpoint API tidak ditemukan'
    ], 404);
});

// Route untuk dokumentasi API
Route::get('/dokumentasi', function () {
    return response()->json([
        'nama' => 'API Sistem Informasi Akademik (SIA)',
        'versi' => '1.0.0',
        'deskripsi' => 'API untuk sistem informasi akademik universitas dengan fitur lengkap dari pendaftaran mahasiswa hingga kelulusan',
        'endpoint_utama' => [
            'autentikasi' => '/api/autentikasi',
            'dashboard' => '/api/dashboard',
            'mahasiswa' => '/api/mahasiswa',
            'dosen' => '/api/dosen',
            'mata_kuliah' => '/api/mata-kuliah',
            'jadwal_kelas' => '/api/jadwal-kelas',
            'nilai' => '/api/nilai',
            'pembayaran' => '/api/pembayaran',
            'laporan' => '/api/laporan',
            'pddikti' => '/api/pddikti'
        ],
        'dokumentasi_lengkap' => env('APP_URL') . '/docs'
    ]);
})->name('dokumentasi');