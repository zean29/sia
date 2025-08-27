<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\HasApiTokens;

class AutentikasiController extends Controller
{
    /**
     * Login pengguna.
     */
    public function masuk(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email',
                'kata_sandi' => 'required|string|min:6',
            ], [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'kata_sandi.required' => 'Kata sandi wajib diisi',
                'kata_sandi.min' => 'Kata sandi minimal 6 karakter',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            // Cari pengguna berdasarkan email
            $pengguna = Pengguna::where('email', $request->email)->first();

            if (!$pengguna || !Hash::check($request->kata_sandi, $pengguna->kata_sandi)) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Email atau kata sandi tidak valid'
                ], 401);
            }

            // Cek apakah pengguna aktif
            if (!$pengguna->aktif) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Akun Anda tidak aktif. Silakan hubungi administrator'
                ], 403);
            }

            // Buat token
            $token = $pengguna->createToken('auth_token', [$pengguna->peran])->plainTextToken;

            // Update waktu terakhir masuk
            $pengguna->updateTerakhirMasuk();

            // Load relasi berdasarkan peran
            $dataRelasi = $this->muatDataRelasi($pengguna);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Login berhasil',
                'data' => [
                    'pengguna' => array_merge($pengguna->makeHidden(['kata_sandi'])->toArray(), $dataRelasi),
                    'token' => $token,
                    'peran' => $pengguna->peran,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat login',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Logout pengguna.
     */
    public function keluar(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'sukses' => true,
                'pesan' => 'Logout berhasil'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat logout',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh token.
     */
    public function refreshToken(Request $request): JsonResponse
    {
        try {
            $pengguna = $request->user();
            
            // Hapus token lama
            $request->user()->currentAccessToken()->delete();
            
            // Buat token baru
            $tokenBaru = $pengguna->createToken('auth_token', [$pengguna->peran])->plainTextToken;

            return response()->json([
                'sukses' => true,
                'pesan' => 'Token berhasil diperbarui',
                'data' => [
                    'token' => $tokenBaru,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat refresh token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mendapatkan informasi pengguna saat ini.
     */
    public function saya(Request $request): JsonResponse
    {
        try {
            $pengguna = $request->user();
            $dataRelasi = $this->muatDataRelasi($pengguna);

            return response()->json([
                'sukses' => true,
                'data' => array_merge($pengguna->makeHidden(['kata_sandi'])->toArray(), $dataRelasi)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengambil data pengguna',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Permintaan reset kata sandi.
     */
    public function lupaKataSandi(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:pengguna,email',
            ], [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.exists' => 'Email tidak terdaftar',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            $pengguna = Pengguna::where('email', $request->email)->first();

            // Generate token reset (dalam implementasi nyata, simpan ke database dan kirim email)
            $tokenReset = str()->random(60);

            // TODO: Simpan token ke database dan kirim email

            return response()->json([
                'sukses' => true,
                'pesan' => 'Link reset kata sandi telah dikirim ke email Anda',
                'data' => [
                    'token_reset' => $tokenReset, // Hanya untuk testing
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat memproses permintaan reset kata sandi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset kata sandi.
     */
    public function resetKataSandi(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|email|exists:pengguna,email',
                'token' => 'required|string',
                'kata_sandi' => 'required|string|min:6|confirmed',
            ], [
                'email.required' => 'Email wajib diisi',
                'email.email' => 'Format email tidak valid',
                'email.exists' => 'Email tidak terdaftar',
                'token.required' => 'Token reset wajib diisi',
                'kata_sandi.required' => 'Kata sandi baru wajib diisi',
                'kata_sandi.min' => 'Kata sandi minimal 6 karakter',
                'kata_sandi.confirmed' => 'Konfirmasi kata sandi tidak cocok',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            // TODO: Validasi token reset dari database

            $pengguna = Pengguna::where('email', $request->email)->first();
            $pengguna->update([
                'kata_sandi' => Hash::make($request->kata_sandi),
            ]);

            // TODO: Hapus token reset dari database

            return response()->json([
                'sukses' => true,
                'pesan' => 'Kata sandi berhasil direset'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat reset kata sandi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ubah kata sandi.
     */
    public function ubahKataSandi(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'kata_sandi_lama' => 'required|string',
                'kata_sandi_baru' => 'required|string|min:6|confirmed',
            ], [
                'kata_sandi_lama.required' => 'Kata sandi lama wajib diisi',
                'kata_sandi_baru.required' => 'Kata sandi baru wajib diisi',
                'kata_sandi_baru.min' => 'Kata sandi baru minimal 6 karakter',
                'kata_sandi_baru.confirmed' => 'Konfirmasi kata sandi tidak cocok',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Data tidak valid',
                    'error' => $validator->errors()
                ], 422);
            }

            $pengguna = $request->user();

            // Cek kata sandi lama
            if (!Hash::check($request->kata_sandi_lama, $pengguna->kata_sandi)) {
                return response()->json([
                    'sukses' => false,
                    'pesan' => 'Kata sandi lama tidak sesuai'
                ], 422);
            }

            // Update kata sandi
            $pengguna->update([
                'kata_sandi' => Hash::make($request->kata_sandi_baru),
            ]);

            return response()->json([
                'sukses' => true,
                'pesan' => 'Kata sandi berhasil diubah'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Terjadi kesalahan saat mengubah kata sandi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Muat data relasi berdasarkan peran pengguna.
     */
    private function muatDataRelasi(Pengguna $pengguna): array
    {
        $dataRelasi = [];

        switch ($pengguna->peran) {
            case 'mahasiswa':
                $dataRelasi['tipe_akses'] = 'mahasiswa';
                break;

            case 'dosen':
                $dataRelasi['tipe_akses'] = 'dosen';
                break;

            case 'staf':
                $dataRelasi['tipe_akses'] = 'staf';
                break;

            case 'admin':
                // Admin bisa akses semua data
                $dataRelasi['hak_akses'] = 'penuh';
                break;
        }

        return $dataRelasi;
    }
}