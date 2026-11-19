<?php

namespace Tests\Unit\Resources;

use App\Http\Resources\ChallengeMissionResource;
use App\Http\Resources\ChallengeResource;
use App\Http\Resources\ComplaintHistoryResource;
use App\Http\Resources\ComplaintResource;
use App\Http\Resources\CourseResource;
use App\Http\Resources\CustomerProfileResource;
use App\Http\Resources\LessonResource;
use App\Http\Resources\MeterReadingResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\UserProfileResource;
use App\Models\Challenge;
use App\Models\ChallengeMission;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\ComplaintHistory;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ApiResourceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    /**
     * Test ComplaintResource transforms Complaint model with all required keys and histories.
     */
    public function test_complaint_resource_transforms_correctly(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        Auth::login($user);
        $cat = ComplaintCategory::first();

        $complaint = Complaint::create([
            'user_id'          => $user->id,
            'category_id'      => $cat->id,
            'ticket_number'    => 'TK-TEST-2026-0001',
            'title'            => 'Pipa Pecah Depan Gerbang',
            'description'      => 'Air menyembur deras di jalan aspal.',
            'district'         => 'Payakumbuh Barat',
            'address'          => 'Jl. Pahlawan No. 10',
            'reporter_name'    => 'Budi Santoso',
            'contact_phone'    => '081234567890',
            'status'           => 'BARU',
            'priority'         => 'TINGGI',
        ]);

        ComplaintHistory::create([
            'complaint_id'  => $complaint->id,
            'actor_name'    => 'Budi Santoso',
            'actor_user_id' => $user->id,
            'status'        => 'BARU',
            'note'          => 'Pengaduan pertama dilaporkan oleh warga.',
        ]);

        $resource = new ComplaintResource($complaint);
        $data = $resource->resolve();

        $this->assertEquals('TK-TEST-2026-0001', $data['id']);
        $this->assertEquals('TK-TEST-2026-0001', $data['ticketId']);
        $this->assertEquals($complaint->id, $data['ticket_id']);
        $this->assertEquals('Pipa Pecah Depan Gerbang', $data['title']);
        $this->assertEquals('BARU', $data['status']);
        $this->assertEquals('Payakumbuh Barat', $data['district']);
        $this->assertEquals('Jl. Pahlawan No. 10', $data['address']);
        $this->assertStringContainsString('Payakumbuh Barat - Jl. Pahlawan No. 10', $data['location']);
        $this->assertEquals('Budi Santoso', $data['userName']);
        $this->assertEquals('081234567890', $data['userPhone']);
        $this->assertIsArray($data['updates']);
        $this->assertNotEmpty($data['updates']);
        $this->assertEquals('BARU', $data['updates'][0]['status']);
        $this->assertEquals('Budi Santoso', $data['updates'][0]['actor']);

        // Uji Proteksi PII: Masking otomatis saat diakses oleh publik / pengunjung umum
        Auth::logout();
        $publicData = (new ComplaintResource($complaint))->resolve();
        $this->assertNotEquals('Budi Santoso', $publicData['userName']);
        $this->assertStringContainsString('*', $publicData['userName']);
        $this->assertStringContainsString('****', $publicData['userPhone']);
        $this->assertStringContainsString('Disamarkan', $publicData['address']);
    }

    /**
     * Test MeterReadingResource transforms model with status mapping and numeric casts.
     */
    public function test_meter_reading_resource_transforms_correctly(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $reading = MeterReading::create([
            'user_id'          => $user->id,
            'meter_number'     => '01000',
            'report_number'    => 'REP-TEST-0001',
            'customer_name'    => 'Budi Santoso',
            'zone'             => 'DIST-A Sektor Utara',
            'previous_reading' => 1000.0,
            'reading'          => 1035.0,
            'consumption'      => 35.0,
            'estimated_cost'   => 275600,
            'status'           => 'Menunggu Validasi',
            'notes'            => 'Foto angka odometer jelas.',
        ]);

        $resource = new MeterReadingResource($reading);
        $data = $resource->resolve();

        $this->assertEquals('REP-TEST-0001', $data['id']);
        $this->assertEquals('REP-TEST-0001', $data['report_number']);
        $this->assertEquals($reading->id, $data['reportId']);
        $this->assertEquals('01000', $data['meterNumber']);
        $this->assertEquals('Budi Santoso', $data['customerName']);
        $this->assertSame(1035.0, $data['ocrReading']);
        $this->assertSame(1000.0, $data['previousReading']);
        $this->assertSame(35.0, $data['consumption']);
        $this->assertSame(275600, $data['estimatedCost']);
        $this->assertEquals('PENDING', $data['badgeStatus']);
        $this->assertEquals('amber', $data['badgeColor']);
        $this->assertNotNull($data['submittedAt']);
    }

    /**
     * Test UserProfileResource formats profile and NEVER exposes password or sensitive tokens.
     */
    public function test_user_profile_resource_never_exposes_passwords(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $resource = new UserProfileResource($user);
        $data = $resource->resolve();

        $this->assertEquals((string)$user->id, $data['id']);
        $this->assertEquals($user->name, $data['name']);
        $this->assertEquals($user->name, $data['fullName']);
        $this->assertEquals($user->email, $data['email']);
        $this->assertEquals('customer', $data['role']);
        $this->assertFalse($data['isAdmin']);
        $this->assertEquals('01000', $data['meterNumber']);

        // Assert sensitive security keys are never present
        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('remember_token', $data);
        $this->assertArrayNotHasKey('two_factor_secret', $data);
    }

    /**
     * Test CourseResource and LessonResource serialization.
     */
    public function test_course_and_lesson_resources_transform_correctly(): void
    {
        $course = Course::with('lessons')->first();
        $this->assertNotNull($course);

        $resource = new CourseResource($course);
        $data = $resource->resolve();

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($course->course_code, $data['id']);
        $this->assertEquals($course->title, $data['title']);
        $this->assertIsArray($data['lessons']);
        $this->assertNotEmpty($data['lessons']);

        $firstLesson = $data['lessons'][0];
        $this->assertArrayHasKey('id', $firstLesson);
        $this->assertArrayHasKey('title', $firstLesson);
        $this->assertArrayHasKey('duration', $firstLesson);
        $this->assertArrayHasKey('order', $firstLesson);
    }

    /**
     * Test ChallengeResource and ChallengeMissionResource serialization.
     */
    public function test_challenge_and_mission_resources_transform_correctly(): void
    {
        $challenge = Challenge::with('missions')->first();
        $this->assertNotNull($challenge);

        $resource = new ChallengeResource($challenge);
        $data = $resource->resolve();

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($challenge->challenge_code, $data['id']);
        $this->assertEquals($challenge->title, $data['title']);
        $this->assertEquals((int)$challenge->duration_days, $data['durationDays']);
        $this->assertEquals((int)$challenge->xp_total, $data['totalXp']);
        $this->assertIsArray($data['missions']);
        $this->assertNotEmpty($data['missions']);

        $firstMission = $data['missions'][0];
        $this->assertArrayHasKey('id', $firstMission);
        $this->assertArrayHasKey('title', $firstMission);
        $this->assertArrayHasKey('xp', $firstMission);
        $this->assertArrayHasKey('order', $firstMission);
    }

    /**
     * Test NotificationResource serialization.
     */
    public function test_notification_resource_transforms_correctly(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $notif = Notification::create([
            'user_id' => $user->id,
            'title'   => 'Tagihan Rekening Terbit',
            'desc'    => 'Tagihan bulan Mei telah siap dicek.',
            'type'    => 'BILL',
            'link'    => '/bill',
            'is_read' => false,
        ]);

        $resource = new NotificationResource($notif);
        $data = $resource->resolve();

        $this->assertEquals($notif->id, $data['id']);
        $this->assertEquals('BILL', $data['type']);
        $this->assertEquals('Tagihan Rekening Terbit', $data['title']);
        $this->assertFalse($data['isRead']);
        $this->assertFalse($data['is_read']);
        $this->assertEquals('/bill', $data['link']);
        $this->assertNotNull($data['time']);
    }
}
