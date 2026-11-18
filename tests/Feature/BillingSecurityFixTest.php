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
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BillingSecurityFixTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected Tariff $provisionalTariff;
    protected Tariff $approvedTariff;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $custRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Pelanggan']);

        $this->admin = User::factory()->create(['name' => 'Petugas Verifikator PDAM']);
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create(['name' => 'Budi Mandiri']);
        $this->customer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-001',
            'meter_number' => 'MTR-001',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
            'last_meter_value' => 100.0,
            'current_meter_value' => 100.0,
        ]);

        // Provisional tariff (simulasi)
        $this->provisionalTariff = Tariff::create([
            'category_code' => '2A',
            'category_name' => '2A - Rumah Tangga Mandiri',
            'admin_fee' => 2600.00,
            'maintenance_fee' => 1500.00,
            'is_active' => true,
            'is_simulation' => true,
            'approval_status' => 'PROVISIONAL',
        ]);

        TariffTier::create([
            'tariff_id' => $this->provisionalTariff->id,
            'tier_number' => 1,
            'tier_name' => 'Blok I',
            'min_consumption' => 0,
            'max_consumption' => 10,
            'rate_per_m3' => 2500.00,
        ]);

        TariffTier::create([
            'tariff_id' => $this->provisionalTariff->id,
            'tier_number' => 2,
            'tier_name' => 'Blok II',
            'min_consumption' => 11,
            'max_consumption' => 20,
            'rate_per_m3' => 3500.00,
        ]);
    }

    /**
     * Test 1: Tagihan dari tarif provisional wajib berstatus SIMULATION dan ditolak oleh generateOfficialBill().
     */
    public function test_provisional_tariff_cannot_generate_official_bill(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'REP-04-1001',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 115.0,
            'previous_reading' => 100.0,
            'consumption' => 15.0,
            'status' => 'Terverifikasi',
            'validated_by' => $this->admin->id,
            'validated_at' => now(),
            'is_anomaly' => false,
        ]);

        $billingService = app(BillingService::class);

        // Penerbitan tagihan biasa menghasilkan SIMULATION
        $bill = $billingService->generateBillFromReading($reading);
        $this->assertEquals('SIMULATION', $bill->bill_type);
        $this->assertTrue($bill->is_simulation);
        $this->assertStringStartsWith('SIM-PYK-', $bill->bill_number);

        // Penerbitan tagihan resmi wajib melempar pengecualian karena belum disetujui PDAM
        $this->expectException(ValidationException::class);
        $billingService->generateOfficialBill($reading);
    }

    /**
     * Test 2: Tagihan dari tarif resmi approved PDAM menghasilkan status OFFICIAL dengan nomor INV-PYK-...
     */
    public function test_approved_tariff_generates_official_bill(): void
    {
        // Ubah tarif menjadi resmi disetujui PDAM
        $this->provisionalTariff->update([
            'is_simulation' => false,
            'approval_status' => 'APPROVED_PDAM',
            'approved_by' => $this->admin->id,
            'approved_at' => now(),
        ]);

        $reading = MeterReading::create([
            'report_number' => 'REP-04-1002',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 120.0,
            'previous_reading' => 100.0,
            'consumption' => 20.0,
            'status' => 'Terverifikasi',
            'validated_by' => $this->admin->id,
            'validated_at' => now(),
            'is_anomaly' => false,
        ]);

        $bill = app(BillingService::class)->generateOfficialBill($reading);

        $this->assertEquals('OFFICIAL', $bill->bill_type);
        $this->assertFalse($bill->is_simulation);
        $this->assertStringStartsWith('INV-PYK-', $bill->bill_number);
    }

    /**
     * Test 3: Fallback flat rate senyap (silent fallback 7800/2600) telah dihapus total.
     */
    public function test_silent_flat_rate_fallback_is_removed(): void
    {
        $emptyTariff = Tariff::create([
            'category_code' => '99',
            'category_name' => 'Tanpa Blok',
            'admin_fee' => 0.00,
            'is_active' => true,
        ]);

        $billingService = app(BillingService::class);

        $this->expectException(ValidationException::class);
        $billingService->calculateConsumptionCharges(10.0, $emptyTariff);
    }

    /**
     * Test 4: Laporan meter AI murni tanpa validator berwenang diblokir dari penerbitan tagihan.
     */
    public function test_ai_reading_without_validator_cannot_generate_bill(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'REP-04-AI-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi AI',
            'validated_by' => null, // Tidak ada ID petugas sah
            'is_anomaly' => false,
        ]);

        $this->expectException(ValidationException::class);
        app(BillingService::class)->generateBillFromReading($reading);
    }

    /**
     * Test 5: Stand meter yang lebih rendah dari stand lalu dideteksi anomali dan memblokir tagihan.
     */
    public function test_reading_anomaly_lower_than_previous_detected_and_blocks_bill(): void
    {
        $meterService = app(MeterReadingService::class);

        // Kirim bacaan 90.0 padahal baseline adalah 100.0
        $reading = $meterService->createReading([
            'meter_number' => 'MTR-001',
            'ocr_reading' => 90.0,
            'last_reading' => 100.0,
            'confidence' => 99.0,
        ], $this->customer);

        $this->assertTrue($reading->is_anomaly);
        $this->assertEquals('Anomali / Perlu Pemeriksaan Petugas', $reading->status);
        $this->assertNotNull($reading->anomaly_reason);
        $this->assertEquals(0.00, $reading->consumption);

        // Upaya penerbitan tagihan dari reading anomali wajib dilempari ValidationException
        $this->expectException(ValidationException::class);
        app(BillingService::class)->generateBillFromReading($reading);
    }

    /**
     * Test 6: Laporan meter berstatus anomali tidak dapat disetujui tanpa koreksi teknisi.
     */
    public function test_reading_anomaly_cannot_be_approved_without_technical_investigation(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'REP-04-ANOMALY',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 85.0,
            'previous_reading' => 100.0,
            'consumption' => 0.0,
            'status' => 'Anomali / Perlu Pemeriksaan Petugas',
            'is_anomaly' => true,
            'anomaly_reason' => 'Stand menurun drastis.',
        ]);

        $meterService = app(MeterReadingService::class);

        $this->expectException(\InvalidArgumentException::class);
        $meterService->validateReading($reading->id, 'APPROVE', null, $this->admin);
    }

    protected function createDummyReading(): MeterReading
    {
        return MeterReading::create([
            'report_number' => 'REP-DUMMY-' . uniqid(),
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
            'validated_by' => $this->admin->id,
            'validated_at' => now(),
            'is_anomaly' => false,
        ]);
    }

    protected function createDummyBill(array $attributes = []): Bill
    {
        $reading = $this->createDummyReading();
        return Bill::create(array_merge([
            'bill_number' => 'INV-TEST-' . uniqid(),
            'meter_reading_id' => $reading->id,
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-001',
            'meter_number' => 'MTR-001',
            'billing_period' => '2026-11',
            'period_label' => 'November 2026',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
            'previous_reading' => 100.0,
            'current_reading' => 110.0,
            'consumption' => 10.0,
            'water_charge' => 25000.00,
            'admin_fee' => 2600.00,
            'maintenance_fee' => 1500.00,
            'penalty_fee' => 0.00,
            'total_amount' => 29100.00,
            'due_date' => now()->addDays(15),
            'status' => 'UNPAID',
        ], $attributes));
    }

    /**
     * Test 7: Pembacaan meter pertama menggunakan baseline profil pelanggan / 0.00 m3 bukan -19.0 m3 dummy.
     */
    public function test_first_meter_reading_uses_baseline_instead_of_dummy_fallback(): void
    {
        $newCustomer = User::factory()->create(['name' => 'Pelanggan Baru']);
        $custRole = Role::firstOrCreate(['name' => 'customer']);
        $newCustomer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $newCustomer->id,
            'customer_id' => 'CUST-NEW-01',
            'meter_number' => 'MTR-NEW-01',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
            'last_meter_value' => 0.00,
            'current_meter_value' => 0.00,
        ]);

        $meterService = app(MeterReadingService::class);

        $reading = $meterService->createReading([
            'meter_number' => 'MTR-NEW-01',
            'ocr_reading' => 15.0,
            'last_reading' => 0, // Belum ada stand lalu
        ], $newCustomer);

        // Stand lalu baseline harus 0.00 (bukan 15 - 19.0)
        $this->assertEquals(0.00, $reading->previous_reading);
        $this->assertEquals(15.00, $reading->consumption);
    }

    /**
     * Test 8: Nominal pembayaran harus sama persis dengan total tagihan resmi di database (SEC-BILL-04).
     */
    public function test_payment_amount_must_match_exact_bill_total(): void
    {
        Storage::fake('public');

        $bill = $this->createDummyBill([
            'total_amount' => 29100.00,
        ]);

        $paymentService = app(PaymentService::class);
        $file = UploadedFile::fake()->create('proof.jpg', 200, 'image/jpeg');

        // Nominal sengaja diubah menjadi 10.000 (tidak cocok dengan 29.100)
        $this->expectException(ValidationException::class);
        $paymentService->submitManualPayment($bill, $this->customer->id, [
            'amount' => 10000.00,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Mandiri',
        ], $file);
    }

    /**
     * Test 9: Cegah submit pembayaran ganda jika masih ada transaksi berstatus PENDING (SEC-BILL-05).
     */
    public function test_cannot_submit_duplicate_payment_while_pending_exists(): void
    {
        Storage::fake('public');

        $bill = $this->createDummyBill([
            'total_amount' => 27600.00,
        ]);

        $paymentService = app(PaymentService::class);
        $file = UploadedFile::fake()->create('proof1.jpg', 200, 'image/jpeg');

        // Submit pembayaran pertama
        $paymentService->submitManualPayment($bill, $this->customer->id, [
            'amount' => 27600.00,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Mandiri',
        ], $file);

        // Submit pembayaran kedua saat pembayaran pertama masih PENDING
        $file2 = UploadedFile::fake()->create('proof2.jpg', 200, 'image/jpeg');
        $this->expectException(ValidationException::class);
        $paymentService->submitManualPayment($bill, $this->customer->id, [
            'amount' => 27600.00,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Mandiri',
        ], $file2);
    }

    /**
     * Test 10: Tagihan yang sudah berstatus PAID tidak dapat dibayar kembali.
     */
    public function test_cannot_pay_already_paid_bill(): void
    {
        Storage::fake('public');

        $bill = $this->createDummyBill([
            'status' => 'PAID', // Sudah lunas
        ]);

        $paymentService = app(PaymentService::class);
        $file = UploadedFile::fake()->create('proof.jpg', 200, 'image/jpeg');

        $this->expectException(ValidationException::class);
        $paymentService->submitManualPayment($bill, $this->customer->id, [
            'amount' => 29100.00,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Mandiri',
        ], $file);
    }

    /**
     * Test 11: Verifikasi pembayaran oleh admin mengubah status tagihan ke PAID dengan pessimistic lock (SEC-BILL-06).
     */
    public function test_admin_verification_changes_bill_to_paid_and_locks_state(): void
    {
        Storage::fake('public');

        $bill = $this->createDummyBill([
            'total_amount' => 27600.00,
            'status' => 'PENDING_VERIFICATION',
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-PYK-001',
            'bill_id' => $bill->id,
            'user_id' => $this->customer->id,
            'amount' => 27600.00,
            'payment_method' => 'manual_transfer',
            'proof_photo_path' => 'proof.jpg',
            'status' => 'PENDING',
        ]);

        $paymentService = app(PaymentService::class);
        $result = $paymentService->verifyPayment($payment, $this->admin->id, 'Struk transfer sah.');

        $this->assertTrue($result);
        $this->assertEquals('VERIFIED', $payment->fresh()->status);
        $this->assertEquals('PAID', $bill->fresh()->status);
        $this->assertNotNull($bill->fresh()->paid_at);
        $this->assertDatabaseHas('payment_histories', [
            'payment_id' => $payment->id,
            'new_status' => 'VERIFIED',
            'actor_id' => $this->admin->id,
        ]);
    }

    /**
     * Test 12: Penolakan oleh admin mengembalikan status tagihan ke UNPAID dengan catatan penolakan.
     */
    public function test_admin_rejection_reverts_bill_to_unpaid(): void
    {
        Storage::fake('public');

        $bill = $this->createDummyBill([
            'total_amount' => 27600.00,
            'status' => 'PENDING_VERIFICATION',
        ]);

        $payment = Payment::create([
            'payment_number' => 'PAY-PYK-002',
            'bill_id' => $bill->id,
            'user_id' => $this->customer->id,
            'amount' => 27600.00,
            'payment_method' => 'manual_transfer',
            'proof_photo_path' => 'proof.jpg',
            'status' => 'PENDING',
        ]);

        $paymentService = app(PaymentService::class);
        $result = $paymentService->rejectPayment($payment, $this->admin->id, 'Bukti transfer buram dan nominal tidak terbaca.');

        $this->assertTrue($result);
        $this->assertEquals('REJECTED', $payment->fresh()->status);
        $this->assertEquals('UNPAID', $bill->fresh()->status);
        $this->assertEquals('Bukti transfer buram dan nominal tidak terbaca.', $payment->fresh()->rejection_reason);
        $this->assertDatabaseHas('payment_histories', [
            'payment_id' => $payment->id,
            'new_status' => 'REJECTED',
            'actor_id' => $this->admin->id,
        ]);
    }
}
