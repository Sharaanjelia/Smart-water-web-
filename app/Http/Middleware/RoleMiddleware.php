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
        if (!$user) {
            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                @session_start();
            }
            $sessionUserId = (function_exists('session') && session()->has('swc_user.id'))
                ? session('swc_user.id')
                : ($_SESSION['swc_user']['id'] ?? ($_SESSION['swc_user']['user_id'] ?? null));

            if (!$sessionUserId && !empty($_SESSION['swc_user']['email'])) {
                $user = User::where('email', $_SESSION['swc_user']['email'])->first();
            } elseif ($sessionUserId) {
                $user = User::find($sessionUserId);
            }

            if ($user) {
                Auth::setUser($user);
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

            return redirect(url('/login'))->with('error', 'Silakan masuk akun terlebih dahulu.');
        }

        // 2. Pemeriksaan Otorisasi Role (RBAC Berbasis Database MySQL)
        if (!empty($roles) && !$user->hasRole($roles)) {
            if ($request->expectsJson() || $request->is('api/*') || $request->isJson()) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Akses ditolak: Anda tidak memiliki hak akses yang memadai.',
                ], 403);
            }

            // Jika pengguna adalah Pelanggan yang mencoba mengakses area operasional internal
            if ($user->isCustomer()) {
                return redirect(url('/user'))->with('error', 'Akses ditolak: Anda tidak memiliki wewenang administrator.');
            }

            // Jika staf internal mencoba membuka modul di luar kewenangannya (misal Field Officer membuka SCADA)
            $destination = \App\Services\AuthService::getRedirectForUser($user);
            return redirect($destination)->with('error', 'Akses ditolak: Anda tidak memiliki hak akses ke modul ini.');
        }

        return $next($request);
    }
}
