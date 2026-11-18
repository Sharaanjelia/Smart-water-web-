<?php

namespace Tests\Feature;

use App\Models\Bill;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Payment;
use App\Models\Role;
use App\Models\Tariff;
use App\Models\TariffTier;
use App\Models\User;
use App\Services\BillingService;
use App\Services\MeterReadingService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BillingPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $otherCustomer;
    protected Tariff $tariff;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Roles
        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $custRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Pelanggan']);

        // 2. Users
        $this->admin = User::factory()->create(['name' => 'Admin PDAM']);
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create(['name' => 'Budi Santoso']);
        $this->customer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-PYK-001',
            'meter_number' => 'MTR-1001',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
            'current_meter_value' => 120.0,
            'last_meter_value' => 100.0,
        ]);

        $this->otherCustomer = User::factory()->create(['name' => 'Siti Rahma']);
        $this->otherCustomer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->otherCustomer->id,
            'customer_id' => 'CUST-PYK-002',
            'meter_number' => 'MTR-1002',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
            'current_meter_value' => 50.0,
            'last_meter_value' => 40.0,
        ]);

        // 3. Dynamic Tariff (sample provisional multi-tier)
        $this->tariff = Tariff::create([
            'category_code' => '2A',
            'category_name' => '2A - Rumah Tangga Mandiri',
            'description' => 'Tarif Rumah Tangga Dinamis',
            'admin_fee' => 2600.00,
            'maintenance_fee' => 0.00,
            'penalty_flat' => 10000.00,
            'due_day_of_month' => 20,
            'is_active' => true,
        ]);

        TariffTier::create([
            'tariff_id' => $this->tariff->id,
            'tier_number' => 1,
            'tier_name' => 'Blok I (0-10 m3)',
            'min_consumption' => 0,
            'max_consumption' => 10,
            'rate_per_m3' => 2500.00,
        ]);

        TariffTier::create([
            'tariff_id' => $this->tariff->id,
            'tier_number' => 2,
            'tier_name' => 'Blok II (11-20 m3)',
            'min_consumption' => 11,
            'max_consumption' => 20,
            'rate_per_m3' => 3200.00,
        ]);
    }

    public function test_meter_reading_approval_generates_bill_dynamically(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0001',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 115.0,
            'previous_reading' => 100.0,
            'consumption' => 15.0, // 10 m3 @ 2500 (=25000) + 5 m3 @ 3200 (=16000) = 41000 + 2600 admin = 43600
            'status' => 'Menunggu Validasi',
        ]);

        $meterService = app(MeterReadingService::class);
        $meterService->validateReading($reading->id, 'APPROVE', null, $this->admin);

        $bill = Bill::where('meter_reading_id', $reading->id)->first();
        $this->assertNotNull($bill);
        $this->assertEquals('UNPAID', $bill->status);
        $this->assertEquals(15.0, (float) $bill->consumption);
        $this->assertEquals(41000.0, (float) $bill->water_charge);
        $this->assertEquals(2600.0, (float) $bill->admin_fee);
        $this->assertEquals(43600.0, (float) $bill->total_amount);
        $this->assertNotNull($bill->tariff_snapshot);
        $this->assertCount(3, $bill->details); // Blok 1, Blok 2, Admin Fee
    }

    public function test_bill_generation_is_idempotent(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0002',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => $this->admin->id,
        ]);

        $billingService = app(BillingService::class);
        $bill1 = $billingService->generateBillFromReading($reading);
        $bill2 = $billingService->generateBillFromReading($reading);

        $this->assertEquals($bill1->id, $bill2->id);
        $this->assertEquals(1, Bill::where('meter_reading_id', $reading->id)->count());
    }

    public function test_customer_can_submit_manual_payment_and_status_is_pending_not_paid(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0003',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => $this->admin->id,
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $this->assertEquals('UNPAID', $bill->status);

        $file = UploadedFile::fake()->create('bukti_transfer.jpg', 200, 'image/jpeg');

        $response = $this->actingAs($this->customer)->postJson('/api/v1/payments', [
            'bill_id' => $bill->id,
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'bank_name' => 'Bank Nagari',
            'sender_name' => 'Budi Santoso',
            'proof_photo' => $file,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('payments', [
            'bill_id' => $bill->id,
            'user_id' => $this->customer->id,
            'status' => 'PENDING',
            'bank_name' => 'Bank Nagari',
        ]);

        // CRITICAL: Bill status MUST be PENDING_VERIFICATION, strictly NOT PAID yet!
        $bill->refresh();
        $this->assertEquals('PENDING_VERIFICATION', $bill->status);
        $this->assertNull($bill->paid_at);
    }

    public function test_customer_cannot_submit_payment_for_other_customer_bill(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0004',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => $this->admin->id,
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);

        $file = UploadedFile::fake()->create('bukti.jpg', 200, 'image/jpeg');

        // Other customer tries to pay customer's bill
        $response = $this->actingAs($this->otherCustomer)->postJson('/api/v1/payments', [
            'bill_id' => $bill->id,
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Siti Rahma',
            'proof_photo' => $file,
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_verification_changes_bill_status_to_paid(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0005',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => $this->admin->id,
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $file = UploadedFile::fake()->create('bukti.png', 200, 'image/png');

        $payment = app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'bank_name' => 'Bank Nagari',
            'sender_name' => 'Budi Santoso',
        ], $file);

        $this->assertEquals('PENDING', $payment->status);
        $this->assertEquals('PENDING_VERIFICATION', $bill->fresh()->status);

        // Customer CANNOT verify payment (403)
        $unauthorized = $this->actingAs($this->customer)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
        ]);
        $unauthorized->assertStatus(403);

        // Admin verifies payment
        $response = $this->actingAs($this->admin)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'VERIFIED',
            'notes' => 'Mutasi rekening Bank Nagari sesuai.',
        ]);

        $response->assertStatus(200);

        // Check payment is VERIFIED
        $payment->refresh();
        $this->assertEquals('VERIFIED', $payment->status);
        $this->assertEquals($this->admin->id, $payment->verified_by);
        $this->assertNotNull($payment->verified_at);

        // CRITICAL RULE: Bill status MUST NOW BE PAID!
        $bill->refresh();
        $this->assertEquals('PAID', $bill->status);
        $this->assertNotNull($bill->paid_at);
    }

    public function test_admin_rejection_reverts_bill_status_to_unpaid(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-202611-0006',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-1001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => $this->admin->id,
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $file = UploadedFile::fake()->create('bukti_palsu.png', 200, 'image/png');

        $payment = app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file);

        // Admin rejects payment
        $response = $this->actingAs($this->admin)->postJson("/api/v1/payments/{$payment->id}/verify", [
            'status' => 'REJECTED',
            'rejection_reason' => 'Struk tidak terbaca atau nominal tidak cocok dengan mutasi.',
        ]);

        $response->assertStatus(200);

        $payment->refresh();
        $this->assertEquals('REJECTED', $payment->status);
        $this->assertEquals('Struk tidak terbaca atau nominal tidak cocok dengan mutasi.', $payment->rejection_reason);

        // Bill reverts to UNPAID
        $bill->refresh();
        $this->assertEquals('UNPAID', $bill->status);
        $this->assertNull($bill->paid_at);
    }
}
