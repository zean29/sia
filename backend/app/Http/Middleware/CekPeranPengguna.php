<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CekPeranPengguna
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$peranYangDiizinkan): Response
    {
        // Check if user is authenticated
        if (!$request->user()) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Akses ditolak. Silakan login terlebih dahulu.'
            ], 401);
        }

        $pengguna = $request->user();

        // Check if user is active
        if (!$pengguna->aktif) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Akun Anda tidak aktif. Silakan hubungi administrator.'
            ], 403);
        }

        // Check user role
        if (!empty($peranYangDiizinkan) && !in_array($pengguna->peran, $peranYangDiizinkan)) {
            return response()->json([
                'sukses' => false,
                'pesan' => 'Akses ditolak. Anda tidak memiliki hak akses untuk fitur ini.'
            ], 403);
        }

        return $next($request);
    }
}