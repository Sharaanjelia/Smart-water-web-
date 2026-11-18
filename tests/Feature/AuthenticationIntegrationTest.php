<?php

namespace Tests\Feature;

use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected User $adminUser;
    protected User $staffUser;
    protected User $customerUser;

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
        $this->customerUser = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
    }

    protected function tearDown(): void
    {
        unset($_SESSION['swc_user'], $_SESSION['swc_role']);
        Auth::logout();
        parent::tearDown();
    }

    /**
     * 1. Login berhasil sebagai Pelanggan melalui form POST (Bcrypt Hash di MySQL)
     */
    public function test_customer_can_login_with_valid_credentials(): void
    {
        $response = $this->post(route('login.post'), [
            'identifier' => 'budi.santoso@warga.payakumbuh.go.id',
            'password'   => 'Pelanggan123!',
        ]);

        $response->assertRedirect(url('/report-meter'));
        $this->assertTrue(Auth::check());
        $this->assertSame($this->customerUser->id, Auth::id());
        $this->assertSame('customer', session('swc_role'));
    }

    /**
     * 2. Login berhasil sebagai Petugas melalui NIP dan Password dinas
     */
    public function test_staff_can_login_with_nip_and_password(): void
    {
        $nip = $this->staffUser->staffProfile->nip;

        $response = $this->post(route('admin-login.post'), [
            'identifier' => $nip,
            'password'   => 'Petugas123!',
        ]);

        $response->assertRedirect(url('/admin-dashboard'));
        $this->assertTrue(Auth::check());
        $this->assertSame($this->staffUser->id, Auth::id());
        $this->assertSame('staff', session('swc_role'));
    }

    /**
     * 3. Login gagal jika kata sandi salah (tidak cocok dengan Bcrypt di MySQL)
     */
    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->from('/login')->post(route('login.post'), [
            'identifier' => 'budi.santoso@warga.payakumbuh.go.id',
            'password'   => 'PasswordSalahTotal!',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    /**
     * 4. Login gagal jika email/NIP tidak terdaftar di database MySQL
     */
    public function test_login_fails_with_unregistered_identifier(): void
    {
        $response = $this->from('/login')->post(route('login.post'), [
            'identifier' => 'pengguna.tidak.ada@payakumbuh.go.id',
            'password'   => 'SembarangPassword123',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('error');
        $this->assertFalse(Auth::check());
    }

    /**
     * 5. Pendaftaran Pelanggan Baru menyimpan ke MySQL dengan Bcrypt, Role Customer, dan CustomerProfile
     */
    public function test_customer_registration_creates_database_record_and_profile(): void
    {
        $newEmail = 'warga.baru.' . time() . '@payakumbuh.go.id';

        $response = $this->post(route('register.post'), [
            'name'                  => 'Warga Baru Mandiri',
            'email'                 => $newEmail,
            'phone'                 => '081299887766',
            'password'              => 'Rahasia123!',
            'password_confirmation' => 'Rahasia123!',
            'agree_terms'           => '1',
        ]);

        $response->assertRedirect(url('/report-meter'));

        $createdUser = User::where('email', $newEmail)->first();
        $this->assertNotNull($createdUser);
        $this->assertSame('Warga Baru Mandiri', $createdUser->name);
        $this->assertTrue(Hash::check('Rahasia123!', $createdUser->password));

        // Verifikasi relasi role customer terpasang
        $this->assertTrue($createdUser->hasRole('customer'));

        // Verifikasi profil pelanggan otomatis dibuat
        $this->assertNotNull($createdUser->customerProfile);
        $this->assertSame('081299887766', $createdUser->customerProfile->phone);
        $this->assertSame('2A - Rumah Tangga Mandiri', $createdUser->customerProfile->tariff_category);
        $this->assertNotEmpty($createdUser->customerProfile->meter_number);

        // Verifikasi sesi login aktif
        $this->assertTrue(Auth::check());
        $this->assertSame($createdUser->id, Auth::id());
    }

    /**
     * 6. Pendaftaran gagal jika email sudah terdaftar sebelumnya
     */
    public function test_registration_fails_if_email_already_exists(): void
    {
        $response = $this->from('/daftar')->post(route('register.post'), [
            'name'                  => 'Budi Duplikat',
            'email'                 => 'budi.santoso@warga.payakumbuh.go.id',
            'phone'                 => '081299990000',
            'password'              => 'Baru123!',
            'password_confirmation' => 'Baru123!',
            'agree_terms'           => '1',
        ]);

        $response->assertRedirect('/daftar');
        $response->assertSessionHasErrors(['email']);
    }

    /**
     * 7. Logout membersihkan sesi aplikasi dan mencegah akses ke rute terproteksi
     */
    public function test_logout_clears_session_and_authenticates_out(): void
    {
        // Login terlebih dahulu
        AuthService::syncSession($this->customerUser);
        $this->assertTrue(Auth::check());

        // Lakukan logout
        $response = $this->get(route('logout'));

        $response->assertRedirect(url('/login'));
        $this->assertFalse(Auth::check());
        $this->assertNull(session('swc_user'));
        $this->assertNull($_SESSION['swc_user'] ?? null);
    }

    /**
     * 8. Pengguna tanpa autentikasi tidak dapat membuka dashboard admin (Redirect 302 ke admin-login)
     */
    public function test_unauthenticated_user_cannot_access_admin_dashboard(): void
    {
        $response = $this->get(route('admin-dashboard'));

        $response->assertRedirect(url('/admin-login'));
        $response->assertSessionHas('error');
    }

    /**
     * 9. Akun Pelanggan tidak dapat membuka area operasional Admin/Staff (Ditolak / Redirect ke /user)
     */
    public function test_customer_cannot_access_admin_dashboard(): void
    {
        AuthService::syncSession($this->customerUser);

        $response = $this->get(route('admin-dashboard'));

        $response->assertRedirect(url('/user'));
        $response->assertSessionHas('error');
    }

    /**
     * 10. Staf dan Admin yang sah dapat mengakses dashboard operasional
     */
    public function test_staff_and_admin_can_access_admin_dashboard(): void
    {
        // Akses oleh Staf
        AuthService::syncSession($this->staffUser);
        $staffResponse = $this->get(route('admin-dashboard'));
        $staffResponse->assertOk();

        // Akses oleh Admin
        AuthService::syncSession($this->adminUser);
        $adminResponse = $this->get(route('admin-dashboard'));
        $adminResponse->assertOk();
    }

    /**
     * 11. Pengunjung tamu (Guest) yang belum login tidak lagi otomatis diset sebagai demo user di /user
     */
    public function test_guest_visiting_user_profile_sees_guest_view(): void
    {
        $response = $this->get('/user');
        $response->assertOk();

        // Harus menampilkan tampilan Halaman Pribadi Pelanggan (Guest)
        $response->assertSee('Halaman Pribadi Pelanggan');
        $response->assertSee('Masuk ke Akun Anda');
        $response->assertDontSee('Masuk Demo Pelanggan');
    }

    /**
     * 12. Rute /portal-petugas tidak lagi mengizinkan direct bypass tanpa login
     */
    public function test_enter_admin_direct_redirects_unauthenticated_user_to_admin_login(): void
    {
        $response = $this->get(route('portal-petugas'));

        $response->assertRedirect(url('/admin-login'));
        $response->assertSessionHas('info');
        $this->assertFalse(Auth::check());
    }

    /**
     * 13. Pelanggan yang mengakses /portal-petugas ditolak dan diarahkan ke /user
     */
    public function test_enter_admin_direct_denies_customer_access(): void
    {
        AuthService::syncSession($this->customerUser);

        $response = $this->get(route('portal-petugas'));

        $response->assertRedirect(url('/user'));
        $response->assertSessionHas('error');
    }

    /**
     * 14. API auth.php menolak bypass Google Sign-In tanpa integrasi OAuth
     */
    public function test_google_signin_bypass_is_rejected(): void
    {
        $response = $this->postJson('/api/auth.php', [
            'action' => 'google_signin',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'ok' => false,
        ]);
        $this->assertFalse(Auth::check());
    }

    /**
     * 15. API auth.php menolak instant demo pada admin_login tanpa password yang valid
     */
    public function test_instant_demo_on_admin_login_is_rejected(): void
    {
        $response = $this->postJson('/api/auth.php', [
            'action'      => 'admin_login',
            'instantDemo' => true,
        ]);

        $response->assertStatus(422);
        $this->assertFalse(Auth::check());
    }

    /**
     * 16. Isolasi Data Pengguna: Data profil pelanggan hanya memuat milik user yang bersangkutan
     */
    public function test_user_data_isolation(): void
    {
        AuthService::syncSession($this->customerUser);

        $userData = AuthService::formatUserData($this->customerUser);
        $this->assertSame($this->customerUser->email, $userData['email']);
        $this->assertSame($this->customerUser->customerProfile->meter_number, $userData['meterNumber']);

        // Data tidak bercampur dengan admin/staff
        $this->assertNotSame($this->adminUser->email, $userData['email']);
        $this->assertSame('customer', $userData['role']);
    }
}
