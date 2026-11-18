<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Role;
use App\Models\User;
use App\Policies\BillPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BillingPaymentSafetyCheckTest extends TestCase
{
    use RefreshDatabase;

    protected User $customer;
    protected User $otherCustomer;
    protected BillPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $custRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Pelanggan']);

        $this->customer = User::factory()->create(['name' => 'Budi Santoso']);
        $this->customer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-001',
            'meter_number' => 'MTR-001',
            'tariff_category' => '2A - Rumah Tangga',
        ]);

        $this->otherCustomer = User::factory()->create(['name' => 'Ahmad Dani']);
        $this->otherCustomer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->otherCustomer->id,
            'customer_id' => 'CUST-002',
            'meter_number' => 'MTR-002',
            'tariff_category' => '2A - Rumah Tangga',
        ]);

        $this->policy = new BillPolicy();
    }

    protected function createBill(array $overrides = []): Bill
    {
        $reading = MeterReading::create([
            'user_id' => $overrides['user_id'] ?? $this->customer->id,
            'meter_number' => 'MTR-001',
            'reading' => 20.0,
            'previous_reading' => 10.0,
            'consumption' => 10.0,
            'status' => 'APPROVED',
            'is_anomaly' => false,
        ]);

        $defaults = [
            'user_id' => $this->customer->id,
            'meter_reading_id' => $reading->id,
            'bill_number' => 'BILL-' . uniqid(),
            'customer_id' => 'CUST-001',
            'meter_number' => 'MTR-001',
            'tariff_category' => '2A - Rumah Tangga',
            'billing_period' => '2026-05',
            'period_label' => 'Mei 2026',
            'previous_reading' => 10.00,
            'current_reading' => 20.00,
            'consumption' => 10.00,
            'water_charge' => 25000.00,
            'admin_fee' => 3000.00,
            'penalty_fee' => 0.00,
            'total_amount' => 28000.00,
            'status' => 'UNPAID',
            'bill_type' => 'OFFICIAL',
            'is_simulation' => false,
            'due_date' => now()->addDays(15)->format('Y-m-d'),
        ];

        return Bill::create(array_merge($defaults, $overrides));
    }

    /**
     * Safety Check 1: BillPolicy menolak pembayaran untuk tagihan SIMULASI.
     */
    public function test_simulation_bill_cannot_be_paid_via_policy(): void
    {
        $simBill = $this->createBill([
            'bill_type' => 'SIMULATION',
            'is_simulation' => true,
        ]);

        $this->assertFalse($this->policy->pay($this->customer, $simBill));
    }

    /**
     * Safety Check 2: BillPolicy menolak pembayaran untuk tagihan yang sedang PENDING_VERIFICATION.
     */
    public function test_pending_verification_bill_cannot_be_paid_via_policy(): void
    {
        $pendingBill = $this->createBill([
            'status' => 'PENDING_VERIFICATION',
        ]);

        $this->assertFalse($this->policy->pay($this->customer, $pendingBill));
    }

    /**
     * Safety Check 3: BillPolicy menolak pembayaran untuk tagihan yang sudah PAID.
     */
    public function test_paid_bill_cannot_be_paid_via_policy(): void
    {
        $paidBill = $this->createBill([
            'status' => 'PAID',
        ]);

        $this->assertFalse($this->policy->pay($this->customer, $paidBill));
    }

    /**
     * Safety Check 4: Pelanggan lain tidak dapat membayar tagihan milik pelanggan berbeda (IDOR Check).
     */
    public function test_other_customer_cannot_pay_bill_via_policy(): void
    {
        $bill = $this->createBill(['user_id' => $this->customer->id]);

        $this->assertFalse($this->policy->pay($this->otherCustomer, $bill));
    }

    /**
     * Safety Check 5: Halaman /payment-method menampilkan pemberitahuan dan tombol disabled.
     */
    public function test_payment_method_page_displays_preparation_notice(): void
    {
        $response = $this->actingAs($this->customer)->get('/payment-method');

        $response->assertStatus(200);
        $response->assertSee('Fitur pembayaran sedang dipersiapkan.');
        $response->assertSee('disabled', false);
    }

    /**
     * Safety Check 6: Halaman /payment-instruction menampilkan pemberitahuan dan tombol disabled.
     */
    public function test_payment_instruction_page_displays_preparation_notice(): void
    {
        $response = $this->actingAs($this->customer)->get('/payment-instruction');

        $response->assertStatus(200);
        $response->assertSee('Fitur pembayaran sedang dipersiapkan.');
        $response->assertSee('disabled', false);
    }

    /**
     * Safety Check 7: Halaman /payment-success bersih dari script mutasi payBill client-side.
     */
    public function test_payment_success_page_does_not_mutate_bill(): void
    {
        $response = $this->actingAs($this->customer)->get('/payment-success');

        $response->assertStatus(200);
        $response->assertDontSee('window.SWC.payBill');
        $response->assertDontSee('SWC.payBill');
    }

    /**
     * Safety Check 8: Tombol bayar pada /bill berstatus non-aktif untuk tagihan official sebelum Task 10B.
     */
    public function test_bill_index_active_bill_has_payment_disabled_notice(): void
    {
        $this->createBill(['status' => 'UNPAID']);

        $response = $this->actingAs($this->customer)->get('/bill');

        $response->assertStatus(200);
        $response->assertSee('Fitur pembayaran sedang dipersiapkan.');
    }

    /**
     * Safety Check 9: Tombol bayar pada /bill-detail berstatus non-aktif sebelum Task 10B.
     */
    public function test_bill_detail_has_payment_disabled_notice(): void
    {
        $bill = $this->createBill(['status' => 'UNPAID']);

        $response = $this->actingAs($this->customer)->get('/bill-detail?id=' . $bill->id);

        $response->assertStatus(200);
        $response->assertSee('Fitur pembayaran sedang dipersiapkan.');
    }
}
