<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Pengguna;
use App\Models\Fakultas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    /**
     * Display a listing of lecturers with filters and pagination.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Dosen::with(['pengguna', 'fakultas']);

            // Filter by faculty
            if ($request->has('fakultas')) {
                $query->where('id_fakultas', $request->fakultas);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by functional position
            if ($request->has('jabatan_fungsional')) {
                $query->where('jabatan_fungsional', $request->jabatan_fungsional);
            }

            // Search by name, NIDN, or email
            if ($request->has('cari')) {
                $cari = $request->cari;
                $query->where(function ($q) use ($cari) {
                    $q->where('nidn', 'like', "%{$cari}%")
                      ->orWhere('nip', 'like', "%{$cari}%")
                      ->orWhere('nomor_dosen', 'like', "%{$cari}%")
                      ->orWhereJsonContains('data_pribadi->nama_lengkap', $cari)
                      ->orWhereHas('pengguna', function ($userQuery) use ($cari) {
                          $userQuery->where('nama_pengguna', 'like', "%{$cari}%")
                                   ->orWhere('email', 'like', "%{$cari}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'nomor_dosen');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (in_array($sortBy, ['nomor_dosen', 'nidn', 'nip', 'tanggal_mulai_kerja'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $dosen = $query->paginate($perPage);

            return response()->json([
                'sukses' => true,
                'data' => $dosen->items(),
                'pagination' => [
                    'current_page' => $dosen->currentPage(),
                    'last_page' => $dosen->lastPage(),
                    'per_page' => $dosen->perPage(),
                    'total' => $dosen->total(),
                    'from' => $dosen->firstItem(),
                    'to' => $dosen->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil data dosen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created lecturer.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_pengguna' => 'required|string|unique:pengguna,nama_pengguna',
                'email' => 'required|email|unique:pengguna,email',
                'nidn' => 'nullable|string|unique:dosen,nidn',
                'nip' => 'nullable|string|unique:dosen,nip',
                'id_fakultas' => 'required|exists:fakultas,id',
                'data_pribadi' => 'required|array',
                'data_pribadi.nama_lengkap' => 'required|string|max:255',
                'data_pribadi.tanggal_lahir' => 'required|date',
                'data_pribadi.jenis_kelamin' => 'required|in:laki-laki,perempuan',
                'kredensial_akademik' => 'required|array',
                'kredensial_akademik.pendidikan_terakhir' => 'required|string',
                'jabatan_fungsional' => 'required|in:asisten_ahli,lektor,lektor_kepala,guru_besar,tidak_ada',
                'tanggal_mulai_kerja' => 'required|date',
            ], [
                'nama_pengguna.required' => 'Nama pengguna wajib diisi',
                'email.required' => 'Email wajib diisi',
                'nidn.unique' => 'NIDN sudah terdaftar',
                'nip.unique' => 'NIP sudah terdaftar',
                'id_fakultas.required' => 'Fakultas wajib dipilih',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Generate lecturer number
            $nomorDosen = $this->generateNomorDosen($request->id_fakultas);

            // Create user account
            $pengguna = Pengguna::create([
                'nama_pengguna' => $request->nama_pengguna,
                'email' => $request->email,
                'kata_sandi' => Hash::make('dosen123'), // Default password
                'peran' => 'dosen',
                'aktif' => true,
                'email_terverifikasi_pada' => now(),
            ]);

            // Create lecturer record
            $dosen = Dosen::create([
                'id_pengguna' => $pengguna->id,
                'nomor_dosen' => $nomorDosen,
                'nidn' => $request->nidn,
                'nip' => $request->nip,
                'id_fakultas' => $request->id_fakultas,
                'data_pribadi' => $request->data_pribadi,
                'data_kontak' => $request->data_kontak ?? [],
                'kredensial_akademik' => $request->kredensial_akademik,
                'status_kepegawaian' => $request->status_kepegawaian ?? 'tetap',
                'jabatan_fungsional' => $request->jabatan_fungsional,
                'tanggal_mulai_kerja' => $request->tanggal_mulai_kerja,
                'beban_mengajar_min' => $request->beban_mengajar_min ?? 12,
                'beban_mengajar_max' => $request->beban_mengajar_max ?? 16,
                'bidang_keahlian' => $request->bidang_keahlian ?? [],
                'status' => 'aktif',
            ]);

            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Dosen berhasil ditambahkan',
                'data' => $dosen->load(['pengguna', 'fakultas'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menambah dosen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified lecturer.
     */
    public function show($id): JsonResponse
    {
        try {
            $dosen = Dosen::with([
                'pengguna',
                'fakultas',
                'jadwalKelas.mataKuliah',
                'jadwalKelas.periodeAkademik'
            ])->findOrFail($id);

            // Check access - lecturer can only see their own data
            $pengguna = auth()->user();
            if ($pengguna->peran === 'dosen' && $pengguna->dosen->id !== $dosen->id) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Akses ditolak'
                ], 403);
            }

            return response()->json([
                'sukses' => true,
                'data' => $dosen
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Dosen tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update the specified lecturer.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $dosen = Dosen::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'email' => 'sometimes|email|unique:pengguna,email,' . $dosen->id_pengguna,
                'nidn' => 'sometimes|string|unique:dosen,nidn,' . $id,
                'nip' => 'sometimes|string|unique:dosen,nip,' . $id,
                'status' => 'sometimes|in:aktif,tidak_aktif,pensiun,resign',
                'data_pribadi' => 'sometimes|array',
                'data_kontak' => 'sometimes|array',
                'kredensial_akademik' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update user account if email changed
            if ($request->has('email')) {
                $dosen->pengguna->update([
                    'email' => $request->email,
                ]);
            }

            // Update lecturer record
            $updateData = $request->only([
                'nidn', 'nip', 'status', 'data_pribadi', 'data_kontak',
                'kredensial_akademik', 'jabatan_fungsional', 'beban_mengajar_min',
                'beban_mengajar_max', 'bidang_keahlian'
            ]);

            $dosen->update($updateData);

            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Data dosen berhasil diperbarui',
                'data' => $dosen->fresh(['pengguna', 'fakultas'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat memperbarui data dosen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified lecturer.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $dosen = Dosen::findOrFail($id);
            
            DB::beginTransaction();
            
            // Soft delete lecturer and user account
            $dosen->delete();
            $dosen->pengguna->delete();
            
            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Dosen berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menghapus dosen',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get lecturer's teaching schedule.
     */
    public function jadwalMengajar($id): JsonResponse
    {
        try {
            $dosen = Dosen::findOrFail($id);
            
            // Check access
            $pengguna = auth()->user();
            if ($pengguna->peran === 'dosen' && $pengguna->dosen->id !== $dosen->id) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Akses ditolak'
                ], 403);
            }

            $jadwalMengajar = $dosen->jadwalKelas()
                ->with(['mataKuliah', 'periodeAkademik'])
                ->where('status', '!=', 'dibatalkan')
                ->get()
                ->map(function ($jadwal) {
                    return [
                        'id' => $jadwal->id,
                        'mata_kuliah' => $jadwal->mataKuliah->nama_mata_kuliah,
                        'kode_mata_kuliah' => $jadwal->mataKuliah->kode_mata_kuliah,
                        'sks' => $jadwal->mataKuliah->sks,
                        'kelas' => $jadwal->nama_kelas,
                        'ruang' => $jadwal->ruang_kelas,
                        'jadwal' => $jadwal->getJadwalTeksAttribute(),
                        'periode' => $jadwal->periodeAkademik->nama_periode,
                        'jumlah_mahasiswa' => $jadwal->jumlah_terdaftar,
                    ];
                });

            return response()->json([
                'sukses' => true,
                'data' => $jadwalMengajar
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil jadwal mengajar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate lecturer number.
     */
    private function generateNomorDosen($idFakultas): string
    {
        $fakultas = Fakultas::findOrFail($idFakultas);
        $kodeF = $fakultas->kode_fakultas;
        
        $lastDosen = Dosen::where('id_fakultas', $idFakultas)
            ->orderBy('nomor_dosen', 'desc')
            ->first();

        if ($lastDosen) {
            $lastNumber = (int) substr($lastDosen->nomor_dosen, -4);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return 'DSN' . $kodeF . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}