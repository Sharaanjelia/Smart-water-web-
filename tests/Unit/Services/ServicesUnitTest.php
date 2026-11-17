<?php

namespace Tests\Unit\Services;

use App\Models\Challenge;
use App\Models\ChallengeMission;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeSubmission;
use App\Models\Complaint;
use App\Models\Course;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\User;
use App\Services\AcademyService;
use App\Services\ChallengeService;
use App\Services\ComplaintService;
use App\Services\MeterReadingService;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServicesUnitTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected NotificationService $notificationService;
    protected ComplaintService $complaintService;
    protected MeterReadingService $meterReadingService;
    protected AcademyService $academyService;
    protected ChallengeService $challengeService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = app(NotificationService::class);
        $this->complaintService    = app(ComplaintService::class);
        $this->meterReadingService = app(MeterReadingService::class);
        $this->academyService      = app(AcademyService::class);
        $this->challengeService    = app(ChallengeService::class);
    }

    /**
     * Test 1: ComplaintService membuat pengaduan dengan nomor tiket unik & history awal.
     */
    public function test_complaint_service_creates_ticket_with_unique_number_and_history(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $complaint = $this->complaintService->createComplaint([
            'title'       => 'Pipa Pecah Depan Pasar Ibuh',
            'description' => 'Genangan air deras meluber ke trotoar jalan pasar.',
            'district'    => 'Payakumbuh Barat',
            'address'     => 'Jl. Ahmad Yani No. 15',
            'userPhone'   => '0812-9988-7766',
            'category'    => 'pipa_bocor',
        ], $user);

        $this->assertNotNull($complaint->id);
        $this->assertStringStartsWith('TK-PYK-', $complaint->ticket_number);
        $this->assertEquals('BARU', $complaint->status);
        $this->assertEquals($user->id, $complaint->user_id);

        $this->assertDatabaseHas('complaint_histories', [
            'complaint_id' => $complaint->id,
            'status'       => 'BARU',
        ]);
    }

    /**
     * Test 2: ComplaintService penugasan teknisi dan penyelesaian tuntas.
     */
    public function test_complaint_service_assign_and_resolve_flow(): void
    {
        $user  = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        $complaint = $this->complaintService->createComplaint([
            'title'       => 'Air Keruh Sektor Timur',
            'description' => 'Air keruh berwarna kecokelatan sejak subuh tadi.',
            'district'    => 'Payakumbuh Timur',
            'address'     => 'Jl. Tiakar No. 5',
            'userPhone'   => '0812-1122-3344',
        ], $user);

        // Penugasan teknisi
        $assigned = $this->complaintService->assignTechnician(
            $complaint->id,
            $staff->id,
            'Ditugaskan ke teknisi TRC Ahmad Pratama',
            $staff
        );

        $this->assertEquals('DIPROSES', $assigned->status);
        $this->assertEquals($staff->id, $assigned->assigned_to);
        $this->assertNotNull($assigned->assigned_at);

        // Tanggapan
        $responded = $this->complaintService->addResponse(
            $complaint->id,
            'Petugas sudah tiba di lokasi dan menutup valve pipa.',
            $staff
        );
        $this->assertCount(3, $responded->histories);

        // Penyelesaian
        $resolved = $this->complaintService->resolveComplaint(
            $complaint->id,
            'Pengurasan pipa dan pembersihan sedimen telah tuntas.',
            $staff
        );

        $this->assertEquals('SELESAI', $resolved->status);
        $this->assertNotNull($resolved->resolved_at);
        $this->assertStringContainsString('Pengurasan pipa', $resolved->resolution_notes);
    }

    /**
     * Test 3: MeterReadingService menghitung konsumsi kubikasi dan estimasi biaya secara akurat.
     */
    public function test_meter_reading_service_calculates_consumption_and_cost(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $reading = $this->meterReadingService->createReading([
            'meter'       => '01000',
            'ocrReading'  => 1025.0,
            'lastReading' => 1000.0,
            'name'        => 'Budi Santoso',
            'zone'        => 'DIST-A Sektor Utara',
        ], $user);

        $this->assertEquals(25.0, $reading->consumption);
        // Biaya: (25 * 7800) + 2600 = 195000 + 2600 = 197600
        $this->assertEquals(197600, $reading->estimated_cost);
        $this->assertEquals('Menunggu Validasi', $reading->status);
    }

    /**
     * Test 4: MeterReadingService validasi petugas memperbarui CustomerProfile dan kirim notifikasi.
     */
    public function test_meter_reading_validation_updates_customer_profile(): void
    {
        $user  = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $staff = User::where('email', 'ahmad.pratama@tirtasago.id')->first();

        $reading = $this->meterReadingService->createReading([
            'meter'       => '01000',
            'ocrReading'  => 1050.0,
            'lastReading' => 1020.0,
            'name'        => 'Budi Santoso',
        ], $user);

        $approved = $this->meterReadingService->validateReading(
            $reading->report_number,
            'APPROVE',
            null,
            $staff
        );

        $this->assertEquals('Terverifikasi AI', $approved->status);
        $this->assertEquals($staff->id, $approved->validated_by);

        $user->customerProfile->refresh();
        $this->assertEquals(1050.0, (float)$user->customerProfile->current_meter_value);
        $this->assertEquals(1020.0, (float)$user->customerProfile->last_meter_value);
    }

    /**
     * Test 5: AcademyService mencegah pemberian poin XP ganda pada kuis yang dikirim berulang.
     */
    public function test_academy_service_prevents_duplicate_xp_points(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $initialPoints = $user->customerProfile->points;

        // Kirim kuis kelulusan pertama (skor 90)
        $res1 = $this->academyService->completeQuiz('course-1', 90, 100, $user);
        $this->assertTrue($res1['isPassed']);

        $user->customerProfile->refresh();
        $pointsAfterFirst = $user->customerProfile->points;
        $this->assertEquals($initialPoints + 100, $pointsAfterFirst);

        // Kirim kuis yang sama untuk kedua kalinya (skor 95)
        $res2 = $this->academyService->completeQuiz('course-1', 95, 100, $user);
        $this->assertTrue($res2['isPassed']);

        $user->customerProfile->refresh();
        $pointsAfterSecond = $user->customerProfile->points;

        // Poin TIDAK boleh bertambah lagi (harus tetap sama dengan setelah submit pertama)
        $this->assertEquals($pointsAfterFirst, $pointsAfterSecond);
    }

    /**
     * Test 6: ChallengeService mencegah klaim poin ganda pada misi yang sama.
     */
    public function test_challenge_service_prevents_duplicate_mission_points(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $initialPoints = $user->customerProfile->points;

        $challenge = Challenge::first();
        $mission = $challenge->missions()->first();

        // Pendaftaran
        $this->challengeService->enrollChallenge($challenge->challenge_code, $user);

        // Submit misi pertama kali
        $sub1 = $this->challengeService->completeMission(
            $challenge->challenge_code,
            $mission->mission_code,
            'Bukti catatan hemat air pagi ini',
            null,
            $user
        );
        $this->assertFalse($sub1['already']);

        $user->customerProfile->refresh();
        $pointsAfterFirst = $user->customerProfile->points;
        $this->assertEquals($initialPoints + $mission->xp, $pointsAfterFirst);

        // Submit misi yang sama kedua kali
        $sub2 = $this->challengeService->completeMission(
            $challenge->challenge_code,
            $mission->mission_code,
            'Mencoba klaim poin ulang',
            null,
            $user
        );
        $this->assertTrue($sub2['already']);

        $user->customerProfile->refresh();
        $pointsAfterSecond = $user->customerProfile->points;

        // Poin TIDAK boleh bertambah lagi
        $this->assertEquals($pointsAfterFirst, $pointsAfterSecond);
    }

    /**
     * Test 7: NotificationService menyimpan notifikasi dan menandai seluruhnya dibaca.
     */
    public function test_notification_service_send_and_mark_all_read(): void
    {
        $user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();

        $notif = $this->notificationService->send(
            $user->id,
            'SYSTEM',
            'Pemberitahuan Uji Coba Unit Test',
            'Deskripsi notifikasi uji coba',
            '/user'
        );

        $this->assertNotNull($notif->id);
        $this->assertFalse($notif->is_read);

        $unreadCount = $this->notificationService->getUnreadCount($user->id);
        $this->assertGreaterThanOrEqual(1, $unreadCount);

        $this->notificationService->markAllAsRead($user->id);
        $this->assertEquals(0, $this->notificationService->getUnreadCount($user->id));
    }
}
