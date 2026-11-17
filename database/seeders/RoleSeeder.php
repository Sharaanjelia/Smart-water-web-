<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Super Administrator',
                'description' => 'Akses penuh ke Command Center SCADA, disposisi tiket pengaduan, dan manajemen seluruh data sistem.',
            ],
            [
                'name' => 'staff',
                'display_name' => 'Petugas & Operator SCADA',
                'description' => 'Operator teknis, verifikator AI-OCR meteran warga, dan petugas tindak lanjut lapangan.',
            ],
            [
                'name' => 'customer',
                'display_name' => 'Pelanggan Warga',
                'description' => 'Warga pelanggan sambungan air Perumda Air Minum Tirta Sago Kota Payakumbuh.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['name' => $role['name']], $role);
        }
    }
}
