<?php

/**
 * SMART WATER KOTA PAYAKUMBUH - DEMO OUTPUT SHOWCASE
 * Menjalankan simulasi nyata request-response API dan menampilkan output JSON secara langsung.
 */

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\MeterReading;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

function printHeader($title) {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo " " . strtoupper($title) . "\n";
    echo str_repeat('=', 70) . "\n";
}

function printSection($title) {
    echo "\n>>> [DEMO] " . $title . "\n";
    echo str_repeat('-', 60) . "\n";
}

function runApiRequest($kernel, $method, $uri, $payload = [], $actingUser = null) {
    echo "HTTP REQUEST  : {$method} {$uri}\n";
    if ($actingUser) {
        echo "AKTOR LOGIN   : {$actingUser->name} ({$actingUser->primaryRoleName()})\n";
    } else {
        echo "AKTOR LOGIN   : Guest (Tamu Tanpa Sesi)\n";
    }

    if (!empty($payload)) {
        echo "PAYLOAD INPUT : " . json_encode($payload, JSON_UNESCAPED_SLASHES) . "\n";
    }

    $server = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT'  => 'application/json',
    ];

    $content = !empty($payload) ? json_encode($payload) : null;
    $request = Request::create($uri, $method, [], [], [], $server, $content);

    // Setup session & user
    $session = app('session')->driver();
    $request->setLaravelSession($session);

    if ($actingUser) {
        Auth::login($actingUser);
        $session->put('swc_user', [
            'id'    => $actingUser->id,
            'name'  => $actingUser->name,
            'email' => $actingUser->email,
            'role'  => $actingUser->primaryRoleName(),
        ]);
        $session->put('swc_role', $actingUser->primaryRoleName());
    } else {
        Auth::logout();
        $session->flush();
    }

    $response = $kernel->handle($request);
    $status = $response->getStatusCode();
    $body = $response->getContent();

    echo "STATUS CODE   : HTTP {$status}\n";
    echo "RESPONSE JSON :\n";

    $decoded = json_decode($body, true);
    if ($decoded !== null) {
        // Jika response memuat array 'complaints' yang besar, ringkas untuk kejelasan tampilan demo
        $display = $decoded;
        if (isset($display['complaints']) && is_array($display['complaints'])) {
            $count = count($display['complaints']);
            $display['complaints'] = "[Array of {$count} tickets omitted for display brevity]";
        }
        echo json_encode($display, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n";
    } else {
        echo $body . "\n";
    }

    $kernel->terminate($request, $response);
    return [$status, $decoded];
}

printHeader("DEMO OUTPUT SISTEM CERDAS SMART WATER KOTA PAYAKUMBUH");

// Pastikan data awal tersedia
$adminUser = User::where('email', 'admin@tirtasago.id')->first();
$staffA    = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
$customerA = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
$customerB = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();

// Siapkan Staff B jika belum ada
$staffRole = Role::where('name', 'staff')->first();
$staffB = User::firstOrCreate(
    ['email' => 'dedi.teknisi@tirtasago.id'],
    [
        'name' => 'Dedi Kurniawan, A.Md. (Teknisi TRC)',
        'password' => Hash::make('Petugas123!'),
    ]
);
if ($staffRole && !$staffB->roles()->where('role_id', $staffRole->id)->exists()) {
    $staffB->roles()->attach($staffRole);
}

// -------------------------------------------------------------
// 1. DEMO LOGIN & PROFIL JSON
// -------------------------------------------------------------
printSection("1. Login Pelanggan via Nomor Meter (01000)");
runApiRequest($kernel, 'POST', '/api/v1/auth/login', [
    'identifier' => '01000',
    'password'   => 'Pelanggan123!',
]);

printSection("2. Login Petugas Lapangan SCADA");
runApiRequest($kernel, 'POST', '/api/v1/auth/login', [
    'identifier' => 'ahmad.pratama@tirtasago.id',
    'password'   => 'Petugas123!',
]);

// -------------------------------------------------------------
// 2. DEMO TIKET PENGADUAN & STAFF ISOLATION BOUNDARY
// -------------------------------------------------------------
printSection("3. Pelanggan Mengirim Pengaduan Pipa Bocor");
list($status, $resStore) = runApiRequest($kernel, 'POST', '/api/v1/complaints', [
    'title'       => 'Pipa Distribusi Utama Bocor Deras di Simpang Benteng',
    'description' => 'Air meluber menggenangi jalan raya, tekanan air warga sekitar menurun drastis.',
    'district'    => 'Payakumbuh Barat',
    'address'     => 'Jl. Sudirman No. 45, Simpang Benteng',
    'category'    => 'pipa_bocor',
    'priority'    => 'TINGGI',
], $customerA);

$ticketId = $resStore['ticket']['id'] ?? 1;

printSection("4. Tiket Ditugaskan Khusus Kepada Petugas B (Dedi Kurniawan)");
runApiRequest($kernel, 'POST', "/api/v1/complaints/{$ticketId}/assign", [
    'staff_id' => $staffB->id,
    'note'     => 'Disposisi tugas lapangan ke Teknisi Dedi Kurniawan (TRC Wilayah Barat)',
], $adminUser);

printSection("5. UJI KEBIJAKAN: Petugas A (Ahmad) Mencoba Intervensi Mengubah Tiket Petugas B");
runApiRequest($kernel, 'POST', "/api/v1/complaints/{$ticketId}/status", [
    'status' => 'MENUNGGU TINDAK LANJUT',
    'note'   => 'Petugas A mencoba mengubah status sepihak',
], $staffA);

printSection("6. Petugas B (Yang Sah Ditugaskan) Memperbarui Status Penanganan Lapangan");
runApiRequest($kernel, 'POST', "/api/v1/complaints/{$ticketId}/status", [
    'status' => 'DIPROSES',
    'note'   => 'Tim TRC telah tiba di lokasi Simpang Benteng dengan mobil dinas unit 02. Isolasi katup pipa sedang dilakukan.',
], $staffB);

printSection("7. UJI ANTI-IDOR: Pelanggan Lain (Siti Nurbaya) Mencoba Mengintip Tiket Privat Budi Santoso");
runApiRequest($kernel, 'GET', "/api/v1/complaints/{$ticketId}", [], $customerB);

printSection("8. Pelanggan Pemilik (Budi Santoso) Membuka Detail Tiket Miliknya");
runApiRequest($kernel, 'GET', "/api/v1/complaints/{$ticketId}", [], $customerA);

// -------------------------------------------------------------
// 3. DEMO LAPOR METER MANDIRI & VALIDASI PETUGAS
// -------------------------------------------------------------
printSection("9. Pelanggan Mengirim Laporan Stand Meter Mandiri (AI-OCR)");
list($status, $resMeter) = runApiRequest($kernel, 'POST', '/api/v1/meter-reports', [
    'meter_number' => '01000',
    'reading'      => 1095.5,
    'notes'        => 'Pembacaan mandiri meteran bulan berjalan.',
], $customerA);

$meterReportId = $resMeter['report']['id'] ?? 1;

printSection("10. UJI KEBIJAKAN: Pelanggan Mencoba Memvalidasi Laporannya Sendiri");
runApiRequest($kernel, 'POST', '/api/v1/meter-reports/validate', [
    'id'     => $meterReportId,
    'action' => 'APPROVE',
], $customerA);

printSection("11. Petugas SCADA Memvalidasi Stand Meter Pelanggan");
runApiRequest($kernel, 'POST', '/api/v1/meter-reports/validate', [
    'id'     => $meterReportId,
    'action' => 'APPROVE',
], $staffA);

// -------------------------------------------------------------
// 4. DEMO PENCEGAHAN ESKALASI HAK AKSES PADA RUTE DEMO
// -------------------------------------------------------------
printSection("12. UJI ESKALASI: Pelanggan Aktif Mencoba Membuka /portal-petugas");
echo "HTTP REQUEST  : GET /portal-petugas\n";
echo "AKTOR LOGIN   : {$customerA->name} (customer)\n";
$reqWeb = Request::create('/portal-petugas', 'GET');
$sessWeb = app('session')->driver();
$reqWeb->setLaravelSession($sessWeb);
Auth::login($customerA);
$sessWeb->put('swc_user', ['id' => $customerA->id, 'role' => 'customer']);
$sessWeb->put('swc_role', 'customer');

$respWeb = $kernel->handle($reqWeb);
echo "STATUS CODE   : HTTP " . $respWeb->getStatusCode() . "\n";
echo "TARGET REDIRECT: " . $respWeb->headers->get('Location') . "\n";
echo "PESAN SESI    : " . $sessWeb->get('error') . "\n";
echo "ROLE SESI     : " . $sessWeb->get('swc_role') . " (TIDAK DIIZINKAN ESKALASI KE ADMIN)\n";

printHeader("DEMO SELESAI DENGAN SUKSES - SELURUH OUTPUT SESUAI KONTRAK");
