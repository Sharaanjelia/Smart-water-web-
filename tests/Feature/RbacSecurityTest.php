<?php

namespace Tests\Feature;

use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class RbacSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $adminUser;
    protected User $staffUser;
    protected User $customerUser;
    protected User $otherCustomerUser;
    protected ComplaintCategory $defaultCategory;

    protected function setUp(): void
    {
        parent::setUp();

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        unset($_SESSION['swc_user'], $_SESSION['swc_role'], $_SESSION['swc_academy_progress'], $_SESSION['swc_challenge_progress']);
        Auth::logout();

        $this->adminUser = User::where('email', 'admin@tirtasago.id')->first();
        $this->staffUser = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
        $this->customerUser = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $this->otherCustomerUser = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();
        $this->defaultCategory = ComplaintCategory::first();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['swc_user'], $_SESSION['swc_role']);
        Auth::logout();
        parent::tearDown();
    }

    /**
     * Helper membuat tiket pengaduan uji.
     */
    protected function createTestComplaint(?User $owner = null): Complaint
    {
        return Complaint::create([
            'user_id'          => $owner?->id,
            'category_id'      => $this->defaultCategory->id,
            'ticket_number'    => 'TK-SEC-' . random_int(1000, 9999),
            'title'            => 'Laporan Keamanan Air',
            'description'      => 'Pengaduan untuk pengujian RBAC.',
            'district'         => 'Payakumbuh Barat',
            'address'          => 'Jl. Sudirman No. 1',
            'reporter_name'    => $owner?->name ?? 'Warga Anonim',
            'contact_phone'    => '0812-0000-1111',
            'status'           => 'BARU',
            'priority'         => 'NORMAL',
        ]);
    }

    /**
     * Helper membuat laporan meter uji.
     */
    protected function createTestMeterReading(?User $owner = null): MeterReading
    {
        return MeterReading::create([
            'user_id'          => $owner?->id,
            'meter_number'     => $owner?->customerProfile?->meter_number ?? '01000',
            'report_number'    => 'REP-SEC-' . random_int(1000, 9999),
            'customer_name'    => $owner?->name ?? 'Warga Anonim',
            'previous_reading' => 1000.0,
            'reading'          => 1025.0,
            'consumption'      => 25.0,
            'estimated_cost'   => 197600,
            'status'           => 'Menunggu Validasi',
        ]);
    }

    /**
     * Test 1: Admin dapat mengakses Command Center dan endpoint operasional.
     */
    public function test_admin_can_access_administrative_endpoints(): void
    {
        $response = $this->actingAs($this->adminUser)->get('/admin-dashboard');
        $response->assertStatus(200);

        $responseWeb = $this->actingAs($this->adminUser)->get('/admin');
        $responseWeb->assertStatus(200);
    }

    /**
     * Test 2: Staff diizinkan mengakses dashboard operasional SCADA.
     */
    public function test_staff_can_access_operational_endpoints(): void
    {
        $response = $this->actingAs($this->staffUser)->get('/admin-dashboard');
        $response->assertStatus(200);

        $responseMonitoring = $this->actingAs($this->staffUser)->get('/water-monitoring');
        $responseMonitoring->assertStatus(200);
    }

    /**
     * Test 3: Pelanggan (Customer) ditolak mengakses Web Admin & dialihkan ke /user.
     */
    public function test_customer_cannot_access_admin_web_pages(): void
    {
        $response = $this->actingAs($this->customerUser)->get('/admin-dashboard');
        $response->assertStatus(302);
        $response->assertRedirect(url('/user'));

        $responseAdmin = $this->actingAs($this->customerUser)->get('/admin');
        $responseAdmin->assertStatus(302);
        $responseAdmin->assertRedirect(url('/user'));
    }

    /**
     * Test 4: Tamu (Guest) ditolak mengakses Web Admin & dialihkan ke /admin-login.
     */
    public function test_guest_cannot_access_admin_web_pages(): void
    {
        $response = $this->get('/admin-dashboard');
        $response->assertStatus(302);
        $response->assertRedirect(url('/admin-login'));

        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertStatus(302);
        $responseAdmin->assertRedirect(url('/admin-login'));
    }

    /**
     * Test 5: Pelanggan ditolak saat mencoba memvalidasi laporan meter (HTTP 403).
     */
    public function test_customer_cannot_validate_meter_readings(): void
    {
        $reading = $this->createTestMeterReading($this->customerUser);

        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/meter-reports/validate', [
            'id'     => $reading->report_number,
            'action' => 'APPROVE',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 6: Pelanggan ditolak saat mencoba menugaskan teknisi pada tiket (HTTP 403).
     */
    public function test_customer_cannot_assign_complaint_ticket(): void
    {
        $complaint = $this->createTestComplaint($this->customerUser);

        $response = $this->actingAs($this->customerUser)->postJson("/api/v1/complaints/{$complaint->ticket_number}/assign", [
            'staff_id' => $this->staffUser->id,
            'note'     => 'Mencoba menugaskan sendiri.',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 7: Pelanggan ditolak saat mencoba menyelesaikan tiket (HTTP 403).
     */
    public function test_customer_cannot_resolve_complaint_ticket(): void
    {
        $complaint = $this->createTestComplaint($this->customerUser);

        $response = $this->actingAs($this->customerUser)->postJson("/api/v1/complaints/{$complaint->ticket_number}/resolve", [
            'status'           => 'SELESAI',
            'resolution_notes' => 'Menyelesaikan sepihak.',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 8: IDOR Guard pada Pengaduan - Customer A dilarang melihat tiket privat Customer B (HTTP 403).
     */
    public function test_idor_prevented_customer_cannot_view_other_customer_complaint(): void
    {
        $privateTicket = $this->createTestComplaint($this->otherCustomerUser);

        // Customer Budi mencoba melihat tiket milik Siti -> Ditolak HTTP 403
        $response = $this->actingAs($this->customerUser)->getJson("/api/v1/complaints/{$privateTicket->ticket_number}");
        $response->assertStatus(403);

        // Tamu (Guest) mencoba melihat tiket tanpa login -> Ditolak HTTP 401
        Auth::logout();
        unset($_SESSION['swc_user'], $_SESSION['swc_role']);
        $responseGuest = $this->getJson("/api/v1/complaints/{$privateTicket->ticket_number}");
        $responseGuest->assertStatus(401);

        // Pemilik asli (Siti) diizinkan melihat tiket miliknya -> HTTP 200
        $responseOwner = $this->actingAs($this->otherCustomerUser)->getJson("/api/v1/complaints/{$privateTicket->ticket_number}");
        $responseOwner->assertStatus(200);

        // Petugas diizinkan melihat tiket untuk keperluan tindak lanjut -> HTTP 200
        $responseStaff = $this->actingAs($this->staffUser)->getJson("/api/v1/complaints/{$privateTicket->ticket_number}");
        $responseStaff->assertStatus(200);
    }

    /**
     * Test 9: IDOR Guard pada Lapor Meter - Tamu tidak mendapatkan data meter warga (array kosong).
     */
    public function test_idor_prevented_guest_receives_empty_meter_reports(): void
    {
        $response = $this->getJson('/api/v1/meter-reports');
        $response->assertStatus(200);
        $response->assertJsonPath('ok', true);
        $response->assertJsonCount(0, 'reports');
    }

    /**
     * Test 10: IDOR Guard pada Lapor Meter - Pelanggan hanya melihat laporan meter miliknya sendiri.
     */
    public function test_idor_prevented_customer_only_sees_own_meter_reports(): void
    {
        $budiReport = $this->createTestMeterReading($this->customerUser);
        $sitiReport = $this->createTestMeterReading($this->otherCustomerUser);

        // Budi meminta daftar laporan meter -> Hanya menerima laporannya sendiri
        $response = $this->actingAs($this->customerUser)->getJson('/api/v1/meter-reports');
        $response->assertStatus(200);
        $data = $response->json('reports');

        $this->assertNotEmpty($data);
        foreach ($data as $r) {
            $this->assertNotEquals($sitiReport->report_number, $r['report_number'] ?? $r['id']);
        }

        // Petugas meminta daftar laporan meter -> Melihat seluruh laporan untuk verifikasi
        $responseStaff = $this->actingAs($this->staffUser)->getJson('/api/v1/meter-reports');
        $responseStaff->assertStatus(200);
        $staffData = $responseStaff->json('reports');
        $reportNumbers = array_column($staffData, 'report_number');
        $this->assertContains($budiReport->report_number, $reportNumbers);
        $this->assertContains($sitiReport->report_number, $reportNumbers);
    }

    /**
     * Test 11: Parameter Tampering Prevention - Manipulasi role pada update profil diabaikan.
     */
    public function test_privilege_escalation_prevented_on_profile_update(): void
    {
        $response = $this->actingAs($this->customerUser)->postJson('/api/v1/profile/update', [
            'phone'    => '0812-9999-8888',
            'address'  => 'Jl. Baru No. 1',
            'role'     => 'admin',
            'is_admin' => 1,
            'points'   => 999999,
        ]);

        $response->assertStatus(200);
        $this->customerUser->refresh();

        // Pastikan role tetap customer dan tidak eskalasi menjadi admin
        $this->assertFalse($this->customerUser->hasRole('admin'));
        $this->assertTrue($this->customerUser->hasRole('customer'));
    }

    /**
     * Test 12: Parameter Tampering Prevention - Pendaftaran akun baru selalu menetapkan role customer.
     */
    public function test_registration_always_assigns_customer_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Hacker Wannabe',
            'email'    => 'hacker@wannabe.io',
            'password' => 'Rahasia123!',
            'role'     => 'admin',
        ]);

        $response->assertStatus(201);
        $newUser = User::where('email', 'hacker@wannabe.io')->first();

        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('customer'));
        $this->assertFalse($newUser->hasRole('admin'));
        $this->assertFalse($newUser->hasRole('staff'));
    }

    /**
     * Test 13: Privilege Escalation Prevention - Pelanggan dilarang beralih role ke admin via /switch-role/admin.
     */
    public function test_customer_cannot_switch_role_to_admin(): void
    {
        $response = $this->actingAs($this->customerUser)->get('/switch-role/admin');
        $response->assertStatus(302);
        $response->assertRedirect(url('/user'));
    }
}
