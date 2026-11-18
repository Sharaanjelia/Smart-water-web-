<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Role;
use App\Models\Tariff;
use App\Models\TariffTier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingDataIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected Tariff $tariff;
    protected MeterReading $reading;

    protected function setUp(): void
    {
        parent::setUp();

        $custRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Pelanggan']);

        $this->customer = User::factory()->create([
            'name' => 'Budi Pratama',
            'email' => 'budi.pratama@example.com',
        ]);
        $this->customer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-PYK-001',
            'meter_number' => 'MTR-7788',
            'tariff_category' => '2A - Rumah Tangga',
            'zone' => 'DIST-A Payakumbuh Utara',
            'last_meter_value' => 0.00,
            'current_meter_value' => 25.00,
        ]);

        $this->otherCustomer = User::factory()->create([
            'name' => 'Siti Rahmawati',
            'email' => 'siti.rahmawati@example.com',
        ]);
        $this->otherCustomer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->otherCustomer->id,
            'customer_id' => 'CUST-PYK-002',
            'meter_number' => 'MTR-9900',
            'tariff_category' => '2A - Rumah Tangga',
            'zone' => 'DIST-B Payakumbuh Selatan',
            'last_meter_value' => 0.00,
            'current_meter_value' => 15.00,
        ]);

        $this->reading = MeterReading::create([
            'user_id' => $this->customer->id,
            'meter_number' => 'MTR-7788',
            'reading' => 25.0,
            'previous_reading' => 10.0,
            'consumption' => 15.0,
            'status' => 'APPROVED',
            'is_anomaly' => false,
        ]);
    }

    protected function createBill(array $overrides = []): Bill
    {
        $userId = $overrides['user_id'] ?? $this->customer->id;
        $meterNum = $overrides['meter_number'] ?? 'MTR-7788';

        $reading = MeterReading::create([
            'user_id' => $userId,
            'meter_number' => $meterNum,
            'reading' => 25.0,
            'previous_reading' => 10.0,
            'consumption' => 15.0,
            'status' => 'APPROVED',
            'is_anomaly' => false,
        ]);

        $defaults = [
            'user_id' => $userId,
            'meter_reading_id' => $reading->id,
            'bill_number' => 'BILL-PYK-' . uniqid(),
            'customer_id' => 'CUST-PYK-001',
            'meter_number' => $meterNum,
            'tariff_category' => '2A - Rumah Tangga',
            'billing_period' => '2026-05',
            'period_label' => 'Mei 2026',
            'previous_reading' => 10.00,
            'current_reading' => 25.00,
            'consumption' => 15.00,
            'water_charge' => 45000.00,
            'admin_fee' => 3000.00,
            'penalty_fee' => 0.00,
            'total_amount' => 48000.00,
            'status' => 'UNPAID',
            'bill_type' => 'OFFICIAL',
            'is_simulation' => false,
            'due_date' => now()->addDays(15)->format('Y-m-d'),
        ];

        $bill = Bill::create(array_merge($defaults, $overrides));

        BillDetail::create([
            'bill_id' => $bill->id,
            'item_type' => 'WATER_USAGE',
            'description' => 'Pemakaian Air Blok I (10 m³)',
            'volume' => 10.0,
            'rate' => 2500.0,
            'subtotal' => 25000.0,
        ]);

        BillDetail::create([
            'bill_id' => $bill->id,
            'item_type' => 'WATER_USAGE',
            'description' => 'Pemakaian Air Blok II (5 m³)',
            'volume' => 5.0,
            'rate' => 4000.0,
            'subtotal' => 20000.0,
        ]);

        BillDetail::create([
            'bill_id' => $bill->id,
            'item_type' => 'ADMIN_FEE',
            'description' => 'Biaya Administrasi & Beban',
            'volume' => 0.0,
            'rate' => 3000.0,
            'subtotal' => 3000.0,
        ]);

        return $bill;
    }

    /**
     * Test 1: Pelanggan dapat melihat tagihan miliknya sendiri pada halaman /bill.
     */
    public function test_01_customer_can_view_own_bill_on_bill_index(): void
    {
        $bill = $this->createBill([
            'bill_number' => 'BILL-PYK-2026-001',
            'total_amount' => 48000.00,
        ]);

        $response = $this->actingAs($this->customer)->get('/bill');

        $response->assertStatus(200);
        $response->assertViewHas('activeBill');
        $response->assertSee('BILL-PYK-2026-001');
        $response->assertSee('48.000');
    }

    /**
     * Test 2: IDOR Protection: Pelanggan tidak dapat melihat tagihan milik pelanggan lain pada /bill-detail.
     */
    public function test_02_customer_cannot_view_another_customer_bill_idor_protection(): void
    {
        $otherBill = $this->createBill([
            'user_id' => $this->otherCustomer->id,
            'customer_id' => 'CUST-PYK-002',
            'bill_number' => 'BILL-SECRET-OTHER-002',
        ]);

        $response = $this->actingAs($this->customer)->get('/bill-detail?id=' . $otherBill->id);

        $response->assertStatus(403);
    }

    /**
     * Test 3: Rincian tagihan pada /bill-detail sesuai dengan data MySQL.
     */
    public function test_03_bill_detail_matches_database_data(): void
    {
        $bill = $this->createBill([
            'bill_number' => 'BILL-DETAIL-TEST-003',
            'consumption' => 15.00,
            'total_amount' => 48000.00,
        ]);

        $response = $this->actingAs($this->customer)->get('/bill-detail?id=' . $bill->id);

        $response->assertStatus(200);
        $response->assertSee('BILL-DETAIL-TEST-003');
        $response->assertSee('Pemakaian Air Blok I');
        $response->assertSee('Pemakaian Air Blok II');
        $response->assertSee('Biaya Administrasi & Beban');
        $response->assertSee('48.000');
        $response->assertSee('15,0 m³');
    }

    /**
     * Test 4: Riwayat tagihan pada /bill-history menampilkan data dari MySQL.
     */
    public function test_04_bill_history_displays_database_records(): void
    {
        $paidBill1 = $this->createBill([
            'bill_number' => 'BILL-HIST-001',
            'billing_period' => '2026-03',
            'status' => 'PAID',
            'total_amount' => 35000.00,
        ]);

        $paidBill2 = $this->createBill([
            'bill_number' => 'BILL-HIST-002',
            'billing_period' => '2026-04',
            'status' => 'PAID',
            'total_amount' => 42000.00,
        ]);

        $response = $this->actingAs($this->customer)->get('/bill-history');

        $response->assertStatus(200);
        $response->assertSee('BILL-HIST-001');
        $response->assertSee('BILL-HIST-002');
        $response->assertSee('35.000');
        $response->assertSee('42.000');
    }

    /**
     * Test 5: Tagihan SIMULATION menampilkan badge 'SIMULASI / DEMO'.
     */
    public function test_05_simulation_bill_has_simulation_badge(): void
    {
        $simBill = $this->createBill([
            'bill_number' => 'BILL-SIM-005',
            'bill_type' => 'SIMULATION',
            'is_simulation' => true,
        ]);

        $responseIndex = $this->actingAs($this->customer)->get('/bill');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('SIMULASI / DEMO');

        $responseDetail = $this->actingAs($this->customer)->get('/bill-detail?id=' . $simBill->id);
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('SIMULASI / DEMO');
    }

    /**
     * Test 6: Tagihan SIMULATION tidak memiliki link pembayaran aktif.
     */
    public function test_06_simulation_bill_has_no_active_payment_link(): void
    {
        $simBill = $this->createBill([
            'bill_number' => 'BILL-SIM-006',
            'bill_type' => 'SIMULATION',
            'is_simulation' => true,
        ]);

        $responseIndex = $this->actingAs($this->customer)->get('/bill');
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Simulasi — Pembayaran Non-Aktif');
        $responseIndex->assertDontSee('/payment-method?bill_id=' . $simBill->id);

        $responseDetail = $this->actingAs($this->customer)->get('/bill-detail?id=' . $simBill->id);
        $responseDetail->assertStatus(200);
        $responseDetail->assertSee('Tagihan Simulasi — Pembayaran Resmi Tidak Dapat Dilakukan');
        $responseDetail->assertDontSee('/payment-method?bill_id=' . $simBill->id);
    }

    /**
     * Test 7: Tagihan OFFICIAL memiliki badge 'TAGIHAN RESMI' dan status yang benar.
     */
    public function test_07_official_bill_has_official_badge_and_proper_status(): void
    {
        $officialBill = $this->createBill([
            'bill_number' => 'BILL-OFFICIAL-007',
            'bill_type' => 'OFFICIAL',
            'is_simulation' => false,
            'status' => 'UNPAID',
        ]);

        $response = $this->actingAs($this->customer)->get('/bill-detail?id=' . $officialBill->id);
        $response->assertStatus(200);
        $response->assertSee('TAGIHAN RESMI');
        $response->assertSee('BELUM BAYAR');
        $response->assertSee('Fitur pembayaran sedang dipersiapkan.');
    }

    /**
     * Test 8: Status PENDING_VERIFICATION ditampilkan sebagai 'Menunggu Verifikasi' (bukan LUNAS).
     */
    public function test_08_pending_verification_is_displayed_as_menunggu_verifikasi(): void
    {
        $pendingBill = $this->createBill([
            'bill_number' => 'BILL-PENDING-008',
            'status' => 'PENDING_VERIFICATION',
        ]);

        $response = $this->actingAs($this->customer)->get('/bill-detail?id=' . $pendingBill->id);
        $response->assertStatus(200);
        $response->assertSee('MENUNGGU VERIFIKASI');
        $response->assertSee('Bukti Pembayaran Sedang Diverifikasi oleh Petugas PDAM');
    }

    /**
     * Test 9: Akun pelanggan login tidak melihat invoice hardcoded INV-PYK-2025-04-1000.
     */
    public function test_09_authenticated_customer_does_not_see_hardcoded_april_2025_invoice(): void
    {
        $bill = $this->createBill([
            'bill_number' => 'BILL-REAL-2026-009',
        ]);

        $response = $this->actingAs($this->customer)->get('/bill');
        $response->assertStatus(200);
        $response->assertSee('BILL-REAL-2026-009');
        $response->assertDontSee('INV-PYK-2025-04-1000');
    }

    /**
     * Test 10: Unauthenticated diarahkan ke login secara aman dan customer tanpa tagihan render 200 tanpa error.
     */
    public function test_10_unauthenticated_demo_mode_renders_safely(): void
    {
        // Tamu tanpa login diarahkan ke login
        $resBill = $this->get('/bill');
        $resBill->assertStatus(302);
        $resBill->assertRedirect('/login');

        $resDetail = $this->get('/bill-detail');
        $resDetail->assertStatus(302);
        $resDetail->assertRedirect('/login');

        $resHistory = $this->get('/bill-history');
        $resHistory->assertStatus(302);
        $resHistory->assertRedirect('/login');

        // Customer login tanpa tagihan render dengan aman (status 200)
        $emptyCustomer = User::factory()->create();
        $emptyCustomer->roles()->attach(Role::where('name', 'customer')->first());

        $resCustomerBill = $this->actingAs($emptyCustomer)->get('/bill');
        $resCustomerBill->assertStatus(200);

        $resCustomerDetail = $this->actingAs($emptyCustomer)->get('/bill-detail');
        $resCustomerDetail->assertStatus(200);

        $resCustomerHistory = $this->actingAs($emptyCustomer)->get('/bill-history');
        $resCustomerHistory->assertStatus(200);
    }
}
