<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MataKuliahController extends Controller
{
    /**
     * Display a listing of courses with filters.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MataKuliah::with(['programStudi.fakultas']);

            // Filter by program studi
            if ($request->has('program_studi')) {
                $query->where('id_program_studi', $request->program_studi);
            }

            // Filter by semester
            if ($request->has('semester')) {
                $query->where('semester_rekomendasi', $request->semester);
            }

            // Filter by course type
            if ($request->has('jenis')) {
                $query->where('jenis', $request->jenis);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Search by course code or name
            if ($request->has('cari')) {
                $cari = $request->cari;
                $query->where(function ($q) use ($cari) {
                    $q->where('kode_mata_kuliah', 'like', "%{$cari}%")
                      ->orWhere('nama_mata_kuliah', 'like', "%{$cari}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'kode_mata_kuliah');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (in_array($sortBy, ['kode_mata_kuliah', 'nama_mata_kuliah', 'sks', 'semester_rekomendasi'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $mataKuliah = $query->paginate($perPage);

            return response()->json([
                'sukses' => true,
                'data' => $mataKuliah->items(),
                'pagination' => [
                    'current_page' => $mataKuliah->currentPage(),
                    'last_page' => $mataKuliah->lastPage(),
                    'per_page' => $mataKuliah->perPage(),
                    'total' => $mataKuliah->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil data mata kuliah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kode_mata_kuliah' => 'required|string|unique:mata_kuliah,kode_mata_kuliah',
                'nama_mata_kuliah' => 'required|string|max:255',
                'id_program_studi' => 'required|exists:program_studi,id',
                'sks' => 'required|integer|min:1|max:6',
                'jenis' => 'required|in:wajib,pilihan,konsentrasi',
                'semester_rekomendasi' => 'required|integer|min:1|max:14',
                'deskripsi' => 'nullable|string',
                'kapasitas_kelas' => 'sometimes|integer|min:1',
            ], [
                'kode_mata_kuliah.required' => 'Kode mata kuliah wajib diisi',
                'kode_mata_kuliah.unique' => 'Kode mata kuliah sudah digunakan',
                'nama_mata_kuliah.required' => 'Nama mata kuliah wajib diisi',
                'id_program_studi.required' => 'Program studi wajib dipilih',
                'sks.required' => 'SKS wajib diisi',
                'sks.min' => 'SKS minimal 1',
                'sks.max' => 'SKS maksimal 6',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            $mataKuliah = MataKuliah::create([
                'kode_mata_kuliah' => $request->kode_mata_kuliah,
                'nama_mata_kuliah' => $request->nama_mata_kuliah,
                'id_program_studi' => $request->id_program_studi,
                'sks' => $request->sks,
                'jenis' => $request->jenis,
                'semester_rekomendasi' => $request->semester_rekomendasi,
                'prasyarat' => $request->prasyarat ?? null,
                'minimal_ipk' => $request->minimal_ipk,
                'minimal_semester' => $request->minimal_semester,
                'deskripsi' => $request->deskripsi,
                'tujuan_pembelajaran' => $request->tujuan_pembelajaran ?? [],
                'capaian_pembelajaran' => $request->capaian_pembelajaran ?? [],
                'materi_pembelajaran' => $request->materi_pembelajaran ?? [],
                'metode_penilaian' => $request->metode_penilaian ?? [],
                'referensi' => $request->referensi ?? [],
                'kapasitas_kelas' => $request->kapasitas_kelas ?? 40,
                'minimal_peserta' => $request->minimal_peserta ?? 10,
                'tersedia_online' => $request->tersedia_online ?? false,
                'status' => 'aktif',
            ]);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Mata kuliah berhasil ditambahkan',
                'data' => $mataKuliah->load('programStudi.fakultas')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menambah mata kuliah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified course.
     */
    public function show($id): JsonResponse
    {
        try {
            $mataKuliah = MataKuliah::with([
                'programStudi.fakultas',
                'jadwalKelas' => function ($query) {
                    $query->where('status', '!=', 'dibatalkan');
                }
            ])->findOrFail($id);

            return response()->json([
                'sukses' => true,
                'data' => $mataKuliah
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Mata kuliah tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $mataKuliah = MataKuliah::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'kode_mata_kuliah' => 'sometimes|string|unique:mata_kuliah,kode_mata_kuliah,' . $id,
                'nama_mata_kuliah' => 'sometimes|string|max:255',
                'sks' => 'sometimes|integer|min:1|max:6',
                'jenis' => 'sometimes|in:wajib,pilihan,konsentrasi',
                'semester_rekomendasi' => 'sometimes|integer|min:1|max:14',
                'status' => 'sometimes|in:aktif,tidak_aktif,arsip',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only([
                'kode_mata_kuliah', 'nama_mata_kuliah', 'sks', 'jenis',
                'semester_rekomendasi', 'prasyarat', 'minimal_ipk',
                'minimal_semester', 'deskripsi', 'tujuan_pembelajaran',
                'capaian_pembelajaran', 'materi_pembelajaran', 'metode_penilaian',
                'referensi', 'kapasitas_kelas', 'minimal_peserta',
                'tersedia_online', 'status'
            ]);

            $mataKuliah->update($updateData);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Mata kuliah berhasil diperbarui',
                'data' => $mataKuliah->fresh('programStudi.fakultas')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat memperbarui mata kuliah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified course.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $mataKuliah = MataKuliah::findOrFail($id);
            
            // Check if course has active classes
            $hasActiveClasses = $mataKuliah->jadwalKelas()
                ->whereIn('status', ['terbuka', 'berjalan'])
                ->exists();

            if ($hasActiveClasses) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Mata kuliah tidak dapat dihapus karena masih memiliki kelas aktif'
                ], 422);
            }

            $mataKuliah->delete();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Mata kuliah berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menghapus mata kuliah',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check prerequisites for a student.
     */
    public function cekPrasyarat(Request $request, $id): JsonResponse
    {
        try {
            $mataKuliah = MataKuliah::findOrFail($id);
            $pengguna = auth()->user();

            if ($pengguna->peran !== 'mahasiswa') {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Hanya mahasiswa yang dapat mengecek prasyarat'
                ], 403);
            }

            $mahasiswa = $pengguna->mahasiswa;
            $memenuhi = $mataKuliah->memenuhinPrasyarat($mahasiswa);

            $response = [
                'sukses' => true,
                'data' => [
                    'memenuhi_prasyarat' => $memenuhi,
                    'mata_kuliah' => $mataKuliah->nama_mata_kuliah,
                    'prasyarat' => []
                ]
            ];

            // Detail prasyarat
            if ($mataKuliah->minimal_ipk) {
                $response['data']['prasyarat'][] = [
                    'jenis' => 'ipk',
                    'nilai_required' => $mataKuliah->minimal_ipk,
                    'nilai_mahasiswa' => $mahasiswa->ipk,
                    'terpenuhi' => $mahasiswa->ipk >= $mataKuliah->minimal_ipk
                ];
            }

            if ($mataKuliah->minimal_semester) {
                $response['data']['prasyarat'][] = [
                    'jenis' => 'semester',
                    'nilai_required' => $mataKuliah->minimal_semester,
                    'nilai_mahasiswa' => $mahasiswa->semester_aktif,
                    'terpenuhi' => $mahasiswa->semester_aktif >= $mataKuliah->minimal_semester
                ];
            }

            if (!empty($mataKuliah->prasyarat) && isset($mataKuliah->prasyarat['mata_kuliah'])) {
                $mataKuliahPrasyarat = $mataKuliah->getMataKuliahPrasyaratAttribute();
                foreach ($mataKuliahPrasyarat as $mk) {
                    $sudahLulus = $mahasiswa->nilai()
                        ->where('id_mata_kuliah', $mk->id)
                        ->where('lulus', true)
                        ->where('status', 'final')
                        ->exists();

                    $response['data']['prasyarat'][] = [
                        'jenis' => 'mata_kuliah',
                        'nama' => $mk->nama_mata_kuliah,
                        'kode' => $mk->kode_mata_kuliah,
                        'terpenuhi' => $sudahLulus
                    ];
                }
            }

            return response()->json($response);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengecek prasyarat',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get courses available for current period.
     */
    public function tersediaSekarang(Request $request): JsonResponse
    {
        try {
            $pengguna = auth()->user();
            
            if ($pengguna->peran !== 'mahasiswa') {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Hanya mahasiswa yang dapat melihat mata kuliah tersedia'
                ], 403);
            }

            $mahasiswa = $pengguna->mahasiswa;
            
            $query = MataKuliah::with(['programStudi', 'jadwalKelas' => function ($q) {
                $q->where('status', 'terbuka');
            }])
            ->where('id_program_studi', $mahasiswa->id_program_studi)
            ->where('status', 'aktif')
            ->whereHas('jadwalKelas', function ($q) {
                $q->where('status', 'terbuka');
            });

            $mataKuliahTersedia = $query->get()->map(function ($mk) use ($mahasiswa) {
                return [
                    'id' => $mk->id,
                    'kode_mata_kuliah' => $mk->kode_mata_kuliah,
                    'nama_mata_kuliah' => $mk->nama_mata_kuliah,
                    'sks' => $mk->sks,
                    'semester_rekomendasi' => $mk->semester_rekomendasi,
                    'jenis' => $mk->jenis,
                    'memenuhi_prasyarat' => $mk->memenuhinPrasyarat($mahasiswa),
                    'kelas_tersedia' => $mk->kelasYangTersedia()->count(),
                    'jadwal_kelas' => $mk->jadwalKelas->map(function ($jadwal) {
                        return [
                            'id' => $jadwal->id,
                            'nama_kelas' => $jadwal->nama_kelas,
                            'ruang' => $jadwal->ruang_kelas,
                            'jadwal' => $jadwal->getJadwalTeksAttribute(),
                            'sisa_kapasitas' => $jadwal->getSisaKapasitasAttribute(),
                        ];
                    })
                ];
            });

            return response()->json([
                'sukses' => true,
                'data' => $mataKuliahTersedia
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil mata kuliah tersedia',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}