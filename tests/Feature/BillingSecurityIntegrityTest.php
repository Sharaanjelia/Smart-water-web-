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
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BillingSecurityIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $customer;
    protected User $otherCustomer;
    protected Tariff $tariff;

    protected function setUp(): void
    {
        parent::setUp();

        $adminRole = Role::firstOrCreate(['name' => 'admin'], ['display_name' => 'Administrator']);
        $custRole = Role::firstOrCreate(['name' => 'customer'], ['display_name' => 'Pelanggan']);

        $this->admin = User::factory()->create(['name' => 'Admin PDAM']);
        $this->admin->roles()->attach($adminRole);

        $this->customer = User::factory()->create(['name' => 'Budi Santoso']);
        $this->customer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->customer->id,
            'customer_id' => 'CUST-001',
            'meter_number' => 'MTR-001',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
        ]);

        $this->otherCustomer = User::factory()->create(['name' => 'Siti Rahma']);
        $this->otherCustomer->roles()->attach($custRole);

        CustomerProfile::create([
            'user_id' => $this->otherCustomer->id,
            'customer_id' => 'CUST-002',
            'meter_number' => 'MTR-002',
            'tariff_category' => '2A - Rumah Tangga Mandiri',
        ]);

        $this->tariff = Tariff::create([
            'category_code' => '2A',
            'category_name' => '2A - Rumah Tangga Mandiri',
            'admin_fee' => 2600.00,
            'is_active' => true,
        ]);

        TariffTier::create([
            'tariff_id' => $this->tariff->id,
            'tier_number' => 1,
            'tier_name' => 'Blok I',
            'min_consumption' => 0,
            'max_consumption' => 10,
            'rate_per_m3' => 2500.00,
        ]);
    }

    /**
     * Test 1: Meter reading yang belum divalidasi tidak boleh menghasilkan tagihan.
     */
    public function test_unapproved_meter_reading_cannot_generate_bill(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'RPT-PENDING-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Menunggu Validasi', // Belum disetujui petugas
        ]);

        $this->expectException(ValidationException::class);
        app(BillingService::class)->generateBillFromReading($reading);
    }

    /**
     * Test 2: Pelanggan lain tidak dapat membayar tagihan yang bukan miliknya (IDOR Check).
     */
    public function test_other_customer_cannot_pay_someone_else_bill(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-APPROVED-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);

        $file = UploadedFile::fake()->create('struk.jpg', 150, 'image/jpeg');

        // Other customer attempts to submit payment
        $response = $this->actingAs($this->otherCustomer)->postJson('/api/v1/payments', [
            'bill_id' => $bill->id,
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Siti Rahma',
            'proof_photo' => $file,
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test 3: Tagihan PAID tidak bisa dibayar ulang.
     */
    public function test_paid_bill_cannot_be_paid_again(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-PAID-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);

        // Mark bill as PAID
        $bill->update(['status' => 'PAID', 'paid_at' => now()]);

        $file = UploadedFile::fake()->create('struk2.jpg', 150, 'image/jpeg');

        $this->expectException(ValidationException::class);
        app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file);
    }

    /**
     * Test 4: Snapshot tarif lama tetap tidak berubah saat master tarif dinaikkan.
     */
    public function test_existing_bill_preserves_snapshot_when_tariff_is_modified(): void
    {
        $reading = MeterReading::create([
            'report_number' => 'RPT-SNAP-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0, // 10 m3 @ 2500 = 25000 + 2600 = 27600
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $originalAmount = (float) $bill->total_amount;
        $this->assertEquals(27600.00, $originalAmount);

        // Master tarif diubah / dinaikkan di kemudian hari
        $tier = $this->tariff->tiers()->first();
        $tier->update(['rate_per_m3' => 9999.00]);
        $this->tariff->update(['admin_fee' => 50000.00]);

        // Tagihan lama harus tetap mempertahankan snapshot lama
        $bill->refresh();
        $this->assertEquals(27600.00, (float) $bill->total_amount);
        $this->assertEquals(2500.00, (float) $bill->tariff_snapshot['tiers'][0]['rate']);
    }

    /**
     * Test 5: Pembayaran ditolak admin lalu pelanggan mengajukan ulang dengan bukti baru.
     */
    public function test_payment_rejection_and_resubmission_lifecycle(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-REJECT-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $file1 = UploadedFile::fake()->create('struk_buram.jpg', 150, 'image/jpeg');

        // 1. Submit payment 1
        $payment1 = app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file1);

        $this->assertEquals('PENDING', $payment1->status);
        $this->assertEquals('PENDING_VERIFICATION', $bill->fresh()->status);

        // 2. Admin rejects payment 1
        app(PaymentService::class)->rejectPayment($payment1, $this->admin->id, 'Foto struk buram dan tidak terbaca');

        $payment1->refresh();
        $bill->refresh();
        $this->assertEquals('REJECTED', $payment1->status);
        $this->assertEquals('UNPAID', $bill->status); // Status tagihan kembali UNPAID

        // 3. Pelanggan mengajukan ulang dengan bukti struk yang jelas
        $file2 = UploadedFile::fake()->create('struk_jelas.jpg', 150, 'image/jpeg');
        $payment2 = app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file2);

        $this->assertEquals('PENDING', $payment2->status);
        $this->assertEquals('PENDING_VERIFICATION', $bill->fresh()->status);

        // 4. Admin menyetujui payment 2
        app(PaymentService::class)->verifyPayment($payment2, $this->admin->id, 'Struk jelas, mutasi cocok');

        $payment2->refresh();
        $bill->refresh();
        $this->assertEquals('VERIFIED', $payment2->status);
        $this->assertEquals('PAID', $bill->status); // Status tagihan sekarang resmi LUNAS (PAID)

        // Riwayat kedua pembayaran tetap tersimpan di database
        $this->assertEquals(2, Payment::where('bill_id', $bill->id)->count());
    }

    /**
     * Test 6 (Audit Finding - Medium Risk): Validasi nominal pembayaran saat ini belum memblokir nominal yang berbeda dari total tagihan.
     */
    public function test_amount_tampering_behavior_audit(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-TAMPER-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $this->assertEquals(27600.00, (float) $bill->total_amount);

        $file = UploadedFile::fake()->create('struk_manipulasi.jpg', 150, 'image/jpeg');

        // Mengirim nominal 500 rupiah untuk tagihan 27.600 rupiah
        $response = $this->actingAs($this->customer)->postJson('/api/v1/payments', [
            'bill_id' => $bill->id,
            'amount' => 500.00, // Nominal tidak cocok dengan total tagihan
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
            'proof_photo' => $file,
        ]);

        // Temuan Audit: Saat ini request lolos (HTTP 201) karena aturan validasi hanya min:0
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('payments', [
            'bill_id' => $bill->id,
            'amount' => 500.00,
        ]);
    }

    /**
     * Test 7 (Audit Finding - Medium Risk): Pengajuan bukti pembayaran ganda saat pembayaran sebelumnya masih PENDING.
     */
    public function test_multiple_pending_payments_behavior_audit(): void
    {
        Storage::fake('public');

        $reading = MeterReading::create([
            'report_number' => 'RPT-MULTI-01',
            'user_id' => $this->customer->id,
            'customer_name' => $this->customer->name,
            'meter_number' => 'MTR-001',
            'reading' => 110.0,
            'previous_reading' => 100.0,
            'consumption' => 10.0,
            'status' => 'Terverifikasi',
        ]);

        $bill = app(BillingService::class)->generateBillFromReading($reading);
        $file1 = UploadedFile::fake()->create('struk_1.jpg', 150, 'image/jpeg');
        $file2 = UploadedFile::fake()->create('struk_2.jpg', 150, 'image/jpeg');

        // Pengajuan pertama
        app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file1);

        // Pengajuan kedua saat pengajuan pertama masih PENDING
        app(PaymentService::class)->submitManualPayment($bill, $this->customer->id, [
            'amount' => $bill->total_amount,
            'payment_method' => 'manual_transfer',
            'sender_name' => 'Budi Santoso',
        ], $file2);

        // Temuan Audit: Dua record pembayaran berstatus PENDING terbentuk untuk tagihan yang sama
        $pendingCount = Payment::where('bill_id', $bill->id)->where('status', 'PENDING')->count();
        $this->assertEquals(2, $pendingCount);
    }

    /**
     * Test 8 (Audit Finding - High Risk): Tabel tariffs saat ini belum memiliki penanda is_simulation atau status persetujuan resmi.
     */
    public function test_tariff_simulation_flag_audit(): void
    {
        // Temuan Audit: Model Tariff belum memiliki atribut 'is_simulation' atau 'is_approved'
        $attributes = array_keys($this->tariff->getAttributes());
        $this->assertNotContains('is_simulation', $attributes);
        $this->assertNotContains('is_approved', $attributes);
    }
}
