<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user() ?? Auth::user();

        // Fallback resolver untuk sesi pengguna legacy jika Auth belum terikat
        if (!$user && function_exists('session') && session()->has('swc_user.id')) {
            $sessionUserId = session('swc_user.id');
            if ($sessionUserId) {
                $user = User::find($sessionUserId);
                if ($user) {
                    Auth::setUser($user);
                }
            }
        }

        // 1. Pemeriksaan Autentikasi (Belum Login)
        if (!$user) {
            if ($request->expectsJson() || $request->is('api/*') || $request->isJson()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Unauthenticated: Silakan masuk akun terlebih dahulu.',
                ], 401);
            }

            if (in_array('admin', $roles, true) || in_array('staff', $roles, true)) {
                return redirect(url('/admin-login'))->with('error', 'Halaman ini khusus untuk Petugas & Operator PDAM. Silakan masuk dengan akun kedinasan Anda.');
            }

            return redirect(url('/login'))->with('error', 'Silakan masuk akun terlebih dahulu.');
        }

        // 2. Pemeriksaan Otorisasi Role (RBAC)
        if (!empty($roles) && !$user->hasRole($roles)) {
            if ($request->expectsJson() || $request->is('api/*') || $request->isJson()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Akses ditolak: Anda tidak memiliki hak akses yang memadai.',
                ], 403);
            }

            // Jika pengguna adalah Pelanggan yang mencoba mengakses area operasional Admin/Staff
            if ($user->hasRole('customer') && (in_array('admin', $roles, true) || in_array('staff', $roles, true))) {
                return redirect(url('/user'))->with('error', 'Akses ditolak: Anda tidak memiliki wewenang administrator.');
            }

            abort(403, 'Akses Ditolak: Anda tidak memiliki izin untuk membuka halaman ini.');
        }

        return $next($request);
    }
}
