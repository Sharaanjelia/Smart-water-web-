<?php

namespace Database\Seeders;

use App\Models\AcademyCategory;
use App\Models\Course;
use App\Models\Lesson;
use Illuminate\Database\Seeder;

class AcademySeeder extends Seeder
{
    public function run(): void
    {
        $cat1 = AcademyCategory::firstOrCreate(
            ['slug' => 'konservasi-dasar'],
            [
                'name' => 'Konservasi & Efisiensi Air',
                'description' => 'Panduan praktis menghemat air dan audit kebocoran mandiri di hunian warga Payakumbuh.',
            ]
        );

        $cat2 = AcademyCategory::firstOrCreate(
            ['slug' => 'teknologi-tarif'],
            [
                'name' => 'Teknologi Meter & Regulasi Tarif',
                'description' => 'Pemahaman skema 4 blok tarif progresif PDAM dan penggunaan fitur pembacaan meter AI-OCR.',
            ]
        );

        // Course 1
        $c1 = Course::firstOrCreate(
            ['course_code' => 'course-1'],
            [
                'category_id' => $cat1->id,
                'title' => 'Dasar Konservasi Air Rumah Tangga',
                'slug' => 'dasar-konservasi-air-rumah-tangga',
                'description' => 'Pelajari teknik audit kebocoran tersembunyi, pasang aerator keran, dan kurangi pemborosan air harian hingga 40%.',
                'level' => 'Pemula',
                'estimated_minutes' => 15,
                'xp_reward' => 150,
                'badge_reward' => 'badge-sago-guardian',
                'is_published' => true,
            ]
        );

        Lesson::firstOrCreate(
            ['course_id' => $c1->id, 'lesson_code' => 'lesson-1-1'],
            [
                'order_index' => 1,
                'title' => 'Mengenal Siklus Hidrologi & Sumber Air Batang Agam',
                'duration' => '3 Menit',
                'content' => 'Kota Payakumbuh diberkahi mata air alami dari hulu Gunung Sago dan aliran Batang Agam. Air baku diproses melalui Instalasi Pengolahan Air (IPA) sebelum didistribusikan ke tandon penampung warga. Memahami asal air memicu rasa tanggung jawab menjaga setiap tetesnya.',
            ]
        );

        Lesson::firstOrCreate(
            ['course_id' => $c1->id, 'lesson_code' => 'lesson-1-2'],
            [
                'order_index' => 2,
                'title' => 'Audit Mandiri Kebocoran Tersembunyi (Uji Statis)',
                'duration' => '4 Menit',
                'content' => 'Uji Statis adalah cara paling ampuh mendeteksi pipa bocor di dalam dinding atau bawah lantai. Langkahnya: Tutup semua keran air di rumah selama 30 menit tanpa mencuci atau menggunakan toilet. Lihat meteran air di depan pagar—jika jarum merah berputar, berarti ada kebocoran yang harus segera diperbaiki!',
            ]
        );

        Lesson::firstOrCreate(
            ['course_id' => $c1->id, 'lesson_code' => 'lesson-1-3'],
            [
                'order_index' => 3,
                'title' => 'Pemasangan Nozzle Aerator & Kebiasaan Bijak',
                'duration' => '3 Menit',
                'content' => 'Aerator keran mencampurkan udara ke dalam aliran air sehingga debit terasa deras meski volume air yang keluar berkurang hingga 50%. Padukan dengan kebiasaan mematikan keran saat menggosok gigi dan menggunakan baskom saat mencuci piring.',
            ]
        );

        // Course 2
        $c2 = Course::firstOrCreate(
            ['course_code' => 'course-2'],
            [
                'category_id' => $cat2->id,
                'title' => 'Memahami Tarif Progresif & Lapor Meter AI',
                'slug' => 'memahami-tarif-progresif-lapor-meter-ai',
                'description' => 'Kuasai perhitungan tagihan air berdasarkan 4 blok tarif Perumda Tirta Sago dan cara foto odometer meteran secara presisi.',
                'level' => 'Menengah',
                'estimated_minutes' => 20,
                'xp_reward' => 200,
                'badge_reward' => 'badge-water-scholar',
                'is_published' => true,
            ]
        );

        Lesson::firstOrCreate(
            ['course_id' => $c2->id, 'lesson_code' => 'lesson-2-1'],
            [
                'order_index' => 1,
                'title' => 'Struktur 4 Blok Tarif Progresif PDAM Tirta Sago',
                'duration' => '4 Menit',
                'content' => 'Tarif air Kota Payakumbuh menerapkan sistem keadilan sosial: Blok I (0-10 m3) tarif bersubsidi Rp 2.500/m3, Blok II (11-20 m3) Rp 3.200/m3, Blok III (21-30 m3) Rp 4.500/m3, dan Blok IV (>30 m3) Rp 6.000/m3. Semakin hemat pemakaian, semakin murah biaya per kubiknya.',
            ]
        );

        Lesson::firstOrCreate(
            ['course_id' => $c2->id, 'lesson_code' => 'lesson-2-2'],
            [
                'order_index' => 2,
                'title' => 'Cara Membaca Angka Dial Meteran & AI-OCR',
                'duration' => '4 Menit',
                'content' => 'Dial meter air memiliki angka hitam (satuan kubik / m3) dan angka merah (satuan liter). AI-OCR Smart Water fokus membaca deretan angka hitam. Pastikan lensa kamera bersih, kaca meter dilap, dan pencahayaan cukup sebelum mengambil foto.',
            ]
        );
    }
}
