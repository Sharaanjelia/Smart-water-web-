<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartWaterBackendTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;
    /**
     * Test 1: Halaman publik utama dapat dibuka (HTTP 200).
     */
    public function test_public_pages_are_accessible(): void
    {
        $response = $this->get('/beranda');
        $response->assertStatus(200);

        $responseLogin = $this->get('/login');
        $responseLogin->assertStatus(200);

        // Guest accessing protected pages is redirected to login with redirect parameter
        $this->get('/pusat-layanan')->assertRedirect(url('/login?redirect=' . urlencode(url('/pusat-layanan'))));
        $this->get('/akademi-air')->assertRedirect(url('/login?redirect=' . urlencode(url('/akademi-air'))));
        $this->get('/tantangan')->assertRedirect(url('/login?redirect=' . urlencode(url('/tantangan'))));

        // Authenticated customer can access all service, academy, and challenge pages
        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $this->actingAs($customer);

        $responseService = $this->get('/pusat-layanan');
        $responseService->assertStatus(200);

        $responseAcademy = $this->get('/akademi-air');
        $responseAcademy->assertStatus(200);

        $responseChallenge = $this->get('/tantangan');
        $responseChallenge->assertStatus(200);
    }

    /**
     * Test 2: Login pelanggan dengan kredensial yang valid berhasil.
     */
    public function test_customer_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi.santoso@warga.payakumbuh.go.id',
            'password'   => 'Pelanggan123!',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
        $response->assertJsonPath('role', 'customer');
    }

    /**
     * Test 3: Login dengan kata sandi salah ditolak (HTTP 401/422).
     */
    public function test_login_with_invalid_password_is_rejected(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => 'budi.santoso@warga.payakumbuh.go.id',
            'password'   => 'SalahTotal123',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test 4: Registrasi pelanggan baru tersimpan ke database MySQL.
     */
    public function test_new_customer_can_register(): void
    {
        $email = 'warga.baru.' . time() . '@payakumbuh.go.id';

        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Warga Baru Payakumbuh',
            'email'    => $email,
            'phone'    => '0812-3344-5566',
            'password' => 'PasswordAman123!',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', ['email' => $email]);
    }

    /**
     * Test 5: Pengaduan baru tersimpan ke database beserta histori statusnya.
     */
    public function test_complaint_can_be_created_and_stored(): void
    {
        $response = $this->postJson('/api/v1/complaints', [
            'title'       => 'Pipa Pecah Uji Coba Otomatis',
            'description' => 'Rembesan air bersih terlihat di jalan utama.',
            'district'    => 'Payakumbuh Barat',
            'address'     => 'Jl. Sudirman No. 10',
            'userPhone'   => '0811-2233-4455',
            'category'    => 'pipa_bocor',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseHas('complaints', [
            'title'    => 'Pipa Pecah Uji Coba Otomatis',
            'district' => 'Payakumbuh Barat',
        ]);
    }

    /**
     * Test 6: Modul Akademi Air membaca kursus dan progres.
     */
    public function test_academy_returns_courses_and_progress(): void
    {
        $response = $this->getJson('/api/v1/academy');
        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
    }

    /**
     * Test 7: Modul Tantangan Air mengembalikan daftar tantangan.
     */
    public function test_challenges_returns_active_list(): void
    {
        $response = $this->getJson('/api/v1/challenges');
        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
    }

    /**
     * Test 8: Lapor meter mandiri tersimpan ke database.
     */
    public function test_meter_reading_report_is_stored(): void
    {
        $response = $this->postJson('/api/v1/meter-reports', [
            'meter'       => '01000',
            'ocrReading'  => 1025.0,
            'lastReading' => 1000.0,
            'name'        => 'Budi Santoso',
            'zone'        => 'DIST-A Sektor Utara',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        $this->assertDatabaseHas('meter_readings', [
            'meter_number' => '01000',
            'reading'      => 1025.0,
        ]);
    }

    /**
     * Test 9: Petugas dapat memvalidasi laporan meter mandiri.
     */
    public function test_staff_can_validate_meter_reading(): void
    {
        $reading = MeterReading::create([
            'report_number'    => 'REP-04-TEST-99',
            'meter_number'     => '01000',
            'reading'          => 1030.0,
            'previous_reading' => 1000.0,
            'consumption'      => 30.0,
            'estimated_cost'   => 236600,
            'customer_name'    => 'Budi Santoso',
            'status'           => 'Menunggu Validasi',
        ]);

        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        $response = $this->actingAs($staff)->postJson('/api/v1/meter-reports/validate', [
            'id'     => 'REP-04-TEST-99',
            'action' => 'APPROVE',
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);

        $reading->refresh();
        $this->assertEquals('Terverifikasi AI', $reading->status);
    }
}
