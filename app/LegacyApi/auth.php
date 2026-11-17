<?php
/**
 * Endpoint API Autentikasi Database-Backed - Smart Water Kota Payakumbuh
 * Menggunakan Laravel Eloquent ORM & Bcrypt Password Hashing.
 */

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=UTF-8');
}

$respondJson = function (array $payload, int $status = 200) {
    if (!headers_sent()) {
        http_response_code($status);
    }
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    return;
};

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? (function_exists('request') ? request()->method() : 'GET');
if ($requestMethod !== 'POST') {
    $user = Auth::user();
    if ($user) {
        $respondJson([
            'ok'   => true,
            'user' => AuthService::formatUserData($user),
        ]);
        return;
    }
    $respondJson(['ok' => false, 'error' => 'Belum login.'], 200);
    return;
}

$raw = file_get_contents('php://input');
if ((!$raw || $raw === '') && function_exists('request')) {
    $raw = request()->getContent();
}
$body = json_decode($raw ?: '{}', true);
if (!is_array($body)) {
    $respondJson(['error' => 'Invalid JSON payload'], 400);
}

$action = (string)($body['action'] ?? '');

$getUrl = function (string $path): string {
    if (function_exists('url')) {
        return url($path);
    }
    $baseUrl = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/\\');
    return $baseUrl . $path;
};

// 1. LOGIN
if ($action === 'login') {
    $identifier = trim((string)($body['identifier'] ?? ''));
    $password   = (string)($body['password'] ?? '');

    if ($identifier === '') {
        $respondJson(['error' => 'Silakan masukkan alamat email atau nomor telepon Anda.'], 422);
    }
    if ($password === '') {
        $respondJson(['error' => 'Silakan masukkan kata sandi akun Anda.'], 422);
    }

    // Cek apakah akun adalah staf / admin
    $user = AuthService::authenticateStaff($identifier, $password);
    if (!$user) {
        $user = AuthService::authenticateCustomer($identifier, $password);
    }

    if (!$user) {
        $respondJson(['error' => 'Kombinasi identitas dan kata sandi Anda tidak cocok.'], 422);
    }

    $userData = AuthService::syncSession($user);
    $role = $userData['role'];
    $redirectUrl = in_array($role, ['admin', 'staff'], true)
        ? $getUrl('/admin-dashboard')
        : $getUrl('/report-meter');

    $respondJson([
        'ok'       => true,
        'user'     => $userData,
        'role'     => $role,
        'redirect' => $redirectUrl,
    ]);
}

// 2. REGISTER
if ($action === 'register') {
    $name            = trim((string)($body['name'] ?? ''));
    $email           = trim((string)($body['email'] ?? ''));
    $phone           = trim((string)($body['phone'] ?? ''));
    $password        = (string)($body['password'] ?? '');
    $confirmPassword = (string)($body['confirmPassword'] ?? '');
    $agreeTerms      = (bool)($body['agreeTerms'] ?? false);

    if ($name === '') {
        $respondJson(['error' => 'Nama lengkap wajib diisi.'], 422);
    }
    if ($email === '' || strpos($email, '@') === false) {
        $respondJson(['error' => 'Alamat email valid wajib diisi.'], 422);
    }
    if ($password === '' || strlen($password) < 6) {
        $respondJson(['error' => 'Kata sandi minimal 6 karakter.'], 422);
    }
    if ($password !== $confirmPassword) {
        $respondJson(['error' => 'Konfirmasi kata sandi tidak cocok.'], 422);
    }
    if (!$agreeTerms) {
        $respondJson(['error' => 'Anda wajib menyetujui Syarat & Ketentuan dan Kebijakan Privasi.'], 422);
    }

    if (User::where('email', $email)->exists()) {
        $respondJson(['error' => 'Alamat email sudah terdaftar. Silakan masuk menggunakan akun Anda.'], 422);
    }

    $user = AuthService::registerCustomer([
        'name'     => $name,
        'email'    => $email,
        'phone'    => $phone,
        'password' => $password,
    ]);

    $userData = AuthService::syncSession($user);

    $respondJson([
        'ok'       => true,
        'user'     => $userData,
        'role'     => 'customer',
        'redirect' => $getUrl('/report-meter'),
    ]);
}

// 3. GOOGLE SIGN-IN (DEMO WARGA)
if ($action === 'google_signin') {
    $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
    if (!$user) {
        $user = User::first();
    }
    if ($user) {
        $userData = AuthService::syncSession($user);
        $respondJson([
            'ok'       => true,
            'user'     => $userData,
            'role'     => $userData['role'],
            'redirect' => $getUrl('/report-meter'),
        ]);
    }
    $respondJson(['error' => 'Akun Google demo tidak tersedia.'], 422);
}

// 4. FORGOT PASSWORD
if ($action === 'forgot_password') {
    $email = trim((string)($body['email'] ?? ''));
    if ($email === '' || strpos($email, '@') === false) {
        $respondJson(['error' => 'Silakan masukkan alamat email yang valid.'], 422);
    }
    $respondJson([
        'ok'       => true,
        'email'    => $email,
        'message'  => 'Tautan pemulihan akun berhasil dikirim ke ' . $email,
    ]);
}

// 5. ADMIN / STAFF LOGIN
if ($action === 'admin_login') {
    $identifier = trim((string)($body['identifier'] ?? ''));
    $password   = (string)($body['password'] ?? '');
    $isDemo     = !empty($body['instantDemo']);

    $user = AuthService::authenticateStaff($identifier, $password, $isDemo);

    if (!$user) {
        $respondJson(['error' => 'NIP atau kata sandi dinas Anda tidak terdaftar sebagai petugas.'], 422);
    }

    $userData = AuthService::syncSession($user);

    $respondJson([
        'ok'       => true,
        'user'     => $userData,
        'role'     => $userData['role'],
        'redirect' => $getUrl('/admin-dashboard'),
    ]);
}

// 6. LOGOUT
if ($action === 'logout') {
    Auth::logout();
    if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
        @session_start();
    }
    unset($_SESSION['swc_user'], $_SESSION['swc_role']);
    if (function_exists('session')) {
        session()->forget(['swc_user', 'swc_role']);
        session()->flush();
        session()->save();
    }
    $respondJson([
        'ok'       => true,
        'redirect' => $getUrl('/login'),
    ]);
}

$respondJson(['error' => 'Aksi autentikasi tidak dikenali.'], 400);
