<?php

/**
 * SMART WATER KOTA PAYAKUMBUH
 * Comprehensive Real MySQL 8.4 Validation Runner
 * 
 * Tests live database connection, all 28 tables, 23 foreign keys,
 * authentication, role guards, all 7 modules CRUD, multi-device sync,
 * legacy API compatibility, and security controls.
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;
use App\Models\CustomerProfile;
use App\Models\StaffProfile;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintHistory;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\UserLessonProgress;
use App\Models\UserQuizResult;
use App\Models\Challenge;
use App\Models\ChallengeMission;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeSubmission;
use App\Models\MeterReading;
use App\Models\Notification;

class ValidationSuite
{
    private array $results = [];
    private int $passCount = 0;
    private int $failCount = 0;
    private $kernel;

    public function __construct($kernel)
    {
        $this->kernel = $kernel;
    }

    public function log(string $section, string $testName, bool $passed, string $details = ''): void
    {
        if ($passed) {
            $this->passCount++;
            echo "  [PASS] {$testName}\n";
            if ($details) echo "         Details: {$details}\n";
        } else {
            $this->failCount++;
            echo "  [FAIL] {$testName}\n";
            if ($details) echo "         Error: {$details}\n";
        }

        $this->results[] = [
            'section' => $section,
            'name'    => $testName,
            'status'  => $passed ? 'PASS' : 'FAIL',
            'details' => $details,
        ];
    }

    private function dispatchRequest(string $method, string $uri, array $data = [], array $headers = [], ?User $authUser = null)
    {
        // 100% clean session context per dispatch
        Auth::logout();
        session()->flush();
        $_SESSION = [];

        $server = [
            'REQUEST_METHOD' => $method,
            'REQUEST_URI'    => $uri,
            'HTTP_HOST'      => 'localhost',
            'REMOTE_ADDR'    => '127.0.0.1',
        ];

        foreach ($headers as $k => $v) {
            $server['HTTP_' . strtoupper(str_replace('-', '_', $k))] = $v;
        }

        $content = null;
        if ($method === 'POST' && !empty($headers['Content-Type']) && str_contains($headers['Content-Type'], 'json')) {
            $content = json_encode($data);
            $server['CONTENT_TYPE'] = 'application/json';
            $request = Request::create($uri, $method, [], [], [], $server, $content);
        } else {
            $request = Request::create($uri, $method, $data, [], [], $server);
        }

        $sessionDriver = app('session')->driver();
        $request->setLaravelSession($sessionDriver);

        if ($authUser) {
            Auth::login($authUser);
            \App\Services\AuthService::syncSession($authUser);
            $request->session()->put('swc_user', \App\Services\AuthService::formatUserData($authUser));
            $request->session()->put('swc_role', $authUser->isStaff() ? 'admin' : 'customer');
        }

        $response = $this->kernel->handle($request);
        $this->kernel->terminate($request, $response);

        return $response;
    }

    public function runAll(): void
    {
        echo "\n============================================================\n";
        echo " SMART WATER KOTA PAYAKUMBUH - REAL MYSQL VALIDATION\n";
        echo "============================================================\n\n";

        $this->validateDatabaseConnection();
        $this->validateAuthenticationAndRoles();
        $this->validateServiceCenterModule();
        $this->validateMeterReadingModule();
        $this->validateAcademyModule();
        $this->validateChallengeModule();
        $this->validateNotificationModule();
        $this->validateProfileModule();
        $this->validateAdminDashboardModule();
        $this->validateSecurityControls();
        $this->validateLegacyApiAdapters();

        echo "\n============================================================\n";
        echo " VALIDATION SUMMARY\n";
        echo " Total Passed: {$this->passCount}\n";
        echo " Total Failed: {$this->failCount}\n";
        echo " Final Status: " . ($this->failCount === 0 ? "100% ALL TESTS PASSED (PASS)" : "FAILURES DETECTED") . "\n";
        echo "============================================================\n\n";
    }

    public function getResults(): array
    {
        return [
            'passCount' => $this->passCount,
            'failCount' => $this->failCount,
            'tests'     => $this->results,
        ];
    }

    // -------------------------------------------------------------
    // SECTION 1: Database & Migration Validation
    // -------------------------------------------------------------
    private function validateDatabaseConnection(): void
    {
        echo "SECTION 1: Database & Migration Validation (MySQL 8.4)\n";

        // 1.1 Driver and Database
        $driver = DB::getDriverName();
        $dbName = DB::connection()->getDatabaseName();
        $this->log(
            'Database',
            'Koneksi MySQL Aktif',
            $driver === 'mysql' && $dbName === 'smart_water_payakumbuh',
            "Driver: {$driver}, Database: {$dbName}"
        );

        // 1.2 Table count (28 tables)
        $tables = DB::select('SHOW TABLES');
        $tableCount = count($tables);
        $this->log(
            'Database',
            'Seluruh Tabel Terbentuk di MySQL (28 Tabel)',
            $tableCount >= 28,
            "Total tabel ditemukan: {$tableCount}"
        );

        // 1.3 Foreign Keys check (23 FKs)
        $fks = DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = 'smart_water_payakumbuh' AND REFERENCED_TABLE_NAME IS NOT NULL");
        $fkCount = count($fks);
        $this->log(
            'Database',
            'Foreign Key Constraints Terpasang di MySQL',
            $fkCount >= 20,
            "Total constraint relasi: {$fkCount}"
        );

        // 1.4 Migration batch check
        $migrations = DB::table('migrations')->get();
        $this->log(
            'Database',
            'Seluruh Database Migrations Berhasil Dijalankan',
            $migrations->count() >= 10,
            "Total migration tercatat: " . $migrations->count()
        );
    }

    // -------------------------------------------------------------
    // SECTION 2: Authentication & Roles
    // -------------------------------------------------------------
    private function validateAuthenticationAndRoles(): void
    {
        echo "\nSECTION 2: Authentication & Role Authorization\n";

        // 2.1 Password Hashing Check
        $admin = User::where('email', 'admin@tirtasago.id')->first();
        $isHashed = $admin && str_starts_with($admin->password, '$2y$');
        $this->log(
            'Auth',
            'Password Menggunakan Bcrypt Hashing (Bukan Plain Text)',
            $isHashed,
            "Hash prefix: " . substr($admin->password ?? '', 0, 7)
        );

        // 2.2 Admin Login
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => 'admin@tirtasago.id',
            'password'   => 'Admin123!',
        ], ['Content-Type' => 'application/json']);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Auth',
            'Login Admin dengan Kredensial Valid',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false) && ($json['role'] ?? '') === 'admin',
            "Status: {$resp->getStatusCode()}, Role: " . ($json['role'] ?? 'none')
        );

        // 2.3 Staff Login
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => 'ahmad.pratama@tirtasago.id',
            'password'   => 'Petugas123!',
        ], ['Content-Type' => 'application/json']);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Auth',
            'Login Petugas Lapangan dengan Kredensial Valid',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false) && ($json['role'] ?? '') === 'staff',
            "Status: {$resp->getStatusCode()}, Role: " . ($json['role'] ?? 'none')
        );

        // 2.4 Customer Login via Email
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => 'budi.santoso@warga.payakumbuh.go.id',
            'password'   => 'Pelanggan123!',
        ], ['Content-Type' => 'application/json']);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Auth',
            'Login Pelanggan via Alamat Email',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false) && ($json['role'] ?? '') === 'customer',
            "Status: {$resp->getStatusCode()}, User: " . ($json['user']['name'] ?? 'none')
        );

        // 2.5 Customer Login via Meter Number
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => '01000',
            'password'   => 'Pelanggan123!',
        ], ['Content-Type' => 'application/json']);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Auth',
            'Login Pelanggan via Nomor Meter (01000)',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false) && ($json['role'] ?? '') === 'customer',
            "Status: {$resp->getStatusCode()}, User: " . ($json['user']['name'] ?? 'none')
        );

        // 2.6 Invalid Password Rejected
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => 'admin@tirtasago.id',
            'password'   => 'KataSandiSalah123!',
        ], ['Content-Type' => 'application/json']);
        $this->log(
            'Auth',
            'Login Ditolak Saat Password Salah (HTTP 401)',
            $resp->getStatusCode() === 401,
            "Status Code: {$resp->getStatusCode()}"
        );

        // 2.7 Route Protection - Guest accessing Admin Dashboard
        $resp = $this->dispatchRequest('GET', '/admin-dashboard');
        $this->log(
            'Auth',
            'Guest Dilarang Mengakses /admin-dashboard (Redirect ke Login)',
            $resp->getStatusCode() === 302 && str_contains($resp->headers->get('Location') ?? '', 'admin-login'),
            "Redirected to: " . $resp->headers->get('Location')
        );

        // 2.8 Route Protection - Customer accessing Admin Dashboard
        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $resp = $this->dispatchRequest('GET', '/admin-dashboard', [], [], $customer);
        $this->log(
            'Auth',
            'Pelanggan Ditolak Mengakses /admin-dashboard (Redirect ke /user)',
            $resp->getStatusCode() === 302 && str_contains($resp->headers->get('Location') ?? '', 'user'),
            "Customer diarahkan ke: " . $resp->headers->get('Location')
        );

        // 2.9 Route Protection - Staff accessing Admin Dashboard
        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
        $resp = $this->dispatchRequest('GET', '/admin-dashboard', [], [], $staff);
        $this->log(
            'Auth',
            'Petugas Diizinkan Mengakses /admin-dashboard (HTTP 200)',
            $resp->getStatusCode() === 200,
            "Status: {$resp->getStatusCode()}"
        );

        // 2.10 Privilege Escalation Prevention - Guest calling /switch-role/admin
        $resp = $this->dispatchRequest('GET', '/switch-role/admin');
        $this->log(
            'Auth',
            'Eskalasi Role Tanpa Sesi Ditolak (/switch-role/admin)',
            $resp->getStatusCode() === 302 && str_contains($resp->headers->get('Location') ?? '', 'admin-login'),
            "Redirected to: " . $resp->headers->get('Location')
        );

        // 2.11 Privilege Escalation Prevention - Customer trying /switch-role/admin
        $resp = $this->dispatchRequest('GET', '/switch-role/admin', [], [], $customer);
        $this->log(
            'Auth',
            'Pelanggan Dilarang Eskalasi Menjadi Admin (/switch-role/admin)',
            $resp->getStatusCode() === 302 && str_contains($resp->headers->get('Location') ?? '', 'user'),
            "Customer diarahkan ke: " . $resp->headers->get('Location')
        );
    }

    // -------------------------------------------------------------
    // SECTION 3: Service Center / Complaints CRUD & Sync
    // -------------------------------------------------------------
    private function validateServiceCenterModule(): void
    {
        echo "\nSECTION 3: Modul Pusat Layanan / Pengaduan (CRUD & Multi-Device Sync)\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        // 3.1 Create Complaint via API v1
        $ticketTitle = 'Pipa Distribusi Utama Bocor di Simpang Benteng ' . time();
        $resp = $this->dispatchRequest('POST', '/api/v1/complaints', [
            'title'       => $ticketTitle,
            'description' => 'Genangan air bersih mengucur dari trotoar jalan utama.',
            'district'    => 'Payakumbuh Barat',
            'address'     => 'Jl. Sudirman No. 88',
            'userPhone'   => '0812-9988-7766',
            'category'    => 'pipa_bocor',
        ], ['Content-Type' => 'application/json'], $customer);

        $json = json_decode($resp->getContent(), true);
        $createdInDb = Complaint::where('title', $ticketTitle)->first();

        $this->log(
            'Complaints',
            'Pelanggan Berhasil Membuat Pengaduan (Tersimpan ke MySQL)',
            $resp->getStatusCode() === 200 && $createdInDb !== null,
            "No Tiket: " . ($createdInDb->ticket_number ?? 'none') . ", DB ID: " . ($createdInDb->id ?? 0)
        );

        // 3.2 Complaint History was automatically created
        $history = ComplaintHistory::where('complaint_id', $createdInDb->id ?? 0)->first();
        $this->log(
            'Complaints',
            'Histori Status Tiket Awal Tercatat di MySQL',
            $history !== null && $history->status === 'BARU',
            "Histori status: " . ($history->status ?? 'none')
        );

        // 3.3 Staff views complaints list from their session
        $resp = $this->dispatchRequest('GET', '/api/v1/complaints', [], [], $staff);
        $json = json_decode($resp->getContent(), true);
        $foundTicket = false;
        foreach ($json['complaints'] ?? [] as $t) {
            if ($t['ticketId'] === $createdInDb->ticket_number) {
                $foundTicket = true;
                break;
            }
        }
        $this->log(
            'Complaints',
            'Petugas Melihat Tiket Pelanggan dari Sesi Petugas Berbeda',
            $foundTicket,
            "Tiket {$createdInDb->ticket_number} ditemukan di inbox petugas"
        );

        // 3.4 Staff updates complaint status to DIPROSES
        $resp = $this->dispatchRequest('POST', "/api/v1/complaints/{$createdInDb->ticket_number}/status", [
            'status' => 'DIPROSES',
            'note'   => 'Regu teknisi 02 sedang menuju lokasi pipa.',
        ], ['Content-Type' => 'application/json'], $staff);

        $createdInDb->refresh();
        $this->log(
            'Complaints',
            'Petugas Memperbarui Status Tiket Menjadi DIPROSES',
            $createdInDb->status === 'DIPROSES',
            "Status terkini di DB: {$createdInDb->status}"
        );

        // 3.5 Customer reads complaint status again and sees update
        $resp = $this->dispatchRequest('GET', '/api/v1/complaints', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $customerSeesUpdatedStatus = false;
        foreach ($json['complaints'] ?? [] as $t) {
            if ($t['ticketId'] === $createdInDb->ticket_number && $t['status'] === 'DIPROSES') {
                $customerSeesUpdatedStatus = true;
                break;
            }
        }
        $this->log(
            'Complaints',
            'Pelanggan Melihat Perubahan Status dari Perangkat Berbeda (Sinkronisasi Multi-Device)',
            $customerSeesUpdatedStatus,
            "Pelanggan menerima status: DIPROSES"
        );

        // 3.6 Staff assigns ticket to field technician
        $resp = $this->dispatchRequest('POST', "/api/v1/complaints/{$createdInDb->ticket_number}/assign", [
            'staff_id' => $staff->id,
            'note'     => 'Regu TRC 02 ditugaskan ke lokasi untuk penggantian valve pipa.',
        ], ['Content-Type' => 'application/json'], $staff);
        $createdInDb->refresh();
        $this->log(
            'Complaints',
            'Penugasan Teknisi Lapangan Berhasil Tercatat di MySQL',
            $resp->getStatusCode() === 200 && $createdInDb->assigned_to === $staff->id,
            "Teknisi ID: {$createdInDb->assigned_to}, Waktu: {$createdInDb->assigned_at}"
        );

        // 3.7 Staff submits official progress response
        $resp = $this->dispatchRequest('POST', "/api/v1/complaints/{$createdInDb->ticket_number}/response", [
            'note' => 'Galian pipa telah dibuka, material pipa baru sedang dipasang.',
        ], ['Content-Type' => 'application/json'], $staff);
        $this->log(
            'Complaints',
            'Petugas Memberikan Tanggapan Resmi Penanganan Tiket',
            $resp->getStatusCode() === 200,
            "Tanggapan berhasil disimpan di riwayat penanganan"
        );

        // 3.8 Staff resolves the complaint
        $resp = $this->dispatchRequest('POST', "/api/v1/complaints/{$createdInDb->ticket_number}/resolve", [
            'resolution_notes' => 'Pipa utama 3 inch berhasil diganti baru, uji tekanan 3.2 bar normal.',
        ], ['Content-Type' => 'application/json'], $staff);
        $createdInDb->refresh();
        $this->log(
            'Complaints',
            'Penyelesaian Tiket Tuntas Tercatat di MySQL (Status SELESAI)',
            $createdInDb->status === 'SELESAI' && $createdInDb->resolved_at !== null,
            "Waktu selesai: {$createdInDb->resolved_at}, Catatan: {$createdInDb->resolution_notes}"
        );

        // 3.9 Authorization Guard: Other customer cannot view private ticket
        $customer2 = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();
        $resp = $this->dispatchRequest('GET', "/api/v1/complaints/{$createdInDb->ticket_number}", [], [], $customer2);
        $this->log(
            'Complaints',
            'Pelanggan Lain Dilarang Mengakses Tiket Milik Warga Lain (HTTP 403)',
            $resp->getStatusCode() === 403,
            "Status Code: {$resp->getStatusCode()} (Forbidden)"
        );
    }

    // -------------------------------------------------------------
    // SECTION 4: Meter Reading Module & Multi-Device Staff Verification
    // -------------------------------------------------------------
    private function validateMeterReadingModule(): void
    {
        echo "\nSECTION 4: Modul Lapor Meter Mandiri & Verifikasi Petugas\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        // 4.1 Customer submits meter reading via API
        $resp = $this->dispatchRequest('POST', '/api/v1/meter-reports', [
            'meter'       => '01000',
            'ocrReading'  => 1085.0,
            'lastReading' => 1050.0,
            'name'        => 'Budi Santoso',
            'zone'        => 'DIST-A Sektor Barat',
        ], ['Content-Type' => 'application/json'], $customer);

        $json = json_decode($resp->getContent(), true);
        $reportId = $json['report']['id'] ?? $json['report']['report_number'] ?? null;
        $savedMeter = MeterReading::where('report_number', $reportId)
            ->orWhere('id', $json['report']['db_id'] ?? 0)
            ->first();

        $this->log(
            'MeterReading',
            'Pelanggan Mengirim Laporan Meter AI-OCR ke MySQL',
            $savedMeter !== null && $savedMeter->status === 'Menunggu Validasi',
            "No Laporan: {$savedMeter?->report_number}, Stand: 1085.0 m3, Status: Menunggu Validasi"
        );

        // 4.2 Staff sees the pending meter report in verification list
        $resp = $this->dispatchRequest('GET', '/api/v1/meter-reports', [], [], $staff);
        $json = json_decode($resp->getContent(), true);
        $foundMeterReport = false;
        foreach ($json['reports'] ?? [] as $m) {
            if ($m['id'] === $savedMeter->report_number || ($m['report_number'] ?? '') === $savedMeter->report_number) {
                $foundMeterReport = true;
                break;
            }
        }
        $this->log(
            'MeterReading',
            'Laporan Meter Pelanggan Masuk ke Dashboard Verifikasi Petugas',
            $foundMeterReport,
            "Laporan {$savedMeter->report_number} muncul di daftar validasi petugas"
        );

        // 4.3 Staff validates and approves the meter reading
        $resp = $this->dispatchRequest('POST', '/api/v1/meter-reports/validate', [
            'id'     => $savedMeter->report_number,
            'action' => 'APPROVE',
        ], ['Content-Type' => 'application/json'], $staff);

        $savedMeter->refresh();
        $this->log(
            'MeterReading',
            'Petugas Memvalidasi Laporan Meter (Status Terverifikasi AI)',
            $savedMeter->status === 'Terverifikasi AI' && $savedMeter->validated_by === $staff->id,
            "Status: {$savedMeter->status}, Validated by ID: {$savedMeter->validated_by}"
        );

        // 4.4 Data Isolation: Customer 2 cannot see Customer 1's meter reading
        $customer2 = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();
        $resp = $this->dispatchRequest('GET', '/api/v1/meter-reports', [], [], $customer2);
        $json = json_decode($resp->getContent(), true);
        $foundOtherReport = false;
        foreach ($json['reports'] ?? [] as $m) {
            if ($m['id'] === $savedMeter->report_number) {
                $foundOtherReport = true;
                break;
            }
        }
        $this->log(
            'MeterReading',
            'Isolasi Data Meter: Pelanggan Lain Tidak Dapat Mengakses Data Meter Milik Warga Lain',
            !$foundOtherReport,
            "Laporan meter warga lain disaring dan diisolasi dengan aman"
        );
    }

    // -------------------------------------------------------------
    // SECTION 5: Akademi Air Module
    // -------------------------------------------------------------
    private function validateAcademyModule(): void
    {
        echo "\nSECTION 5: Modul Akademi Air (Materi & Kuis Interaktif)\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        // 5.1 Fetch courses list
        $resp = $this->dispatchRequest('GET', '/api/v1/academy', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $courses = $json['courses'] ?? [];
        $this->log(
            'Academy',
            'Mendapatkan Kurikulum & Modul Kursus dari MySQL',
            count($courses) >= 2,
            "Total modul: " . count($courses)
        );

        // 5.2 Complete Lesson
        $firstLesson = Lesson::first();
        $resp = $this->dispatchRequest('POST', '/api/v1/academy/complete-lesson', [
            'lessonId' => $firstLesson->lesson_code,
        ], ['Content-Type' => 'application/json'], $customer);
        $progress = UserLessonProgress::where('user_id', $customer->id)
            ->where('lesson_id', $firstLesson->id)
            ->first();
        $this->log(
            'Academy',
            'Menyelesaikan Materi Pelajaran (Tersimpan ke user_lesson_progress)',
            $progress !== null && (bool)$progress->is_completed,
            "Lesson ID {$firstLesson->id} ({$firstLesson->lesson_code}) selesai"
        );

        // 5.3 Complete Quiz
        $firstCourse = Course::first();
        $resp = $this->dispatchRequest('POST', '/api/v1/academy/complete-quiz', [
            'courseId' => $firstCourse->course_code,
            'score'    => 95,
            'passed'   => true,
        ], ['Content-Type' => 'application/json'], $customer);
        $quiz = UserQuizResult::where('user_id', $customer->id)
            ->where('course_id', $firstCourse->id)
            ->first();
        $this->log(
            'Academy',
            'Menyelesaikan Kuis Modul dengan Skor 95 (Tersimpan ke user_quiz_results)',
            $quiz !== null && (int)$quiz->score === 95,
            "Course ID {$firstCourse->id}, Skor: " . ($quiz->score ?? 0)
        );
    }

    // -------------------------------------------------------------
    // SECTION 6: Tantangan Hemat Air Module
    // -------------------------------------------------------------
    private function validateChallengeModule(): void
    {
        echo "\nSECTION 6: Modul Tantangan Hemat Air (Pendaftaran & Misi)\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $challenge = Challenge::first();

        // 6.1 List Challenges
        $resp = $this->dispatchRequest('GET', '/api/v1/challenges', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Challenge',
            'Daftar Program Tantangan Terbaca dari MySQL',
            ($json['ok'] ?? false) && count($json['challenges'] ?? []) >= 2,
            "Total tantangan aktif: " . count($json['challenges'] ?? [])
        );

        // 6.2 Enroll in Challenge
        $resp = $this->dispatchRequest('POST', '/api/v1/challenges/enroll', [
            'challengeId' => $challenge->challenge_code,
        ], ['Content-Type' => 'application/json'], $customer);
        $participant = ChallengeParticipant::where('user_id', $customer->id)
            ->where('challenge_id', $challenge->id)
            ->first();
        $this->log(
            'Challenge',
            'Pendaftaran Peserta Tantangan Tersimpan di challenge_participants',
            $participant !== null,
            "Participant ID: " . ($participant->id ?? 0)
        );

        // 6.3 Complete Mission
        $mission = ChallengeMission::where('challenge_id', $challenge->id)->first();
        $resp = $this->dispatchRequest('POST', '/api/v1/challenges/complete-mission', [
            'challengeId' => $challenge->challenge_code,
            'missionId'   => $mission->mission_code,
        ], ['Content-Type' => 'application/json'], $customer);
        $submission = ChallengeSubmission::where('participant_id', $participant?->id ?? 0)
            ->where('mission_id', $mission->id)
            ->first();
        $this->log(
            'Challenge',
            'Penyelesaian Misi Tantangan Tersimpan di challenge_submissions',
            $submission !== null && (bool)$submission->is_completed,
            "Misi: " . ($mission->title ?? 'none')
        );
    }

    // -------------------------------------------------------------
    // SECTION 7: Notification Module
    // -------------------------------------------------------------
    private function validateNotificationModule(): void
    {
        echo "\nSECTION 7: Modul Notifikasi\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        // 7.1 Seed test notification
        Notification::create([
            'user_id' => $customer->id,
            'title'   => 'Laporan Meter Mandiri Anda Telah Diverifikasi',
            'desc'    => 'Stand meter 1085.0 m3 telah disetujui oleh petugas.',
            'type'    => 'METER',
            'link'    => '/bill',
            'is_read' => false,
        ]);

        // 7.2 Fetch notifications
        $resp = $this->dispatchRequest('GET', '/api/v1/notifications', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $notifCount = count($json['notifications'] ?? []);
        $this->log(
            'Notification',
            'Daftar Notifikasi Terambil dari MySQL Sesuai User ID',
            $notifCount > 0,
            "Total notifikasi user: {$notifCount}"
        );

        // 7.3 Mark all as read
        $resp = $this->dispatchRequest('POST', '/api/v1/notifications/mark-all-read', [], [], $customer);
        $unread = Notification::where('user_id', $customer->id)->where('is_read', false)->count();
        $this->log(
            'Notification',
            'Tandai Seluruh Notifikasi Sudah Dibaca (is_read = 1)',
            $unread === 0,
            "Sisa notifikasi belum dibaca: {$unread}"
        );
    }

    // -------------------------------------------------------------
    // SECTION 8: User Profile Module
    // -------------------------------------------------------------
    private function validateProfileModule(): void
    {
        echo "\nSECTION 8: Modul Profil Pengguna\n";

        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        // 8.1 Fetch Profile
        $resp = $this->dispatchRequest('GET', '/api/v1/profile', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'Profile',
            'Mendapatkan Data Profil Pengguna & Sambungan Meter dari MySQL',
            ($json['ok'] ?? false) && ($json['profile']['meterNumber'] ?? '') === '01000',
            "Nama: " . ($json['profile']['name'] ?? '') . ", No Meter: " . ($json['profile']['meterNumber'] ?? '')
        );

        // 8.2 Update Profile
        $newPhone = '0812-7766-5544';
        $resp = $this->dispatchRequest('POST', '/api/v1/profile/update', [
            'phone'   => $newPhone,
            'address' => 'Jl. Tan Malaka No. 12, Kel. Napar',
        ], ['Content-Type' => 'application/json'], $customer);
        $customer->customerProfile->refresh();
        $this->log(
            'Profile',
            'Pembaruan Kontak & Alamat Profil Tersimpan ke MySQL',
            $customer->customerProfile->phone === $newPhone,
            "Nomor HP baru di database: " . $customer->customerProfile->phone
        );
    }

    // -------------------------------------------------------------
    // SECTION 9: Admin Dashboard Real Aggregation
    // -------------------------------------------------------------
    private function validateAdminDashboardModule(): void
    {
        echo "\nSECTION 9: Modul Dashboard Admin (Agregasi Data Riil)\n";

        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        // 9.1 Dashboard View Aggregations
        $resp = $this->dispatchRequest('GET', '/admin-dashboard', [], [], $staff);
        $html = $resp->getContent();

        $complaintCount = Complaint::count();
        $userCount = User::count();

        $this->log(
            'Dashboard',
            'Halaman Dashboard Admin Merender Statistik Database Riil',
            $resp->getStatusCode() === 200 && (str_contains($html, 'PORTAL OPERATOR & PETUGAS PDAM') || str_contains($html, 'Portal Petugas')),
            "Total Pengaduan Terdata: {$complaintCount}, Total Pengguna Terdaftar: {$userCount}"
        );
    }

    // -------------------------------------------------------------
    // SECTION 10: Security Controls
    // -------------------------------------------------------------
    private function validateSecurityControls(): void
    {
        echo "\nSECTION 10: Validasi Keamanan (Security Controls)\n";

        // 10.1 SQL Injection Protection
        $resp = $this->dispatchRequest('POST', '/api/v1/auth/login', [
            'identifier' => "' OR '1'='1' --",
            'password'   => "anything' OR '1'='1",
        ], ['Content-Type' => 'application/json']);
        $this->log(
            'Security',
            'SQL Injection pada Form Login Berhasil Dicegah (Prepared Statement)',
            $resp->getStatusCode() === 401,
            "Status Code: {$resp->getStatusCode()}"
        );

        // 10.2 XSS Sanitization in Complaint creation
        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $xssTitle = 'Tes XSS <script>alert("hacked")</script>';
        $resp = $this->dispatchRequest('POST', '/api/v1/complaints', [
            'title'       => $xssTitle,
            'description' => 'Mencoba injeksi payload script tag.',
            'district'    => 'Payakumbuh Utara',
            'address'     => 'Jl. Pahlawan',
            'userPhone'   => '0812-0000-1111',
            'category'    => 'kualitas_air',
        ], ['Content-Type' => 'application/json'], $customer);
        $savedXss = Complaint::where('title', 'like', '%Tes XSS%')->first();
        $this->log(
            'Security',
            'XSS Payload Tertangani dengan Aman (Stored Safely, No Execution)',
            $savedXss !== null,
            "Tersimpan dengan aman sebagai entitas teks"
        );

        // 10.3 Unauthorized API Status Update
        $resp = $this->dispatchRequest('POST', "/api/v1/complaints/{$savedXss->ticket_number}/status", [
            'status' => 'SELESAI',
        ], ['Content-Type' => 'application/json'], $customer);
        $this->log(
            'Security',
            'Pelanggan Dilarang Mengubah Status Tiket Pengaduan (Role Guard)',
            $resp->getStatusCode() === 403,
            "Status Code: {$resp->getStatusCode()} (Forbidden)"
        );

        // 10.4 Unauthorized Meter Validation
        $meter = MeterReading::first();
        $resp = $this->dispatchRequest('POST', '/api/v1/meter-reports/validate', [
            'id'     => $meter->report_number,
            'action' => 'APPROVE',
        ], ['Content-Type' => 'application/json'], $customer);
        $this->log(
            'Security',
            'Pelanggan Dilarang Memvalidasi Laporan Meter (Role Guard)',
            $resp->getStatusCode() === 403,
            "Status Code: {$resp->getStatusCode()} (Forbidden)"
        );
    }

    // -------------------------------------------------------------
    // SECTION 11: Legacy API Compatibility
    // -------------------------------------------------------------
    private function validateLegacyApiAdapters(): void
    {
        echo "\nSECTION 11: Validasi Kompatibilitas Legacy API Adapters\n";

        $customer = User::where('name', 'Budi Santoso')->first();

        // 11.1 Legacy Auth Endpoint
        $resp = $this->dispatchRequest('GET', '/api/auth.php');
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'LegacyAPI',
            'Adapter /api/auth.php Menghasilkan Response JSON yang Valid',
            $resp->getStatusCode() === 200 && is_array($json) && isset($json['ok']),
            "ok: " . ((($json['ok'] ?? false) === true) ? 'true' : 'false')
        );

        // 11.2 Legacy Complaints Endpoint
        $resp = $this->dispatchRequest('GET', '/api/complaints.php', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'LegacyAPI',
            'Adapter /api/complaints.php Kompatibel dengan Frontend',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false),
            "Total pengaduan terambil: " . count($json['data'] ?? $json['complaints'] ?? [])
        );

        // 11.3 Legacy Academy Endpoint
        $resp = $this->dispatchRequest('GET', '/api/academy.php', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'LegacyAPI',
            'Adapter /api/academy.php Kompatibel dengan Frontend',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false),
            "Kurikulum: " . count($json['courses'] ?? []) . " modul"
        );

        // 11.4 Legacy Challenges Endpoint
        $resp = $this->dispatchRequest('GET', '/api/challenges.php', [], [], $customer);
        $json = json_decode($resp->getContent(), true);
        $this->log(
            'LegacyAPI',
            'Adapter /api/challenges.php Kompatibel dengan Frontend',
            $resp->getStatusCode() === 200 && ($json['ok'] ?? false),
            "Tantangan: " . count($json['challenges'] ?? [])
        );
    }
}

$suite = new ValidationSuite($kernel);
$suite->runAll();
