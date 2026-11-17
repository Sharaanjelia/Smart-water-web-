<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengeMission;
use Illuminate\Database\Seeder;

class ChallengeSeeder extends Seeder
{
    public function run(): void
    {
        // Challenge 1
        $ch1 = Challenge::firstOrCreate(
            ['challenge_code' => 'challenge-1'],
            [
                'title' => 'Tantangan 7 Hari Penghematan Pagi Hari',
                'slug' => 'tantangan-7-hari-penghematan-pagi-hari',
                'subtitle' => 'Mulai kebiasaan hemat air di jam-jam puncak pemakaian pagi',
                'description' => 'Jam 05:00 - 08:00 WIB adalah waktu beban transmisi tertinggi di Payakumbuh. Selesaikan 4 misi mudah ini untuk menjaga stabilitas tandon bersama.',
                'level' => 'Mudah',
                'duration_days' => 7,
                'xp_total' => 250,
                'badge_code' => 'badge-morning-saver',
                'badge_title' => 'Ksatria Hemat Pagi',
                'is_active' => true,
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch1->id, 'mission_code' => 'm-1-1'],
            [
                'order_index' => 1,
                'title' => 'Gunakan baskom penampung saat mencuci piring & bahan masakan pagi',
                'xp' => 50,
                'tip' => 'Menghemat hingga 25 liter dibanding membiarkan air keran mengalir deras.',
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch1->id, 'mission_code' => 'm-1-2'],
            [
                'order_index' => 2,
                'title' => 'Batasi durasi mandi pagi maksimal 5 hingga 7 menit',
                'xp' => 50,
                'tip' => 'Gunakan timer di ponsel untuk melatih kesadaran waktu mandi.',
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch1->id, 'mission_code' => 'm-1-3'],
            [
                'order_index' => 3,
                'title' => 'Matikan keran saat menyikat gigi dan bercukur',
                'xp' => 50,
                'tip' => 'Gunakan satu gelas air kumur untuk menghemat 6 liter per sesi.',
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch1->id, 'mission_code' => 'm-1-4'],
            [
                'order_index' => 4,
                'title' => 'Manfaatkan air bilasan beras untuk menyiram tanaman pekarangan',
                'xp' => 100,
                'tip' => 'Kandungan nutrisi cucian beras sangat baik untuk kesuburan tanah.',
            ]
        );

        // Challenge 2
        $ch2 = Challenge::firstOrCreate(
            ['challenge_code' => 'challenge-2'],
            [
                'title' => 'Operasi Detektif Kebocoran Rumah',
                'slug' => 'operasi-detektif-kebocoran-rumah',
                'subtitle' => 'Temukan dan laporkan rembesan tersembunyi',
                'description' => 'Banyak warga kaget tagihan melonjak karena rembesan pelampung toren atau kloset bocor halus. Jadilah detektif di rumah sendiri!',
                'level' => 'Menengah',
                'duration_days' => 3,
                'xp_total' => 200,
                'badge_code' => 'badge-leak-hunter',
                'badge_title' => 'Detektif Kebocoran',
                'is_active' => true,
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch2->id, 'mission_code' => 'm-2-1'],
            [
                'order_index' => 1,
                'title' => 'Lakukan Uji Statis meteran air selama 30 menit tanpa aktivitas',
                'xp' => 75,
                'tip' => 'Pastikan tidak ada keran yang menetes sebelum mengamati jarum meter.',
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch2->id, 'mission_code' => 'm-2-2'],
            [
                'order_index' => 2,
                'title' => 'Cek fisik katup pelampung toren air di atap rumah',
                'xp' => 75,
                'tip' => 'Toren yang luber sering tidak disadari karena pipa pembuangannya tersembunyi.',
            ]
        );

        ChallengeMission::firstOrCreate(
            ['challenge_id' => $ch2->id, 'mission_code' => 'm-2-3'],
            [
                'order_index' => 3,
                'title' => 'Foto dan laporkan kebocoran fasilitas umum di Pusat Layanan jika ada',
                'xp' => 50,
                'tip' => 'Bantu warga Payakumbuh mencegah susut air jaringan transmisi.',
            ]
        );
    }
}
