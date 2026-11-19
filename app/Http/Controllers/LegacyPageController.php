<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegacyPageController extends Controller
{
    public function show(string $page): View|\Illuminate\Http\RedirectResponse
    {
        $pageModules = [
            'admin' => 'php-admin',
            'akademi-air' => 'php-akademi-air',
            'beranda' => 'php-beranda',
            'chat-ai' => 'php-chat-ai',
            'login-daftar' => 'php-login-daftar',
            'pusat-layanan' => 'php-pusat-layanan',
            'tantangan' => 'php-tantangan',
            'tentang' => 'php-tentang',
            'user' => 'php-user',
        ];

        abort_unless(isset($pageModules[$page]), 404);

        $module = $pageModules[$page];
        $baseUrl = rtrim(request()->getBaseUrl(), '/');
        $legacyBaseHref = $baseUrl . '/pages/' . $module . '/';
        $legacyPagePath = resource_path('views/pages/' . $page);
        $legacySharedPath = resource_path('views/shared');
        $SWC_PAGE_MODULE = $module;
        $SWC_PAGE_URL = $baseUrl . '/' . $page;

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        if (!\Illuminate\Support\Facades\Auth::check()) {
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('users')) {
                    $sessionUid = (function_exists('session') && session()->has('swc_user.id'))
                        ? session('swc_user.id')
                        : ($_SESSION['swc_user']['id'] ?? ($_SESSION['swc_user']['user_id'] ?? null));

                    $authDbUser = null;
                    if (!$sessionUid && !empty($_SESSION['swc_user']['email'])) {
                        $authDbUser = \App\Models\User::where('email', $_SESSION['swc_user']['email'])->first();
                    } elseif ($sessionUid) {
                        $authDbUser = \App\Models\User::find($sessionUid);
                    }

                    if ($authDbUser) {
                        \Illuminate\Support\Facades\Auth::setUser($authDbUser);
                        if (function_exists('session')) {
                            session(['swc_user' => \App\Services\AuthService::formatUserData($authDbUser)]);
                            session(['swc_role' => $authDbUser->roles->first()?->name ?? 'customer']);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan kesalahan DB pada test unmigrated
            }
        }

        // Aturan Hak Akses: Tamu (Guest) hanya diizinkan mengakses Beranda.
        // Halaman Pusat Layanan, AI OCR, Akademi Air, Tentang PDAM, Tantangan Air, dan Chat AI
        // wajib login terlebih dahulu sebelum dapat diakses.
        $protectedPages = [
            'pusat-layanan',
            'akademi-air',
            'tantangan',
            'tentang',
            'chat-ai',
        ];

        $isAuthed = \Illuminate\Support\Facades\Auth::check()
            || !empty(session('swc_user'))
            || !empty($_SESSION['swc_user']);

        if (in_array($page, $protectedPages, true) && !$isAuthed) {
            $dest = request()->fullUrl();
            return redirect(url('/login?redirect=' . urlencode($dest)))
                ->with('info', 'Silakan masuk terlebih dahulu untuk mengakses layanan dan fitur Smart Water.');
        }

        if (function_exists('session')) {
            if (session()->has('swc_user') && empty($_SESSION['swc_user'])) {
                $_SESSION['swc_user'] = session('swc_user');
                $_SESSION['swc_role'] = session('swc_role');
            } elseif (!empty($_SESSION['swc_user']) && !session()->has('swc_user')) {
                session(['swc_user' => $_SESSION['swc_user'], 'swc_role' => $_SESSION['swc_role'] ?? 'customer']);
            }
        }

        require base_path('app/LegacyConfig.php');

        $viewData = get_defined_vars();
        unset(
            $viewData['this'],
            $viewData['pageModules'],
            $viewData['page'],
            $viewData['module'],
            $viewData['baseUrl'],
            $viewData['viewData']
        );

        return view('pages.' . $page, $viewData);
    }
}
