<?php
/**
 * Endpoint API Profil Pelanggan Database-Backed - Smart Water Kota Payakumbuh
 */

use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\MeterReadingController;
use App\Models\CustomerProfile;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;

if (!headers_sent()) {
    @header('Content-Type: application/json; charset=utf-8');
}

$SWC_PAGE_MODULE = 'php-user';
require_once dirname(__DIR__, 2) . '/app/LegacyConfig.php';

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? (function_exists('request') ? request()->method() : 'GET');
if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
    return;
}

$raw = file_get_contents('php://input');
if ((!$raw || $raw === '') && function_exists('request')) {
    $raw = request()->getContent();
}
$payload = json_decode($raw ?: '[]', true);
if (!is_array($payload)) {
    $payload = $_POST;
}

$action = trim((string) ($payload['action'] ?? ''));

if ($action === 'logout') {
    AuthService::performLogout();
    echo json_encode([
        'ok'      => true,
        'message' => 'Anda telah keluar dari sesi pelanggan.',
    ]);
    return;
}

$authUser = Auth::user();
if (!$authUser && !empty($_SESSION['swc_user']['email'])) {
    $authUser = User::where('email', $_SESSION['swc_user']['email'])->first();
    if ($authUser) {
        Auth::login($authUser);
    }
}

if (!$authUser) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Silakan masuk akun pelanggan terlebih dahulu.']);
    return;
}

switch ($action) {
    case 'update_contact':
        $phone   = trim((string) ($payload['phone'] ?? ''));
        $email   = trim((string) ($payload['email'] ?? ''));
        $address = trim((string) ($payload['address'] ?? ''));

        if ($authUser) {
            if ($email !== '' && $email !== $authUser->email) {
                $authUser->email = $email;
                $authUser->save();
            }
            $profile = $authUser->customerProfile;
            if ($profile) {
                if ($phone !== '') $profile->phone = $phone;
                if ($address !== '') $profile->address = $address;
                $profile->save();
            }
            $userData = AuthService::syncSession($authUser->fresh(['customerProfile']));
        } else {
            if ($phone !== '') $_SESSION['swc_user']['phone'] = $phone;
            if ($email !== '') $_SESSION['swc_user']['email'] = $email;
            if ($address !== '') $_SESSION['swc_user']['address'] = $address;
            $userData = $_SESSION['swc_user'];
        }

        echo json_encode([
            'ok'      => true,
            'message' => 'Data identitas dan kontak berhasil diperbarui di database!',
            'user'    => $userData,
        ]);
        return;

    case 'save_meter_reading':
        $stand = (float) ($payload['standMeterCurrent'] ?? 0);
        $lastStand = (float) ($authUser?->customerProfile?->current_meter_value ?? ($_SESSION['swc_user']['currentMeterValue'] ?? 1000));

        $volume = max(0, round($stand - $lastStand, 1));
        $cost   = round(($volume * 7800) + 2600);

        if ($authUser) {
            $reading = \App\Models\MeterReading::create([
                'report_number'    => 'REP-04-' . str_pad((string)random_int(1000, 9999), 4, '0', STR_PAD_LEFT),
                'user_id'          => $authUser->id,
                'meter_number'     => $authUser->customerProfile?->meter_number ?? '01000',
                'reading'          => $stand,
                'previous_reading' => $lastStand,
                'consumption'      => $volume,
                'estimated_cost'   => $cost,
                'customer_name'    => $authUser->name,
                'zone'             => $authUser->customerProfile?->zone ?? 'DIST-A Sektor Utara',
                'status'           => 'Menunggu Validasi',
                'notes'            => 'Pencatatan meter mandiri via tab profil',
            ]);

            $profile = $authUser->customerProfile;
            if ($profile) {
                $profile->update([
                    'last_meter_value'    => $lastStand,
                    'current_meter_value' => $stand,
                ]);
            }
            $userData = AuthService::syncSession($authUser->fresh(['customerProfile']));
        } else {
            $_SESSION['swc_user']['currentMeterValue'] = $stand;
            $userData = $_SESSION['swc_user'];
        }

        echo json_encode([
            'ok'      => true,
            'message' => "Stand meter ({$stand} m³) berhasil dicatat! Pemakaian terhitung: {$volume} m³.",
            'user'    => $userData,
        ]);
        return;

    case 'create_complaint':
        $response = app(ComplaintController::class)->store(request());
        http_response_code($response->getStatusCode());
        echo $response->getContent();
        return;

    default:
        echo json_encode(['ok' => false, 'message' => 'Aksi tidak dikenal.']);
        return;
}
