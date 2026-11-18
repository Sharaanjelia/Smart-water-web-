<?php

use App\Http\Controllers\LegacyPageController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SmartWaterPortalController;
use Illuminate\Support\Facades\Route;

$renderPage = static fn (string $page) => app(LegacyPageController::class)->show($page);
$basePath = static fn (string $path): string => rtrim(request()->getBaseUrl(), '/') . $path;

Route::get('/', fn () => $renderPage('beranda'));
Route::get('/pages/index.php', fn () => $renderPage((string) request()->query('page', 'beranda')));

Route::get('/beranda', fn () => $renderPage('beranda'));
Route::get('/meter-reading', fn () => redirect($basePath('/report-meter')))->name('meter-reading.index');
Route::post('/meter-reading', fn () => redirect($basePath('/report-meter')));
Route::get('/lapor-meter', fn () => redirect($basePath('/report-meter')));
Route::get('/meter', fn () => redirect($basePath('/report-meter')));
Route::get('/ai-ocr', fn () => redirect($basePath('/report-meter')))->name('ai-ocr');
Route::get('/layanan', fn () => $renderPage('pusat-layanan'));
Route::get('/pusat-layanan', fn () => $renderPage('pusat-layanan'));
Route::get('/akademi', fn () => $renderPage('akademi-air'));
Route::get('/akademi-air', fn () => $renderPage('akademi-air'));
Route::get('/tantangan', fn () => $renderPage('tantangan'));
Route::get('/tentang', fn () => $renderPage('tentang'));
Route::get('/login', fn () => $renderPage('login-daftar'))->name('login');
Route::post('/login', [SmartWaterPortalController::class, 'handleLogin'])->name('login.post');
Route::get('/login-daftar', fn () => $renderPage('login-daftar'));
Route::get('/daftar', fn () => $renderPage('login-daftar'))->name('daftar');
Route::get('/register', fn () => $renderPage('login-daftar'))->name('register');
Route::post('/register', [SmartWaterPortalController::class, 'handleRegister'])->name('register.post');
Route::post('/daftar', [SmartWaterPortalController::class, 'handleRegister']);
Route::any('/logout', [SmartWaterPortalController::class, 'logout'])->name('logout');
Route::get('/portal-petugas', [SmartWaterPortalController::class, 'enterAdminDirect'])->name('portal-petugas');
Route::get('/masuk-petugas', [SmartWaterPortalController::class, 'enterAdminDirect'])->name('masuk-petugas');
Route::get('/admin-login', [SmartWaterPortalController::class, 'adminLoginForm'])->name('admin-login');
Route::post('/admin-login', [SmartWaterPortalController::class, 'handleAdminLogin'])->name('admin-login.post');
Route::get('/petugas/login', [SmartWaterPortalController::class, 'adminLoginForm']);
Route::get('/admin/login', [SmartWaterPortalController::class, 'adminLoginForm']);
Route::get('/chat-ai', fn () => $renderPage('chat-ai'));
Route::get('/admin', fn () => $renderPage('admin'))->middleware('role:admin,staff');
Route::get('/user', fn () => $renderPage('user'));
Route::get('/profil', fn () => redirect($basePath('/profile')));

// ==========================================
// RUTE SMART WATER PORTAL (Pelanggan & Admin)
// ==========================================
Route::get('/switch-role/{role}', [SmartWaterPortalController::class, 'switchRole'])->name('switch-role');

// 1. Autentikasi & Akun
Route::get('/forgot-password', [SmartWaterPortalController::class, 'forgotPassword'])->name('forgot-password');

// 2. Alur AI-OCR Lapor Meter Mandiri
Route::get('/report-meter', [SmartWaterPortalController::class, 'reportMeter'])->name('report-meter');
Route::get('/scanning-meter', [SmartWaterPortalController::class, 'scanningMeter'])->name('scanning-meter');
Route::get('/meter-result', [SmartWaterPortalController::class, 'meterResult'])->name('meter-result');
Route::get('/meter-confirmation', [SmartWaterPortalController::class, 'meterConfirmation'])->name('meter-confirmation');

// 3. Alur Tagihan & Pembayaran (Payment Gateway)
Route::get('/bill', [SmartWaterPortalController::class, 'bill'])->name('bill');
Route::get('/bill-detail/{bill?}', [SmartWaterPortalController::class, 'billDetail'])->name('bill-detail');
Route::get('/bill-history', [SmartWaterPortalController::class, 'billHistory'])->name('bill-history');
Route::get('/payment-method', [SmartWaterPortalController::class, 'paymentMethod'])->name('payment-method');
Route::get('/payment-instruction', [SmartWaterPortalController::class, 'paymentInstruction'])->name('payment-instruction');
Route::get('/payment-success', [SmartWaterPortalController::class, 'paymentSuccess'])->name('payment-success');
Route::post('/payment/submit-manual', [PaymentController::class, 'store'])->name('payment.store');
Route::post('/admin/payments/{payment}/verify', [PaymentController::class, 'verify'])->name('admin.payments.verify')->middleware('role:admin,staff');

// 4. Layanan, Monitoring & Profil Pengguna
Route::get('/water-condition', [SmartWaterPortalController::class, 'waterCondition'])->name('water-condition');
Route::get('/notification', [SmartWaterPortalController::class, 'notification'])->name('notification');
Route::get('/profile', [SmartWaterPortalController::class, 'profile'])->name('profile');
Route::get('/wallet', [SmartWaterPortalController::class, 'wallet'])->name('wallet');
Route::get('/settings', [SmartWaterPortalController::class, 'settings'])->name('settings');

// 5. Portal Petugas / Admin & SCADA IoT (Terproteksi RoleMiddleware)
Route::middleware('role:admin,staff')->group(function () {
    Route::get('/admin-dashboard', [SmartWaterPortalController::class, 'adminDashboard'])->name('admin-dashboard');
    Route::get('/water-monitoring', [SmartWaterPortalController::class, 'waterMonitoring'])->name('water-monitoring');
    Route::get('/reservoir-detail', [SmartWaterPortalController::class, 'reservoirDetail'])->name('reservoir-detail');
    Route::get('/distribution-network', [SmartWaterPortalController::class, 'distributionNetwork'])->name('distribution-network');
    Route::get('/distribution-detail', [SmartWaterPortalController::class, 'distributionDetail'])->name('distribution-detail');
});

$legacyPages = [
    'php-admin' => '/admin',
    'php-akademi-air' => '/akademi-air',
    'php-beranda' => '/beranda',
    'php-chat-ai' => '/chat-ai',
    'php-login-daftar' => '/login',
    'php-pusat-layanan' => '/pusat-layanan',
    'php-tantangan' => '/tantangan',
    'php-tentang' => '/tentang',
    'php-user' => '/user',
];

foreach ($legacyPages as $module => $pageUrl) {
    $redirectToPage = static function () use ($pageUrl, $basePath) {
        $query = request()->query();
        $queryString = $query ? '?' . http_build_query($query) : '';

        return redirect($basePath($pageUrl) . $queryString, 302);
    };

    Route::get('/' . $module, $redirectToPage);
    Route::get('/' . $module . '/index.php', $redirectToPage);
    Route::get('/pages/' . $module, $redirectToPage);
    Route::get('/pages/' . $module . '/index.php', $redirectToPage);
}

$executeLegacyApi = static function (string $script) {
    $cleanScript = basename($script, '.php');
    $apiFile = base_path('app/LegacyApi/' . $cleanScript . '.php');
    if (!file_exists($apiFile)) {
        abort(404, 'API not found');
    }
    $_SERVER['REQUEST_METHOD'] = request()->method();

    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }
    if (function_exists('session')) {
        if (session()->has('swc_user') && empty($_SESSION['swc_user'])) {
            $_SESSION['swc_user'] = session('swc_user');
        }
        if (session()->has('swc_role') && empty($_SESSION['swc_role'])) {
            $_SESSION['swc_role'] = session('swc_role');
        }
    }

    ob_start();
    require $apiFile;
    $output = ob_get_clean();

    $status = http_response_code();
    if (!$status || $status < 200 || $status > 599) {
        $status = 200;
    }

    return response($output, $status)->header('Content-Type', 'application/json; charset=utf-8');
};

Route::any('/api/{script}.php', $executeLegacyApi);
Route::any('/api/{script}', $executeLegacyApi);
Route::any('/api/{folder}/{script}.php', fn ($folder, $script) => $executeLegacyApi($script));
Route::any('/api/{folder}/{script}', fn ($folder, $script) => $executeLegacyApi($script));
Route::any('/{prefix}/api/{script}.php', fn ($prefix, $script) => $executeLegacyApi($script));
Route::any('/{prefix}/api/{script}', fn ($prefix, $script) => $executeLegacyApi($script));

$legacyApis = [
    'php-admin/api/admin.php' => 'api/admin.php',
    'php-akademi-air/api/academy.php' => 'api/academy.php',
    'php-akademi-air/api/chat.php' => 'api/chat.php',
    'php-beranda/api/chat.php' => 'api/chat.php',
    'php-chat-ai/api/chat.php' => 'api/chat.php',
    'php-login-daftar/api/auth.php' => 'api/auth.php',
    'php-login-daftar/api/chat.php' => 'api/chat.php',
    'php-pusat-layanan/api/complaints.php' => 'api/complaints.php',
    'php-pusat-layanan/api/chat.php' => 'api/chat.php',
    'php-tantangan/api/challenges.php' => 'api/challenges.php',
    'php-tantangan/api/chat.php' => 'api/chat.php',
    'php-tentang/api/chat.php' => 'api/chat.php',
    'php-user/api/profile.php' => 'api/profile.php',
    'php-user/api/chat.php' => 'api/chat.php',
    'api/admin/admin.php' => 'api/admin.php',
    'api/academy/academy.php' => 'api/academy.php',
    'api/auth/auth.php' => 'api/auth.php',
    'api/beranda/chat.php' => 'api/chat.php',
    'api/challenges/challenges.php' => 'api/challenges.php',
    'api/chat/chat.php' => 'api/chat.php',
    'api/complaints/complaints.php' => 'api/complaints.php',
    'api/profile/profile.php' => 'api/profile.php',
];

foreach ($legacyApis as $legacyPath => $apiPath) {
    $redirectToApi = static function () use ($apiPath, $basePath) {
        $query = request()->query();
        $queryString = $query ? '?' . http_build_query($query) : '';

        return redirect($basePath('/' . $apiPath) . $queryString, 307);
    };

    Route::any('/' . $legacyPath, $redirectToApi);
    Route::any('/pages/' . $legacyPath, $redirectToApi);
}
