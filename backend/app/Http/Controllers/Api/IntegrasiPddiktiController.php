<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PddiktiService;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class IntegrasiPddiktiController extends Controller
{
    protected PddiktiService $pddiktiService;

    public function __construct(PddiktiService $pddiktiService)
    {
        $this->pddiktiService = $pddiktiService;
    }

    /**
     * Synchronize student data to PDDIKTI.
     */
    public function syncMahasiswa(Request $request): JsonResponse
    {
        try {
            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            // Get students to sync
            $query = Mahasiswa::with(['programStudi.fakultas']);
            
            if ($request->has('id')) {
                $query->where('id', $request->id);
            } elseif ($request->has('program_studi')) {
                $query->where('id_program_studi', $request->program_studi);
            } else {
                // Sync all active students
                $query->where('status', 'aktif');
            }

            $mahasiswa = $query->get();

            foreach ($mahasiswa as $mhs) {
                $result = $this->pddiktiService->syncMahasiswa($mhs);
                
                $results[] = [
                    'id' => $mhs->id,
                    'nim' => $mhs->nim,
                    'nama' => $mhs->data_pribadi['nama_lengkap'],
                    'status' => $result['success'] ? 'berhasil' : 'gagal',
                    'pesan' => $result['message']
                ];

                if ($result['success']) {
                    $totalSuccess++;
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'sukses' => true,
                'pesan' => "Sinkronisasi selesai. Berhasil: {$totalSuccess}, Gagal: {$totalFailed}",
                'data' => [
                    'total_processed' => count($results),
                    'total_success' => $totalSuccess,
                    'total_failed' => $totalFailed,
                    'details' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat sinkronisasi mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize lecturer data to PDDIKTI.
     */
    public function syncDosen(Request $request): JsonResponse
    {
        try {
            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            $query = Dosen::with('fakultas');
            
            if ($request->has('id')) {
                $query->where('id', $request->id);
            } elseif ($request->has('fakultas')) {
                $query->where('id_fakultas', $request->fakultas);
            } else {
                $query->where('status', 'aktif');
            }

            $dosenList = $query->get();

            foreach ($dosenList as $dosen) {
                $result = $this->pddiktiService->syncDosen($dosen);
                
                $results[] = [
                    'id' => $dosen->id,
                    'nidn' => $dosen->nidn,
                    'nama' => $dosen->data_pribadi['nama_lengkap'],
                    'status' => $result['success'] ? 'berhasil' : 'gagal',
                    'pesan' => $result['message']
                ];

                if ($result['success']) {
                    $totalSuccess++;
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'sukses' => true,
                'pesan' => "Sinkronisasi dosen selesai. Berhasil: {$totalSuccess}, Gagal: {$totalFailed}",
                'data' => [
                    'total_processed' => count($results),
                    'total_success' => $totalSuccess,
                    'total_failed' => $totalFailed,
                    'details' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat sinkronisasi dosen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize course data to PDDIKTI.
     */
    public function syncMataKuliah(Request $request): JsonResponse
    {
        try {
            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            $query = MataKuliah::with('programStudi.fakultas');
            
            if ($request->has('id')) {
                $query->where('id', $request->id);
            } elseif ($request->has('program_studi')) {
                $query->where('id_program_studi', $request->program_studi);
            } else {
                $query->where('status', 'aktif');
            }

            $mataKuliahList = $query->get();

            foreach ($mataKuliahList as $mk) {
                $result = $this->pddiktiService->syncMataKuliah($mk);
                
                $results[] = [
                    'id' => $mk->id,
                    'kode' => $mk->kode_mata_kuliah,
                    'nama' => $mk->nama_mata_kuliah,
                    'status' => $result['success'] ? 'berhasil' : 'gagal',
                    'pesan' => $result['message']
                ];

                if ($result['success']) {
                    $totalSuccess++;
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'sukses' => true,
                'pesan' => "Sinkronisasi mata kuliah selesai. Berhasil: {$totalSuccess}, Gagal: {$totalFailed}",
                'data' => [
                    'total_processed' => count($results),
                    'total_success' => $totalSuccess,
                    'total_failed' => $totalFailed,
                    'details' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat sinkronisasi mata kuliah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Synchronize grade data to PDDIKTI.
     */
    public function syncNilai(Request $request): JsonResponse
    {
        try {
            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            $query = Nilai::with(['mahasiswa', 'mataKuliah', 'periodeAkademik'])
                ->where('status', 'final');
            
            if ($request->has('periode_akademik')) {
                $query->where('id_periode_akademik', $request->periode_akademik);
            }

            if ($request->has('mahasiswa')) {
                $query->where('id_mahasiswa', $request->mahasiswa);
            }

            // Limit to recent period if no specific period selected
            if (!$request->has('periode_akademik')) {
                $query->whereHas('periodeAkademik', function ($q) {
                    $q->where('status', '!=', 'rencana');
                });
            }

            $nilaiList = $query->get();

            foreach ($nilaiList as $nilai) {
                $result = $this->pddiktiService->syncNilai($nilai);
                
                $results[] = [
                    'id' => $nilai->id,
                    'mahasiswa' => $nilai->mahasiswa->nim,
                    'mata_kuliah' => $nilai->mataKuliah->kode_mata_kuliah,
                    'nilai_huruf' => $nilai->nilai_huruf,
                    'status' => $result['success'] ? 'berhasil' : 'gagal',
                    'pesan' => $result['message']
                ];

                if ($result['success']) {
                    $totalSuccess++;
                } else {
                    $totalFailed++;
                }
            }

            return response()->json([
                'sukses' => true,
                'pesan' => "Sinkronisasi nilai selesai. Berhasil: {$totalSuccess}, Gagal: {$totalFailed}",
                'data' => [
                    'total_processed' => count($results),
                    'total_success' => $totalSuccess,
                    'total_failed' => $totalFailed,
                    'details' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat sinkronisasi nilai',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get synchronization status.
     */
    public function statusSync(): JsonResponse
    {
        try {
            $status = $this->pddiktiService->getStatusSync();

            // Add percentage calculations
            $status['persentase_mahasiswa'] = $status['total_mahasiswa'] > 0 
                ? round(($status['mahasiswa_synced'] / $status['total_mahasiswa']) * 100, 2) 
                : 0;

            $status['persentase_dosen'] = $status['total_dosen'] > 0 
                ? round(($status['dosen_synced'] / $status['total_dosen']) * 100, 2) 
                : 0;

            $status['persentase_mata_kuliah'] = $status['total_mata_kuliah'] > 0 
                ? round(($status['mata_kuliah_synced'] / $status['total_mata_kuliah']) * 100, 2) 
                : 0;

            $status['persentase_nilai'] = $status['total_nilai'] > 0 
                ? round(($status['nilai_synced'] / $status['total_nilai']) * 100, 2) 
                : 0;

            // Recent failed syncs
            $failedRecent = DB::table('integrasi_pddikti')
                ->where('status_sinkronisasi', 'sync_gagal')
                ->where('terakhir_sync', '>=', now()->subDays(7))
                ->select('jenis_data', 'pesan_error', 'terakhir_sync')
                ->orderBy('terakhir_sync', 'desc')
                ->limit(10)
                ->get();

            return response()->json([
                'sukses' => true,
                'data' => [
                    'statistik' => $status,
                    'gagal_terbaru' => $failedRecent,
                    'terakhir_update' => now()->format('Y-m-d H:i:s')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil status sinkronisasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retry failed synchronizations.
     */
    public function retryFailedSync(Request $request): JsonResponse
    {
        try {
            $jenisData = $request->get('jenis_data', 'all');
            $maxRetry = $request->get('max_retry', 3);

            $query = DB::table('integrasi_pddikti')
                ->where('status_sinkronisasi', 'sync_gagal')
                ->where('percobaan_sync', '<', $maxRetry);

            if ($jenisData !== 'all') {
                $query->where('jenis_data', $jenisData);
            }

            $failedItems = $query->get();
            $results = [];
            $totalSuccess = 0;
            $totalFailed = 0;

            foreach ($failedItems as $item) {
                try {
                    $result = null;
                    
                    switch ($item->jenis_data) {
                        case 'mahasiswa':
                            $mahasiswa = Mahasiswa::find($item->entitas_id);
                            if ($mahasiswa) {
                                $result = $this->pddiktiService->syncMahasiswa($mahasiswa);
                            }
                            break;
                            
                        case 'dosen':
                            $dosen = Dosen::find($item->entitas_id);
                            if ($dosen) {
                                $result = $this->pddiktiService->syncDosen($dosen);
                            }
                            break;
                            
                        case 'mata_kuliah':
                            $mataKuliah = MataKuliah::find($item->entitas_id);
                            if ($mataKuliah) {
                                $result = $this->pddiktiService->syncMataKuliah($mataKuliah);
                            }
                            break;
                            
                        case 'nilai':
                            $nilai = Nilai::find($item->entitas_id);
                            if ($nilai) {
                                $result = $this->pddiktiService->syncNilai($nilai);
                            }
                            break;
                    }

                    if ($result) {
                        $results[] = [
                            'jenis' => $item->jenis_data,
                            'entitas_id' => $item->entitas_id,
                            'status' => $result['success'] ? 'berhasil' : 'gagal',
                            'pesan' => $result['message']
                        ];

                        if ($result['success']) {
                            $totalSuccess++;
                        } else {
                            $totalFailed++;
                        }
                    }

                } catch (\Exception $e) {
                    $results[] = [
                        'jenis' => $item->jenis_data,
                        'entitas_id' => $item->entitas_id,
                        'status' => 'gagal',
                        'pesan' => $e->getMessage()
                    ];
                    $totalFailed++;
                }
            }

            return response()->json([
                'sukses' => true,
                'pesan' => "Retry sinkronisasi selesai. Berhasil: {$totalSuccess}, Gagal: {$totalFailed}",
                'data' => [
                    'total_processed' => count($results),
                    'total_success' => $totalSuccess,
                    'total_failed' => $totalFailed,
                    'details' => $results
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat retry sinkronisasi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}