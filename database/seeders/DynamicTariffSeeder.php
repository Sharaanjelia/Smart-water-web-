<?php

namespace Database\Seeders;

use App\Models\Tariff;
use App\Models\TariffTier;
use Illuminate\Database\Seeder;

class DynamicTariffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Note: Nominal tarif, beban tetap, dan tanggal jatuh tempo di bawah ini merupakan
     * data awal/simulasi dinamis yang dapat disesuaikan setelah ketentuan resmi PDAM diperoleh.
     */
    public function run(): void
    {
        // Golongan 2A - Rumah Tangga Mandiri (Default Customer)
        $tariff2A = Tariff::firstOrCreate(
            ['category_code' => '2A'],
            [
                'category_name' => '2A - Rumah Tangga Mandiri',
                'description' => 'Golongan pelanggan rumah tangga mandiri (provisional sample tariff)',
                'admin_fee' => 2600.00,
                'maintenance_fee' => 0.00,
                'penalty_flat' => 10000.00,
                'penalty_percentage' => 0.00,
                'due_day_of_month' => 20,
                'is_active' => true,
            ]
        );

        if ($tariff2A->tiers()->count() === 0) {
            TariffTier::create([
                'tariff_id' => $tariff2A->id,
                'tier_number' => 1,
                'tier_name' => 'Blok I (Kebutuhan Pokok)',
                'min_consumption' => 0,
                'max_consumption' => 10,
                'rate_per_m3' => 2500.00,
            ]);
            TariffTier::create([
                'tariff_id' => $tariff2A->id,
                'tier_number' => 2,
                'tier_name' => 'Blok II',
                'min_consumption' => 11,
                'max_consumption' => 20,
                'rate_per_m3' => 3200.00,
            ]);
            TariffTier::create([
                'tariff_id' => $tariff2A->id,
                'tier_number' => 3,
                'tier_name' => 'Blok III',
                'min_consumption' => 21,
                'max_consumption' => 30,
                'rate_per_m3' => 4500.00,
            ]);
            TariffTier::create([
                'tariff_id' => $tariff2A->id,
                'tier_number' => 4,
                'tier_name' => 'Blok IV (Konsumsi Tinggi)',
                'min_consumption' => 31,
                'max_consumption' => null, // Tanpa batas
                'rate_per_m3' => 6000.00,
            ]);
        }

        // Golongan 1A - Sosial
        $tariff1A = Tariff::firstOrCreate(
            ['category_code' => '1A'],
            [
                'category_name' => '1A - Sosial Khusus & Umum',
                'description' => 'Golongan pelanggan sosial dan tempat ibadah',
                'admin_fee' => 1500.00,
                'maintenance_fee' => 0.00,
                'penalty_flat' => 5000.00,
                'penalty_percentage' => 0.00,
                'due_day_of_month' => 20,
                'is_active' => true,
            ]
        );

        if ($tariff1A->tiers()->count() === 0) {
            TariffTier::create([
                'tariff_id' => $tariff1A->id,
                'tier_number' => 1,
                'tier_name' => 'Blok Bersubsidi Sosial',
                'min_consumption' => 0,
                'max_consumption' => null,
                'rate_per_m3' => 1800.00,
            ]);
        }

        // Golongan 3A - Niaga Kecil
        $tariff3A = Tariff::firstOrCreate(
            ['category_code' => '3A'],
            [
                'category_name' => '3A - Niaga Kecil',
                'description' => 'Golongan usaha komersial dan niaga kecil',
                'admin_fee' => 5000.00,
                'maintenance_fee' => 2500.00,
                'penalty_flat' => 25000.00,
                'penalty_percentage' => 0.00,
                'due_day_of_month' => 20,
                'is_active' => true,
            ]
        );

        if ($tariff3A->tiers()->count() === 0) {
            TariffTier::create([
                'tariff_id' => $tariff3A->id,
                'tier_number' => 1,
                'tier_name' => 'Blok Niaga',
                'min_consumption' => 0,
                'max_consumption' => null,
                'rate_per_m3' => 8500.00,
            ]);
        }
    }
}
