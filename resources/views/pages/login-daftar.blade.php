<?php
/**
 * Halaman Login & Daftar - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 * Sesuai persis dengan src/components/auth/AuthPage.tsx + PayakumbuhIllustration.tsx
 * Lengkap dengan background kaca buram (frosted glass), 3 feature card blur, 4 mode form, dan modal legal.
 */
require_once $legacySharedPath . '/brand_logo.php';
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
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../../assets/css/shared.css" />
    <link rel="stylesheet" href="../../assets/css/pages/login-daftar.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="relative min-h-screen flex flex-col justify-between text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900 overflow-x-hidden">
      
      <!-- Full-Page Scenic Background with Ambient Frosted Gradients -->
      <div class="fixed inset-0 z-0 pointer-events-none select-none">
        <img
          src="../../assets/images/hero-lake-bright.jpg"
          alt="Lanskap Danau dan Sumber Air Alami Payakumbuh"
          class="w-full h-full object-cover object-center transform scale-105"
        />
        <div class="absolute inset-0 bg-gradient-to-tr from-[#EBF5FB]/88 via-white/80 to-[#F0F8FF]/85 backdrop-blur-[3px]"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-sky-500/15 blur-3xl pointer-events-none"></div>
      </div>

      <!-- ======================================================== -->
      <!-- 1. TOP HEADER NAVIGATION BAR                            -->
      <!-- ======================================================== -->
      <header class="relative z-10 w-full px-6 sm:px-10 lg:px-16 py-3.5 flex items-center justify-between border-b border-white/60 bg-white/70 backdrop-blur-md shadow-2xs">
        <div class="flex items-center">
          <?php renderBrandLogo('md', false, url('/beranda')); ?>
          <div class="hidden sm:block h-6 w-px bg-slate-300 mx-4"></div>
          <span class="hidden sm:inline-block text-xs text-slate-500 font-medium">
            Portal Layanan, Edukasi, dan Aksi Hemat Air
          </span>
        </div>

        <nav class="flex items-center gap-5 sm:gap-6 text-xs font-semibold text-slate-600">
          <a
            href="<?= url('/beranda') ?>"
            class="flex items-center gap-1.5 hover:text-[#0284C7] transition-colors"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            <span>Beranda</span>
          </a>
          <button
            type="button"
            onclick="openLegalModal('Pusat Bantuan & Layanan Air Payakumbuh')"
            class="flex items-center gap-1.5 hover:text-[#0284C7] transition-colors cursor-pointer"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>
            </svg>
            <span>Pusat Bantuan</span>
          </button>
        </nav>
      </header>



      <!-- ======================================================== -->
      <!-- 2. MAIN TWO-COLUMN SPLIT CONTAINER                      -->
      <!-- ======================================================== -->
      <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-5 sm:px-8 lg:px-12 pt-3 sm:pt-4 lg:pt-5 pb-8 lg:pb-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- LEFT COLUMN: HERO INFORMATION & SCENIC ILLUSTRATION -->
        <div class="lg:col-span-7 space-y-6">
          <div class="space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 backdrop-blur-md border border-white/80 text-[#0284C7] text-[11px] font-bold shadow-xs">
              <svg class="w-3.5 h-3.5 text-sky-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
                <path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>
              </svg>
              <span>Ekosistem Perumda Tirta Sago Kota Payakumbuh</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-[40px] font-black text-[#0F2C59] tracking-tight leading-[1.15] drop-shadow-xs">
              Selamat Datang di <br class="hidden sm:inline" />
              Smart Water
            </h1>
            <p class="text-sm sm:text-base text-slate-700 font-medium leading-relaxed max-w-xl">
              Bersama kita wujudkan masyarakat yang peduli air, melalui layanan yang mudah, edukasi yang bermanfaat, dan aksi nyata untuk masa depan yang lebih baik.
            </p>
          </div>

          <!-- Three Feature Cards in a Horizontal Row with Frosted Glass Effect -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
            <!-- Feature 1 -->
            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 hover:bg-white/95 hover:shadow-lg transition-all flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0284C7] to-sky-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                </svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">Akses Layanan</h4>
                <p class="text-[11px] text-slate-600 leading-tight">
                  Informasi, pengaduan, dan pelacakan layanan air.
                </p>
              </div>
            </div>

            <!-- Feature 2 -->
            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 hover:bg-white/95 hover:shadow-lg transition-all flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0284C7] to-sky-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                </svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">Belajar Bersama</h4>
                <p class="text-[11px] text-slate-600 leading-tight">
                  Materi interaktif dan simulasi penggunaan air.
                </p>
              </div>
            </div>

            <!-- Feature 3 -->
            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 hover:bg-white/95 hover:shadow-lg transition-all flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0284C7] to-sky-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/>
                </svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">Tantangan Hemat Air</h4>
                <p class="text-[11px] text-slate-600 leading-tight">
                  Ikuti misi, kumpulkan poin, dan raih badge.
                </p>
              </div>
            </div>
          </div>

          <!-- Payakumbuh Reservoir Dam & Smiling Youth Illustration -->
          <div class="pt-2">
            
<?php
/**
 * Ilustrasi Vektor Bendungan Payakumbuh, Rumah Gadang, & Warga
 * Sesuai persis dengan src/components/auth/PayakumbuhIllustration.tsx
 */
?>
<div class="relative w-full overflow-hidden rounded-3xl shadow-xl shadow-sky-950/10 border border-white/80 bg-white/40 backdrop-blur-md">
  <svg
    viewBox="0 0 860 480"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    class="w-full h-auto block select-none pointer-events-none"
    preserveAspectRatio="xMidYMid meet"
  >
    <defs>
      <linearGradient id="skyGrad" x1="430" y1="0" x2="430" y2="300" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#7DD3FC" />
        <stop offset="45%" stop-color="#BAE6FD" />
        <stop offset="100%" stop-color="#E0F2FE" />
      </linearGradient>

      <linearGradient id="mountainFar" x1="430" y1="120" x2="430" y2="280" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#7EBAE4" stop-opacity="0.8" />
        <stop offset="100%" stop-color="#A5D4F3" stop-opacity="0.4" />
      </linearGradient>
      <linearGradient id="mountainMid" x1="430" y1="140" x2="430" y2="300" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#4F9EC9" />
        <stop offset="60%" stop-color="#68B4DC" />
        <stop offset="100%" stop-color="#9BD1EC" />
      </linearGradient>
      <linearGradient id="mountainNear" x1="430" y1="170" x2="430" y2="320" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#2E7E62" />
        <stop offset="50%" stop-color="#3C987A" />
        <stop offset="100%" stop-color="#67BFA0" />
      </linearGradient>

      <linearGradient id="waterGrad" x1="430" y1="280" x2="430" y2="480" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#38BDF8" />
        <stop offset="35%" stop-color="#0EA5E9" />
        <stop offset="70%" stop-color="#0284C7" />
        <stop offset="100%" stop-color="#0369A1" />
      </linearGradient>

      <linearGradient id="damConcrete" x1="480" y1="240" x2="780" y2="340" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#E2E8F0" />
        <stop offset="50%" stop-color="#CBD5E1" />
        <stop offset="100%" stop-color="#94A3B8" />
      </linearGradient>

      <linearGradient id="waterSpill" x1="600" y1="280" x2="600" y2="370" gradientUnits="userSpaceOnUse">
        <stop offset="0%" stop-color="#E0F2FE" />
        <stop offset="40%" stop-color="#BAE6FD" />
        <stop offset="80%" stop-color="#38BDF8" />
        <stop offset="100%" stop-color="#0284C7" />
      </linearGradient>

      <linearGradient id="skinGrad" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#FBD38D" />
        <stop offset="100%" stop-color="#F6AD55" />
      </linearGradient>
      <linearGradient id="hoodieBlue" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#38BDF8" />
        <stop offset="100%" stop-color="#0284C7" />
      </linearGradient>
      <linearGradient id="jacketCyan" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#06B6D4" />
        <stop offset="100%" stop-color="#0891B2" />
      </linearGradient>
      <linearGradient id="shirtTeal" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#2DD4BF" />
        <stop offset="100%" stop-color="#0D9488" />
      </linearGradient>

      <linearGradient id="leafGrad1" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#22C55E" />
        <stop offset="100%" stop-color="#15803D" />
      </linearGradient>
      <linearGradient id="leafGrad2" x1="0" y1="0" x2="1" y2="1">
        <stop offset="0%" stop-color="#4ADE80" />
        <stop offset="100%" stop-color="#16A34A" />
      </linearGradient>
    </defs>

    <!-- 1. SKY -->
    <rect width="860" height="480" fill="url(#skyGrad)" />

    <!-- Soft Fluffy Clouds -->
    <path d="M-20 110 Q40 80 120 95 Q180 60 260 85 Q320 70 380 90 Q430 80 470 100 Q490 120 460 140 L-20 140 Z" fill="white" fill-opacity="0.55" />
    <path d="M350 70 Q410 40 480 55 Q540 25 610 50 Q660 35 720 55 Q770 45 810 65 L860 70 L860 110 L350 110 Z" fill="white" fill-opacity="0.5" />
    <path d="M60 150 Q140 125 240 140 Q310 120 390 145 Q440 135 490 150 L60 150 Z" fill="white" fill-opacity="0.4" />

    <!-- 2. DISTANT MOUNTAIN RIDGE (Gunung Sago) -->
    <path
      d="M0 240 L60 215 L150 185 L260 150 L340 170 L430 135 L520 165 L610 130 L700 175 L800 190 L860 205 L860 320 L0 320 Z"
      fill="url(#mountainFar)"
    />
    <path
      d="M0 260 L80 230 L180 190 L270 205 L350 160 L430 180 L540 145 L640 175 L730 160 L820 185 L860 200 L860 340 L0 340 Z"
      fill="url(#mountainMid)"
    />

    <!-- 3. GREEN HILLS & CITYSCAPE -->
    <path
      d="M180 270 Q280 220 390 240 Q490 225 590 245 Q690 220 800 245 L860 255 L860 340 L180 340 Z"
      fill="url(#mountainNear)"
    />

    <!-- Traditional Minangkabau Rumah Gadang Roofs (Gonjong) -->
    <g transform="translate(340, 220) scale(0.65)">
      <rect x="20" y="45" width="80" height="25" rx="3" fill="#FFFBEB" stroke="#92400E" stroke-width="1.5" />
      <rect x="25" y="50" width="10" height="15" fill="#78350F" />
      <rect x="42" y="50" width="10" height="15" fill="#78350F" />
      <rect x="60" y="50" width="10" height="15" fill="#78350F" />
      <rect x="78" y="50" width="10" height="15" fill="#78350F" />
      <path
        d="M5 45 Q30 35 40 15 Q43 25 60 30 Q77 25 80 15 Q90 35 115 45 Q60 40 5 45 Z"
        fill="#B91C1C"
        stroke="#7F1D1D"
        stroke-width="2"
      />
      <path
        d="M-5 46 Q20 30 25 8 Q32 24 60 25 Q88 24 95 8 Q100 30 125 46 Q60 38 -5 46 Z"
        fill="#991B1B"
      />
    </g>

    <g transform="translate(430, 200) scale(0.9)">
      <rect x="25" y="48" width="110" height="32" rx="4" fill="#FEF3C7" stroke="#78350F" stroke-width="2" />
      <rect x="35" y="55" width="12" height="18" rx="2" fill="#92400E" />
      <rect x="55" y="55" width="12" height="18" rx="2" fill="#92400E" />
      <rect x="75" y="55" width="12" height="18" rx="2" fill="#92400E" />
      <rect x="95" y="55" width="12" height="18" rx="2" fill="#92400E" />
      <rect x="115" y="55" width="12" height="18" rx="2" fill="#92400E" />
      <path
        d="M10 50 Q40 40 48 10 Q58 35 80 32 Q102 35 112 10 Q120 40 150 50 Q80 44 10 50 Z"
        fill="#7F1D1D"
        stroke="#450A0A"
        stroke-width="2"
      />
      <path
        d="M0 52 Q35 32 38 0 Q50 30 80 26 Q110 30 122 0 Q125 32 160 52 Q80 42 0 52 Z"
        fill="#991B1B"
      />
      <circle cx="38" cy="0" r="2.5" fill="#FBBF24" />
      <circle cx="122" cy="0" r="2.5" fill="#FBBF24" />
    </g>

    <!-- City Buildings -->
    <rect x="580" y="235" width="22" height="35" fill="#E2E8F0" />
    <rect x="605" y="225" width="28" height="45" fill="#CBD5E1" />
    <rect x="635" y="240" width="18" height="30" fill="#E2E8F0" />
    <rect x="655" y="230" width="24" height="40" fill="#CBD5E1" />

    <!-- 4. WATER RESERVOIR & DAM INFRASTRUCTURE -->
    <path
      d="M0 320 C180 310 350 300 480 305 L860 305 L860 480 L0 480 Z"
      fill="url(#waterGrad)"
    />

    <g id="damStructure">
      <path
        d="M450 305 L790 305 L790 318 L450 318 Z"
        fill="#94A3B8"
        stroke="#64748B"
        stroke-width="1.5"
      />
      <path
        d="M450 298 L790 298"
        stroke="#E2E8F0"
        stroke-width="2"
        stroke-dasharray="4 3"
      />
      <path
        d="M470 305 L470 345 L500 345 L500 305 Z
           M520 305 L520 345 L550 345 L550 305 Z
           M570 305 L570 345 L600 345 L600 305 Z
           M620 305 L620 345 L650 345 L650 305 Z
           M670 305 L670 345 L700 345 L700 305 Z
           M720 305 L720 345 L750 345 L750 305 Z"
        fill="url(#damConcrete)"
        stroke="#64748B"
        stroke-width="1.5"
      />
      <path
        d="M500 318 Q510 335 510 370 Q515 375 520 318 Z
           M550 318 Q560 335 560 370 Q565 375 570 318 Z
           M600 318 Q610 335 610 370 Q615 375 620 318 Z
           M650 318 Q660 335 660 370 Q665 375 670 318 Z
           M700 318 Q710 335 710 370 Q715 375 720 318 Z"
        fill="url(#waterSpill)"
      />
      <ellipse cx="510" cy="370" rx="16" ry="7" fill="white" fill-opacity="0.85" />
      <ellipse cx="560" cy="370" rx="16" ry="7" fill="white" fill-opacity="0.85" />
      <ellipse cx="610" cy="370" rx="16" ry="7" fill="white" fill-opacity="0.85" />
      <ellipse cx="660" cy="370" rx="16" ry="7" fill="white" fill-opacity="0.85" />
      <ellipse cx="710" cy="370" rx="16" ry="7" fill="white" fill-opacity="0.85" />
    </g>

    <!-- Water Surface Ripples -->
    <g stroke="white" stroke-width="1.5" stroke-linecap="round" stroke-opacity="0.45">
      <path d="M260 340 Q310 336 360 340" />
      <path d="M380 355 Q450 350 520 355" />
      <path d="M190 370 Q270 365 350 370" />
      <path d="M280 395 Q380 390 480 395" />
      <path d="M340 425 Q460 418 580 425" />
      <path d="M420 445 Q530 440 640 445" />
    </g>

    <!-- 5. WHITE CURSIVE CALLIGRAPHY ON WATER -->
    <g transform="translate(420, 395) rotate(-3)">
      <text
        x="0"
        y="0"
        fill="white"
        fill-opacity="0.95"
        font-size="26"
        font-family="'Caveat', 'Segoe Script', 'Brush Script MT', cursive"
        font-weight="bold"
        filter="drop-shadow(0 2px 4px rgba(2, 44, 89, 0.45))"
      >
        Air Bersih
      </text>
      <text
        x="-10"
        y="26"
        fill="white"
        fill-opacity="0.95"
        font-size="24"
        font-family="'Caveat', 'Segoe Script', 'Brush Script MT', cursive"
        font-weight="bold"
        filter="drop-shadow(0 2px 4px rgba(2, 44, 89, 0.45))"
      >
        untuk Payakumbuh
      </text>
      <text
        x="20"
        y="52"
        fill="white"
        fill-opacity="0.95"
        font-size="24"
        font-family="'Caveat', 'Segoe Script', 'Brush Script MT', cursive"
        font-weight="bold"
        filter="drop-shadow(0 2px 4px rgba(2, 44, 89, 0.45))"
      >
        yang Lebih Baik
      </text>
    </g>

    <!-- 6. CHARACTERS IN FOREGROUND -->
    <g id="charMan" transform="translate(20, 240)">
      <path d="M45 100 Q40 140 35 180" stroke="#1E293B" stroke-width="7" stroke-linecap="round" />
      <path d="M20 120 C10 160 5 210 0 240 L110 240 C110 210 105 160 95 120 Z" fill="url(#hoodieBlue)" />
      <path d="M45 110 Q58 125 70 110 L70 85 Q58 92 45 85 Z" fill="#F6AD55" />
      <path d="M38 105 Q58 135 78 105" stroke="white" stroke-width="3" fill="none" />
      <ellipse cx="58" cy="65" rx="22" ry="26" fill="#FBD38D" />
      <path d="M36 58 C36 30 50 25 65 25 C80 25 85 40 85 58 C80 50 72 45 60 45 C48 45 40 52 36 58 Z" fill="#1E293B" />
      <path d="M38 52 Q44 60 46 68 Q50 56 60 54 Q68 56 78 50 Q75 40 65 42" fill="#0F172A" />
      <ellipse cx="64" cy="62" rx="3" ry="3.5" fill="#0F172A" />
      <path d="M58 75 Q65 82 72 75" stroke="#9A3412" stroke-width="2" stroke-linecap="round" fill="none" />
    </g>

    <g id="charWoman" transform="translate(100, 260)">
      <path d="M15 110 C5 150 0 190 -10 220 L95 220 C90 190 85 145 75 110 Z" fill="url(#jacketCyan)" />
      <path d="M35 110 Q45 140 55 110 Z" fill="white" />
      <path d="M38 95 L52 95 L52 110 L38 110 Z" fill="#FBD38D" />
      <ellipse cx="45" cy="85" rx="18" ry="21" fill="#FBD38D" />
      <path d="M25 85 C25 60 35 55 50 55 C65 55 70 70 72 105 C65 95 60 90 50 90 C40 90 32 98 25 85 Z" fill="#1E1B4B" />
      <ellipse cx="50" cy="83" rx="2.5" ry="3" fill="#0F172A" />
      <path d="M42 94 Q48 100 54 94" stroke="#C2410C" stroke-width="1.8" stroke-linecap="round" fill="none" />
      <ellipse cx="45" cy="60" rx="36" ry="12" fill="#FEF08A" stroke="#CA8A04" stroke-width="1.5" />
      <path d="M22 60 C22 42 32 38 45 38 C58 38 68 42 68 60 Z" fill="#FDE047" stroke="#CA8A04" stroke-width="1.5" />
      <path d="M22 60 Q45 64 68 60" stroke="#0284C7" stroke-width="3" fill="none" />
    </g>

    <g id="charGirl" transform="translate(35, 320)">
      <path d="M10 90 C5 120 0 150 -5 160 L75 160 C70 140 68 115 60 90 Z" fill="white" stroke="#E2E8F0" stroke-width="1.5" />
      <path d="M15 100 L25 160 M55 100 L45 160" stroke="#0284C7" stroke-width="4" />
      <ellipse cx="35" cy="68" rx="19" ry="20" fill="#FED7AA" />
      <path d="M15 65 C15 40 25 35 38 35 C50 35 60 45 60 65 C55 58 48 52 38 52 C28 52 20 60 15 65 Z" fill="#0F172A" />
      <path d="M18 52 Q38 42 58 52" stroke="#38BDF8" stroke-width="4" stroke-linecap="round" fill="none" />
      <ellipse cx="28" cy="68" rx="3.5" ry="4" fill="#0F172A" />
      <circle cx="27" cy="66" r="1.2" fill="white" />
      <ellipse cx="44" cy="68" rx="3.5" ry="4" fill="#0F172A" />
      <circle cx="43" cy="66" r="1.2" fill="white" />
      <path d="M30 76 Q36 84 42 76" stroke="#BE123C" stroke-width="2" stroke-linecap="round" fill="#F43F5E" />
      <ellipse cx="22" cy="74" rx="4" ry="2" fill="#FDA4AF" fill-opacity="0.6" />
      <ellipse cx="49" cy="74" rx="4" ry="2" fill="#FDA4AF" fill-opacity="0.6" />
    </g>

    <g id="charBoy" transform="translate(130, 310)">
      <path d="M15 95 C8 130 0 160 -5 170 L95 170 C90 150 85 125 75 95 Z" fill="url(#hoodieBlue)" />
      <ellipse cx="45" cy="70" rx="19" ry="20" fill="#FED7AA" />
      <ellipse cx="38" cy="68" rx="3" ry="3.5" fill="#0F172A" />
      <circle cx="37" cy="66" r="1" fill="white" />
      <ellipse cx="53" cy="68" rx="3" ry="3.5" fill="#0F172A" />
      <circle cx="52" cy="66" r="1" fill="white" />
      <path d="M39 77 Q46 87 54 77" stroke="#BE123C" stroke-width="2" stroke-linecap="round" fill="#F43F5E" />
      <path d="M25 60 C25 40 35 35 48 35 C60 35 70 45 70 60 Z" fill="white" stroke="#CBD5E1" stroke-width="1.5" />
      <path d="M45 42 Q75 36 90 48" stroke="#0284C7" stroke-width="6" stroke-linecap="round" />
      <path d="M75 110 Q110 85 140 68" stroke="url(#hoodieBlue)" stroke-width="16" stroke-linecap="round" />
      <path d="M135 70 L158 52" stroke="#FED7AA" stroke-width="11" stroke-linecap="round" />
      <path d="M154 54 L170 42" stroke="#FED7AA" stroke-width="5" stroke-linecap="round" />
    </g>

    <!-- 7. LUSH TROPICAL FOLIAGE -->
    <g id="tropicalLeavesLeft" transform="translate(-30, 360)">
      <path d="M0 120 C20 60 70 40 120 70 C100 80 80 85 75 95 C110 90 125 110 110 120 Z" fill="url(#leafGrad1)" />
      <path d="M30 120 C50 70 100 50 140 85 C125 95 105 100 100 110 C130 105 145 125 125 140 Z" fill="url(#leafGrad2)" />
      <path d="M-10 80 C15 30 65 10 95 40 C80 50 65 55 60 65 C85 60 95 75 80 90 Z" fill="#15803D" />
    </g>

    <g id="tropicalLeavesCenter" transform="translate(220, 420)">
      <path d="M30 60 C50 10 90 0 120 25 C105 32 90 35 85 45 C110 40 120 55 105 65 Z" fill="url(#leafGrad1)" />
      <path d="M100 60 C120 15 160 5 190 30 C175 38 160 40 155 50 C180 45 190 60 175 70 Z" fill="url(#leafGrad2)" />
    </g>

    <g id="tropicalLeavesRight" transform="translate(740, 400)">
      <path d="M120 80 C80 30 40 20 0 50 C20 58 35 62 40 70 C15 68 5 80 20 90 Z" fill="url(#leafGrad1)" />
      <path d="M140 100 C100 45 60 35 20 65 C40 72 55 75 60 85 C35 82 25 95 40 105 Z" fill="url(#leafGrad2)" />
    </g>
  </svg>
</div>


          </div>
        </div>

        <!-- RIGHT COLUMN: FLOATING AUTHENTICATION CARD WITH FROSTED GLASS EFFECT -->
        <div class="lg:col-span-5 flex justify-center">
          <div class="w-full max-w-[460px] bg-white/85 backdrop-blur-xl rounded-[32px] p-7 sm:p-9 shadow-2xl shadow-sky-950/15 border border-white/80 relative">
            
            <!-- Top Card Logo -->
            <div class="flex justify-start">
              <?php renderBrandLogo('md', false, url('/beranda')); ?>
            </div>

            <!-- ============================================== -->
            <!-- SEGMENTED TABS: Masuk | Daftar Akun            -->
            <!-- ============================================== -->
            <div class="grid grid-cols-2 p-1 rounded-2xl bg-slate-100/90 border border-slate-200/80 mt-5 mb-1 text-center text-xs font-bold select-none shadow-2xs">
              <button
                type="button"
                id="tab-btn-login"
                onclick="switchAuthMode('login')"
                class="py-2.5 px-3 rounded-xl transition-all cursor-pointer bg-white text-slate-900 shadow-xs font-bold"
              >
                Masuk
              </button>
              <button
                type="button"
                id="tab-btn-register"
                onclick="switchAuthMode('register')"
                class="py-2.5 px-3 rounded-xl transition-all cursor-pointer text-slate-500 hover:text-slate-900 font-semibold"
              >
                Daftar Akun
              </button>
            </div>

            <!-- ============================================== -->
            <!-- MODE 1: LOGIN (Masuk ke Akun Anda)            -->
            <!-- ============================================== -->
            <div id="auth-mode-login" class="mt-5 space-y-5 animate-fade-zoom">
              <div class="space-y-1">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                  Masuk ke Akun Anda
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                  Silakan login untuk mengakses layanan, edukasi, dan tantangan hemat air.
                </p>
              </div>

              <div id="login-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <span id="login-error-text"></span>
              </div>

              <form onsubmit="handleLoginSubmit(event)" class="space-y-4">
                <div>
                  <label for="login-identifier" class="text-xs font-semibold text-slate-700 block mb-1.5">
                    Email atau Nomor Telepon
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <input
                      id="login-identifier"
                      type="text"
                      placeholder="Masukkan email atau nomor telepon"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                      autocomplete="username"
                    />
                  </div>
                </div>

                <div>
                  <label for="login-password" class="text-xs font-semibold text-slate-700 block mb-1.5">
                    Password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input
                      id="login-password"
                      type="password"
                      placeholder="Masukkan password"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-10 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                      autocomplete="current-password"
                    />
                    <button
                      type="button"
                      onclick="togglePasswordVisibility('login-password', 'login-eye-open', 'login-eye-closed')"
                      class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                      tabindex="-1"
                      aria-label="Toggle Password Visibility"
                    >
                      <svg id="login-eye-open" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                      <svg id="login-eye-closed" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                  </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-0.5">
                  <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600 font-medium">
                    <input
                      type="checkbox"
                      checked
                      class="w-4 h-4 rounded text-[#0284C7] border-slate-300 focus:ring-[#0284C7] focus:ring-offset-0"
                    />
                    <span>Ingat saya</span>
                  </label>

                  <button
                    type="button"
                    onclick="switchAuthMode('forgot-password')"
                    class="font-semibold text-[#0284C7] hover:underline cursor-pointer"
                  >
                    Lupa password?
                  </button>
                </div>

                <button
                  id="login-submit-btn"
                  type="submit"
                  class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-all shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 cursor-pointer"
                >
                  <span>Masuk</span>
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
              </form>

              <div class="relative flex items-center justify-center my-3">
                <div class="w-full border-t border-slate-200"></div>
                <span class="bg-white/90 px-3 text-[11px] text-slate-400 font-medium whitespace-nowrap rounded-full">
                  atau masuk dengan
                </span>
              </div>

              <button
                type="button"
                onclick="handleGoogleSignIn()"
                class="w-full py-3 px-4 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors flex items-center justify-center gap-2.5 shadow-2xs cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Lanjut dengan Google</span>
              </button>

              <div class="text-center text-xs text-slate-500 pt-1">
                <span>Belum punya akun? </span>
                <button
                  type="button"
                  onclick="switchAuthMode('register')"
                  class="font-bold text-[#0284C7] hover:underline cursor-pointer"
                >
                  Daftar sekarang
                </button>
              </div>
            </div>

            <!-- ============================================== -->
            <!-- MODE 2: REGISTER (Bergabung / Buat Akun)      -->
            <!-- ============================================== -->
            <div id="auth-mode-register" class="hidden mt-5 space-y-4 animate-fade-zoom">
              <div class="space-y-1">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                  Bergabung dengan Smart Water
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                  Mulai belajar, ikuti tantangan hemat air, dan akses layanan masyarakat.
                </p>
              </div>

              <div id="reg-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <span id="reg-error-text"></span>
              </div>

              <form onsubmit="handleRegisterSubmit(event)" class="space-y-3.5">
                <div>
                  <label for="reg-name" class="text-xs font-semibold text-slate-700 block mb-1">
                    Nama lengkap
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <input
                      id="reg-name"
                      type="text"
                      placeholder="Masukkan nama lengkap Anda"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                    />
                  </div>
                </div>

                <div>
                  <label for="reg-email" class="text-xs font-semibold text-slate-700 block mb-1">
                    Email
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <input
                      id="reg-email"
                      type="email"
                      placeholder="nama@email.com"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                    />
                  </div>
                </div>

                <div>
                  <label for="reg-phone" class="text-xs font-semibold text-slate-700 block mb-1">
                    Nomor HP <span class="text-slate-400 font-normal">(opsional)</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <input
                      id="reg-phone"
                      type="tel"
                      placeholder="0812-xxxx-xxxx"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                    />
                  </div>
                </div>

                <div>
                  <label for="reg-password" class="text-xs font-semibold text-slate-700 block mb-1">
                    Password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input
                      id="reg-password"
                      type="password"
                      placeholder="Minimal 6 karakter"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-10 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                    />
                    <button
                      type="button"
                      onclick="togglePasswordVisibility('reg-password', 'reg-eye-open', 'reg-eye-closed')"
                      class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                      tabindex="-1"
                    >
                      <svg id="reg-eye-open" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                      <svg id="reg-eye-closed" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                  </div>
                </div>

                <div>
                  <label for="reg-confirm-password" class="text-xs font-semibold text-slate-700 block mb-1">
                    Konfirmasi password
                  </label>
                  <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <input
                      id="reg-confirm-password"
                      type="password"
                      placeholder="Ulangi kata sandi"
                      class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-10 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                    />
                    <button
                      type="button"
                      onclick="togglePasswordVisibility('reg-confirm-password', 'reg2-eye-open', 'reg2-eye-closed')"
                      class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors cursor-pointer"
                      tabindex="-1"
                    >
                      <svg id="reg2-eye-open" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                      <svg id="reg2-eye-closed" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/></svg>
                    </button>
                  </div>
                </div>

                <label class="flex items-start gap-2 cursor-pointer select-none text-[11px] text-slate-600 pt-1">
                  <input
                    id="reg-agree-terms"
                    type="checkbox"
                    checked
                    class="w-4 h-4 mt-0.5 rounded text-[#0284C7] border-slate-300 focus:ring-[#0284C7] shrink-0"
                  />
                  <span class="leading-tight">
                    Saya menyetujui
                    <button
                      type="button"
                      onclick="event.preventDefault(); openLegalModal('Syarat & Ketentuan Layanan');"
                      class="text-[#0284C7] font-semibold underline hover:text-[#0369A1] cursor-pointer"
                    >
                      Syarat &amp; Ketentuan
                    </button>
                    dan
                    <button
                      type="button"
                      onclick="event.preventDefault(); openLegalModal('Kebijakan Privasi Pengguna');"
                      class="text-[#0284C7] font-semibold underline hover:text-[#0369A1] cursor-pointer"
                    >
                      Kebijakan Privasi
                    </button>
                  </span>
                </label>

                <button
                  id="reg-submit-btn"
                  type="submit"
                  class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-all shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 cursor-pointer"
                >
                  <span>Buat Akun</span>
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
              </form>

              <div class="relative flex items-center justify-center my-2.5">
                <div class="w-full border-t border-slate-200"></div>
                <span class="bg-white/90 px-3 text-[11px] text-slate-400 font-medium whitespace-nowrap rounded-full">
                  atau daftar dengan
                </span>
              </div>

              <button
                type="button"
                onclick="handleGoogleSignIn()"
                class="w-full py-2.5 px-4 rounded-xl text-xs font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-colors flex items-center justify-center gap-2.5 shadow-2xs cursor-pointer"
              >
                <svg class="w-4 h-4" viewBox="0 0 24 24">
                  <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                  <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                  <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/>
                  <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/>
                </svg>
                <span>Lanjut dengan Google</span>
              </button>

              <div class="text-center text-xs text-slate-500 pt-0.5">
                <span>Sudah punya akun? </span>
                <button
                  type="button"
                  onclick="switchAuthMode('login')"
                  class="font-bold text-[#0284C7] hover:underline cursor-pointer"
                >
                  Masuk
                </button>
              </div>
            </div>

            <!-- ============================================== -->
            <!-- MODE 3: FORGOT PASSWORD (Lupa password?)       -->
            <!-- ============================================== -->
            <div id="auth-mode-forgot-password" class="hidden mt-5 space-y-5 animate-fade-zoom">
              <div class="space-y-1">
                <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                  Lupa password?
                </h2>
                <p class="text-xs text-slate-500 leading-relaxed">
                  Jangan khawatir. Kami akan mengirimkan tautan untuk mengatur ulang password ke email kamu.
                </p>
              </div>

              <div id="forgot-success-wrap" class="hidden space-y-4 pt-2">
                <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 space-y-2 text-xs">
                  <div class="flex items-center gap-2 font-bold text-emerald-800">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                    <span>Tautan Terkirim!</span>
                  </div>
                  <p class="text-[11px] leading-relaxed text-emerald-800">
                    Instruksi pemulihan telah dikirim ke <strong id="forgot-success-email" class="font-mono"></strong>. Periksa kotak masuk atau folder spam Anda.
                  </p>
                </div>

                <button
                  type="button"
                  onclick="switchAuthMode('login')"
                  class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-all shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 cursor-pointer"
                >
                  <span>Kembali ke Halaman Masuk</span>
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                </button>
              </div>

              <div id="forgot-form-wrap" class="space-y-4">
                <div id="forgot-error" class="hidden p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                  <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                  <span id="forgot-error-text"></span>
                </div>

                <form onsubmit="handleForgotSubmit(event)" class="space-y-4">
                  <div>
                    <label for="forgot-email" class="text-xs font-semibold text-slate-700 block mb-1.5">
                      Email terdaftar
                    </label>
                    <div class="relative">
                      <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                      </div>
                      <input
                        id="forgot-email"
                        type="email"
                        placeholder="Masukkan email terdaftar Anda"
                        class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-3 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                      />
                    </div>
                  </div>

                  <div class="p-3 rounded-xl bg-sky-50 border border-sky-100 text-[11px] text-sky-900 flex items-start gap-2">
                    <svg class="w-4 h-4 text-[#0284C7] shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                    <span class="leading-tight">
                      Tautan reset akan dikirimkan ke email terdaftar Anda dan berlaku selama 15 menit demi keamanan akun.
                    </span>
                  </div>

                  <button
                    type="submit"
                    class="w-full py-3.5 px-4 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-all shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 cursor-pointer"
                  >
                    <span>Kirim Link Reset</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                  </button>
                </form>

                <div class="pt-2 text-center">
                  <button
                    type="button"
                    onclick="switchAuthMode('login')"
                    class="inline-flex items-center gap-1.5 text-xs font-bold text-[#0284C7] hover:underline cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    <span>Kembali ke halaman masuk</span>
                  </button>
                </div>
              </div>
            </div>

          </div>
        </div>
      </main>

      <!-- ======================================================== -->
      <!-- 3. BOTTOM FOOTER BAR                                    -->
      <!-- ======================================================== -->
      <footer class="relative z-10 w-full px-6 sm:px-10 lg:px-16 py-4 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-600 border-t border-white/60 bg-white/70 backdrop-blur-md shadow-2xs">
        <div class="flex items-center gap-1.5 font-medium">
          <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/>
          </svg>
          <span><?= e($APP_META['city']) ?></span>
        </div>

        <div class="flex items-center gap-3 sm:gap-4">
          <button
            type="button"
            onclick="openLegalModal('Kebijakan Privasi')"
            class="hover:text-slate-800 transition-colors cursor-pointer"
          >
            Kebijakan Privasi
          </button>
          <span>|</span>
          <button
            type="button"
            onclick="openLegalModal('Syarat & Ketentuan')"
            class="hover:text-slate-800 transition-colors cursor-pointer"
          >
            Syarat &amp; Ketentuan
          </button>
          <span>|</span>
          <button
            type="button"
            onclick="openLegalModal('Kontak Layanan')"
            class="hover:text-slate-800 transition-colors cursor-pointer"
          >
            Kontak
          </button>
        </div>
      </footer>

      <!-- Legal & Help Info Modal -->
      <div id="auth-legal-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 space-y-4 border border-slate-200 shadow-2xl relative animate-fade-zoom">
          <button
            type="button"
            onclick="closeLegalModal()"
            class="absolute top-5 right-5 p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer"
            aria-label="Tutup modal"
          >
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          </button>

          <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
            <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center font-bold">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            </div>
            <h3 id="auth-legal-modal-title" class="text-base font-bold text-slate-900">Informasi</h3>
          </div>

          <div class="text-xs text-slate-600 space-y-2.5 max-h-64 overflow-y-auto leading-relaxed">
            <p>
              Platform Smart Water dikelola untuk mendukung warga Kota Payakumbuh dalam memantau penggunaan air bersih, menyampaikan pengaduan layanan pelanggan secara transparan, serta mengedukasi masyarakat tentang konservasi air.
            </p>
            <p>
              Data pribadi pelanggan (nomor meteran, nomor kontak, dan riwayat kubikasi) dilindungi dengan standar keamanan privasi dan hanya digunakan untuk keperluan layanan resmi PDAM Tirta Sago Kota Payakumbuh.
            </p>
            <p class="font-semibold text-slate-700">
              Layanan Pelanggan Darurat: <?= e($APP_META['call_center']) ?> (TRC Payakumbuh)
            </p>
          </div>

          <button
            type="button"
            onclick="closeLegalModal()"
            class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
          >
            Tutup
          </button>
        </div>
      </div>

      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <script>
      window.SWC_INITIAL_MODE = <?= json_encode($initialMode) ?>;
      window.SWC_CURRENT_USER = <?= json_encode($currentUser, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="../../assets/js/pages/login-daftar.js"></script>
  </body>
</html>
