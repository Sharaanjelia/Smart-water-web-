<?php

namespace App\Services;

use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    /**
     * Format Model User Eloquent via UserProfileResource (single source of truth).
     */
    public static function formatUserData(User $user): array
    {
        return (new \App\Http\Resources\UserProfileResource($user))->resolve();
    }

    /**
     * Sinkronisasi sesi login Laravel Auth & Legacy PHP Session.
     */
    public static function syncSession(User $user): array
    {
        Auth::login($user);
        $userData = self::formatUserData($user);
        $role = $userData['role'];

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $_SESSION['swc_user'] = $userData;
        $_SESSION['swc_role'] = $role;

        if (function_exists('session')) {
            session(['swc_user' => $userData, 'swc_role' => $role]);
            session()->save();
        }

        return $userData;
    }

    /**
     * Autentikasi Pengguna Pelanggan via Email atau Nomor HP.
     */
    public static function authenticateCustomer(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);
        $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);

        $user = User::where('email', $identifier)
            ->orWhereHas('customerProfile', function ($q) use ($identifier, $cleanPhone) {
                $q->where('phone', $identifier)
                  ->orWhere('customer_id', $identifier)
                  ->orWhere('meter_number', $identifier);
                if (!empty($cleanPhone) && strlen($cleanPhone) >= 9) {
                    $q->orWhereRaw("REPLACE(REPLACE(phone, '-', ''), ' ', '') = ?", [$cleanPhone]);
                }
            })
            ->first();

        if (!$user) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        return $user;
    }

    /**
     * Autentikasi Admin atau Petugas SCADA.
     */
    public static function authenticateStaff(string $identifier, string $password): ?User
    {
        $identifier = trim($identifier);
        if ($identifier === '' || $password === '') {
            return null;
        }

        $user = User::where('email', $identifier)
            ->orWhereHas('staffProfile', function ($q) use ($identifier) {
                $q->where('nip', $identifier);
            })
            ->first();

        if (!$user) {
            return null;
        }

        if (!Hash::check($password, $user->password)) {
            return null;
        }

        if (!$user->isStaff()) {
            return null;
        }

        return $user;
    }

    /**
     * Autentikasi Pengguna (Staf atau Pelanggan) dan sinkronisasikan sesi.
     */
    public static function attemptLogin(string $identifier, string $password): ?array
    {
        $user = self::authenticateStaff($identifier, $password);
        if (!$user) {
            $user = self::authenticateCustomer($identifier, $password);
        }

        if (!$user) {
            return null;
        }

        return self::syncSession($user);
    }

    /**
     * Tentukan URL pengalihan (redirect) berdasarkan role akun pengguna di database.
     */
    public static function getRedirectForUser(?User $user): string
    {
        if (!$user) {
            return url('/login');
        }

        $role = $user->primaryRoleName();

        return match ($role) {
            'admin'          => url('/admin-dashboard'),
            'scada_operator' => url('/water-monitoring'),
            'field_officer'  => url('/admin-dashboard'),
            'staff'          => url('/admin-dashboard'),
            default          => url('/user'),
        };
    }

    /**
     * Bersihkan seluruh sesi login aplikasi (Laravel Auth & PHP Session).
     */
    public static function performLogout(): void
    {
        Auth::logout();

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        unset($_SESSION['swc_user'], $_SESSION['swc_role'], $_SESSION['swc_academy_progress'], $_SESSION['swc_challenge_progress']);

        if (function_exists('session')) {
            session()->forget(['swc_user', 'swc_role']);
            session()->flush();
            if (function_exists('request') && request()->hasSession()) {
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            }
            session()->save();
        }
    }

    /**
     * Registrasi Akun Pelanggan Baru ke Database MySQL.
     */
    public static function registerCustomer(array $data): User
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $customerRole = Role::firstOrCreate(['name' => 'customer'], [
                'display_name' => 'Pelanggan Warga',
                'description'  => 'Warga pengguna air PDAM Tirta Sago',
            ]);
            $user->roles()->syncWithoutDetaching([$customerRole->id]);

            $randomCustId = (string)random_int(1000000000, 9999999999);
            $randomMeterNo = '01' . str_pad((string)random_int(100, 999), 3, '0', STR_PAD_LEFT);

            CustomerProfile::create([
                'user_id'             => $user->id,
                'customer_id'         => $randomCustId,
                'meter_number'        => $randomMeterNo,
                'tariff_category'     => '2A - Rumah Tangga Mandiri',
                'phone'               => $data['phone'] ?? null,
                'address'             => $data['address'] ?? 'Kota Payakumbuh',
                'district'            => $data['district'] ?? 'Payakumbuh Barat',
                'zone'                => 'DIST-A Sektor Utara',
                'last_meter_value'    => 0,
                'current_meter_value' => 0,
                'wallet_balance'      => 0,
                'points'              => 50, // Bonus XP pendaftaran
            ]);

            app(NotificationService::class)->send(
                $user->id,
                'SYSTEM',
                'Selamat Datang di Smart Water Kota Payakumbuh!',
                'Akun Anda berhasil diaktivasi. Anda memperoleh 50 poin sambutan untuk program hemat air.',
                '/user'
            );

            return $user;
        });
    }
}
