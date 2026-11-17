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
            class="group relative overflow-hidden min-h-[580px] flex items-center justify-center pt-20 pb-24 border-b border-slate-900/30"
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

              <!-- 3 CTAs: Centered -->
              <div class="flex flex-wrap items-center justify-center gap-3.5 pt-2">
                <a
                  href="<?= url('/pusat-layanan') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-bold text-slate-950 bg-cyan-400 hover:bg-cyan-300 transition-all shadow-lg shadow-cyan-400/25 hover:shadow-cyan-400/40 hover:scale-[1.02] flex items-center gap-2 cursor-pointer"
                >
                  <span>Jelajahi Layanan</span>
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M5 12h14"/>
                    <path d="m12 5 7 7-7 7"/>
                  </svg>
                </a>

                <a
                  href="<?= url('/akademi-air') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-semibold text-white bg-white/20 hover:bg-white/30 backdrop-blur-md border border-white/30 transition-all hover:scale-[1.02] flex items-center gap-2 shadow-sm cursor-pointer"
                >
                  <svg class="w-4 h-4 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
                    <path d="M22 10v6"/>
                    <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                  </svg>
                  <span>Mulai Belajar</span>
                </a>

                <a
                  href="<?= url('/tantangan') ?>"
                  class="px-6 py-3 rounded-xl text-xs font-semibold text-white bg-slate-900/60 hover:bg-slate-900/80 backdrop-blur-md border border-white/20 transition-all hover:scale-[1.02] flex items-center gap-2 shadow-sm cursor-pointer"
                >
                  <svg class="w-4 h-4 text-amber-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
                  </svg>
                  <span>Ikuti Tantangan</span>
                </a>
              </div>

              <!-- 3 Feature Highlights: Centered with glassmorphism -->
              <div class="pt-8 border-t border-white/20 flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-xs text-slate-100">
                <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <svg class="w-4 h-4 text-cyan-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                    <path d="m9 12 2 2 4-4"/>
                  </svg>
                  <span class="font-medium text-white">Sumber Air Gunung Sago &amp; Batang Agam</span>
                </div>
                <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <svg class="w-4 h-4 text-emerald-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m9 12 2 2 4-4"/>
                  </svg>
                  <span class="font-medium text-white">Tiket Pelayanan Transparan</span>
                </div>
                <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-slate-950/40 backdrop-blur-md border border-white/15 shadow-sm">
                  <svg class="w-4 h-4 text-amber-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                    <path d="M20 3v4"/>
                    <path d="M22 5h-4"/>
                    <path d="M4 17v2"/>
                    <path d="M5 18H3"/>
                  </svg>
                  <span class="font-medium text-white">Gamifikasi &amp; Reward XP Warga</span>
                </div>
              </div>

            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 2. PROGRAM UNGGULAN — KARTU MISI 01-04                    -->
          <!-- ======================================================== -->
          <section id="program-unggulan" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden py-12 sm:py-16 px-6 sm:px-10 lg:px-12 rounded-[32px] bg-gradient-to-b from-[#f8fbff] via-[#fcfeff] to-[#f6f9fc] border border-slate-100 shadow-[0_4px_25px_-5px_rgba(0,0,0,0.02)]">
              <!-- Subtle Ambient Pastel Glows -->
              <div class="absolute -top-24 -left-24 w-80 h-80 bg-sky-100/40 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -top-20 -right-20 w-80 h-80 bg-emerald-100/35 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -bottom-24 right-1/4 w-80 h-80 bg-purple-100/30 rounded-full blur-3xl pointer-events-none"></div>

              <!-- Section Heading & Subtitle -->
              <div class="text-center max-w-3xl mx-auto space-y-3 mb-10 sm:mb-14 relative z-10">
                <div class="inline-flex items-center justify-center gap-3">
                  <span class="h-[2px] w-8 sm:w-10 bg-[#1d82f5] rounded-full"></span>
                  <span class="text-xs sm:text-sm font-bold tracking-[0.22em] text-[#1d82f5] uppercase font-sans">
                    PROGRAM UNGGULAN
                  </span>
                  <span class="h-[2px] w-8 sm:w-10 bg-[#1d82f5] rounded-full"></span>
                </div>

                <h2 class="text-2xl sm:text-3xl lg:text-[34px] font-extrabold text-[#0c1e3c] tracking-tight">
                  Mewujudkan Kota Payakumbuh yang Lebih Cerdas
                </h2>

                <p class="text-xs sm:text-sm md:text-base text-slate-500 font-normal max-w-2xl mx-auto leading-relaxed">
                  Melalui inovasi, teknologi, dan kolaborasi untuk pelayanan publik yang lebih baik.
                </p>
              </div>

              <!-- 4 Kartu Misi Strategis -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 relative z-10">
                
                <!-- KARTU MISI 01 — KONSERVASI HULU -->
                <div class="group relative bg-white rounded-[26px] border border-sky-100/90 p-6 sm:p-7 flex flex-col justify-between min-h-[330px] pb-8 shadow-[0_8px_30px_-6px_rgba(56,189,248,0.12)] hover:shadow-[0_16px_36px_-6px_rgba(56,189,248,0.22)] hover:border-sky-300 transition-all duration-300 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-start justify-between">
                      <div class="w-14 h-14 rounded-2xl bg-[#eaf4fe] flex items-center justify-center text-[#1d82f5] shadow-xs">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                          <path d="m8 3 4 8 5-5 5 15H2L8 3z"/>
                        </svg>
                      </div>
                      <svg class="w-5 h-5 text-[#38bdf8] mt-1 mr-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="8" y1="12" x2="14" y2="6" />
                        <line x1="13" y1="17" x2="16" y2="14" />
                      </svg>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-[#e1f0fe] text-[#0066cc]">
                        MISI 01 - KONSERVASI HULU
                      </span>
                    </div>

                    <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0c1e3c] leading-snug">
                      Perlindungan Tangkapan Air Gunung Sago
                    </h3>

                    <p class="text-[13px] text-slate-500 leading-relaxed">
                      Menjaga debit alami Mata Air Sikabu dan hulu DAS Batang Agam dari sedimentasi, penebangan liar, dan pencemaran limbah cair.
                    </p>
                  </div>

                  <div class="absolute bottom-0 right-0 pointer-events-none select-none z-0">
                    <svg class="w-36 h-24 text-[#eaf4fe]" viewBox="0 0 150 100" fill="none">
                      <path d="M0 100 C 50 100 65 35 150 20 L 150 100 Z" fill="currentColor" />
                    </svg>
                    <div class="absolute bottom-3 right-4">
                      <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none">
                        <path d="M12 28 C 16 22 20 16 24 8" stroke="#38bdf8" stroke-width="2.2" stroke-linecap="round" />
                        <path d="M24 8 C 21 6 17 8 18 13 C 21 14 25 12 24 8 Z" fill="#38bdf8" />
                        <path d="M18 16 C 14 15 12 18 14 21 C 17 21 19 19 18 16 Z" fill="#38bdf8" />
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- KARTU MISI 02 — LITERASI WARGA -->
                <div class="group relative bg-white rounded-[26px] border border-emerald-100/90 p-6 sm:p-7 flex flex-col justify-between min-h-[330px] pb-8 shadow-[0_8px_30px_-6px_rgba(16,185,129,0.12)] hover:shadow-[0_16px_36px_-6px_rgba(16,185,129,0.22)] hover:border-emerald-300 transition-all duration-300 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-start justify-between">
                      <div class="w-14 h-14 rounded-2xl bg-[#eaf8f1] flex items-center justify-center text-[#10b981] shadow-xs">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
                          <path d="M22 10v6"/>
                          <path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                        </svg>
                      </div>
                      <svg class="w-5 h-5 text-[#34d399] mt-1 mr-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="8" y1="12" x2="14" y2="6" />
                        <line x1="13" y1="17" x2="16" y2="14" />
                      </svg>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-[#dcf4e7] text-[#059669]">
                        MISI 02 - LITERASI WARGA
                      </span>
                    </div>

                    <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0c1e3c] leading-snug">
                      Edukasi Kebiasaan Hemat Air Gen-Z
                    </h3>

                    <p class="text-[13px] text-slate-500 leading-relaxed">
                      Membudayakan audit kebocoran mandiri di rumah tangga, teknik mencuci hemat, dan perhitungan kubikasi register meteran air.
                    </p>
                  </div>

                  <div class="absolute bottom-0 right-0 pointer-events-none select-none z-0">
                    <svg class="w-36 h-24 text-[#eaf8f1]" viewBox="0 0 150 100" fill="none">
                      <path d="M0 100 C 50 100 65 35 150 20 L 150 100 Z" fill="currentColor" />
                    </svg>
                    <div class="absolute bottom-3 right-4">
                      <svg class="w-8 h-8" viewBox="0 0 32 32" fill="none">
                        <path d="M16 28 C 16 20 16 16 16 13" stroke="#10b981" stroke-width="2.2" stroke-linecap="round" />
                        <path d="M16 16 C 10 14 8 19 11 22 C 14 23 16 19 16 16 Z" fill="#10b981" />
                        <path d="M16 16 C 22 14 24 19 21 22 C 18 23 16 19 16 16 Z" fill="#10b981" />
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- KARTU MISI 03 — RESPONS CEPAT -->
                <div class="group relative bg-white rounded-[26px] border border-orange-100/90 p-6 sm:p-7 flex flex-col justify-between min-h-[330px] pb-8 shadow-[0_8px_30px_-6px_rgba(249,115,22,0.12)] hover:shadow-[0_16px_36px_-6px_rgba(249,115,22,0.22)] hover:border-orange-300 transition-all duration-300 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-start justify-between">
                      <div class="w-14 h-14 rounded-2xl bg-[#fff3eb] flex items-center justify-center text-[#f97316] shadow-xs">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                          <path d="m9 12 2 2 4-4"/>
                        </svg>
                      </div>
                      <svg class="w-5 h-5 text-[#fb923c] mt-1 mr-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="8" y1="12" x2="14" y2="6" />
                        <line x1="13" y1="17" x2="16" y2="14" />
                      </svg>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-[#ffedd5] text-[#ea580c]">
                        MISI 03 - RESPONS CEPAT
                      </span>
                    </div>

                    <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0c1e3c] leading-snug">
                      Pelayanan Publik Tanpa Hambatan
                    </h3>

                    <p class="text-[13px] text-slate-500 leading-relaxed">
                      Memastikan seluruh laporan semburan pipa bocor atau gangguan kualitas air ditindaklanjuti secara terbuka dengan status terlacak.
                    </p>
                  </div>

                  <div class="absolute bottom-0 right-0 pointer-events-none select-none z-0">
                    <svg class="w-36 h-24 text-[#fff3eb]" viewBox="0 0 150 100" fill="none">
                      <path d="M0 100 C 50 100 65 35 150 20 L 150 100 Z" fill="currentColor" />
                    </svg>
                    <div class="absolute bottom-3 right-4">
                      <svg class="w-7 h-8" viewBox="0 0 28 32" fill="none">
                        <path d="M14 4 C 14 4 6 15 6 20 C 6 24.4 9.6 28 14 28 C 18.4 28 22 24.4 22 20 C 22 15 14 4 14 4 Z" fill="#f97316" fill-opacity="0.9" />
                        <circle cx="11" cy="18" r="2" fill="white" fill-opacity="0.45" />
                      </svg>
                    </div>
                  </div>
                </div>

                <!-- KARTU MISI 04 — TEKNOLOGI CERDAS -->
                <div class="group relative bg-white rounded-[26px] border border-purple-100/90 p-6 sm:p-7 flex flex-col justify-between min-h-[330px] pb-8 shadow-[0_8px_30px_-6px_rgba(139,92,246,0.12)] hover:shadow-[0_16px_36px_-6px_rgba(139,92,246,0.22)] hover:border-purple-300 transition-all duration-300 overflow-hidden">
                  <div class="space-y-4 relative z-10">
                    <div class="flex items-start justify-between">
                      <div class="w-14 h-14 rounded-2xl bg-[#f3efff] flex items-center justify-center text-[#8b5cf6] shadow-xs">
                        <svg class="w-7 h-7 text-[#8b5cf6]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <circle cx="12" cy="12" r="3" fill="currentColor" fill-opacity="0.2" />
                          <circle cx="12" cy="3.5" r="1.5" fill="currentColor" />
                          <circle cx="12" cy="20.5" r="1.5" fill="currentColor" />
                          <circle cx="4" cy="8" r="1.5" fill="currentColor" />
                          <circle cx="20" cy="8" r="1.5" fill="currentColor" />
                          <circle cx="4" cy="16" r="1.5" fill="currentColor" />
                          <circle cx="20" cy="16" r="1.5" fill="currentColor" />
                          <line x1="12" y1="9" x2="12" y2="5" />
                          <line x1="12" y1="15" x2="12" y2="19" />
                          <line x1="9.5" y1="10.5" x2="5.5" y2="8.8" />
                          <line x1="14.5" y1="10.5" x2="18.5" y2="8.8" />
                          <line x1="9.5" y1="13.5" x2="5.5" y2="15.2" />
                          <line x1="14.5" y1="13.5" x2="18.5" y2="15.2" />
                        </svg>
                      </div>
                      <svg class="w-5 h-5 text-[#c084fc] mt-1 mr-0.5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                        <line x1="8" y1="12" x2="14" y2="6" />
                        <line x1="13" y1="17" x2="16" y2="14" />
                      </svg>
                    </div>

                    <div>
                      <span class="inline-flex items-center px-3.5 py-1 rounded-full text-[10px] sm:text-[11px] font-bold tracking-wider uppercase bg-[#ede9fe] text-[#7c3aed]">
                        MISI 04 - TEKNOLOGI CERDAS
                      </span>
                    </div>

                    <h3 class="text-[17px] sm:text-[18px] font-bold text-[#0c1e3c] leading-snug">
                      Integrasi AI &amp; Komunitas
                    </h3>

                    <p class="text-[13px] text-slate-500 leading-relaxed">
                      Menggabungkan ketepatan AI-OCR pembacaan meter mobile dengan transparansi data telemetri di portal komunitas web.
                    </p>
                  </div>

                  <div class="absolute bottom-0 right-0 pointer-events-none select-none z-0">
                    <svg class="w-36 h-24 text-[#f3efff]" viewBox="0 0 150 100" fill="none">
                      <path d="M0 100 C 50 100 65 35 150 20 L 150 100 Z" fill="currentColor" />
                    </svg>
                    <div class="absolute bottom-3 right-4">
                      <div class="grid grid-cols-2 gap-1.5 p-1 text-[#c084fc]">
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-current opacity-75"></span>
                      </div>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 3. FALSAFAH & NILAI KEARIFAN AIR PAYAKUMBUH              -->
          <!-- ======================================================== -->
          <section id="falsafah" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-slate-50 rounded-3xl border border-slate-200 p-8 sm:p-12 space-y-8">
              <div class="max-w-2xl space-y-2">
                <div class="flex items-center gap-2 text-cyan-800 text-xs font-bold uppercase">
                  <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>
                    <path d="M12 5 9.04 7.96a2.17 2.17 0 0 0 0 3.08c.82.82 2.13.85 3 .07l2.07-1.9a2.82 2.82 0 0 1 3.79 0l2.96 2.66"/>
                    <path d="m18 15-2-2"/>
                    <path d="m15 18-2-2"/>
                  </svg>
                  <span>Nilai Luhur &amp; Falsafah Air Minangkabau</span>
                </div>
                <h3 class="text-2xl font-bold text-slate-900">
                  “Aia Tagang Manyerak, Aia Janiah Manyanangkan”
                </h3>
                <p class="text-xs text-slate-600 leading-relaxed">
                  Kearifan lokal mengajarkan bahwa air bukan sekadar komoditas yang dibeli, melainkan amanah alam yang harus dijaga kejernihan dan kesucian alirannya bagi sesama warga.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-slate-200">
                <div class="space-y-2">
                  <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-cyan-600"></span>
                    <span>Tanggung Jawab Kolektif</span>
                  </h4>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    Pemborosan air di satu rumah tangga mengurangi cadangan tekanan pipa bagi warga di ujung jaringan pipa transmisi.
                  </p>
                </div>

                <div class="space-y-2">
                  <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-600"></span>
                    <span>Integritas Kejujuran Meteran</span>
                  </h4>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    Pembacaan meter mandiri berbasis AI-OCR melatih transparansi antara pelanggan dan pengelola air Tirta Sago.
                  </p>
                </div>

                <div class="space-y-2">
                  <h4 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Aksi Nyata Generasi Muda</span>
                  </h4>
                  <p class="text-xs text-slate-600 leading-relaxed">
                    Gen-Z Payakumbuh menjadi penggerak perubahan lewat misi harian dan deteksi kebocoran pipa di lingkungan sekitar.
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 4. MANIFESTO & IKRAR WARGA HEMAT AIR PAYAKUMBUH          -->
          <!-- ======================================================== -->
          <section id="manifesto" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-cyan-900 via-sky-950 to-slate-950 text-white rounded-3xl p-8 sm:p-12 space-y-8 relative overflow-hidden">
              <div class="relative z-10 max-w-2xl space-y-3">
                <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight">
                  Manifesto Komunitas Air Payakumbuh
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                  Komitmen moral bersama untuk menjaga keberlanjutan air bersih di rumah masing-masing:
                </p>
              </div>

              <!-- 4 Pledge Checkpoints -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4 relative z-10">
                <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-1">
                  <div class="flex items-center gap-2 font-semibold text-xs text-cyan-200">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    <span>1. Menutup Keran Saat Tidak Mengalirkan Faedah</span>
                  </div>
                  <p class="text-[11px] text-slate-400 pl-6">
                    Tidak membiarkan air mengalir bebas saat menggosok gigi, sabunan, atau berwudhu.
                  </p>
                </div>

                <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-1">
                  <div class="flex items-center gap-2 font-semibold text-xs text-cyan-200">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    <span>2. Melakukan Uji Kebocoran Meteran Berkala</span>
                  </div>
                  <p class="text-[11px] text-slate-400 pl-6">
                    Memeriksa jarum meteran air di malam hari sebulan sekali untuk mencegah silent leak.
                  </p>
                </div>

                <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-1">
                  <div class="flex items-center gap-2 font-semibold text-xs text-cyan-200">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    <span>3. Memanfaatkan Air Reused yang Aman</span>
                  </div>
                  <p class="text-[11px] text-slate-400 pl-6">
                    Menyiram pot tanaman dengan air cucian sayur/beras daripada menyia-nyiakan air minum.
                  </p>
                </div>

                <div class="p-4 rounded-xl bg-white/5 border border-white/10 space-y-1">
                  <div class="flex items-center gap-2 font-semibold text-xs text-cyan-200">
                    <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M20 6 9 17l-5-5"/>
                    </svg>
                    <span>4. Peduli Terhadap Kebocoran Pipa Publik</span>
                  </div>
                  <p class="text-[11px] text-slate-400 pl-6">
                    Segera melapor di portal Smart Water bila melihat kebocoran pipa air bersih di jalan.
                  </p>
                </div>
              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 5. TARGET PENCAPAIAN KOMUNITAS                           -->
          <!-- ======================================================== -->
          <section id="target-komunitas" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="text-center max-w-2xl mx-auto space-y-2">
              <span class="text-xs font-bold uppercase tracking-wider text-cyan-800">
                Tolok Ukur Keberhasilan
              </span>
              <h2 class="text-2xl font-bold text-slate-900">
                Target Capaian Kolektif Komunitas Payakumbuh
              </h2>
              <p class="text-xs text-slate-500">
                Indikator dampak nyata yang kita tuju bersama demi masa depan air kota kita.
              </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
              <?php foreach ($COMMUNITY_TARGETS as $target): ?>
                <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center space-y-2 shadow-xs">
                  <span class="text-3xl font-extrabold font-mono <?= e($target['color_class']) ?> tabular-nums">
                    <?= e($target['value']) ?>
                  </span>
                  <h4 class="text-xs font-bold text-slate-900">
                    <?= e($target['title']) ?>
                  </h4>
                  <p class="text-[11px] text-slate-500">
                    <?= e($target['desc']) ?>
                  </p>
                </div>
              <?php endforeach; ?>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 6. CALL-TO-ACTION (CTA) BANNER (YANG DI-SCREENSHOT USER) -->
          <!-- ======================================================== -->
          <section id="cta-gabung" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[36px] bg-gradient-to-r from-slate-900 via-sky-950 to-cyan-950 text-white p-8 sm:p-14 text-center space-y-6 shadow-2xl border border-slate-800">
              <!-- Decorative Circles -->
              <div class="absolute -top-24 -left-24 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>
              <div class="absolute -bottom-24 -right-24 w-72 h-72 bg-sky-500/15 rounded-full blur-3xl pointer-events-none"></div>

              <div class="max-w-2xl mx-auto space-y-3 relative z-10">
                <span class="text-xs font-bold font-mono text-cyan-400 uppercase tracking-widest block">
                  MARI BERKONTRIBUSI HARI INI
                </span>
                <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight">
                  Mulai Pengalaman Layanan Air Cerdas Bersama Smart Water
                </h2>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                  Catat stand meter mandiri, pantau rekening keluarga, dan nikmati kemudahan akses informasi publik dalam satu platform terpercaya.
                </p>
              </div>

              <div class="flex flex-wrap items-center justify-center gap-4 pt-2 relative z-10">
                <a
                  href="<?= url('/report-meter') ?>"
                  class="btn-shimmer relative px-8 py-3.5 rounded-2xl text-xs sm:text-sm font-extrabold text-slate-950 bg-cyan-400 hover:bg-cyan-300 shadow-xl shadow-cyan-400/25 transition-all hover:scale-105 cursor-pointer"
                >
                  Coba Lapor Meter Mandiri AI Sekarang &rarr;
                </a>
                <a
                  href="<?= url('/pusat-layanan') ?>"
                  class="px-7 py-3.5 rounded-2xl text-xs sm:text-sm font-bold text-white bg-white/10 hover:bg-white/20 border border-white/20 transition-all hover:scale-105 cursor-pointer backdrop-blur-md"
                >
                  Pusat Bantuan &amp; Pengaduan
                </a>
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
