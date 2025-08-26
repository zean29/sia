<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Pengguna;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    /**
     * Daftar mahasiswa dengan filter dan paginasi.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Mahasiswa::with(['pengguna', 'programStudi.fakultas']);

            // Filter berdasarkan program studi
            if ($request->has('program_studi')) {
                $query->where('id_program_studi', $request->program_studi);
            }

            // Filter berdasarkan status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter berdasarkan angkatan
            if ($request->has('angkatan')) {
                $query->whereYear('tanggal_masuk', $request->angkatan);
            }

            // Filter berdasarkan semester
            if ($request->has('semester')) {
                $query->where('semester_aktif', $request->semester);
            }

            // Pencarian berdasarkan nama atau NIM
            if ($request->has('cari')) {
                $cari = $request->cari;
                $query->where(function ($q) use ($cari) {
                    $q->where('nim', 'like', "%{$cari}%")
                      ->orWhere('nomor_mahasiswa', 'like', "%{$cari}%")
                      ->orWhereJsonContains('data_pribadi->nama_lengkap', $cari)
                      ->orWhereHas('pengguna', function ($userQuery) use ($cari) {
                          $userQuery->where('nama_pengguna', 'like', "%{$cari}%")
                                   ->orWhere('email', 'like', "%{$cari}%");
                      });
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'nim');
            $sortOrder = $request->get('sort_order', 'asc');
            
            if (in_array($sortBy, ['nim', 'nomor_mahasiswa', 'tanggal_masuk', 'ipk', 'semester_aktif'])) {
                $query->orderBy($sortBy, $sortOrder);
            }

            // Paginasi
            $perPage = $request->get('per_page', 15);
            $mahasiswa = $query->paginate($perPage);

            return response()->json([
                'sukses' => true,
                'data' => $mahasiswa->items(),
                'pagination' => [
                    'current_page' => $mahasiswa->currentPage(),
                    'last_page' => $mahasiswa->lastPage(),
                    'per_page' => $mahasiswa->perPage(),
                    'total' => $mahasiswa->total(),
                    'from' => $mahasiswa->firstItem(),
                    'to' => $mahasiswa->lastItem(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tambah mahasiswa baru.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_pengguna' => 'required|string|unique:pengguna,nama_pengguna',
                'email' => 'required|email|unique:pengguna,email',
                'id_program_studi' => 'required|exists:program_studi,id',
                'data_pribadi' => 'required|array',
                'data_pribadi.nama_lengkap' => 'required|string|max:255',
                'data_pribadi.tanggal_lahir' => 'required|date',
                'data_pribadi.tempat_lahir' => 'required|string|max:100',
                'data_pribadi.jenis_kelamin' => 'required|in:laki-laki,perempuan',
                'data_pribadi.agama' => 'required|string|max:50',
                'data_kontak' => 'required|array',
                'data_kontak.telepon' => 'required|string|max:20',
                'data_kontak.alamat' => 'required|string',
                'data_akademik' => 'required|array',
                'data_akademik.asal_sekolah' => 'required|string|max:255',
                'data_akademik.tahun_lulus' => 'required|integer|min:1990|max:' . date('Y'),
            ], [
                'nama_pengguna.required' => 'Nama pengguna wajib diisi',
                'nama_pengguna.unique' => 'Nama pengguna sudah digunakan',
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.unique' => 'Email sudah terdaftar',
                'id_program_studi.required' => 'Program studi wajib dipilih',
                'id_program_studi.exists' => 'Program studi tidak valid',
                'data_pribadi.nama_lengkap.required' => 'Nama lengkap wajib diisi',
                'data_pribadi.tanggal_lahir.required' => 'Tanggal lahir wajib diisi',
                'data_pribadi.jenis_kelamin.required' => 'Jenis kelamin wajib dipilih',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Generate NIM dan nomor mahasiswa
            $programStudi = ProgramStudi::findOrFail($request->id_program_studi);
            $tahunMasuk = date('Y');
            $nomorUrut = $this->generateNomorUrut($programStudi->id, $tahunMasuk);
            
            $nim = $tahunMasuk . str_pad($programStudi->id, 2, '0', STR_PAD_LEFT) . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);
            $nomorMahasiswa = $tahunMasuk . str_pad($programStudi->id, 2, '0', STR_PAD_LEFT) . str_pad($nomorUrut, 4, '0', STR_PAD_LEFT);

            // Buat pengguna
            $pengguna = Pengguna::create([
                'nama_pengguna' => $request->nama_pengguna,
                'email' => $request->email,
                'kata_sandi' => Hash::make('mahasiswa123'), // Password default
                'peran' => 'mahasiswa',
                'aktif' => true,
                'email_terverifikasi_pada' => now(),
            ]);

            // Buat mahasiswa
            $mahasiswa = Mahasiswa::create([
                'id_pengguna' => $pengguna->id,
                'nomor_mahasiswa' => $nomorMahasiswa,
                'nim' => $nim,
                'id_program_studi' => $request->id_program_studi,
                'status' => 'aktif',
                'tanggal_masuk' => now(),
                'jenis_masuk' => $request->jenis_masuk ?? 'reguler',
                'data_pribadi' => $request->data_pribadi,
                'data_kontak' => $request->data_kontak,
                'data_akademik' => $request->data_akademik,
                'data_orangtua' => $request->data_orangtua ?? [],
                'total_biaya_kuliah' => $request->total_biaya_kuliah ?? 0,
                'semester_aktif' => 1,
            ]);

            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Mahasiswa berhasil ditambahkan',
                'data' => $mahasiswa->load(['pengguna', 'programStudi.fakultas'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menambah mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan detail mahasiswa.
     */
    public function show($id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::with([
                'pengguna', 
                'programStudi.fakultas',
                'pengambilanMataKuliah.jadwalKelas.mataKuliah',
                'nilai.mataKuliah',
                'pembayaran',
                'rekamAkademik'
            ])->findOrFail($id);

            // Cek hak akses - mahasiswa hanya bisa lihat data sendiri
            $pengguna = auth()->user();
            if ($pengguna->peran === 'mahasiswa' && $pengguna->mahasiswa->id !== $mahasiswa->id) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Akses ditolak'
                ], 403);
            }

            return response()->json([
                'sukses' => true,
                'data' => $mahasiswa
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Mahasiswa tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Update data mahasiswa.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'email' => 'sometimes|email|unique:pengguna,email,' . $mahasiswa->id_pengguna,
                'status' => 'sometimes|in:aktif,tidak_aktif,lulus,do,skorsing,cuti',
                'data_pribadi' => 'sometimes|array',
                'data_kontak' => 'sometimes|array',
                'data_akademik' => 'sometimes|array',
                'data_orangtua' => 'sometimes|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            DB::beginTransaction();

            // Update pengguna jika ada
            if ($request->has('email')) {
                $mahasiswa->pengguna->update([
                    'email' => $request->email,
                ]);
            }

            // Update mahasiswa
            $updateData = $request->only([
                'status', 'data_pribadi', 'data_kontak', 
                'data_akademik', 'data_orangtua'
            ]);

            if ($request->has('tanggal_lulus') && $request->status === 'lulus') {
                $updateData['tanggal_lulus'] = $request->tanggal_lulus;
            }

            $mahasiswa->update($updateData);

            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Data mahasiswa berhasil diperbarui',
                'data' => $mahasiswa->fresh(['pengguna', 'programStudi.fakultas'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat memperbarui data mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus mahasiswa (soft delete).
     */
    public function destroy($id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::findOrFail($id);
            
            DB::beginTransaction();
            
            // Soft delete mahasiswa dan pengguna
            $mahasiswa->delete();
            $mahasiswa->pengguna->delete();
            
            DB::commit();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Mahasiswa berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat menghapus mahasiswa',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate transkrip mahasiswa.
     */
    public function transkrip($id): JsonResponse
    {
        try {
            $mahasiswa = Mahasiswa::with([
                'pengguna',
                'programStudi.fakultas',
                'nilai' => function ($query) {
                    $query->where('status', 'final')
                          ->where('lulus', true)
                          ->with('mataKuliah');
                }
            ])->findOrFail($id);

            // Cek hak akses
            $pengguna = auth()->user();
            if ($pengguna->peran === 'mahasiswa' && $pengguna->mahasiswa->id !== $mahasiswa->id) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Akses ditolak'
                ], 403);
            }

            $transkrip = [
                'data_mahasiswa' => [
                    'nama_lengkap' => $mahasiswa->data_pribadi['nama_lengkap'],
                    'nim' => $mahasiswa->nim,
                    'program_studi' => $mahasiswa->programStudi->nama_program_studi,
                    'fakultas' => $mahasiswa->programStudi->fakultas->nama_fakultas,
                    'ipk' => $mahasiswa->ipk,
                    'total_sks' => $mahasiswa->total_sks,
                ],
                'nilai' => $mahasiswa->nilai->map(function ($nilai) {
                    return [
                        'kode_mata_kuliah' => $nilai->mataKuliah->kode_mata_kuliah,
                        'nama_mata_kuliah' => $nilai->mataKuliah->nama_mata_kuliah,
                        'sks' => $nilai->mataKuliah->sks,
                        'nilai_huruf' => $nilai->nilai_huruf,
                        'nilai_indeks' => $nilai->nilai_indeks,
                        'semester' => $nilai->periodeAkademik->nama_periode ?? '',
                    ];
                }),
                'tanggal_cetak' => now()->format('d-m-Y H:i:s'),
            ];

            return response()->json([
                'sukses' => true,
                'data' => $transkrip
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat generate transkrip',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate nomor urut mahasiswa untuk program studi dan tahun tertentu.
     */
    private function generateNomorUrut($idProgramStudi, $tahun): int
    {
        $lastMahasiswa = Mahasiswa::whereHas('programStudi', function ($query) use ($idProgramStudi) {
                $query->where('id', $idProgramStudi);
            })
            ->whereYear('tanggal_masuk', $tahun)
            ->orderBy('nim', 'desc')
            ->first();

        if ($lastMahasiswa) {
            $lastNim = $lastMahasiswa->nim;
            $lastNomor = (int) substr($lastNim, -4);
            return $lastNomor + 1;
        }

        return 1;
    }
}