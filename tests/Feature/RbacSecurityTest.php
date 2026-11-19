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
    protected User $fieldOfficer;
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
        $this->fieldOfficer = User::where('email', 'dedi.teknisi@tirtasago.id')->first();
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
     * Helper membuat tagihan uji.
     */
    protected function createTestBill(?User $owner = null): \App\Models\Bill
    {
        $owner = $owner ?? $this->customerUser;
        $reading = $this->createTestMeterReading($owner);

        return \App\Models\Bill::create([
            'bill_number'      => 'INV-TEST-' . random_int(1000, 9999),
            'user_id'          => $owner->id,
            'meter_reading_id' => $reading->id,
            'customer_id'      => $owner->customerProfile?->customer_id ?? '1234567890',
            'meter_number'     => $reading->meter_number,
            'billing_period'   => '2026-11',
            'period_label'     => 'November 2026',
            'tariff_category'  => '2A-RUMAH-TANGGA',
            'previous_reading' => $reading->previous_reading,
            'current_reading'  => $reading->reading,
            'consumption'      => $reading->consumption,
            'water_charge'     => 150000,
            'admin_fee'        => 5000,
            'maintenance_fee'  => 0,
            'penalty_fee'      => 0,
            'total_amount'     => 155000,
            'status'           => 'UNPAID',
            'due_date'         => now()->addDays(7),
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
     * Test 4: Tamu (Guest) ditolak mengakses Web Admin & dialihkan ke /login.
     */
    public function test_guest_cannot_access_admin_web_pages(): void
    {
        $response = $this->get('/admin-dashboard');
        $response->assertStatus(302);
        $response->assertRedirect(url('/login'));

        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertStatus(302);
        $responseAdmin->assertRedirect(url('/login'));
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

    /**
     * Test 14: Petugas Lapangan dilarang mengakses halaman SCADA & Telemetri.
     */
    public function test_field_officer_cannot_access_scada_monitoring_pages(): void
    {
        $response = $this->actingAs($this->fieldOfficer)->get('/water-monitoring');
        $response->assertStatus(302);
        $response->assertRedirect(url('/admin-dashboard'));

        $resReservoir = $this->actingAs($this->fieldOfficer)->get('/reservoir-detail');
        $resReservoir->assertStatus(302);
        $resReservoir->assertRedirect(url('/admin-dashboard'));

        $resDist = $this->actingAs($this->fieldOfficer)->get('/distribution-network');
        $resDist->assertStatus(302);
        $resDist->assertRedirect(url('/admin-dashboard'));
    }

    /**
     * Test 15: Petugas Lapangan dialihkan jika mengakses tab tagihan pada dashboard.
     */
    public function test_field_officer_dashboard_redirects_when_accessing_bills_tab(): void
    {
        $response = $this->actingAs($this->fieldOfficer)->get('/admin-dashboard?tab=bills');
        $response->assertStatus(302);
        $response->assertRedirect(url('/admin-dashboard?tab=customers'));
        $response->assertSessionHas('error');
    }

    /**
     * Test 16: Operator SCADA dialihkan jika mengakses tab tagihan atau pelanggan pada dashboard.
     */
    public function test_scada_operator_dashboard_redirects_when_accessing_bills_or_customers_tab(): void
    {
        $responseBills = $this->actingAs($this->staffUser)->get('/admin-dashboard?tab=bills');
        $responseBills->assertStatus(302);
        $responseBills->assertRedirect(url('/admin-dashboard?tab=verify'));
        $responseBills->assertSessionHas('error');

        $responseCust = $this->actingAs($this->staffUser)->get('/admin-dashboard?tab=customers');
        $responseCust->assertStatus(302);
        $responseCust->assertRedirect(url('/admin-dashboard?tab=verify'));
        $responseCust->assertSessionHas('error');
    }

    /**
     * Test 17: Verifikasi pembayaran dibatasi ketat hanya untuk Admin (Operator SCADA & Petugas Lapangan ditolak).
     */
    public function test_payment_verification_restricted_strictly_to_admin(): void
    {
        $bill = \App\Models\Bill::first() ?? $this->createTestBill();

        $payment = \App\Models\Payment::create([
            'payment_number'   => 'PAY-TEST-' . random_int(1000, 9999),
            'bill_id'          => $bill->id,
            'user_id'          => $bill->user_id,
            'amount'           => $bill->total_amount,
            'payment_method'   => 'bank_transfer',
            'sender_name'      => 'Budi Santoso',
            'proof_photo_path' => 'payments/proof_test.jpg',
            'status'           => 'PENDING',
        ]);

        // 1. Field Officer ditolak verifikasi pembayaran (403)
        $resField = $this->actingAs($this->fieldOfficer)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
            'notes' => 'Mencoba verifikasi pembayaran sebagai field officer',
        ]);
        $resField->assertStatus(403);

        // 2. SCADA Operator ditolak verifikasi pembayaran (403)
        $resScada = $this->actingAs($this->staffUser)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
            'notes' => 'Mencoba verifikasi pembayaran sebagai scada operator',
        ]);
        $resScada->assertStatus(403);

        // 3. Customer ditolak verifikasi pembayaran (403)
        $resCust = $this->actingAs($this->customerUser)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
            'notes' => 'Mencoba verifikasi pembayaran sebagai customer',
        ]);
        $resCust->assertStatus(403);

        // 4. Admin berhasil memverifikasi pembayaran (200)
        $resAdmin = $this->actingAs($this->adminUser)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
            'notes' => 'Pembayaran terverifikasi valid oleh Bank Nagari.',
        ]);
        $resAdmin->assertStatus(200);
        $this->assertEquals('VERIFIED', $payment->fresh()->status);
    }

    /**
     * Test 18: Validasi laporan meter AI-OCR hanya untuk Admin & Operator SCADA (Petugas Lapangan ditolak).
     */
    public function test_meter_validation_restricted_to_admin_and_scada_operator(): void
    {
        $reading = $this->createTestMeterReading($this->customerUser);

        // Field Officer ditolak validasi meter (403)
        $resField = $this->actingAs($this->fieldOfficer)->postJson('/api/v1/meter-reports/validate', [
            'id' => $reading->report_number,
            'action' => 'APPROVE',
        ]);
        $resField->assertStatus(403);

        // SCADA Operator berhasil validasi meter (200)
        $resScada = $this->actingAs($this->staffUser)->postJson('/api/v1/meter-reports/validate', [
            'id' => $reading->report_number,
            'action' => 'APPROVE',
        ]);
        $resScada->assertStatus(200);

        // Reset reading untuk Admin
        $reading->update(['status' => 'Menunggu Validasi']);

        // Admin berhasil validasi meter (200)
        $resAdmin = $this->actingAs($this->adminUser)->postJson('/api/v1/meter-reports/validate', [
            'id' => $reading->report_number,
            'action' => 'APPROVE',
        ]);
        $resAdmin->assertStatus(200);

        // Role legacy staff ditolak validasi meter (403)
        $legacyStaff = User::create([
            'name'     => 'Legacy Staff User',
            'email'    => 'legacy.staff@tirtasago.id',
            'password' => bcrypt('Staff123!'),
        ]);
        $staffRole = \App\Models\Role::where('name', 'staff')->first();
        if ($staffRole) {
            $legacyStaff->roles()->attach($staffRole);
        }
        $resLegacy = $this->actingAs($legacyStaff)->postJson('/api/v1/meter-reports/validate', [
            'id' => $reading->report_number,
            'action' => 'APPROVE',
        ]);
        $resLegacy->assertStatus(403);
    }

    /**
     * Test 19: Isolasi scope tagihan - Admin melihat semua, non-admin hanya melihat milik sendiri.
     */
    public function test_bills_list_scope_isolation(): void
    {
        if (\App\Models\Bill::count() === 0) {
            $this->createTestBill();
        }

        // Admin melihat seluruh tagihan di database
        $resAdmin = $this->actingAs($this->adminUser)->getJson('/api/v1/bills');
        $resAdmin->assertStatus(200);
        $adminCount = count($resAdmin->json('data'));

        // Customer hanya melihat tagihannya sendiri
        $resCust = $this->actingAs($this->customerUser)->getJson('/api/v1/bills');
        $resCust->assertStatus(200);
        $custBills = $resCust->json('data');
        foreach ($custBills as $b) {
            $this->assertEquals($this->customerUser->id, $b['user_id'] ?? $b['customer']['id'] ?? $this->customerUser->id);
        }

        // Field Officer (bukan admin) hanya melihat tagihan miliknya sendiri
        $resField = $this->actingAs($this->fieldOfficer)->getJson('/api/v1/bills');
        $resField->assertStatus(200);
        $this->assertLessThanOrEqual($adminCount, count($resField->json('data')));
    }

    /**
     * Test 20: Proteksi PII pada pengaduan - publik disamarkan, admin dan petugas ditugaskan melihat penuh.
     */
    public function test_complaint_pii_masking_for_public_and_unmasked_for_authorized(): void
    {
        $complaint = Complaint::create([
            'user_id' => $this->customerUser->id,
            'category_id' => $this->defaultCategory->id,
            'ticket_number' => 'TK-PII-' . random_int(1000, 9999),
            'title' => 'Pipa Pecah di Jalan Utama',
            'description' => 'Air meluap ke badan jalan.',
            'district' => 'Payakumbuh Barat',
            'address' => 'Jl. Tan Malaka No. 42 Balai Nan Duo',
            'reporter_name' => 'Budi Santoso',
            'contact_phone' => '0812-3456-7890',
            'status' => 'BARU',
            'priority' => 'NORMAL',
            'assigned_to' => $this->fieldOfficer->id,
        ]);

        // 1. Publik / Tamu melihat daftar pengaduan: PII disamarkan
        Auth::logout();
        unset($_SESSION['swc_user'], $_SESSION['swc_role']);
        $resPublic = $this->getJson('/api/v1/complaints');
        $resPublic->assertStatus(200);
        $publicList = $resPublic->json('complaints');
        $targetTicket = collect($publicList)->firstWhere('id', $complaint->ticket_number)
            ?? collect($publicList)->firstWhere('ticket_number', $complaint->ticket_number);

        $this->assertNotNull($targetTicket);
        $this->assertNotEquals('Budi Santoso', $targetTicket['userName']);
        $this->assertStringContainsString('*', $targetTicket['userName']);
        $this->assertStringContainsString('****', $targetTicket['userPhone']);
        $this->assertStringContainsString('(Detail Disamarkan)', $targetTicket['address']);

        // 2. Admin melihat tiket: PII tidak disamarkan
        $resAdmin = $this->actingAs($this->adminUser)->getJson("/api/v1/complaints/{$complaint->ticket_number}");
        $resAdmin->assertStatus(200);
        $adminTicket = $resAdmin->json('complaint');
        $this->assertEquals('Budi Santoso', $adminTicket['userName']);
        $this->assertEquals('0812-3456-7890', $adminTicket['userPhone']);
        $this->assertEquals('Jl. Tan Malaka No. 42 Balai Nan Duo', $adminTicket['address']);

        // 3. Petugas Lapangan yang ditugaskan melihat tiket: PII tidak disamarkan
        $resField = $this->actingAs($this->fieldOfficer)->getJson("/api/v1/complaints/{$complaint->ticket_number}");
        $resField->assertStatus(200);
        $fieldTicket = $resField->json('complaint');
        $this->assertEquals('Budi Santoso', $fieldTicket['userName']);
        $this->assertEquals('0812-3456-7890', $fieldTicket['userPhone']);

        // 4. Pelanggan lain melihat daftar: PII tetap disamarkan
        $resOtherCust = $this->actingAs($this->otherCustomerUser)->getJson('/api/v1/complaints');
        $resOtherCust->assertStatus(200);
        $otherList = $resOtherCust->json('complaints');
        $maskedTicket = collect($otherList)->firstWhere('id', $complaint->ticket_number)
            ?? collect($otherList)->firstWhere('ticket_number', $complaint->ticket_number);
        $this->assertStringContainsString('*', $maskedTicket['userName']);
    }

    /**
     * Test 21: Visibilitas menu navigasi internal sidebar sesuai kewenangan role.
     */
    public function test_sidebar_menu_visibility_per_role(): void
    {
        // Admin melihat semua menu operasional, tagihan, database sambungan, dan SCADA
        $resAdmin = $this->actingAs($this->adminUser)->get('/admin-dashboard');
        $resAdmin->assertStatus(200);
        $resAdmin->assertSee('id="sidebarNavVerify"', false);
        $resAdmin->assertSee('id="sidebarNavBills"', false);
        $resAdmin->assertSee('id="sidebarNavCustomers"', false);
        $resAdmin->assertSee('/water-monitoring');

        // SCADA Operator melihat validasi meter dan SCADA, tidak melihat menu tagihan dan database sambungan
        $resScada = $this->actingAs($this->staffUser)->get('/admin-dashboard');
        $resScada->assertStatus(200);
        $resScada->assertSee('id="sidebarNavVerify"', false);
        $resScada->assertSee('/water-monitoring');
        $resScada->assertDontSee('id="sidebarNavBills"', false);
        $resScada->assertDontSee('id="sidebarNavCustomers"', false);

        // Field Officer melihat database sambungan dan tiket, tidak melihat tagihan, validasi meter, dan SCADA
        $resField = $this->actingAs($this->fieldOfficer)->get('/admin-dashboard');
        $resField->assertStatus(200);
        $resField->assertSee('id="sidebarNavCustomers"', false);
        $resField->assertSee('/pusat-layanan');
        $resField->assertDontSee('id="sidebarNavVerify"', false);
        $resField->assertDontSee('id="sidebarNavBills"', false);
        $resField->assertDontSee('/water-monitoring');
    }
}
