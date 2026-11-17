<?php

namespace Database\Seeders;

use App\Models\CustomerProfile;
use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('name', 'admin')->first();
        $staffRole = Role::where('name', 'staff')->first();
        $customerRole = Role::where('name', 'customer')->first();

        // 1. Akun Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@tirtasago.id'],
            [
                'name' => 'Ir. Hendra Pratama, S.T.',
                'password' => Hash::make('Admin123!'),
            ]
        );
        if ($adminRole && !$admin->roles()->where('role_id', $adminRole->id)->exists()) {
            $admin->roles()->attach($adminRole);
        }
        StaffProfile::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'nip' => 'NIP-TS-198402-089',
                'department' => 'Divisi Transmisi, Distribusi & SCADA',
                'position' => 'Super Admin & Dispatcher Teknis',
                'phone_dinas' => '0811-6677-8899',
            ]
        );

        // 2. Akun Petugas Lapangan & Operator SCADA
        $staff = User::firstOrCreate(
            ['email' => 'ahmad.pratama@tirtasago.id'],
            [
                'name' => 'Ahmad Pratama, S.T.',
                'password' => Hash::make('Petugas123!'),
            ]
        );
        if ($staffRole && !$staff->roles()->where('role_id', $staffRole->id)->exists()) {
            $staff->roles()->attach($staffRole);
        }
        StaffProfile::firstOrCreate(
            ['user_id' => $staff->id],
            [
                'nip' => 'NIP-198804-004',
                'department' => 'Seksi Pengolahan Air & SCADA',
                'position' => 'Operator SCADA & Verifikator AI-OCR',
                'phone_dinas' => '0811-6677-8800',
            ]
        );

        // 3. Akun Pelanggan Standar (Budi Santoso)
        $customer = User::firstOrCreate(
            ['email' => 'budi.santoso@warga.payakumbuh.go.id'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('Pelanggan123!'),
            ]
        );
        if ($customerRole && !$customer->roles()->where('role_id', $customerRole->id)->exists()) {
            $customer->roles()->attach($customerRole);
        }
        CustomerProfile::firstOrCreate(
            ['user_id' => $customer->id],
            [
                'customer_id' => '1234567890',
                'meter_number' => '01000',
                'tariff_category' => '2A - Rumah Tangga Mandiri',
                'phone' => '0812-7389-4902',
                'address' => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo, Kec. Payakumbuh Barat, Kota Payakumbuh',
                'district' => 'Payakumbuh Barat',
                'zone' => 'DIST-A Sektor Utara',
                'last_meter_value' => 981.00,
                'current_meter_value' => 1000.00,
                'wallet_balance' => 2500000.00,
                'points' => 380,
            ]
        );

        // 4. Akun Pelanggan Demo 2 (Ibu Siti Nurbaya)
        $customer2 = User::firstOrCreate(
            ['email' => 'siti.nurbaya@payakumbuh.community'],
            [
                'name' => 'Ibu Siti Nurbaya',
                'password' => Hash::make('Pelanggan123!'),
            ]
        );
        if ($customerRole && !$customer2->roles()->where('role_id', $customerRole->id)->exists()) {
            $customer2->roles()->attach($customerRole);
        }
        CustomerProfile::firstOrCreate(
            ['user_id' => $customer2->id],
            [
                'customer_id' => '1234567891',
                'meter_number' => '01842',
                'tariff_category' => '2A - Rumah Tangga Mandiri',
                'phone' => '0852-6389-1122',
                'address' => 'Jl. Soekarno-Hatta No. 88, Kel. Tiakar, Kec. Payakumbuh Timur, Kota Payakumbuh',
                'district' => 'Payakumbuh Timur',
                'zone' => 'DIST-B Sektor Timur',
                'last_meter_value' => 1420.00,
                'current_meter_value' => 1435.00,
                'wallet_balance' => 0.00,
                'points' => 120,
            ]
        );
    }
}
