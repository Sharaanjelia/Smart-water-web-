<?php

use App\Http\Controllers\AcademyController;
use App\Http\Controllers\BillController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\MeterReadingController;
use App\Http\Controllers\PaymentController;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Notification;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SMART WATER REST API ROUTES (V1)
|--------------------------------------------------------------------------
| Endpoint terpadu untuk integrasi website dan aplikasi mobile.
*/

Route::prefix('v1')->group(function () {

    // --- 1. AUTENTIKASI ---
    Route::prefix('auth')->group(function () {
        Route::post('/login', function (LoginRequest $request) {
            $identifier = trim((string)$request->input('identifier', ''));
            $password   = (string)$request->input('password', '');

            $userData = AuthService::attemptLogin($identifier, $password);
            if (!$userData) {
                return response()->json(['error' => 'Kombinasi identitas dan kata sandi salah.'], 401);
            }

            return response()->json([
                'ok'       => true,
                'message'  => 'Login berhasil.',
                'user'     => $userData,
                'role'     => $userData['role'],
                'redirect' => AuthService::getRedirectForUser(Auth::user()),
            ]);
        });

        Route::post('/register', function (RegisterCustomerRequest $request) {
            $validated = $request->validated();

            $user = AuthService::registerCustomer($validated);
            $userData = AuthService::syncSession($user);

            return response()->json([
                'ok'      => true,
                'message' => 'Pendaftaran akun berhasil.',
                'user'    => $userData,
            ], 201);
        });

        Route::post('/logout', function () {
            AuthService::performLogout();
            return response()->json(['ok' => true, 'message' => 'Berhasil keluar dari sesi.']);
        });

        Route::get('/me', function () {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Belum login.'], 401);
            }
            return response()->json([
                'ok'   => true,
                'user' => new UserProfileResource($user),
            ]);
        });
    });

    // --- 2. PUSAT PENGADUAN (SERVICE CENTER) ---
    Route::get('/complaints', [ComplaintController::class, 'index']);
    Route::post('/complaints', [ComplaintController::class, 'store']);
    Route::get('/complaints/{id}', [ComplaintController::class, 'show']);
    Route::post('/complaints/simulate-progress', [ComplaintController::class, 'simulateProgress']);

    // Tindak Lanjut Pengaduan Khusus Petugas TRC / Admin SCADA
    Route::post('/complaints/{id}/assign', [ComplaintController::class, 'assign'])->middleware('role:admin');
    Route::middleware('role:admin,field_officer')->group(function () {
        Route::post('/complaints/{id}/status', [ComplaintController::class, 'updateStatus']);
        Route::post('/complaints/{id}/response', [ComplaintController::class, 'respond']);
        Route::post('/complaints/{id}/resolve', [ComplaintController::class, 'resolve']);
    });

    // --- 3. AKADEMI AIR ---
    Route::get('/academy', [AcademyController::class, 'index']);
    Route::post('/academy/complete-lesson', [AcademyController::class, 'completeLesson']);
    Route::post('/academy/complete-quiz', [AcademyController::class, 'completeQuiz']);

    // --- 4. TANTANGAN AIR ---
    Route::get('/challenges', [ChallengeController::class, 'index']);
    Route::post('/challenges/enroll', [ChallengeController::class, 'enroll']);
    Route::post('/challenges/complete-mission', [ChallengeController::class, 'completeMission']);

    // --- 5. PEMBACAAN METER AI-OCR ---
    Route::get('/meter-reports', [MeterReadingController::class, 'apiList']);
    Route::post('/meter-reports', [MeterReadingController::class, 'apiStore']);
    Route::post('/meter-reports/validate', [MeterReadingController::class, 'apiValidate'])->middleware('role:admin,scada_operator');

    // --- 6. NOTIFIKASI ---
    Route::get('/notifications', function () {
        $user = Auth::user();
        $notificationService = app(\App\Services\NotificationService::class);
        $notifs = $user ? $notificationService->getForUser($user->id, 20) : collect();

        return response()->json([
            'ok'            => true,
            'notifications' => NotificationResource::collection($notifs),
            'unreadCount'   => $notifs->where('is_read', false)->count(),
        ]);
    });

    Route::post('/notifications/mark-all-read', function () {
        $user = Auth::user();
        if ($user) {
            app(\App\Services\NotificationService::class)->markAllAsRead($user->id);
        }
        return response()->json(['ok' => true]);
    });

    // --- 7. PROFIL PENGGUNA ---
    Route::get('/profile', function () {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Belum masuk sesi akun.'], 401);
        }
        return response()->json([
            'ok'      => true,
            'profile' => new UserProfileResource($user),
        ]);
    });

    Route::post('/profile/update', function (UpdateProfileRequest $request) {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Belum masuk sesi akun.'], 401);
        }

        $phone   = trim((string)$request->input('phone', ''));
        $address = trim((string)$request->input('address', ''));

        $profile = $user->customerProfile;
        if ($profile) {
            if (!Gate::forUser($user)->allows('update', $profile)) {
                return response()->json(['error' => 'Akses ditolak: Anda tidak memiliki wewenang memperbarui profil ini.'], 403);
            }
            if ($phone !== '') $profile->phone = $phone;
            if ($address !== '') $profile->address = $address;
            $profile->save();
        }

        $freshUser = $user->fresh(['customerProfile', 'staffProfile']);
        AuthService::syncSession($freshUser);

        return response()->json([
            'ok'      => true,
            'message' => 'Data profil berhasil diperbarui.',
            'user'    => new UserProfileResource($freshUser),
        ]);
    });

    // --- 8. TAGIHAN & PEMBAYARAN MANUAL (BILLING & PAYMENT) ---
    Route::middleware('auth')->group(function () {
        Route::get('/bills', [BillController::class, 'index']);
        Route::get('/bills/active', [BillController::class, 'active']);
        Route::get('/bills/{bill}', [BillController::class, 'show']);

        Route::get('/payments', [PaymentController::class, 'index']);
        Route::post('/payments', [PaymentController::class, 'store']);
        Route::get('/payments/{payment}', [PaymentController::class, 'show']);
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify'])->middleware('role:admin');
    });

});
