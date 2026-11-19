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
                'name' => 'scada_operator',
                'display_name' => 'Operator PDAM & SCADA',
                'description' => 'Operator teknis pengolahan air, telemetri reservoir, dan verifikator baca meter AI-OCR.',
            ],
            [
                'name' => 'field_officer',
                'display_name' => 'Petugas Lapangan (TRC)',
                'description' => 'Teknisi lapangan, survei sambungan baru, pencatatan fisik meteran, dan tindak lanjut aduan warga.',
            ],
            [
                'name' => 'staff',
                'display_name' => 'Petugas & Operator SCADA (Legacy)',
                'description' => 'Role transisi untuk kompatibilitas ke belakang.',
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
