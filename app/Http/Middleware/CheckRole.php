<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses rute berdasarkan role user.
 *
 * Contoh pemakaian di routes:
 *   ->middleware('role:admin')          // hanya admin
 *   ->middleware('role:admin,kader')    // admin ATAU kader
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login (warga / guest)
        if (! $user) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Silakan login sebagai Kader / Admin terlebih dahulu.',
                ], 401);
            }

            return redirect()->guest(route('login'))
                ->withErrors(['email' => 'Silakan login sebagai Kader / Admin untuk mengakses fitur ini.']);
        }

        // Sudah login tapi role tidak sesuai atau akun nonaktif
        if (! $user->isActive() || ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak. Anda tidak memiliki izin.',
                ], 403);
            }

            abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
        }

        return $next($request);
    }
}
