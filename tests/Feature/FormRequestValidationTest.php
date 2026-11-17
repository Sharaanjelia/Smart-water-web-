<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test 1: LoginRequest menolak request dengan identitas & password kosong (HTTP 422).
     */
    public function test_login_validation_rejects_empty_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'identifier' => '',
            'password'   => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
        $this->assertNotEmpty($response->json('error'));
    }

    /**
     * Test 2: RegisterCustomerRequest menolak email tidak valid & password kurang dari 6 karakter (HTTP 422).
     */
    public function test_register_validation_rejects_invalid_email_and_short_password(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'     => 'Warga Uji',
            'email'    => 'bukan-email-valid',
            'password' => '123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 3: StoreComplaintRequest menolak pengaduan dengan field wajib kosong (HTTP 422).
     */
    public function test_complaint_validation_rejects_empty_required_fields(): void
    {
        $response = $this->postJson('/api/v1/complaints', [
            'title'       => '',
            'description' => '',
            'district'    => '',
            'address'     => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 4: StoreComplaintRequest menolak gambar base64 yang korup / tidak valid (HTTP 422).
     */
    public function test_complaint_validation_rejects_corrupted_base64_image(): void
    {
        $response = $this->postJson('/api/v1/complaints', [
            'title'       => 'Pipa Pecah Uji Validasi',
            'description' => 'Deskripsi laporan lengkap lebih dari delapan karakter.',
            'district'    => 'Payakumbuh Barat',
            'address'     => 'Jl. Sudirman No. 12',
            'imageUrl'    => 'data:image/xyz;base64,!!!bukan_base64_valid!!!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 5: StoreMeterReadingRequest menolak pembacaan non-numerik atau bernilai negatif (HTTP 422).
     */
    public function test_meter_reading_validation_rejects_negative_or_invalid_reading(): void
    {
        $response = $this->postJson('/api/v1/meter-reports', [
            'meter'      => '01000',
            'ocrReading' => -50.5,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);

        $responseStr = $this->postJson('/api/v1/meter-reports', [
            'meter'      => '01000',
            'ocrReading' => 'bukan_angka',
        ]);

        $responseStr->assertStatus(422);
    }

    /**
     * Test 6: ValidateMeterReadingRequest memblokir akses pelanggan tanpa hak petugas (HTTP 403).
     */
    public function test_meter_validation_rejects_unauthorized_customer_access(): void
    {
        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $response = $this->actingAs($customer)->postJson('/api/v1/meter-reports/validate', [
            'id'     => 'REP-04-1234',
            'action' => 'APPROVE',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 7: CompleteLessonRequest menolak jika ID materi (lessonId) kosong (HTTP 422).
     */
    public function test_academy_validation_rejects_missing_lesson_id(): void
    {
        $response = $this->postJson('/api/v1/academy/complete-lesson', [
            'lessonId' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 8: CompleteQuizRequest menolak skor kuis di luar rentang 0-100 (HTTP 422).
     */
    public function test_quiz_validation_rejects_invalid_score_range(): void
    {
        $response = $this->postJson('/api/v1/academy/complete-quiz', [
            'courseId' => 'course-1',
            'score'    => 150,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('ok', false);
    }

    /**
     * Test 9: AssignComplaintRequest memblokir pelanggan yang mencoba menugaskan teknisi (HTTP 403).
     */
    public function test_assign_complaint_rejects_unauthorized_customer(): void
    {
        $customer = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $response = $this->actingAs($customer)->postJson('/api/v1/complaints/1/assign', [
            'staff_id' => 2,
            'note'     => 'Mencoba menugaskan sendiri',
        ]);

        $response->assertStatus(403);
        $response->assertJsonPath('ok', false);
    }
}
