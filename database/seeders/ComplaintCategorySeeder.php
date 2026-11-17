<?php

namespace Database\Seeders;

use App\Models\ComplaintCategory;
use Illuminate\Database\Seeder;

class ComplaintCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Pipa Bocor / Pecah',
                'slug' => 'pipa_bocor',
                'description' => 'Laporan semburan air atau rembesan pipa transmisi & distribusi di jalan maupun lingkungan rumah.',
                'icon' => 'droplet',
                'is_active' => true,
            ],
            [
                'name' => 'Air Mati / Aliran Lemah',
                'slug' => 'air_mati',
                'description' => 'Gangguan suplai air bersih terhenti total atau tekanan keran sangat kecil.',
                'icon' => 'alert-triangle',
                'is_active' => true,
            ],
            [
                'name' => 'Kualitas Air Keruh / Berbau',
                'slug' => 'air_keruh',
                'description' => 'Air kran berwarna kecoklatan, berpasir, berlumpur, atau mengeluarkan bau tidak sedap.',
                'icon' => 'activity',
                'is_active' => true,
            ],
            [
                'name' => 'Meteran Rusak / Jarum Macet',
                'slug' => 'meteran_rusak',
                'description' => 'Kaca dial odometer buram, jarum berputar tidak normal, atau segel meter terlepas.',
                'icon' => 'gauge',
                'is_active' => true,
            ],
            [
                'name' => 'Klarifikasi Rekening Tagihan',
                'slug' => 'tagihan_tidak_sesuai',
                'description' => 'Pemeriksaan lonjakan tagihan rekening air yang tidak sesuai dengan kubikasi pemakaian nyata.',
                'icon' => 'file-text',
                'is_active' => true,
            ],
            [
                'name' => 'Permohonan Sambungan Baru',
                'slug' => 'pasang_baru',
                'description' => 'Pendaftaran instalasi pipa dinas dan pemasangan meteran air baru PDAM Tirta Sago.',
                'icon' => 'plus-circle',
                'is_active' => true,
            ],
        ];

        foreach ($categories as $cat) {
            ComplaintCategory::firstOrCreate(['slug' => $cat['slug']], $cat);
        }
    }
}
