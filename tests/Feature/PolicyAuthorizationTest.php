<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeSubmission;
use App\Models\Complaint;
use App\Models\ComplaintCategory;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\Notification;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use App\Models\UserLessonProgress;
use App\Models\UserQuizResult;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PolicyAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $adminUser;
    protected User $staffA;
    protected User $staffB;
    protected User $customerA;
    protected User $customerB;
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
        $this->staffA    = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
        $this->customerA = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
        $this->customerB = User::where('email', 'siti.nurbaya@payakumbuh.community')->first();
        $this->defaultCategory = ComplaintCategory::first();

        // Buat Petugas Lapangan Kedua (Staff B) untuk menguji batasan kewenangan antar-petugas
        $staffRole = Role::where('name', 'staff')->first();
        $this->staffB = User::firstOrCreate(
            ['email' => 'dedi.teknisi@tirtasago.id'],
            [
                'name' => 'Dedi Kurniawan, A.Md.',
                'password' => Hash::make('Petugas123!'),
            ]
        );
        if ($staffRole && !$this->staffB->roles()->where('role_id', $staffRole->id)->exists()) {
            $this->staffB->roles()->attach($staffRole);
        }
        StaffProfile::firstOrCreate(
            ['user_id' => $this->staffB->id],
            [
                'nip' => 'NIP-TS-199205-012',
                'department' => 'Tim Reaksi Cepat (TRC) Lapangan',
                'position' => 'Teknisi Lapangan Wilayah Barat',
                'phone_dinas' => '0811-7788-9900',
            ]
        );
    }

    protected function tearDown(): void
    {
        unset($_SESSION['swc_user'], $_SESSION['swc_role']);
        Auth::logout();
        parent::tearDown();
    }

    protected function createComplaintAssignedTo(User $staff, ?User $reporter = null): Complaint
    {
        return Complaint::create([
            'user_id'          => $reporter?->id,
            'category_id'      => $this->defaultCategory->id,
            'ticket_number'    => 'TK-POL-' . random_int(1000, 9999),
            'title'            => 'Pipa Bocor Jl. Veteran',
            'description'      => 'Kebocoran air bawah aspal.',
            'district'         => 'Payakumbuh Barat',
            'address'          => 'Jl. Veteran No. 15',
            'reporter_name'    => $reporter?->name ?? 'Warga Kota',
            'contact_phone'    => '0812-1111-2222',
            'status'           => 'DIPROSES',
            'priority'         => 'TINGGI',
            'assigned_to'      => $staff->id,
            'assigned_at'      => now(),
        ]);
    }

    protected function createUnassignedComplaint(?User $reporter = null): Complaint
    {
        return Complaint::create([
            'user_id'          => $reporter?->id,
            'category_id'      => $this->defaultCategory->id,
            'ticket_number'    => 'TK-UNASSIGNED-' . random_int(1000, 9999),
            'title'            => 'Air Keruh Simpang Napar',
            'description'      => 'Air keluar berwarna kecokelatan.',
            'district'         => 'Payakumbuh Utara',
            'address'          => 'Jl. Napar No. 8',
            'reporter_name'    => $reporter?->name ?? 'Warga Napar',
            'contact_phone'    => '0812-3333-4444',
            'status'           => 'BARU',
            'priority'         => 'NORMAL',
            'assigned_to'      => null,
        ]);
    }

    // =========================================================================
    // 1. PENGUJIAN BATASAN KEWENANGAN ANTAR-PETUGAS (STAFF ISOLATION BOUNDARIES)
    // =========================================================================

    public function test_staff_a_cannot_update_status_of_complaint_assigned_to_staff_b(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);

        $response = $this->actingAs($this->staffA)->postJson("/api/v1/complaints/{$complaint->id}/status", [
            'status' => 'MENUNGGU TINDAK LANJUT',
            'note'   => 'Staf A mencoba intervensi tiket Staf B',
        ]);

        $response->assertStatus(403);
        $response->assertJson([
            'ok'    => false,
            'error' => 'Akses ditolak: Anda tidak memiliki wewenang memperbarui tiket yang ditugaskan kepada petugas lain.',
        ]);

        // Verifikasi Integritas Database: Status tiket tetap DIPROSES (Database Immutability)
        $this->assertEquals('DIPROSES', $complaint->fresh()->status);
    }

    public function test_staff_a_cannot_respond_to_complaint_assigned_to_staff_b(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);
        $initialHistoryCount = $complaint->histories()->count();

        $response = $this->actingAs($this->staffA)->postJson("/api/v1/complaints/{$complaint->id}/response", [
            'note' => 'Tanggapan ilegal dari staf yang tidak ditugaskan.',
        ]);

        $response->assertStatus(403);

        // Verifikasi Integritas Database: Tidak ada catatan riwayat baru yang tersimpan
        $this->assertEquals($initialHistoryCount, $complaint->fresh()->histories()->count());
    }

    public function test_staff_a_cannot_resolve_complaint_assigned_to_staff_b(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);

        $response = $this->actingAs($this->staffA)->postJson("/api/v1/complaints/{$complaint->id}/resolve", [
            'status'           => 'SELESAI',
            'resolution_notes' => 'Penyelesaian sepihak oleh staf lain.',
        ]);

        $response->assertStatus(403);

        // Verifikasi Integritas Database: Tiket belum selesai dan tidak memiliki resolved_at
        $fresh = $complaint->fresh();
        $this->assertEquals('DIPROSES', $fresh->status);
        $this->assertNull($fresh->resolved_at);
    }

    public function test_staff_a_cannot_reassign_complaint_assigned_to_staff_b(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);

        $response = $this->actingAs($this->staffA)->postJson("/api/v1/complaints/{$complaint->id}/assign", [
            'staff_id' => $this->staffA->id,
            'note'     => 'Mencoba membajak tiket rekan kerja.',
        ]);

        $response->assertStatus(403);

        // Verifikasi Integritas Database: assigned_to tetap milik Staff B
        $this->assertEquals($this->staffB->id, $complaint->fresh()->assigned_to);
    }

    public function test_assigned_staff_can_update_respond_and_resolve_own_ticket(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);

        // 1. Update Status oleh Petugas yang Ditugaskan (Staff B)
        $resUpdate = $this->actingAs($this->staffB)->postJson("/api/v1/complaints/{$complaint->id}/status", [
            'status' => 'MENUNGGU TINDAK LANJUT',
            'note'   => 'Menunggu suku cadang katup pipa.',
        ]);
        $resUpdate->assertStatus(200);
        $this->assertEquals('MENUNGGU TINDAK LANJUT', $complaint->fresh()->status);

        // 2. Add Response oleh Petugas yang Ditugaskan
        $resResp = $this->actingAs($this->staffB)->postJson("/api/v1/complaints/{$complaint->id}/response", [
            'note' => 'Material katup sudah tiba di lokasi.',
        ]);
        $resResp->assertStatus(200);

        // 3. Resolve oleh Petugas yang Ditugaskan
        $resResolve = $this->actingAs($this->staffB)->postJson("/api/v1/complaints/{$complaint->id}/resolve", [
            'status'           => 'SELESAI',
            'resolution_notes' => 'Pipa tuntas diperbaiki dan aliran air kembali normal.',
        ]);
        $resResolve->assertStatus(200);
        $this->assertEquals('SELESAI', $complaint->fresh()->status);
        $this->assertNotNull($complaint->fresh()->resolved_at);
    }

    public function test_staff_can_claim_and_update_unassigned_complaint(): void
    {
        $complaint = $this->createUnassignedComplaint($this->customerA);

        // Staf A berhak mengklaim tiket yang belum ditugaskan
        $resAssign = $this->actingAs($this->staffA)->postJson("/api/v1/complaints/{$complaint->id}/assign", [
            'staff_id' => $this->staffA->id,
            'note'     => 'Staf A mengklaim penanganan tiket ini.',
        ]);

        $resAssign->assertStatus(200);
        $this->assertEquals($this->staffA->id, $complaint->fresh()->assigned_to);
    }

    public function test_admin_has_full_authority_to_manage_any_ticket(): void
    {
        $complaint = $this->createComplaintAssignedTo($this->staffB, $this->customerA);

        // Admin dapat menugaskan ulang tiket dari Staff B ke Staff A
        $resReassign = $this->actingAs($this->adminUser)->postJson("/api/v1/complaints/{$complaint->id}/assign", [
            'staff_id' => $this->staffA->id,
            'note'     => 'Disposisi ulang oleh pimpinan teknis ke Staf A.',
        ]);
        $resReassign->assertStatus(200);
        $this->assertEquals($this->staffA->id, $complaint->fresh()->assigned_to);

        // Admin dapat menyelesaikan tiket langsung
        $resResolve = $this->actingAs($this->adminUser)->postJson("/api/v1/complaints/{$complaint->id}/resolve", [
            'status'           => 'SELESAI',
            'resolution_notes' => 'Selesai di bawah supervisi langsung Admin SCADA.',
        ]);
        $resResolve->assertStatus(200);
        $this->assertEquals('SELESAI', $complaint->fresh()->status);
    }

    // =========================================================================
    // 2. PENGUJIAN ANTI-IDOR & DATA OWNERSHIP PELANGGAN
    // =========================================================================

    public function test_customer_cannot_view_private_ticket_of_another_customer(): void
    {
        $privateTicket = $this->createUnassignedComplaint($this->customerA);

        $response = $this->actingAs($this->customerB)->getJson("/api/v1/complaints/{$privateTicket->id}");

        $response->assertStatus(403);
        $response->assertJson([
            'ok'    => false,
            'error' => 'Akses ditolak: Anda tidak memiliki wewenang melihat tiket milik pengguna lain.',
        ]);
    }

    public function test_anyone_can_view_anonymous_complaint_ticket(): void
    {
        $anonymousTicket = $this->createUnassignedComplaint(null);

        // Tamu tanpa login dapat melihat tiket publik/anonim
        $resGuest = $this->getJson("/api/v1/complaints/{$anonymousTicket->id}");
        $resGuest->assertStatus(200);
        $resGuest->assertJsonPath('complaint.ticketId', $anonymousTicket->ticket_number);

        // Pelanggan mana pun dapat melihat tiket anonim
        $resCust = $this->actingAs($this->customerB)->getJson("/api/v1/complaints/{$anonymousTicket->id}");
        $resCust->assertStatus(200);
    }

    public function test_customer_cannot_validate_meter_reading_via_policy(): void
    {
        $reading = MeterReading::create([
            'user_id'          => $this->customerA->id,
            'meter_number'     => '01000',
            'report_number'    => 'REP-POL-' . random_int(1000, 9999),
            'customer_name'    => $this->customerA->name,
            'previous_reading' => 100.0,
            'reading'          => 120.0,
            'status'           => 'Menunggu Validasi',
        ]);

        $response = $this->actingAs($this->customerA)->postJson('/api/v1/meter-reports/validate', [
            'id'     => $reading->id,
            'action' => 'APPROVE',
        ]);

        $response->assertStatus(403);
        $this->assertEquals('Menunggu Validasi', $reading->fresh()->status);
    }

    // =========================================================================
    // 3. PENCEGAHAN ESKALASI HAK AKSES PADA RUTE DEMO KEDINASAN
    // =========================================================================

    public function test_authenticated_customer_visiting_portal_petugas_is_rejected_from_escalation(): void
    {
        // Login sebagai pelanggan
        $response = $this->actingAs($this->customerA)
            ->withSession(['swc_user' => ['id' => $this->customerA->id], 'swc_role' => 'customer'])
            ->get('/portal-petugas');

        // Harus diarahkan kembali ke /user dan TIDAK boleh dieskalasi ke admin-dashboard
        $response->assertRedirect(url('/user'));
        $response->assertSessionHas('error', 'Akses ditolak: Akun pelanggan tidak diizinkan masuk ke Portal Petugas SCADA.');

        // Pastikan sesi tetap customer, bukan staff/admin
        $this->assertNotEquals('admin', session('swc_role'));
    }

    public function test_guest_without_session_is_redirected_to_admin_login(): void
    {
        $response = $this->get('/portal-petugas');

        // Pengunjung tanpa sesi diarahkan ke halaman login petugas
        $response->assertRedirect(url('/admin-login'));
        $response->assertSessionHas('info');
    }

    // =========================================================================
    // 4. VERIFIKASI LANGSUNG LARAVEL POLICIES (GATE INTEGRITY)
    // =========================================================================

    public function test_customer_profile_policy_enforcement(): void
    {
        $profileA = $this->customerA->customerProfile;
        $profileB = $this->customerB->customerProfile;

        // Customer A dapat mengupdate profilnya sendiri
        $this->assertTrue(Gate::forUser($this->customerA)->allows('update', $profileA));

        // Customer A DILARANG mengupdate profil Customer B
        $this->assertFalse(Gate::forUser($this->customerA)->allows('update', $profileB));

        // Admin berhak supervisi profil siapa pun
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('update', $profileA));
    }

    public function test_notification_policy_enforcement(): void
    {
        $notifA = Notification::create([
            'user_id' => $this->customerA->id,
            'type'    => 'SYSTEM',
            'title'   => 'Pemberitahuan Tagihan',
            'desc'    => 'Tagihan air telah terbit.',
            'is_read' => false,
        ]);

        // Customer A berhak melihat notifikasi miliknya
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $notifA));

        // Customer B DILARANG melihat notifikasi Customer A
        $this->assertFalse(Gate::forUser($this->customerB)->allows('view', $notifA));
    }

    public function test_academy_progress_policy_enforcement(): void
    {
        $progressA = UserLessonProgress::create([
            'user_id'      => $this->customerA->id,
            'lesson_id'    => 1,
            'is_completed' => true,
        ]);

        $quizA = UserQuizResult::create([
            'user_id'      => $this->customerA->id,
            'course_id'    => 1,
            'score'        => 95,
            'xp_earned'    => 100,
            'is_passed'    => true,
            'completed_at' => now(),
        ]);

        // Customer A berhak melihat progres dan hasil kuis miliknya sendiri
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $progressA));
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $quizA));

        // Customer B DILARANG melihat progres maupun hasil kuis Customer A
        $this->assertFalse(Gate::forUser($this->customerB)->allows('view', $progressA));
        $this->assertFalse(Gate::forUser($this->customerB)->allows('view', $quizA));

        // Admin berhak supervisi progres & kuis warga
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('view', $progressA));
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('view', $quizA));
    }

    public function test_challenge_participant_and_submission_policy_enforcement(): void
    {
        $challenge = Challenge::first();

        $participantA = ChallengeParticipant::create([
            'user_id'      => $this->customerA->id,
            'challenge_id' => $challenge->id,
            'status'       => 'IN_PROGRESS',
        ]);

        $submissionA = ChallengeSubmission::create([
            'participant_id' => $participantA->id,
            'mission_id'     => 1,
            'is_completed'   => true,
            'proof_notes'    => 'Mencuci dengan ember penampung',
        ]);

        // Customer A berhak melihat dan memperbarui partisipasi & submisi miliknya
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $participantA));
        $this->assertTrue(Gate::forUser($this->customerA)->allows('view', $submissionA));
        $this->assertTrue(Gate::forUser($this->customerA)->allows('update', $submissionA));

        // Customer B DILARANG melihat atau memperbarui partisipasi & submisi Customer A
        $this->assertFalse(Gate::forUser($this->customerB)->allows('view', $participantA));
        $this->assertFalse(Gate::forUser($this->customerB)->allows('view', $submissionA));
        $this->assertFalse(Gate::forUser($this->customerB)->allows('update', $submissionA));

        // Admin berhak supervisi
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('view', $participantA));
        $this->assertTrue(Gate::forUser($this->adminUser)->allows('view', $submissionA));
    }
}
