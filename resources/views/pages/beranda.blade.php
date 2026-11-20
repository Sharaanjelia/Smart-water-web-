<?php
/**
 * Halaman Utama Beranda - Smart Water Kota Payakumbuh
 * Menggunakan Layout Asli Beranda dengan Hero Slider Dinamis & Banner CTA Modern
 */
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['swc_user']);
    header('Location: ' . $SWC_PAGE_URL);
    exit;
}

require_once $legacySharedPath . '/brand_logo.php';
$activeUser = $_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null);
$isGuest = empty($activeUser) && !\Illuminate\Support\Facades\Auth::check();
?>
<!doctype html>
<html lang="id">
  <head>
    <base href="{{ $legacyBaseHref }}" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($APP_META['title']) ?></title>
    <meta name="description" content="<?= e($APP_META['description']) ?>" />
    <meta property="og:title" content="<?= e($APP_META['title']) ?>" />
    <meta property="og:description" content="<?= e($APP_META['description']) ?>" />
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet" />
    
    <!-- Tailwind CSS v4 Browser Runtime -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../../assets/css/shared.css" />
    <link rel="stylesheet" href="../../assets/css/pages/beranda.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">
      
      <!-- Top Header Navigasi -->
      <?php
      $SHARED_HEADER_OPTIONS = [
          'handler' => 'handleBerandaNav',
          'logoHref' => url('/beranda'),
          'activeNav' => true,
          'user' => $activeUser,
      ];
      require $legacySharedPath . '/header.php';
      ?>

      <!-- Main View: Halaman Beranda -->
      <main class="flex-1">
        <div class="space-y-20 pb-20">
          
          <!-- ======================================================== -->
          <!-- 1. HERO SECTION DENGAN ROTATING BACKGROUNDS               -->
          <!-- ======================================================== -->
          <section
            id="hero-section"
            class="group relative overflow-hidden min-h-[540px] flex items-center justify-center pt-16 sm:pt-20 pb-10 sm:pb-12 border-b border-slate-900/30"
          >
            <!-- Background Images with Crossfade & Subtle Zoom Animation -->
            <div class="absolute inset-0 z-0 overflow-hidden bg-slate-950">
              <?php foreach ($HERO_BACKGROUNDS as $idx => $bg): ?>
                <?php $isActive = ($idx === 0); ?>
                <div
                  data-hero-slide="<?= $idx ?>"
                  class="hero-slide absolute inset-0 transition-opacity duration-1000 ease-in-out <?= $isActive ? 'opacity-100 z-10' : 'opacity-0 z-0' ?>"
                >
                  <img
                    src="<?= e($bg['url']) ?>"
                    alt="<?= e($bg['title']) ?>"
                    class="hero-slide-img w-full h-full object-cover object-center transition-transform duration-[7000ms] ease-out <?= $isActive ? 'scale-105' : 'scale-100' ?>"
                  />
                </div>
              <?php endforeach; ?>
              <!-- Measured Scrim for Legibility & WCAG AA Contrast -->
              <div class="absolute inset-0 z-10 bg-gradient-to-b from-slate-950/75 via-slate-900/60 to-slate-950/85 backdrop-blur-[1px]"></div>
            </div>

            <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-8">
              
              <!-- Headline: Centered with balanced typography -->
              <h1
                class="text-3xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-white leading-[1.14] drop-shadow-md"
                style="text-wrap: balance;"
              >
                Kenali Air. Jaga Penggunaannya. Bangun Kebiasaan yang Lebih Baik.
              </h1>

              <!-- Description: Centered -->
              <p
                class="text-base sm:text-lg text-slate-100 font-normal leading-relaxed max-w-2xl mx-auto drop-shadow-sm"
                style="text-wrap: balance;"
              >
                Temukan informasi layanan air, pelajari cara menggunakan air dengan bijak, dan ikuti aksi nyata untuk membantu menjaga sumber daya air Kota Payakumbuh.
              </p>

              <!-- 3 CTAs: Centered (Teks Saja) -->
              <div class="flex flex-wrap items-center justify-center gap-3.5 pt-2">
                <a
                  href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/pusat-layanan'))) : url('/pusat-layanan') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-bold text-slate-950 bg-cyan-400 hover:bg-cyan-300 transition-all shadow-lg shadow-cyan-400/25 hover:shadow-cyan-400/40 hover:scale-[1.02] flex items-center justify-center cursor-pointer"
                >
                  <span>Jelajahi Layanan</span>
                </a>

                <a
                  href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/akademi-air'))) : url('/akademi-air') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-semibold text-white bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 transition-all hover:scale-[1.02] flex items-center justify-center shadow-sm cursor-pointer"
                >
                  <span>Mulai Belajar</span>
                </a>

                <a
                  href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/tantangan'))) : url('/tantangan') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-semibold text-white bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md border border-white/20 transition-all hover:scale-[1.02] flex items-center justify-center shadow-sm cursor-pointer"
                >
                  <span>Ikuti Tantangan</span>
                </a>
              </div>

              <!-- 3 Feature Highlights: Centered with glassmorphism (Tanpa Garis & Ikon Dihapus) -->
              <div class="pt-5 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs text-slate-100">
                <div class="flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <span class="font-medium text-white">Sumber Air Gunung Sago &amp; Batang Agam</span>
                </div>
                <div class="flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <span class="font-medium text-white">Tiket Pelayanan Transparan</span>
                </div>
                <div class="flex items-center justify-center px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <span class="font-medium text-white">Gamifikasi &amp; Reward XP Warga</span>
                </div>
              </div>

            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 2. SECTION: MEWUJUDKAN KOTA PAYAKUMBUH YANG LEBIH CERDAS -->
          <!-- ======================================================== -->
          <section id="program-unggulan" class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4 pb-6 sm:pb-10">
            <!-- Ambient Glow Behind -->
            <div class="absolute -top-16 left-1/2 -translate-x-1/2 w-3/4 h-80 bg-gradient-to-b from-cyan-400/10 via-sky-500/5 to-transparent blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-10 sm:space-y-12">
              
              <!-- Section Header: Cinematic Story Intro -->
              <div class="text-center max-w-3xl mx-auto space-y-3.5 scroll-reveal">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-sky-50 border border-sky-200/80 text-sky-800 text-[11px] font-bold uppercase tracking-wider shadow-2xs">
                  <span class="w-2 h-2 rounded-full bg-[#0284C7] animate-pulse"></span>
                  <span>Misi &amp; Program Strategis 2026</span>
                </div>
                <h2 class="text-2xl sm:text-4xl lg:text-[40px] font-black text-[#0B1B3D] tracking-tight leading-[1.18]">
                  Mewujudkan Kota Payakumbuh yang Lebih Cerdas
                </h2>
                <p class="text-xs sm:text-sm md:text-base text-slate-500 font-normal max-w-2xl mx-auto leading-relaxed">
                  Melalui inovasi, teknologi, dan kolaborasi untuk pelayanan publik yang lebih baik.
                </p>
              </div>

              <!-- Flagship Feature Showcase: MISI 01 — KONSERVASI HULU (Panoramic Split Layout) -->
              <div class="group relative rounded-[32px] bg-white border border-slate-200/90 shadow-xl shadow-sky-950/5 overflow-hidden hover:border-sky-300 transition-all duration-500 scroll-reveal">
                <div class="grid grid-cols-1 lg:grid-cols-12 items-stretch">
                  
                  <!-- Left Image: High-res Mountain Spring Panorama -->
                  <div class="lg:col-span-7 relative min-h-[280px] sm:min-h-[360px] overflow-hidden img-reveal-container">
                    <img
                      src="<?= asset('assets/images/gunung-sago.jpg') ?>"
                      alt="Konservasi Hulu Gunung Sago"
                      class="w-full h-full object-cover object-center img-reveal-zoom group-hover:scale-105 transition-transform duration-700"
                    />
                    <!-- Gradients for pristine contrast -->
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/20 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:via-slate-950/20 lg:to-white"></div>
                    
                    <div class="absolute bottom-4 left-4 right-4 text-white lg:hidden">
                      <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-300">Zonasi Perlindungan Sumber Daya Air</span>
                    </div>
                  </div>

                  <!-- Right Content: Narrative Detail -->
                  <div class="lg:col-span-5 p-7 sm:p-9 lg:p-10 flex flex-col justify-between space-y-6 relative z-10 bg-white">
                    <div class="space-y-4">
                      <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] font-extrabold tracking-wider uppercase bg-sky-50 text-[#0284C7] border border-sky-100">
                          MISI 01 - KONSERVASI HULU
                        </span>
                        <span class="text-xs font-mono font-bold text-slate-300">01 / 04</span>
                      </div>

                      <h3 class="text-xl sm:text-2xl font-black text-[#0B1B3D] leading-snug">
                        Perlindungan Tangkapan Air Gunung Sago
                      </h3>

                      <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
                        Menjaga debit alami Mata Air Sikabu dan hulu DAS Batang Agam dari sedimentasi, penebangan liar, dan pencemaran limbah cair.
                      </p>

                      <!-- Key Highlights -->
                      <div class="pt-2 space-y-2.5">
                        <div class="flex items-start gap-2.5 text-xs text-slate-600">
                          <div class="w-5 h-5 rounded-lg bg-sky-100 text-[#0284C7] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                          </div>
                          <span>Pemantauan level reservoir hulu secara berkala demi kestabilan pasokan.</span>
                        </div>
                        <div class="flex items-start gap-2.5 text-xs text-slate-600">
                          <div class="w-5 h-5 rounded-lg bg-sky-100 text-[#0284C7] flex items-center justify-center shrink-0 mt-0.5">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                          </div>
                          <span>Pemberdayaan masyarakat nagari di sekitar hutan tangkapan air alami.</span>
                        </div>
                      </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-[#0284C7]">
                      <span class="flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 1 0 10 10A10 10 0 0 0 12 2zm-2 15-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8z"/></svg>
                        Ekosistem Hulu Terjaga
                      </span>
                      <a href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/tentang'))) : url('/tentang') ?>" class="inline-flex items-center gap-1 text-slate-400 hover:text-[#0284C7] transition-colors cursor-pointer group-hover:translate-x-1">
                        <span>Pelajari Selengkapnya</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                      </a>
                    </div>
                  </div>

                </div>
              </div>

              <!-- Trio of Mission Pillars: MISI 02, 03, 04 (3-Column Modern Grid) -->
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-2">
                
                <!-- KARTU MISI 02 — LITERASI WARGA -->
                <div class="group relative bg-white rounded-3xl border border-emerald-100/90 p-7 flex flex-col justify-between shadow-lg shadow-emerald-950/5 hover:shadow-xl hover:shadow-emerald-500/10 hover:border-emerald-300 transition-all duration-300 scroll-reveal delay-100 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-center justify-between">
                      <div class="w-13 h-13 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                          <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                        </svg>
                      </div>
                      <span class="text-xs font-mono font-bold text-slate-300">02 / 04</span>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-emerald-50 text-emerald-700 border border-emerald-100">
                        MISI 02 - LITERASI WARGA
                      </span>
                    </div>

                    <h3 class="text-lg font-bold text-[#0B1B3D] leading-snug group-hover:text-emerald-700 transition-colors">
                      Edukasi Kebiasaan Hemat Air Gen-Z
                    </h3>

                    <p class="text-xs text-slate-500 leading-relaxed">
                      Membudayakan audit kebocoran mandiri di rumah tangga, teknik mencuci hemat, dan perhitungan kubikasi register meteran air.
                    </p>
                  </div>

                  <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs relative z-10">
                    <span class="text-[11px] font-semibold text-emerald-600">Smart Water Academy</span>
                    <a href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/akademi-air'))) : url('/akademi-air') ?>" class="font-bold text-slate-400 group-hover:text-emerald-600 transition-colors inline-flex items-center gap-1">
                      <span>Mulai Belajar</span>
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                  </div>
                </div>

                <!-- KARTU MISI 03 — RESPONS CEPAT -->
                <div class="group relative bg-white rounded-3xl border border-amber-100/90 p-7 flex flex-col justify-between shadow-lg shadow-amber-950/5 hover:shadow-xl hover:shadow-amber-500/10 hover:border-amber-300 transition-all duration-300 scroll-reveal delay-200 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-center justify-between">
                      <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                          <path d="m9 12 2 2 4-4"/>
                        </svg>
                      </div>
                      <span class="text-xs font-mono font-bold text-slate-300">03 / 04</span>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-amber-50 text-amber-700 border border-amber-100">
                        MISI 03 - RESPONS CEPAT
                      </span>
                    </div>

                    <h3 class="text-lg font-bold text-[#0B1B3D] leading-snug group-hover:text-amber-700 transition-colors">
                      Pelayanan Publik Tanpa Hambatan
                    </h3>

                    <p class="text-xs text-slate-500 leading-relaxed">
                      Memastikan seluruh laporan semburan pipa bocor atau gangguan kualitas air ditindaklanjuti secara terbuka dengan status terlacak.
                    </p>
                  </div>

                  <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs relative z-10">
                    <span class="text-[11px] font-semibold text-amber-600">SLA Respons &lt; 2 Jam</span>
                    <a href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/pusat-layanan'))) : url('/pusat-layanan') ?>" class="font-bold text-slate-400 group-hover:text-amber-600 transition-colors inline-flex items-center gap-1">
                      <span>Lapor Gangguan</span>
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                  </div>
                </div>

                <!-- KARTU MISI 04 — TEKNOLOGI CERDAS -->
                <div class="group relative bg-white rounded-3xl border border-indigo-100/90 p-7 flex flex-col justify-between shadow-lg shadow-indigo-950/5 hover:shadow-xl hover:shadow-indigo-500/10 hover:border-indigo-300 transition-all duration-300 scroll-reveal delay-300 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-center justify-between">
                      <div class="w-13 h-13 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs group-hover:scale-105 transition-transform">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                          <circle cx="12" cy="13" r="4"/>
                        </svg>
                      </div>
                      <span class="text-xs font-mono font-bold text-slate-300">04 / 04</span>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-indigo-50 text-indigo-700 border border-indigo-100">
                        MISI 04 - TEKNOLOGI CERDAS
                      </span>
                    </div>

                    <h3 class="text-lg font-bold text-[#0B1B3D] leading-snug group-hover:text-indigo-700 transition-colors">
                      Integrasi AI &amp; Komunitas
                    </h3>

                    <p class="text-xs text-slate-500 leading-relaxed">
                      Menggabungkan ketepatan AI-OCR pembacaan meter mobile dengan transparansi data telemetri di portal komunitas web.
                    </p>
                  </div>

                  <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs relative z-10">
                    <span class="text-[11px] font-semibold text-indigo-600">AI-OCR Computer Vision</span>
                    <a href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/report-meter'))) : url('/report-meter') ?>" class="font-bold text-slate-400 group-hover:text-indigo-600 transition-colors inline-flex items-center gap-1">
                      <span>Coba AI Meter</span>
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                  </div>
                </div>

              </div>

            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 3. SECTION: NILAI LUHUR & FALSAFAH AIR MINANGKABAU       -->
          <!-- ======================================================== -->
          <section id="falsafah" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            <div class="relative rounded-[36px] bg-gradient-to-br from-[#08152E] via-[#0B1F47] to-[#061126] text-white p-8 sm:p-12 lg:p-16 overflow-hidden shadow-2xl border border-white/10 scroll-reveal">
              <!-- Ambient Backing Image with Smooth Scrim -->
              <div class="absolute inset-0 pointer-events-none opacity-20">
                <img
                  src="<?= asset('assets/images/hero-lake-grass.jpg') ?>"
                  alt="Kearifan Alam Minangkabau"
                  class="w-full h-full object-cover object-center filter grayscale"
                />
                <div class="absolute inset-0 bg-gradient-to-r from-[#08152E] via-[#08152E]/90 to-transparent"></div>
              </div>

              <!-- Glow Orbs -->
              <div class="absolute -top-20 -right-20 w-80 h-80 bg-cyan-500/15 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -bottom-20 -left-20 w-80 h-80 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>

              <div class="relative z-10 grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-14 items-center">
                
                <!-- Left: Proverb & Cultural Story -->
                <div class="lg:col-span-6 space-y-6 scroll-reveal-left">
                  <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-400/15 border border-cyan-400/30 text-cyan-300 text-xs font-bold uppercase tracking-wider">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/>
                    </svg>
                    <span>Nilai Luhur &amp; Falsafah Air Minangkabau</span>
                  </div>

                  <div class="space-y-4">
                    <h3 class="text-2xl sm:text-4xl lg:text-[42px] font-black tracking-tight leading-[1.15] text-white">
                      “Aia Tagang Manyerak, <br />
                      <span class="text-transparent bg-clip-text bg-gradient-to-r from-cyan-300 via-sky-200 to-teal-300">
                        Aia Janiah Manyanangkan”
                      </span>
                    </h3>

                    <p class="text-xs sm:text-sm md:text-base text-slate-300 leading-relaxed max-w-xl font-normal">
                      Kearifan lokal mengajarkan bahwa air bukan sekadar komoditas yang dibeli, melainkan amanah alam yang harus dijaga kejernihan dan kesucian alirannya bagi sesama warga.
                    </p>
                  </div>

                  <div class="pt-2 flex items-center gap-4 text-xs text-slate-400 border-t border-white/10">
                    <span class="font-mono text-cyan-400 font-semibold">&bull; Luhak Nan Bungsu</span>
                    <span class="text-slate-500">|</span>
                    <span>Adat Basandi Syarak, Syarak Basandi Kitabullah</span>
                  </div>
                </div>

                <!-- Right: The 3 Philosophical Pillars in Modern Action -->
                <div class="lg:col-span-6 space-y-4 scroll-reveal-right">
                  
                  <!-- Pillar 1 -->
                  <div class="group p-5 sm:p-6 rounded-2xl bg-white/[0.06] hover:bg-white/[0.1] border border-white/10 hover:border-cyan-400/40 backdrop-blur-md transition-all duration-300 space-y-2">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-cyan-400 shadow-sm shadow-cyan-400"></span>
                        <span>Tanggung Jawab Kolektif</span>
                      </h4>
                      <span class="text-[10px] font-mono text-cyan-300/80 uppercase">Solidaritas Pipa</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-5">
                      Pemborosan air di satu rumah tangga mengurangi cadangan tekanan pipa bagi warga di ujung jaringan pipa transmisi.
                    </p>
                  </div>

                  <!-- Pillar 2 -->
                  <div class="group p-5 sm:p-6 rounded-2xl bg-white/[0.06] hover:bg-white/[0.1] border border-white/10 hover:border-emerald-400/40 backdrop-blur-md transition-all duration-300 space-y-2">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400"></span>
                        <span>Integritas Kejujuran Meteran</span>
                      </h4>
                      <span class="text-[10px] font-mono text-emerald-300/80 uppercase">AI-OCR Transparansi</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-5">
                      Pembacaan meter mandiri berbasis AI-OCR melatih transparansi antara pelanggan dan pengelola air Tirta Sago.
                    </p>
                  </div>

                  <!-- Pillar 3 -->
                  <div class="group p-5 sm:p-6 rounded-2xl bg-white/[0.06] hover:bg-white/[0.1] border border-white/10 hover:border-amber-400/40 backdrop-blur-md transition-all duration-300 space-y-2">
                    <div class="flex items-center justify-between">
                      <h4 class="text-sm sm:text-base font-bold text-white flex items-center gap-2.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 shadow-sm shadow-amber-400"></span>
                        <span>Aksi Nyata Generasi Muda</span>
                      </h4>
                      <span class="text-[10px] font-mono text-amber-300/80 uppercase">Gen-Z Berdaya</span>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-5">
                      Gen-Z Payakumbuh menjadi penggerak perubahan lewat misi harian dan deteksi kebocoran pipa di lingkungan sekitar.
                    </p>
                  </div>

                </div>

              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 4. SECTION: MANIFESTO & IKRAR WARGA HEMAT AIR            -->
          <!-- ======================================================== -->
          <section id="manifesto" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            <div class="relative rounded-[36px] bg-[#07132B] text-white p-8 sm:p-12 lg:p-16 space-y-10 sm:space-y-12 overflow-hidden border border-slate-800 shadow-2xl scroll-reveal">
              
              <!-- Subtle Background Light Streaks -->
              <div class="absolute top-0 right-1/4 w-96 h-96 bg-cyan-600/10 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute bottom-0 left-1/4 w-96 h-96 bg-sky-600/10 rounded-full blur-3xl pointer-events-none"></div>

              <!-- Header -->
              <div class="text-center max-w-2xl mx-auto space-y-3.5 relative z-10">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/5 border border-white/10 text-cyan-300 text-[11px] font-bold uppercase tracking-wider">
                  <svg class="w-3.5 h-3.5 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
                  </svg>
                  <span>Komitmen Moral Warga Luhak 50</span>
                </div>

                <h2 class="text-2xl sm:text-4xl font-black text-white tracking-tight leading-tight">
                  Manifesto Komunitas Air Payakumbuh
                </h2>

                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed max-w-xl mx-auto">
                  Komitmen moral bersama untuk menjaga keberlanjutan air bersih di rumah masing-masing:
                </p>
              </div>

              <!-- 4 Pledge Checkpoints (Prestigious Grid with Big Numbers) -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-5 sm:gap-6 relative z-10">
                
                <!-- Pledge 1 -->
                <div class="group relative rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-cyan-400/50 p-6 sm:p-7 backdrop-blur-md transition-all duration-300 flex flex-col justify-between overflow-hidden">
                  <div class="absolute top-3 right-5 text-4xl sm:text-5xl font-black text-white/[0.05] group-hover:text-cyan-400/10 font-mono transition-colors select-none pointer-events-none">
                    01
                  </div>
                  <div class="space-y-3 relative z-10">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"/>
                        </svg>
                      </div>
                      <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-cyan-200 transition-colors">
                        1. Menutup Keran Saat Tidak Mengalirkan Faedah
                      </h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-12">
                      Tidak membiarkan air mengalir bebas saat menggosok gigi, sabunan, atau berwudhu.
                    </p>
                  </div>
                </div>

                <!-- Pledge 2 -->
                <div class="group relative rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-cyan-400/50 p-6 sm:p-7 backdrop-blur-md transition-all duration-300 flex flex-col justify-between overflow-hidden">
                  <div class="absolute top-3 right-5 text-4xl sm:text-5xl font-black text-white/[0.05] group-hover:text-cyan-400/10 font-mono transition-colors select-none pointer-events-none">
                    02
                  </div>
                  <div class="space-y-3 relative z-10">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"/>
                        </svg>
                      </div>
                      <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-cyan-200 transition-colors">
                        2. Melakukan Uji Kebocoran Meteran Berkala
                      </h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-12">
                      Memeriksa jarum meteran air di malam hari sebulan sekali untuk mencegah silent leak.
                    </p>
                  </div>
                </div>

                <!-- Pledge 3 -->
                <div class="group relative rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-cyan-400/50 p-6 sm:p-7 backdrop-blur-md transition-all duration-300 flex flex-col justify-between overflow-hidden">
                  <div class="absolute top-3 right-5 text-4xl sm:text-5xl font-black text-white/[0.05] group-hover:text-cyan-400/10 font-mono transition-colors select-none pointer-events-none">
                    03
                  </div>
                  <div class="space-y-3 relative z-10">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"/>
                        </svg>
                      </div>
                      <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-cyan-200 transition-colors">
                        3. Memanfaatkan Air Reused yang Aman
                      </h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-12">
                      Menyiram pot tanaman dengan air cucian sayur/beras daripada menyia-nyiakan air minum.
                    </p>
                  </div>
                </div>

                <!-- Pledge 4 -->
                <div class="group relative rounded-2xl bg-white/[0.04] hover:bg-white/[0.08] border border-white/10 hover:border-cyan-400/50 p-6 sm:p-7 backdrop-blur-md transition-all duration-300 flex flex-col justify-between overflow-hidden">
                  <div class="absolute top-3 right-5 text-4xl sm:text-5xl font-black text-white/[0.05] group-hover:text-cyan-400/10 font-mono transition-colors select-none pointer-events-none">
                    04
                  </div>
                  <div class="space-y-3 relative z-10">
                    <div class="flex items-center gap-3">
                      <div class="w-9 h-9 rounded-xl bg-cyan-500/20 text-cyan-300 border border-cyan-400/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                          <polyline points="20 6 9 17 4 12"/>
                        </svg>
                      </div>
                      <h3 class="text-sm sm:text-base font-bold text-white group-hover:text-cyan-200 transition-colors">
                        4. Peduli Terhadap Kebocoran Pipa Publik
                      </h3>
                    </div>
                    <p class="text-xs text-slate-300 leading-relaxed pl-12">
                      Segera melapor di portal Smart Water bila melihat kebocoran pipa air bersih di jalan.
                    </p>
                  </div>
                </div>

              </div>

            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 5. SECTION: ARSITEKTUR EKOSISTEM DIGITAL                 -->
          <!-- ======================================================== -->
          <section id="fitur-platform" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-10 sm:space-y-12">
            
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto space-y-3.5 scroll-reveal">
              <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-800 text-[11px] font-bold uppercase tracking-wider">
                <svg class="w-3.5 h-3.5 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
                  <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
                  <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
                </svg>
                <span>Arsitektur Ekosistem Digital</span>
              </div>

              <h2 class="text-2xl sm:text-4xl lg:text-[40px] font-black text-[#0B1B3D] tracking-tight leading-tight">
                Semua Kebutuhan Air, Lebih Mudah dalam Satu Platform
              </h2>

              <p class="text-xs sm:text-sm md:text-base text-slate-500 max-w-2xl mx-auto leading-relaxed">
                Nikmati kemudahan mengakses layanan, memantau penggunaan air, dan meningkatkan pengetahuan tentang air.
              </p>
            </div>

            <!-- 4 Elevated Interactive Feature Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              
              <!-- Card 1: Pusat Layanan -->
              <a
                href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/pusat-layanan'))) : url('/pusat-layanan') ?>"
                class="group relative rounded-3xl bg-white border border-slate-200/90 hover:border-cyan-400 p-7 flex flex-col justify-between shadow-lg shadow-sky-950/5 hover:shadow-2xl hover:shadow-cyan-500/10 transition-all duration-300 hover:-translate-y-1.5 cursor-pointer scroll-reveal delay-100 overflow-hidden"
              >
                <div class="space-y-4 relative z-10">
                  <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-cyan-50 text-cyan-700 group-hover:bg-cyan-600 group-hover:text-white transition-colors duration-300 flex items-center justify-center shrink-0 shadow-xs">
                      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="7" rx="2" ry="2"/>
                        <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>
                      </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-800">
                      Layanan
                    </span>
                  </div>

                  <div>
                    <h3 class="text-lg font-bold text-[#0B1B3D] group-hover:text-cyan-700 transition-colors">
                      Pusat Layanan
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-2">
                      Kelola informasi pelanggan, laporan meter, tagihan, dan pengaduan.
                    </p>
                  </div>
                </div>

                <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-cyan-700 group-hover:text-cyan-800 relative z-10">
                  <span>Buka Layanan</span>
                  <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                  </svg>
                </div>
              </a>

              <!-- Card 2: AI OCR Meter -->
              <a
                href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/report-meter'))) : url('/report-meter') ?>"
                class="group relative rounded-3xl bg-white border border-slate-200/90 hover:border-sky-400 p-7 flex flex-col justify-between shadow-lg shadow-sky-950/5 hover:shadow-2xl hover:shadow-sky-500/10 transition-all duration-300 hover:-translate-y-1.5 cursor-pointer scroll-reveal delay-200 overflow-hidden"
              >
                <div class="space-y-4 relative z-10">
                  <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-sky-50 text-sky-700 group-hover:bg-sky-600 group-hover:text-white transition-colors duration-300 flex items-center justify-center shrink-0 shadow-xs">
                      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                      </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-800">
                      AI Vision
                    </span>
                  </div>

                  <div>
                    <h3 class="text-lg font-bold text-[#0B1B3D] group-hover:text-sky-700 transition-colors">
                      AI OCR Meter
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-2">
                      Bantu membaca angka meter air melalui foto secara digital.
                    </p>
                  </div>
                </div>

                <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-sky-700 group-hover:text-sky-800 relative z-10">
                  <span>Pindai Meteran</span>
                  <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                  </svg>
                </div>
              </a>

              <!-- Card 3: Akademi Air -->
              <a
                href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/akademi-air'))) : url('/akademi-air') ?>"
                class="group relative rounded-3xl bg-white border border-slate-200/90 hover:border-indigo-400 p-7 flex flex-col justify-between shadow-lg shadow-sky-950/5 hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-300 hover:-translate-y-1.5 cursor-pointer scroll-reveal delay-300 overflow-hidden"
              >
                <div class="space-y-4 relative z-10">
                  <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-indigo-50 text-indigo-700 group-hover:bg-indigo-600 group-hover:text-white transition-colors duration-300 flex items-center justify-center shrink-0 shadow-xs">
                      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                        <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                      </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-800">
                      Literasi &amp; XP
                    </span>
                  </div>

                  <div>
                    <h3 class="text-lg font-bold text-[#0B1B3D] group-hover:text-indigo-700 transition-colors">
                      Akademi Air
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-2">
                      Pelajari pengetahuan air melalui materi dan kuis edukatif.
                    </p>
                  </div>
                </div>

                <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-indigo-700 group-hover:text-indigo-800 relative z-10">
                  <span>Mulai Belajar</span>
                  <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                  </svg>
                </div>
              </a>

              <!-- Card 4: Tantangan Air -->
              <a
                href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/tantangan'))) : url('/tantangan') ?>"
                class="group relative rounded-3xl bg-white border border-slate-200/90 hover:border-amber-400 p-7 flex flex-col justify-between shadow-lg shadow-sky-950/5 hover:shadow-2xl hover:shadow-amber-500/10 transition-all duration-300 hover:-translate-y-1.5 cursor-pointer scroll-reveal delay-400 overflow-hidden"
              >
                <div class="space-y-4 relative z-10">
                  <div class="flex items-center justify-between">
                    <div class="w-13 h-13 rounded-2xl bg-amber-50 text-amber-700 group-hover:bg-amber-600 group-hover:text-white transition-colors duration-300 flex items-center justify-center shrink-0 shadow-xs">
                      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                      </svg>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-800">
                      Komunitas
                    </span>
                  </div>

                  <div>
                    <h3 class="text-lg font-bold text-[#0B1B3D] group-hover:text-amber-700 transition-colors">
                      Tantangan Air
                    </h3>
                    <p class="text-xs text-slate-500 leading-relaxed mt-2">
                      Ikuti tantangan hemat air dan kumpulkan poin edukasi.
                    </p>
                  </div>
                </div>

                <div class="pt-6 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-amber-700 group-hover:text-amber-800 relative z-10">
                  <span>Ambil Tantangan</span>
                  <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
                  </svg>
                </div>
              </a>

            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 6. SECTION: CALL-TO-ACTION (CTA) PENUTUP SEBELUM FOOTER  -->
          <!-- ======================================================== -->
          <section id="cta-gabung" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10">
            <div class="relative overflow-hidden rounded-[40px] bg-gradient-to-r from-[#07132B] via-[#0A1F44] to-[#07132B] text-white p-8 sm:p-14 lg:p-16 text-center space-y-8 shadow-2xl border border-white/10 scroll-reveal">
              
              <!-- Background Scenic Reflection with Scrim -->
              <div class="absolute inset-0 pointer-events-none opacity-20">
                <img
                  src="<?= asset('assets/images/hero-lake-serene.jpg') ?>"
                  alt="Danau Sikabu Payakumbuh"
                  class="w-full h-full object-cover object-center filter grayscale contrast-125"
                />
                <div class="absolute inset-0 bg-gradient-to-t from-[#07132B] via-[#07132B]/80 to-[#07132B]/95"></div>
              </div>

              <!-- Ambient Glow -->
              <div class="absolute -top-28 -left-28 w-80 h-80 bg-cyan-500/20 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -bottom-28 -right-28 w-80 h-80 bg-sky-500/20 rounded-full blur-3xl pointer-events-none"></div>

              <div class="max-w-3xl mx-auto space-y-4 relative z-10">
                <span class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-cyan-400/10 border border-cyan-400/25 text-cyan-300 text-xs font-mono font-bold uppercase tracking-widest">
                  <span class="w-1.5 h-1.5 rounded-full bg-cyan-400 animate-ping"></span>
                  MARI BERKONTRIBUSI HARI INI
                </span>

                <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight leading-[1.12]">
                  Mulai Pengalaman Layanan Air Cerdas Bersama Smart Water
                </h2>

                <p class="text-xs sm:text-sm md:text-base text-slate-300 leading-relaxed max-w-2xl mx-auto font-normal">
                  Catat stand meter mandiri, pantau rekening keluarga, dan nikmati kemudahan akses informasi publik dalam satu platform terpercaya.
                </p>
              </div>

              <!-- Action Buttons -->
              <div class="flex flex-wrap items-center justify-center gap-4 pt-2 relative z-10">
                <a
                  href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/report-meter'))) : url('/report-meter') ?>"
                  class="btn-shimmer relative px-8 py-4 rounded-2xl text-xs sm:text-sm font-extrabold text-slate-950 bg-gradient-to-r from-cyan-400 via-cyan-300 to-sky-400 hover:from-cyan-300 hover:to-sky-300 shadow-xl shadow-cyan-400/25 hover:shadow-cyan-400/40 transition-all hover:scale-105 cursor-pointer flex items-center gap-2"
                >
                  <span>Coba Lapor Meter Mandiri AI Sekarang</span>
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </a>
                
                <a
                  href="<?= $isGuest ? url('/login?redirect=' . urlencode(url('/pusat-layanan'))) : url('/pusat-layanan') ?>"
                  class="px-7 py-4 rounded-2xl text-xs sm:text-sm font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 backdrop-blur-md transition-all hover:scale-105 cursor-pointer flex items-center gap-2"
                >
                  <svg class="w-4 h-4 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span>Pusat Bantuan &amp; Pengaduan</span>
                </a>
              </div>

              <!-- Trust Verification Badges -->
              <div class="pt-6 border-t border-white/10 flex flex-wrap items-center justify-center gap-6 text-[11px] font-medium text-slate-400 relative z-10">
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  Terintegrasi PDAM Tirta Sago
                </span>
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  Layanan Terbuka untuk Seluruh Warga Payakumbuh
                </span>
                <span class="flex items-center gap-1.5">
                  <svg class="w-3.5 h-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  Keamanan Data Terenkripsi
                </span>
              </div>

            </div>
          </section>

        </div>
      </main>

      <!-- Footer Standar -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <!-- Smart Water Client-Side Reactive Store (Firebase-Ready) -->
    <script src="<?= asset('assets/js/swc-store.js') ?>"></script>
    <script src="../../assets/js/pages/beranda.js"></script>
  </body>
</html>
