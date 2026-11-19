<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerandaRedesignTest extends TestCase
{
    use RefreshDatabase;

    public function test_beranda_renders_with_untouched_hero_and_redesigned_sections(): void
    {
        $response = $this->get('/beranda');
        $response->assertStatus(200);

        // 1. VERIFY HERO IS 100% UNTOUCHED
        $response->assertSee('Kenali Air. Jaga Penggunaannya. Bangun Kebiasaan yang Lebih Baik.');
        $response->assertSee('Temukan informasi layanan air, pelajari cara menggunakan air dengan bijak');
        $response->assertSee('Jelajahi Layanan');
        $response->assertSee('Mulai Belajar');
        $response->assertSee('Ikuti Tantangan');
        $response->assertSee('Sumber Air Gunung Sago &amp; Batang Agam', false);
        $response->assertSee('Tiket Pelayanan Transparan');
        $response->assertSee('Gamifikasi &amp; Reward XP Warga', false);

        // 2. VERIFY SECTION 1: MEWUJUDKAN KOTA PAYAKUMBUH
        $response->assertSee('Mewujudkan Kota Payakumbuh yang Lebih Cerdas');
        $response->assertSee('MISI 01 - KONSERVASI HULU');
        $response->assertSee('Perlindungan Tangkapan Air Gunung Sago');
        $response->assertSee('MISI 02 - LITERASI WARGA');
        $response->assertSee('Edukasi Kebiasaan Hemat Air Gen-Z');
        $response->assertSee('MISI 03 - RESPONS CEPAT');
        $response->assertSee('Pelayanan Publik Tanpa Hambatan');
        $response->assertSee('MISI 04 - TEKNOLOGI CERDAS');
        $response->assertSee('Integrasi AI &amp; Komunitas', false);

        // 3. VERIFY SECTION 2: FALSAFAH AIR MINANGKABAU
        $response->assertSee('Nilai Luhur &amp; Falsafah Air Minangkabau', false);
        $response->assertSee('Aia Tagang Manyerak');
        $response->assertSee('Aia Janiah Manyanangkan');
        $response->assertSee('Tanggung Jawab Kolektif');
        $response->assertSee('Integritas Kejujuran Meteran');
        $response->assertSee('Aksi Nyata Generasi Muda');

        // 4. VERIFY SECTION 3: MANIFESTO
        $response->assertSee('Manifesto Komunitas Air Payakumbuh');
        $response->assertSee('1. Menutup Keran Saat Tidak Mengalirkan Faedah');
        $response->assertSee('2. Melakukan Uji Kebocoran Meteran Berkala');
        $response->assertSee('3. Memanfaatkan Air Reused yang Aman');
        $response->assertSee('4. Peduli Terhadap Kebocoran Pipa Publik');

        // 5. VERIFY SECTION 4: ARSITEKTUR EKOSISTEM DIGITAL
        $response->assertSee('Semua Kebutuhan Air, Lebih Mudah dalam Satu Platform');
        $response->assertSee('Arsitektur Ekosistem Digital');
        $response->assertSee('Pusat Layanan');
        $response->assertSee('AI OCR Meter');
        $response->assertSee('Akademi Air');
        $response->assertSee('Tantangan Air');

        // 6. VERIFY SECTION 5: CTA PENUTUP
        $response->assertSee('MARI BERKONTRIBUSI HARI INI');
        $response->assertSee('Mulai Pengalaman Layanan Air Cerdas Bersama Smart Water');
        $response->assertSee('Coba Lapor Meter Mandiri AI Sekarang');
        $response->assertSee('Pusat Bantuan &amp; Pengaduan', false);
    }
}
