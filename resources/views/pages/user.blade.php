<?php
/**
 * Halaman User / Profil Pelanggan - Smart Water Kota Payakumbuh
 * Versi PHP Standalone lengkap dari src/components/profile/ProfileView.tsx + Guest View src/App.tsx
 */

require_once $legacySharedPath . '/brand_logo.php';

$userComplaints = [];
$report         = null;
$completedCoursesCount = 0;
$totalCourses   = count($ACADEMY_COURSES_SUMMARY);
$userBadges     = [];
$privacy        = [];
$billCalc       = null;

if ($currentUser) {
    $userComplaints = array_values(array_filter(
        $allComplaints,
        fn($c) => ($c['userId'] ?? '') === $currentUser['id']
    ));
    $report = $currentUser['personalWaterReport'];
    $completedCoursesCount = count($currentUser['completedQuizzes'] ?? []);
    $userBadges = $currentUser['badges'] ?? [];
    $privacy = $currentUser['privacySettings'] ?? [
        'showInLeaderboard' => true,
        'waNotifications'   => true,
        'twoFactorAuth'     => true,
    ];
    $billCalc = calculateGolongan2ABill((float) ($report['currentMonthM3'] ?? 9.4));
}
?>
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
  <base href="{{ $legacyBaseHref }}" />
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= e($APP_META['title']) ?></title>
  <meta name="description" content="<?= e($APP_META['description']) ?>" />

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS v4 Browser CDN -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
  <link rel="stylesheet" href="../../assets/css/shared.css" />
  <link rel="stylesheet" href="../../assets/css/pages/user.css" />
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex flex-col">

  <!-- Top Header Navbar -->
  <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleUserSiteNav',
    'logoHref' => url('/beranda'),
    'activeNav' => false,
    'showAdmin' => true,
    'showAdminMobile' => true,
    'showLogout' => true,
    'profileLink' => true,
    'profileActive' => true,
    'directLoginLink' => url('/login'),
    'mobileAuthMode' => 'never',
    'mobileProfileAlways' => true,
];
require $legacySharedPath . '/header.php';
?>

  <!-- Toast Notification -->
  <div id="profileToast" class="hidden fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 text-xs font-semibold animate-slide-down border border-slate-700/80">
    <svg class="w-4 h-4 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span id="profileToastMsg">Perubahan berhasil disimpan!</span>
  </div>

  <?php if ($currentUser): ?>
    <!-- ======================================================== -->
    <!-- HALAMAN PROFIL PELANGGAN (SUDAH LOGIN)                   -->
    <!-- ======================================================== -->
    <main class="flex-1 bg-[#F0F6FB] py-6 sm:py-8 px-4 sm:px-6 lg:px-8">
      <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-6">

        <!-- LEFT SIDEBAR -->
        <aside class="w-full lg:w-64 shrink-0 space-y-4">
          <!-- User Mini Card -->
          <div class="bg-white p-4 rounded-2xl border border-sky-100/90 shadow-2xs space-y-2">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-full bg-[#0284C7] flex items-center justify-center text-white text-base font-bold shrink-0 shadow-xs">
                <svg class="w-5 h-5 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              </div>
              <div class="overflow-hidden">
                <h3 class="text-sm font-bold text-[#0F2C59] truncate leading-tight"><?= e($currentUser['name']) ?></h3>
                <div class="flex items-center gap-1.5 mt-1">
                  <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-semibold">
                    <svg class="w-2.5 h-2.5 text-emerald-600 stroke-[3]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Pelanggan Terverifikasi</span>
                  </span>
                  <span class="px-1.5 py-0.5 rounded-full bg-slate-100 text-slate-600 text-[10px] font-bold">2A</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Links -->
          <div class="bg-white p-2.5 rounded-2xl border border-sky-100/90 shadow-2xs space-y-1">
            <button type="button" data-ptab="beranda" onclick="switchProfileTab('beranda')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer bg-[#E3EFFB] text-[#0284C7] font-bold shadow-2xs">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                <span>Beranda</span>
              </div>
            </button>

            <button type="button" data-ptab="info" onclick="switchProfileTab('info')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="text-left">Informasi Akun &amp; Data Pelanggan</span>
              </div>
            </button>

            <button type="button" data-ptab="laporan" onclick="switchProfileTab('laporan')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                <span class="text-left">Laporan Pemakaian Air Pribadi</span>
              </div>
            </button>

            <button type="button" data-ptab="pengaduan" onclick="switchProfileTab('pengaduan')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Riwayat Pengaduan</span>
              </div>
              <span class="bind-complaints-count w-5 h-5 rounded-full bg-[#0284C7] text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                <?= count($userComplaints) ?>
              </span>
            </button>

            <button type="button" data-ptab="belajar" onclick="switchProfileTab('belajar')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                <span class="text-left">Progres Belajar &amp; Kuis</span>
              </div>
              <span class="px-2 py-0.5 rounded-full bg-cyan-100 text-[#0284C7] text-[10px] font-bold">
                <?= $completedCoursesCount ?>/<?= $totalCourses ?>
              </span>
            </button>

            <button type="button" data-ptab="privasi" onclick="switchProfileTab('privasi')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/></svg>
                <span class="text-left">Pengaturan Privasi &amp; Keamanan</span>
              </div>
            </button>

            <button type="button" data-ptab="lencana" onclick="switchProfileTab('lencana')" class="profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50">
              <div class="flex items-center gap-3">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                <span>Lencana Komunitas</span>
              </div>
              <span class="px-2 py-0.5 rounded-full bg-cyan-100 text-[#0284C7] text-[10px] font-bold">
                <?= count($userBadges) ?>/<?= count($BADGE_DEFINITIONS) ?>
              </span>
            </button>
          </div>

          <!-- Slogan Water Droplets Card -->
          <div class="bg-[#EBF5FB] p-5 rounded-2xl border border-sky-100/70 text-center space-y-2 shadow-2xs">
            <div class="w-12 h-12 mx-auto rounded-full bg-white flex items-center justify-center text-[#0284C7] shadow-xs">
              <svg class="w-6 h-6 fill-[#0284C7] text-[#0284C7]" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
            </div>
            <p class="text-xs font-semibold text-[#0F2C59] leading-snug">
              Bersama jaga air, untuk Payakumbuh yang lebih baik
            </p>
          </div>

          <!-- Switch Persona Demo (Memudahkan pengujian multi pelanggan di PHP) -->
          <div class="bg-white p-3 rounded-2xl border border-sky-100/90 shadow-2xs space-y-2 text-[11px]">
            <span class="font-bold text-slate-400 uppercase tracking-wider text-[10px] block">Akun Demo Pelanggan</span>
            <div class="grid grid-cols-2 gap-1.5">
              <a href="index.php?action=login_demo&persona=user-01" class="px-2 py-1.5 rounded-lg text-center font-semibold border transition-colors <?= $currentUser['id'] === 'user-01' ? 'bg-sky-50 border-sky-300 text-[#0284C7]' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
                Warga Google
              </a>
              <a href="index.php?action=login_demo&persona=user-02" class="px-2 py-1.5 rounded-lg text-center font-semibold border transition-colors <?= $currentUser['id'] === 'user-02' ? 'bg-sky-50 border-sky-300 text-[#0284C7]' : 'bg-slate-50 border-slate-200 text-slate-600 hover:bg-slate-100' ?>">
                Ibu Siti N.
              </a>
            </div>
          </div>

          <!-- Keluar Akun Button -->
          <div class="bg-white p-2 rounded-2xl border border-sky-100/90 shadow-2xs">
            <button type="button" onclick="openLogoutModal()" class="w-full flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
              <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
              <span>Keluar Akun</span>
            </button>
          </div>
        </aside>

        <!-- RIGHT MAIN CONTENT AREA -->
        <div id="profile-content-top" class="flex-1 space-y-6 overflow-hidden">

          <!-- ======================================================== -->
          <!-- 1. TAB: BERANDA (OVERVIEW DASHBOARD)                     -->
          <!-- ======================================================== -->
          <section id="profile-tab-beranda" class="profile-tab-panel space-y-6">
            <!-- Header Profil Banner -->
            <div class="relative rounded-3xl border border-sky-100/80 shadow-xs overflow-hidden bg-gradient-to-r from-[#D7EEFD] via-[#E8F4FE] to-[#D5EDFC] p-6 sm:p-8 min-h-[160px] flex flex-col justify-between">
              <!-- Panoramic Landscape Graphic (Jam Gadang & Mountains Payakumbuh) -->
              <div class="absolute right-0 top-0 bottom-0 w-2/5 pointer-events-none opacity-85 hidden md:block">
                <svg viewBox="0 0 360 160" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full object-cover">
                  <defs>
                    <linearGradient id="mountGrad" x1="180" y1="40" x2="180" y2="160" gradientUnits="userSpaceOnUse">
                      <stop offset="0%" stop-color="#93C5FD" stop-opacity="0.6" />
                      <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.2" />
                    </linearGradient>
                  </defs>
                  <path d="M60 120 L130 60 L190 100 L260 40 L330 90 L360 80 L360 160 L0 160 Z" fill="url(#mountGrad)" />
                  <g transform="translate(240, 45) scale(0.7)">
                    <rect x="25" y="45" width="22" height="65" fill="#CBD5E1" stroke="#64748B" stroke-width="1.5" />
                    <rect x="27" y="25" width="18" height="20" fill="#E2E8F0" stroke="#64748B" stroke-width="1.5" />
                    <circle cx="36" cy="35" r="5" fill="white" stroke="#0F172A" stroke-width="1.2" />
                    <path d="M22 25 L36 5 L50 25 Z" fill="#B91C1C" stroke="#7F1D1D" stroke-width="1.5" />
                  </g>
                  <circle cx="210" cy="130" r="16" fill="#15803D" fill-opacity="0.6" />
                  <circle cx="230" cy="125" r="20" fill="#16A34A" fill-opacity="0.7" />
                  <circle cx="290" cy="130" r="18" fill="#15803D" fill-opacity="0.6" />
                  <circle cx="320" cy="125" r="22" fill="#16A34A" fill-opacity="0.7" />
                  <path d="M0 145 Q120 135 240 140 T360 145 L360 160 L0 160 Z" fill="#0284C7" fill-opacity="0.3" />
                </svg>
              </div>

              <div class="relative z-10 space-y-2">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 border border-sky-200/80 text-[11px] font-bold text-[#0284C7]">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/></svg>
                  <span>Dashboard Mandiri Pelanggan Payakumbuh</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2C59] tracking-tight"><?= e($currentUser['name']) ?></h1>
                <p class="text-xs sm:text-sm font-semibold text-slate-700"><?= e($currentUser['role']) ?></p>

                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 pt-2 text-xs text-slate-600 font-medium">
                  <span class="flex items-center gap-1.5 font-mono text-slate-800 font-semibold bg-white/70 px-2.5 py-1 rounded-lg border border-sky-100">
                    <svg class="w-3.5 h-3.5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                    <span>No. Meter: <?= e($currentUser['meterNumber']) ?></span>
                  </span>
                  <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <span class="bind-user-phone"><?= e($currentUser['phone']) ?></span>
                  </span>
                  <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    <span><?= e($currentUser['district']) ?></span>
                  </span>
                  <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                    <span>Bergabung <?= e($currentUser['joinedDate']) ?></span>
                  </span>
                </div>
              </div>
            </div>

            <!-- Empat Kartu Statistik Ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
              <div onclick="switchProfileTab('laporan')" class="bg-white rounded-2xl border border-sky-100/90 p-4 sm:p-5 shadow-2xs space-y-3 cursor-pointer hover:border-[#0284C7] hover:shadow-md transition-all group">
                <div class="w-9 h-9 rounded-full bg-sky-50 text-[#0284C7] flex items-center justify-center group-hover:bg-[#0284C7] group-hover:text-white transition-colors">
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                </div>
                <div>
                  <span class="text-xs font-semibold text-slate-500 block">Pemakaian Air</span>
                  <div class="text-2xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                    <span class="bind-current-m3"><?= e((string) $report['currentMonthM3']) ?></span> m³
                  </div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 pt-1 border-t border-slate-50">
                  <span class="text-[11px] text-[#0284C7] font-semibold group-hover:underline">Lihat Rincian Laporan</span>
                  <svg class="w-3.5 h-3.5 text-[#0284C7] group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </div>
              </div>

              <div onclick="switchProfileTab('laporan')" class="bg-white rounded-2xl border border-sky-100/90 p-4 sm:p-5 shadow-2xs space-y-3 cursor-pointer hover:border-amber-400 hover:shadow-md transition-all group">
                <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition-colors">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                </div>
                <div>
                  <span class="text-xs font-semibold text-slate-500 block">Tagihan Terakhir</span>
                  <div class="bind-current-bill text-xl sm:text-2xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                    <?= formatRupiah((int) $report['currentBillEstimateIdr']) ?>
                  </div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 pt-1 border-t border-slate-50">
                  <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Status Lunas</span>
                  </span>
                  <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </div>
              </div>

              <div onclick="switchProfileTab('pengaduan')" class="bg-white rounded-2xl border border-sky-100/90 p-4 sm:p-5 shadow-2xs space-y-3 cursor-pointer hover:border-[#0284C7] hover:shadow-md transition-all group">
                <div class="w-9 h-9 rounded-full bg-sky-50 text-[#0284C7] flex items-center justify-center group-hover:bg-[#0284C7] group-hover:text-white transition-colors">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14h3a2 2 0 0 1 2 2v3a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-7a9 9 0 0 1 18 0v7a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3"/></svg>
                </div>
                <div>
                  <span class="text-xs font-semibold text-slate-500 block">Pengaduan</span>
                  <div class="bind-complaints-count text-2xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                    <?= count($userComplaints) ?>
                  </div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 pt-1 border-t border-slate-50">
                  <span class="text-[11px] text-slate-500 font-medium">Tiket Resmi</span>
                  <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </div>
              </div>

              <div onclick="switchProfileTab('belajar')" class="bg-white rounded-2xl border border-sky-100/90 p-4 sm:p-5 shadow-2xs space-y-3 cursor-pointer hover:border-amber-400 hover:shadow-md transition-all group">
                <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-500 group-hover:text-white transition-colors">
                  <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                </div>
                <div>
                  <span class="text-xs font-semibold text-slate-500 block">Poin Edukasi</span>
                  <div class="text-2xl font-black font-mono text-slate-900 tracking-tight mt-0.5">
                    <?= (int) ($currentUser['points'] ?? 380) ?>
                  </div>
                </div>
                <div class="flex items-center justify-between text-xs text-slate-400 pt-1 border-t border-slate-50">
                  <span class="text-[11px] text-slate-500 font-medium">XP Terkumpul</span>
                  <svg class="w-3.5 h-3.5 text-slate-400 group-hover:translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </div>
              </div>
            </div>

            <!-- Kartu Digital Pelanggan & Quick Actions -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 shadow-2xs grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
              <!-- Royal Blue Digital Card -->
              <div class="lg:col-span-7 bg-gradient-to-br from-[#0284C7] via-[#0369A1] to-[#0F2C59] rounded-2xl p-6 text-white shadow-md relative overflow-hidden flex flex-col justify-between min-h-[190px]">
                <div class="absolute right-0 top-0 bottom-0 w-1/2 pointer-events-none opacity-20">
                  <svg viewBox="0 0 200 200" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <circle cx="100" cy="100" r="80" stroke="white" stroke-width="10" />
                    <circle cx="100" cy="100" r="50" stroke="white" stroke-width="6" />
                  </svg>
                </div>

                <div class="relative z-10 space-y-3">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white/20 flex items-center justify-center">
                      <svg class="w-4 h-4 fill-white text-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                    </div>
                    <div>
                      <h4 class="text-xs font-extrabold tracking-wider uppercase text-white leading-tight">Tirta Sago</h4>
                      <p class="text-[10px] text-cyan-200">Kartu Digital Pelanggan Resmi</p>
                    </div>
                  </div>

                  <div class="pt-2">
                    <p class="text-base font-extrabold text-white tracking-tight"><?= e($currentUser['name']) ?></p>
                    <div class="grid grid-cols-2 gap-4 mt-2">
                      <div>
                        <span class="text-[10px] text-cyan-200 uppercase font-semibold block">No. Meteran Air</span>
                        <p class="text-base sm:text-lg font-mono font-black text-white mt-0.5"><?= e($currentUser['meterNumber']) ?></p>
                      </div>
                      <div>
                        <span class="text-[10px] text-cyan-200 uppercase font-semibold block">Kategori Tarif</span>
                        <p class="text-xs font-semibold text-cyan-50 mt-1"><?= e($currentUser['tariffCategory']) ?></p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="relative z-10 pt-4 mt-2 border-t border-white/15 flex items-center justify-between text-xs">
                  <span class="flex items-center gap-1.5 text-xs text-cyan-100 font-medium">
                    <svg class="w-3.5 h-3.5 text-cyan-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    <span><?= e($currentUser['district']) ?></span>
                  </span>
                  <span class="px-3 py-1 rounded-full bg-emerald-500 text-white font-bold text-[10px] shadow-xs">Aktif &amp; Normal</span>
                </div>
              </div>

              <!-- Quick Action Shortcuts -->
              <div class="lg:col-span-5 space-y-3 pl-0 lg:pl-2">
                <div class="space-y-1">
                  <span class="text-[11px] font-bold text-[#0284C7] uppercase tracking-wider block">Pintasan Layanan</span>
                  <h3 class="text-lg font-extrabold text-slate-900 tracking-tight">Akses Cepat Pelanggan</h3>
                  <p class="text-xs text-slate-500">
                    Kelola tagihan, pantau konsumsi air bulanan, atau ajukan laporan teknis secara praktis.
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-2.5 pt-1">
                  <button type="button" onclick="switchProfileTab('laporan')" class="p-3 rounded-xl border border-sky-100 bg-sky-50/60 hover:bg-sky-100/80 text-left transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-[#0284C7] mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                    <span class="text-xs font-bold text-slate-800 block">Laporan Air</span>
                    <span class="text-[10px] text-slate-500">Kubikasi &amp; E-Kuitansi</span>
                  </button>

                  <button type="button" onclick="switchProfileTab('pengaduan')" class="p-3 rounded-xl border border-sky-100 bg-sky-50/60 hover:bg-sky-100/80 text-left transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-[#0284C7] mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span class="text-xs font-bold text-slate-800 block">Pengaduan</span>
                    <span class="text-[10px] text-slate-500">Lapor Pipa &amp; Tekanan</span>
                  </button>

                  <button type="button" onclick="switchProfileTab('info')" class="p-3 rounded-xl border border-slate-200 bg-slate-50 hover:bg-slate-100 text-left transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-slate-700 mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <span class="text-xs font-bold text-slate-800 block">Data Akun</span>
                    <span class="text-[10px] text-slate-500">Alamat &amp; Identitas</span>
                  </button>

                  <button type="button" onclick="switchProfileTab('belajar')" class="p-3 rounded-xl border border-amber-100 bg-amber-50/60 hover:bg-amber-100/80 text-left transition-colors cursor-pointer">
                    <svg class="w-4 h-4 text-amber-600 mb-1.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                    <span class="text-xs font-bold text-slate-800 block">Kuis &amp; Modul</span>
                    <span class="text-[10px] text-slate-500">Skor &amp; Sertifikat</span>
                  </button>
                </div>
              </div>
            </div>

            <!-- Portal Homepage Shortcut Banner -->
            <div class="p-5 rounded-3xl bg-gradient-to-r from-sky-500 to-[#0284C7] text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm">
              <div class="space-y-1 text-center sm:text-left">
                <h4 class="text-sm font-extrabold text-white">Ingin Menjelajahi Beranda Utama Kota Payakumbuh?</h4>
                <p class="text-xs text-sky-100">Kembali ke portal beranda untuk melihat peta mata air, kalkulator penghematan, dan program unggulan.</p>
              </div>
              <a href="<?= url('/beranda') ?>" class="px-5 py-2.5 rounded-xl bg-white text-[#0284C7] font-bold text-xs hover:bg-sky-50 transition-colors shadow-xs flex items-center gap-2 shrink-0">
                <span>Buka Beranda Utama</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 7h10v10"/><path d="M7 17 17 7"/></svg>
              </a>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 2. TAB: INFORMASI AKUN & DATA PELANGGAN                  -->
          <!-- ======================================================== -->
          <section id="profile-tab-info" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span>Profil Pelanggan</span>
                    <span>/</span>
                    <span class="text-[#0284C7] font-semibold">Informasi Akun &amp; Data Pelanggan</span>
                  </div>
                  <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Informasi Akun &amp; Data Pelanggan Resmi</h2>
                  <p class="text-xs text-slate-500">
                    Data ini tersinkronisasi langsung dengan register master Perumda Air Minum Tirta Sago Kota Payakumbuh.
                  </p>
                </div>

                <button type="button" onclick="openEditContactModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors flex items-center gap-2 shadow-xs shrink-0 self-start sm:self-auto cursor-pointer">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                  <span>Ubah Data Kontak</span>
                </button>
              </div>

              <!-- Digital Card Preview -->
              <div class="bg-gradient-to-br from-[#0284C7] via-[#0369A1] to-[#0F2C59] rounded-2xl p-6 text-white shadow-md relative overflow-hidden flex flex-col justify-between min-h-[180px]">
                <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                  <div class="space-y-2">
                    <div class="flex items-center gap-2">
                      <svg class="w-5 h-5 fill-white text-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                      <span class="font-extrabold uppercase tracking-wider text-xs">Kartu Digital Tirta Sago</span>
                    </div>
                    <h3 class="text-xl font-black text-white"><?= e($currentUser['name']) ?></h3>
                    <p class="text-xs text-cyan-100"><?= e($currentUser['tariffCategory']) ?></p>
                  </div>

                  <div class="text-left sm:text-right space-y-1">
                    <span class="text-[10px] text-cyan-200 uppercase font-bold block">Nomor Meteran Air</span>
                    <span class="text-xl font-mono font-black text-white block"><?= e($currentUser['meterNumber']) ?></span>
                    <span class="inline-block px-2.5 py-0.5 rounded-full bg-emerald-500 text-white text-[10px] font-bold">Aktif &amp; Terverifikasi</span>
                  </div>
                </div>
              </div>

              <!-- Structured Table Rows -->
              <div class="divide-y divide-slate-100 border border-slate-100 rounded-2xl overflow-hidden text-xs mt-4">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Nama Lengkap</span>
                  </div>
                  <div class="md:col-span-4 font-bold text-slate-900"><?= e($currentUser['name']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Perubahan nama memerlukan verifikasi KTP di kantor PDAM.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Nomor Meteran Air (ID Pelanggan)</span>
                  </div>
                  <div class="md:col-span-4 font-mono font-bold text-slate-900"><?= e($currentUser['meterNumber']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Terhubung langsung ke register meteran fisik rumah tangga.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Kategori Tarif PDAM</span>
                  </div>
                  <div class="md:col-span-4 font-semibold text-slate-800"><?= e($currentUser['tariffCategory']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Berdasarkan Peraturan Walikota Payakumbuh tentang Tarif Air Minum.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Nomor Telepon (WhatsApp)</span>
                  </div>
                  <div class="md:col-span-4 font-mono font-bold text-slate-900 bind-user-phone"><?= e($currentUser['phone']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Digunakan untuk konfirmasi respon tiket dan notifikasi darurat.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Alamat Email</span>
                  </div>
                  <div class="md:col-span-4 font-mono font-semibold text-slate-800 bind-user-email"><?= e($currentUser['email']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Untuk pengiriman e-billing dan bukti kuitansi resmi.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Wilayah Kecamatan</span>
                  </div>
                  <div class="md:col-span-4 font-semibold text-slate-800"><?= e($currentUser['district']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Zona distribusi teknis Perumda Tirta Sago.</div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-12 gap-3 p-4 items-center bg-white hover:bg-slate-50/60 transition-colors">
                  <div class="md:col-span-4 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                    </div>
                    <span class="font-semibold text-slate-700">Alamat Lengkap Sambungan Rumah Tangga</span>
                  </div>
                  <div class="md:col-span-4 text-xs font-medium text-slate-900 leading-relaxed bind-user-address"><?= e($currentUser['address']) ?></div>
                  <div class="md:col-span-4 text-[11px] text-slate-400">Alamat ini otomatis menjadi rujukan petugas jika Anda melaporkan gangguan pipa.</div>
                </div>
              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 3. TAB: LAPORAN PEMAKAIAN AIR PRIBADI                    -->
          <!-- ======================================================== -->
          <section id="profile-tab-laporan" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-4">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span>Profil Pelanggan</span>
                    <span>/</span>
                    <span class="text-[#0284C7] font-semibold">Laporan Pemakaian Air Pribadi</span>
                  </div>
                  <div class="flex items-center gap-2.5">
                    <h2 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight">
                      Laporan Pemakaian Air Pribadi &amp; E-Kuitansi
                    </h2>
                    <span class="hidden sm:inline-flex items-center px-2.5 py-0.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-[10px] font-bold">
                      Data Resmi PDAM
                    </span>
                  </div>
                  <p class="text-xs text-slate-500 max-w-2xl">
                    Transparansi pencatatan kubikasi bulanan, verifikasi stand meteran mandiri, rincian biaya air resmi golongan 2A Rumah Tangga Mandiri, dan arsip bukti pembayaran PDAM Tirta Sago.
                  </p>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                  <button type="button" onclick="window.print()" class="px-3.5 py-2 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
                    <span>Cetak Rekap</span>
                  </button>
                  <button type="button" onclick="showProfileToast('Rekapitulasi laporan pemakaian air 2026 berhasil diunduh dalam format PDF!')" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                    <span>Unduh PDF</span>
                  </button>
                </div>
              </div>

              <!-- 4 Kartu Metrik Utama -->
              <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2">
                <div class="bg-gradient-to-br from-sky-50 to-blue-50/50 rounded-2xl border border-sky-100 p-4 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Konsumsi Bulan Ini</span>
                    <span class="w-7 h-7 rounded-lg bg-[#0284C7] text-white flex items-center justify-center">
                      <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                    </span>
                  </div>
                  <div class="text-3xl font-black font-mono text-slate-900 tracking-tight">
                    <span class="bind-current-m3"><?= e((string) $report['currentMonthM3']) ?></span> <span class="text-base font-semibold text-slate-500">m³</span>
                  </div>
                  <div class="flex items-center gap-1 text-[11px] text-emerald-700 font-semibold">
                    <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 17 13.5 8.5 8.5 13.5 2 7"/><polyline points="16 17 22 17 22 11"/></svg>
                    <span>Turun 16.1% (Hemat 1.8 m³)</span>
                  </div>
                  <p class="text-[10px] text-slate-500 pt-1 border-t border-sky-100/80">
                    Kategori: Sangat Efisien (Di bawah batas subsidi 10 m³)
                  </p>
                </div>

                <div class="bg-gradient-to-br from-amber-50/60 to-orange-50/30 rounded-2xl border border-amber-100 p-4 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Tagihan</span>
                    <span class="w-7 h-7 rounded-lg bg-amber-600 text-white flex items-center justify-center">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                    </span>
                  </div>
                  <div class="bind-current-bill text-2xl font-black font-mono text-slate-900 tracking-tight">
                    <?= formatRupiah((int) $report['currentBillEstimateIdr']) ?>
                  </div>
                  <div class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                    <svg class="w-2.5 h-2.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Status Lunas (<?= e($report['lastPaymentDate']) ?>)</span>
                  </div>
                  <p class="text-[10px] text-slate-500 pt-1 border-t border-amber-100/80">Terbayar via QRIS Bank Nagari</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Rata-Rata Harian</span>
                    <span class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center">
                      <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                    </span>
                  </div>
                  <div class="text-2xl font-black font-mono text-slate-900 tracking-tight">
                    ~<?= (int) $report['dailyAverageLiters'] ?> <span class="text-sm font-semibold text-slate-500">Liter / Hari</span>
                  </div>
                  <div class="text-[11px] text-slate-600 font-medium">
                    ~95 Liter/jiwa/hari (<?= (int) $report['householdMembers'] ?> anggota keluarga)
                  </div>
                  <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-100">Standar SNI Payakumbuh: 120-140 L/jiwa/hari</p>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-4 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Register Stand Meter</span>
                    <span class="w-7 h-7 rounded-lg bg-sky-50 text-[#0284C7] flex items-center justify-center">
                      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                    </span>
                  </div>
                  <div class="text-2xl font-black font-mono text-slate-900 tracking-tight">
                    <span class="bind-stand-current"><?= e((string) $report['standMeterCurrent']) ?></span> <span class="text-sm font-semibold text-slate-500">m³</span>
                  </div>
                  <div id="standDiffText" class="text-[11px] text-slate-500 font-mono">
                    Bulan Lalu: <?= e((string) $report['standMeterLastMonth']) ?> m³ (Selisih: +<?= e((string) ($report['standMeterCurrent'] - $report['standMeterLastMonth'])) ?> m³)
                  </div>
                  <p class="text-[10px] text-slate-400 pt-1 border-t border-slate-100">Terkalibrasi Petugas Lapangan Tirta Sago</p>
                </div>
              </div>
            </div>

            <!-- Grafik Batang Visual Tren Konsumsi 4 Bulan -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-5">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/></svg>
                    <span>Grafik Tren Kubikasi Konsumsi 4 Bulan Terakhir</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">Visualisasi efisiensi penggunaan air terhadap batas pemakaian bersubsidi Blok 1 (10 m³).</p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-sky-50 text-[#0284C7] font-bold text-xs border border-sky-200 self-start sm:self-auto">
                  <svg class="w-3.5 h-3.5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                  <span>Target Efisiensi Tercapai</span>
                </span>
              </div>

              <div class="p-5 sm:p-6 rounded-2xl bg-[#F0F6FB] border border-sky-100 space-y-4">
                <div class="grid grid-cols-4 gap-3 sm:gap-6 pt-2">
                  <?php
                  $bars = [
                      ['month' => 'Juli 2026',      'm3' => 12.2, 'cost' => 61000, 'pct' => 100, 'current' => false],
                      ['month' => 'Agustus 2026',   'm3' => 12.0, 'cost' => 60000, 'pct' => 98,  'current' => false],
                      ['month' => 'September 2026', 'm3' => 11.2, 'cost' => 56000, 'pct' => 91,  'current' => false],
                      ['month' => 'Oktober 2026',   'm3' => 9.4,  'cost' => 47000, 'pct' => 77,  'current' => true],
                  ];
                  foreach ($bars as $item):
                  ?>
                    <div class="flex flex-col items-center gap-2">
                      <div class="w-full h-36 bg-slate-200/80 rounded-2xl flex items-end p-1.5 relative group">
                        <div class="absolute left-0 right-0 border-b-2 border-dashed border-rose-400/70 z-10 pointer-events-none" style="bottom: 82%" title="Batas Blok 1 Hemat (10 m³)"></div>
                        <div class="w-full rounded-xl transition-all duration-500 flex flex-col justify-end p-1 text-center <?= $item['current'] ? 'bg-gradient-to-t from-[#0284C7] to-sky-400 shadow-md shadow-sky-500/20' : 'bg-slate-400 group-hover:bg-slate-500' ?>" style="height: <?= $item['pct'] ?>%">
                          <span class="text-[10px] font-mono font-bold text-white hidden sm:block"><?= $item['m3'] ?></span>
                        </div>
                      </div>
                      <div class="text-center">
                        <span class="font-mono text-xs sm:text-sm font-bold text-slate-900 block"><?= $item['m3'] ?> m³</span>
                        <span class="text-[10px] text-slate-500 font-medium block"><?= e($item['month']) ?></span>
                        <span class="text-[10px] font-mono text-slate-600 block"><?= formatRupiah($item['cost']) ?></span>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>

                <div class="pt-3 border-t border-sky-200/60 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                  <div class="flex items-center gap-4 text-[11px] text-slate-600">
                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded bg-[#0284C7]"></div><span>Bulan Berjalan (Oktober)</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-3 h-3 rounded bg-slate-400"></div><span>Bulan Lalu</span></div>
                    <div class="flex items-center gap-1.5"><div class="w-3 border-b-2 border-dashed border-rose-400"></div><span>Batas Subsidi 10 m³</span></div>
                  </div>
                  <div class="text-[11px] text-slate-600 bg-white/80 px-3 py-1.5 rounded-xl border border-sky-100 flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-[#0284C7] shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span>Hemat Rp 14.000 dibanding bulan Juli berkat pemakaian bijak!</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Transparansi Rumus Tarif Berjenjang Tirta Sago -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-4">
              <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                  <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/></svg>
                  <span>Transparansi Perhitungan Tarif Berjenjang PDAM Tirta Sago</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  Berdasarkan Peraturan Walikota Payakumbuh untuk Kategori <strong>2A - Rumah Tangga Mandiri</strong>.
                </p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-xs">
                <div class="p-4 rounded-2xl border-2 border-emerald-300 bg-emerald-50/40 space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-emerald-800 uppercase tracking-wider text-[11px]">Blok 1 (0 – 10 m³)</span>
                    <span class="px-2 py-0.5 rounded-full bg-emerald-200 text-emerald-900 font-bold text-[10px]">Aktif Digunakan</span>
                  </div>
                  <div class="text-xl font-black font-mono text-emerald-900">Rp 5.000 <span class="text-xs font-semibold text-slate-500">/ m³</span></div>
                  <p class="text-[11px] text-emerald-950 leading-relaxed">Tarif subsidi dasar kebutuhan pokok air bersih bagi masyarakat Payakumbuh.</p>
                  <div class="pt-2 border-t border-emerald-200/80 font-mono text-[11px] text-emerald-900 font-bold">
                    Pemakaian: <?= $billCalc['b1M3'] ?> m³ &times; Rp 5.000 = <?= formatRupiah($billCalc['b1Cost']) ?>
                  </div>
                </div>

                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-2 <?= $billCalc['b2M3'] > 0 ? '' : 'opacity-80' ?>">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Blok 2 (11 – 20 m³)</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px]">Standar</span>
                  </div>
                  <div class="text-xl font-black font-mono text-slate-900">Rp 6.200 <span class="text-xs font-semibold text-slate-500">/ m³</span></div>
                  <p class="text-[11px] text-slate-600 leading-relaxed">Tarif pemakaian sedang untuk kebutuhan domestik tambahan.</p>
                  <div class="pt-2 border-t border-slate-200 font-mono text-[11px] text-slate-600">
                    Pemakaian: <?= $billCalc['b2M3'] ?> m³ &times; Rp 6.200 = <?= formatRupiah($billCalc['b2Cost']) ?>
                  </div>
                </div>

                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/60 space-y-2 <?= $billCalc['b3M3'] > 0 ? '' : 'opacity-80' ?>">
                  <div class="flex items-center justify-between">
                    <span class="font-bold text-slate-700 uppercase tracking-wider text-[11px]">Blok 3 (&gt; 20 m³)</span>
                    <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-700 font-bold text-[10px]">Disinsentif</span>
                  </div>
                  <div class="text-xl font-black font-mono text-slate-900">Rp 7.800 <span class="text-xs font-semibold text-slate-500">/ m³</span></div>
                  <p class="text-[11px] text-slate-600 leading-relaxed">Tarif disinsentif untuk mencegah pemborosan dan mendorong konservasi air.</p>
                  <div class="pt-2 border-t border-slate-200 font-mono text-[11px] text-slate-600">
                    Pemakaian: <?= $billCalc['b3M3'] ?> m³ &times; Rp 7.800 = <?= formatRupiah($billCalc['b3Cost']) ?>
                  </div>
                </div>
              </div>

              <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 text-xs">
                <div class="space-y-0.5">
                  <span class="font-bold text-slate-800">Beban Pemeliharaan Meter &amp; Administrasi:</span>
                  <p class="text-slate-500 text-[11px]">Gratis / Disubsidi penuh oleh Program Komunitas Tirta Sago.</p>
                </div>
                <div class="text-right">
                  <span class="text-slate-500 text-[11px] block">Total Tagihan Bersih:</span>
                  <span class="bind-current-bill font-mono text-lg font-black text-[#0284C7]"><?= formatRupiah($billCalc['total']) ?></span>
                </div>
              </div>
            </div>

            <!-- Tabel Riwayat Tagihan & E-Kuitansi -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                    <span>Riwayat Tagihan &amp; Arsip E-Kuitansi Pembayaran</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">Klik tombol <strong>Lihat E-Kuitansi</strong> untuk mencetak atau mengunduh bukti bayar resmi.</p>
                </div>
              </div>

              <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                  <thead>
                    <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                      <th class="p-3.5 rounded-l-xl">Periode</th>
                      <th class="p-3.5">No. Transaksi</th>
                      <th class="p-3.5">Volume (m³)</th>
                      <th class="p-3.5">Total Bayar</th>
                      <th class="p-3.5">Status</th>
                      <th class="p-3.5 rounded-r-xl text-right">Aksi</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-100">
                    <?php foreach ($report['billingHistory'] as $item): ?>
                      <?php $invNo = $item['invoiceNo']; $payDate = $item['paidDate']; ?>
                      <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="p-3.5 font-bold text-slate-900"><?= e($item['period']) ?></td>
                        <td class="p-3.5 font-mono text-slate-600"><?= e($invNo) ?></td>
                        <td class="p-3.5 font-mono font-bold text-slate-900"><?= e((string) $item['m3']) ?> m³</td>
                        <td class="p-3.5 font-mono font-bold text-slate-900"><?= formatRupiah((int) $item['amountIdr']) ?></td>
                        <td class="p-3.5">
                          <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
                            <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><?= e($item['status']) ?></span>
                          </span>
                        </td>
                        <td class="p-3.5 text-right">
                          <button type="button" onclick="openReceiptModal('<?= e($item['period']) ?>', <?= (float) $item['m3'] ?>, <?= (int) $item['amountIdr'] ?>, '<?= e($payDate) ?>', '<?= e($invNo) ?>')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#0284C7] bg-sky-50 hover:bg-sky-100 border border-sky-200 transition-colors inline-flex items-center gap-1.5 cursor-pointer shadow-2xs">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
                            <span>Lihat E-Kuitansi</span>
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>

            <!-- Kalkulator & Simulator Estimasi Tagihan Mandiri -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-5">
              <div class="border-b border-slate-100 pb-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/></svg>
                    <span>Kalkulator &amp; Simulator Estimasi Tagihan Air Mandiri</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">Geser pengatur volume kubikasi di bawah untuk memprediksi besaran tagihan Anda bulan depan.</p>
                </div>
                <span class="text-xs font-bold text-[#0284C7] bg-sky-50 px-3 py-1 rounded-full border border-sky-200 self-start sm:self-auto">Simulasi Golongan 2A</span>
              </div>

              <div class="p-5 rounded-2xl bg-gradient-to-br from-slate-50 to-sky-50/40 border border-sky-100/80 space-y-4">
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <label class="text-xs font-bold text-slate-700">Pilih Estimasi Konsumsi Air (m³):</label>
                    <span class="font-mono text-xl font-black text-[#0284C7]">
                      <span id="simVolumeValue"><?= e((string) $report['currentMonthM3']) ?> m³</span>
                      <span id="simLitersValue" class="text-xs font-normal text-slate-500">(<?= number_format($report['currentMonthM3'] * 1000, 0, ',', '.') ?> Liter)</span>
                    </span>
                  </div>

                  <input id="billSimSlider" type="range" min="1" max="40" step="0.5" value="<?= e((string) $report['currentMonthM3']) ?>" oninput="updateBillSimulator(this.value)" class="w-full h-2.5 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#0284C7]" />

                  <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                    <span>1 m³ (Hemat)</span>
                    <span>10 m³ (Batas Blok 1)</span>
                    <span>20 m³ (Batas Blok 2)</span>
                    <span>40 m³ (Tinggi)</span>
                  </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-2 text-xs">
                  <div class="p-3 rounded-xl bg-white border border-slate-200">
                    <span class="text-[10px] text-slate-400 font-semibold block">Blok 1 (0-10 m³)</span>
                    <span id="simBlok1Value" class="font-mono font-bold text-slate-800"><?= $billCalc['b1M3'] ?> m³ = <?= formatRupiah($billCalc['b1Cost']) ?></span>
                  </div>
                  <div class="p-3 rounded-xl bg-white border border-slate-200">
                    <span class="text-[10px] text-slate-400 font-semibold block">Blok 2 (11-20 m³)</span>
                    <span id="simBlok2Value" class="font-mono font-bold text-slate-800"><?= $billCalc['b2M3'] ?> m³ = <?= formatRupiah($billCalc['b2Cost']) ?></span>
                  </div>
                  <div class="p-3 rounded-xl bg-white border border-slate-200">
                    <span class="text-[10px] text-slate-400 font-semibold block">Blok 3 (&gt;20 m³)</span>
                    <span id="simBlok3Value" class="font-mono font-bold text-slate-800"><?= $billCalc['b3M3'] ?> m³ = <?= formatRupiah($billCalc['b3Cost']) ?></span>
                  </div>
                  <div class="p-3 rounded-xl bg-[#0284C7] text-white">
                    <span class="text-[10px] text-sky-200 font-semibold block">Total Estimasi</span>
                    <span id="simTotalEstimate" class="font-mono text-base font-black"><?= formatRupiah($billCalc['total']) ?></span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Lapor Stand Meter Mandiri -->
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-4">
              <div class="border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                  <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                  <span>Lapor Stand Meter Mandiri (Self-Meter Reading)</span>
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                  Catat angka register fisik meteran air Anda untuk konfirmasi awal sebelum kunjungan petugas pencatat meter Tirta Sago.
                </p>
              </div>

              <form onsubmit="submitSelfMeterReading(event)" class="grid grid-cols-1 sm:grid-cols-12 gap-4 text-xs items-end">
                <div class="sm:col-span-4 space-y-1">
                  <label class="font-semibold text-slate-700 block">Angka Stand Meteran Terkini (m³)</label>
                  <input id="selfStandInput" type="number" step="0.1" value="<?= e((string) $report['standMeterCurrent']) ?>" placeholder="1842" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 font-mono text-slate-900 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100" />
                  <span class="text-[10px] text-slate-400">Stand bulan lalu: <?= e((string) $report['standMeterLastMonth']) ?> m³</span>
                </div>

                <div class="sm:col-span-4 space-y-1">
                  <label class="font-semibold text-slate-700 block">Tanggal Pencatatan Fisik</label>
                  <input id="selfReadingDateInput" type="date" value="2026-10-21" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100" />
                  <span class="text-[10px] text-slate-400">Pencatatan resmi tanggal 10-25 setiap bulan</span>
                </div>

                <div class="sm:col-span-4 flex items-center gap-2">
                  <button id="meterPhotoBtn" type="button" onclick="toggleMeterPhotoUpload()" class="flex-1 py-2.5 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/><circle cx="12" cy="13" r="3"/></svg>
                    <span id="meterPhotoLabel">Unggah Foto</span>
                  </button>

                  <button type="submit" class="py-2.5 px-5 rounded-xl font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors shadow-xs shrink-0 cursor-pointer">
                    Simpan Stand
                  </button>
                </div>
              </form>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 4. TAB: RIWAYAT PENGADUAN                                -->
          <!-- ======================================================== -->
          <section id="profile-tab-pengaduan" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-6">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span>Profil Pelanggan</span>
                    <span>/</span>
                    <span class="text-[#0284C7] font-semibold">Riwayat Pengaduan</span>
                  </div>
                  <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Riwayat Pengaduan &amp; Laporan Air</h2>
                  <p class="text-xs text-slate-500">Tiket laporan pengaduan resmi Anda ke tim reaksi cepat Tirta Sago Kota Payakumbuh.</p>
                </div>
                <button type="button" onclick="openComplaintModal()" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors flex items-center gap-1.5 shadow-xs cursor-pointer self-start sm:self-auto">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="16"/><line x1="8" x2="16" y1="12" y2="12"/></svg>
                  <span>Buat Pengaduan Baru</span>
                </button>
              </div>

              <div class="flex items-center gap-2 border-b border-slate-100 pb-3 text-xs">
                <button id="cmpFilter-semua" type="button" onclick="filterProfileComplaints('semua')" class="px-3 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer bg-[#0284C7] text-white">
                  Semua (<?= count($userComplaints) ?>)
                </button>
                <button id="cmpFilter-proses" type="button" onclick="filterProfileComplaints('proses')" class="px-3 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer text-slate-600 hover:bg-slate-100">
                  Dalam Penanganan
                </button>
                <button id="cmpFilter-selesai" type="button" onclick="filterProfileComplaints('selesai')" class="px-3 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer text-slate-600 hover:bg-slate-100">
                  Selesai
                </button>
              </div>

              <div id="profileComplaintsContainer" class="space-y-3"></div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 5. TAB: PROGRES BELAJAR & KUIS                           -->
          <!-- ======================================================== -->
          <section id="profile-tab-belajar" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-6">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                <div class="space-y-1">
                  <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                    <span>Profil Pelanggan</span>
                    <span>/</span>
                    <span class="text-[#0284C7] font-semibold">Progres Belajar &amp; Kuis</span>
                  </div>
                  <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">
                    Progres Belajar &amp; Skor Kuis Smart Water Academy
                  </h2>
                  <p class="text-xs text-slate-500">Modul literasi air bersih dan hasil ujian kuis mandiri warga Kota Payakumbuh.</p>
                </div>
                <a href="<?= url('/akademi-air') ?>" class="px-4 py-2 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer self-start sm:self-auto">
                  Buka Akademi
                </a>
              </div>

              <div class="space-y-3">
                <?php foreach ($ACADEMY_COURSES_SUMMARY as $course): ?>
                  <?php
                  $score = $currentUser['completedQuizzes'][$course['id']] ?? null;
                  $isPassed = $score !== null && $score >= 70;
                  ?>
                  <div class="p-4 rounded-xl border border-slate-100 bg-slate-50 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                      <h4 class="font-bold text-slate-900"><?= e($course['title']) ?></h4>
                      <span class="text-[11px] text-slate-500"><?= e($course['difficulty']) ?> &middot; Reward +<?= (int) $course['xpReward'] ?> XP</span>
                    </div>
                    <div class="flex items-center gap-2">
                      <?php if ($score !== null): ?>
                        <span class="font-mono text-xs font-bold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                          Nilai: <?= (int) $score ?>%
                        </span>
                      <?php else: ?>
                        <span class="text-slate-400 text-[11px]">Belum Kuis</span>
                      <?php endif; ?>

                      <?php if ($isPassed): ?>
                        <button type="button" onclick="openCertificateModal('<?= e($course['title']) ?>', <?= (int) $score ?>, 'Oktober 2026', 'CERT-SWA-<?= e(strtoupper($course['id'])) ?>')" class="px-3 py-1 rounded-lg text-xs font-semibold text-[#0284C7] bg-sky-50 hover:bg-sky-100 border border-sky-200 cursor-pointer">
                          Sertifikat
                        </button>
                      <?php endif; ?>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 6. TAB: PENGATURAN PRIVASI & KEAMANAN                    -->
          <!-- ======================================================== -->
          <section id="profile-tab-privasi" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-6">
              <div class="border-b border-slate-100 pb-3 space-y-1">
                <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                  <span>Profil Pelanggan</span>
                  <span>/</span>
                  <span class="text-[#0284C7] font-semibold">Pengaturan Privasi &amp; Keamanan</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Pengaturan Privasi &amp; Keamanan Akun</h2>
              </div>

              <div class="space-y-4 text-xs">
                <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl">
                  <div>
                    <span class="font-bold text-slate-900 block">Tampilkan Nama di Papan Komunitas</span>
                    <span class="text-slate-500 text-[11px]">Menampilkan nama Anda pada papan peringkat tantangan hemat air.</span>
                  </div>
                  <input id="privShowLeaderboard" type="checkbox" <?= !empty($privacy['showInLeaderboard']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-[#0284C7] cursor-pointer" />
                </div>

                <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl">
                  <div>
                    <span class="font-bold text-slate-900 block">Notifikasi WhatsApp</span>
                    <span class="text-slate-500 text-[11px]">Kirim notifikasi update tiket gangguan ke nomor WA terdaftar.</span>
                  </div>
                  <input id="privWaNotif" type="checkbox" <?= !empty($privacy['waNotifications']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-[#0284C7] cursor-pointer" />
                </div>

                <div class="flex items-center justify-between p-3.5 bg-slate-50 rounded-xl">
                  <div>
                    <span class="font-bold text-slate-900 block">Verifikasi 2 Langkah (2FA)</span>
                    <span class="text-slate-500 text-[11px]">Meminta OTP saat perubahan data kontak atau pembayaran.</span>
                  </div>
                  <input id="privTwoFactor" type="checkbox" <?= !empty($privacy['twoFactorAuth']) ? 'checked' : '' ?> class="w-4 h-4 rounded text-[#0284C7] cursor-pointer" />
                </div>
              </div>

              <div class="pt-2 flex justify-end">
                <button type="button" onclick="savePrivacySettings()" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-sm cursor-pointer">
                  Simpan Pengaturan
                </button>
              </div>
            </div>
          </section>

          <!-- ======================================================== -->
          <!-- 7. TAB: LENCANA KOMUNITAS                                -->
          <!-- ======================================================== -->
          <section id="profile-tab-lencana" class="profile-tab-panel hidden space-y-6">
            <div class="bg-white rounded-3xl border border-sky-100/90 p-6 sm:p-7 shadow-2xs space-y-6">
              <div class="border-b border-slate-100 pb-3 space-y-1">
                <div class="flex items-center gap-2 text-xs text-slate-400 font-medium">
                  <span>Profil Pelanggan</span>
                  <span>/</span>
                  <span class="text-[#0284C7] font-semibold">Lencana Komunitas</span>
                </div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight">Lencana Komunitas Hemat Air Payakumbuh</h2>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                <?php foreach ($BADGE_DEFINITIONS as $badge): ?>
                  <?php $isUnlocked = in_array($badge['id'], $userBadges, true); ?>
                  <div class="p-4 rounded-2xl border text-xs space-y-2 <?= $isUnlocked ? 'bg-amber-50/50 border-amber-200' : 'bg-slate-50 border-slate-200 opacity-60' ?>">
                    <div class="flex items-center justify-between">
                      <svg class="w-6 h-6 <?= $isUnlocked ? 'text-amber-600' : 'text-slate-400' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
                      <span class="text-[10px] font-bold px-2 py-0.5 rounded <?= $isUnlocked ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                        <?= $isUnlocked ? 'Terbuka' : 'Terkunci' ?>
                      </span>
                    </div>
                    <h4 class="font-bold text-slate-900"><?= e($badge['name']) ?></h4>
                    <p class="text-[11px] text-slate-500 leading-tight"><?= e($badge['description']) ?></p>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </section>

          <!-- Bottom Profile Sub-Footer -->
          <div class="pt-8 pb-4 border-t border-sky-100/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-[#0284C7] flex items-center justify-center text-white">
                <svg class="w-3.5 h-3.5 fill-white text-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
              </div>
              <div class="leading-tight">
                <span class="font-black text-[#0284C7] block">Tirta Sago</span>
                <span class="text-[10px] text-slate-400">PDAM Kota Payakumbuh</span>
              </div>
            </div>
            <div class="italic text-slate-500 text-[11px] text-center">
              Air Bersih, Hidup Sehat, Payakumbuh Maju
            </div>
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-[#0284C7] flex items-center justify-center text-white">
                <svg class="w-3.5 h-3.5 fill-white text-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
              </div>
              <div class="leading-tight text-right">
                <span class="font-black text-[#0284C7] block">Smart Water</span>
                <span class="text-[10px] text-slate-400">Payakumbuh</span>
              </div>
            </div>
          </div>

        </div>
      </div>
    </main>

    
<?php
/**
 * Kumpulan Modal Halaman Profil Pelanggan
 * 1. Edit Data Kontak Modal
 * 2. Buat Pengaduan Baru Modal
 * 3. E-Kuitansi Pembayaran Air Modal
 * 4. Sertifikat Smart Water Academy Modal
 * 5. Konfirmasi Keluar Akun Modal
 */
?>

<!-- ======================================================== -->
<!-- 1. MODAL: UBAH DATA KONTAK                               -->
<!-- ======================================================== -->
<div id="editContactModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 space-y-5 border border-slate-200 shadow-2xl relative">
    <button type="button" onclick="closeEditContactModal()" class="absolute top-5 right-5 p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
      <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center font-bold">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
      </div>
      <div>
        <h3 class="text-base font-bold text-slate-900">Ubah Data Kontak Pelanggan</h3>
        <p class="text-[11px] text-slate-500">Perbarui nomor telepon, email, dan alamat sambungan.</p>
      </div>
    </div>

    <form onsubmit="submitEditContact(event)" class="space-y-4 text-xs">
      <div>
        <label class="font-semibold text-slate-700 block mb-1">Nomor Telepon (WhatsApp)</label>
        <input id="editPhoneInput" type="text" value="<?= e($currentUser['phone'] ?? '0812-7389-4902') ?>" placeholder="0812-7389-4902" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 font-mono text-slate-900 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100" />
      </div>

      <div>
        <label class="font-semibold text-slate-700 block mb-1">Alamat Email</label>
        <input id="editEmailInput" type="email" value="<?= e($currentUser['email'] ?? 'warga.google@gmail.com') ?>" placeholder="warga.google@gmail.com" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100" />
      </div>

      <div>
        <label class="font-semibold text-slate-700 block mb-1">Alamat Lengkap Sambungan Rumah Tangga</label>
        <textarea id="editAddressInput" rows="3" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-slate-900 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 leading-relaxed"><?= e($currentUser['address'] ?? '') ?></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeEditContactModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
        <button type="submit" class="px-5 py-2 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors shadow-xs cursor-pointer">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================== -->
<!-- 2. MODAL: BUAT PENGADUAN BARU                            -->
<!-- ======================================================== -->
<div id="complaintModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs overflow-y-auto">
  <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 space-y-5 border border-slate-200 shadow-2xl relative my-8">
    <button type="button" onclick="closeComplaintModal()" class="absolute top-5 right-5 p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <div class="flex items-center gap-2.5 border-b border-slate-100 pb-3">
      <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center font-bold">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
      </div>
      <div>
        <h3 class="text-base font-bold text-slate-900">Ajukan Pengaduan Air Resmi</h3>
        <p class="text-[11px] text-slate-500">Laporan langsung diteruskan ke Tim Reaksi Cepat Tirta Sago.</p>
      </div>
    </div>

    <form id="profileComplaintForm" onsubmit="submitProfileComplaint(event)" class="space-y-3.5 text-xs">
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <div>
          <label class="font-semibold text-slate-700 block mb-1">Kategori Kendala</label>
          <select id="cmpCategorySelect" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 bg-white">
            <?php foreach ($COMPLAINT_CATEGORIES as $cat): ?>
              <option value="<?= e($cat['id']) ?>"><?= e($cat['label']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <label class="font-semibold text-slate-700 block mb-1">Kecamatan Lokasi</label>
          <select id="cmpDistrictSelect" class="w-full rounded-xl border border-slate-200 px-3 py-2.5 bg-white">
            <?php foreach ($DISTRICTS_PAYAKUMBUH as $d): ?>
              <option value="<?= e($d) ?>" <?= ($currentUser['district'] ?? '') === $d ? 'selected' : '' ?>><?= e($d) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div>
        <label class="font-semibold text-slate-700 block mb-1">Ringkasan Judul Keluhan</label>
        <input id="cmpTitleInput" type="text" required placeholder="Contoh: Kebocoran Pipa Sambungan Depan Rumah" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:outline-hidden focus:border-[#0284C7]" />
      </div>

      <div>
        <label class="font-semibold text-slate-700 block mb-1">Alamat Lengkap Kejadian</label>
        <input id="cmpAddressInput" type="text" required value="<?= e($currentUser['address'] ?? '') ?>" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:outline-hidden focus:border-[#0284C7]" />
      </div>

      <div>
        <label class="font-semibold text-slate-700 block mb-1">Detail Kronologi / Kondisi Lapangan</label>
        <textarea id="cmpDescInput" rows="3" required placeholder="Jelaskan kondisi semburan air, kekeruhan, atau waktu mulai kejadian..." class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 focus:outline-hidden focus:border-[#0284C7]"></textarea>
      </div>

      <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
        <button type="button" onclick="closeComplaintModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
        <button type="submit" class="px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors shadow-xs cursor-pointer">Kirim Pengaduan Resmi</button>
      </div>
    </form>
  </div>
</div>

<!-- ======================================================== -->
<!-- 3. MODAL: E-KUITANSI PEMBAYARAN AIR                      -->
<!-- ======================================================== -->
<div id="receiptModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
  <div class="bg-white rounded-3xl max-w-md w-full p-6 sm:p-7 space-y-5 border border-slate-200 shadow-2xl relative">
    <button type="button" onclick="closeReceiptModal()" class="absolute top-5 right-5 p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <div class="text-center space-y-1 border-b border-slate-100 pb-4">
      <div class="w-12 h-12 mx-auto rounded-2xl bg-cyan-50 border border-cyan-200 text-cyan-700 flex items-center justify-center mb-2">
        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1 2 1 2-1 2 1 2-1 2 1 2-1 2 1V2l-2 1-2-1-2 1-2-1-2 1-2-1-2 1Z"/><path d="M16 8h-6a2 2 0 1 0 0 4h4a2 2 0 1 1 0 4H8"/><path d="M12 17.5v-11"/></svg>
      </div>
      <h3 class="text-base font-extrabold text-slate-900">Bukti Pembayaran Rekening Air</h3>
      <p class="text-xs text-slate-500">Perumda Air Minum Tirta Sago Kota Payakumbuh</p>
    </div>

    <div class="bg-slate-50 p-4 rounded-2xl border border-slate-100 space-y-3 text-xs">
      <div class="flex justify-between">
        <span class="text-slate-500">No. Transaksi</span>
        <span id="rcInvoice" class="font-mono font-bold text-slate-900">-</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Periode Tagihan</span>
        <span id="rcPeriod" class="font-semibold text-slate-900">-</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">ID / No. Meteran</span>
        <span id="rcMeter" class="font-mono font-semibold text-slate-900"><?= e($currentUser['meterNumber'] ?? '') ?></span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Nama Pelanggan</span>
        <span id="rcName" class="font-semibold text-slate-900"><?= e($currentUser['name'] ?? '') ?></span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Pemakaian Air</span>
        <span id="rcM3" class="font-mono font-bold text-slate-900">-</span>
      </div>
      <div class="flex justify-between">
        <span class="text-slate-500">Waktu Bayar</span>
        <span id="rcDate" class="text-slate-800">-</span>
      </div>
      <div class="pt-2 border-t border-slate-200 flex justify-between items-baseline">
        <span class="font-bold text-slate-900">Total Tagihan Lunas</span>
        <span id="rcAmount" class="font-mono text-base font-black text-[#0284C7]">-</span>
      </div>
    </div>

    <div class="flex items-center justify-center gap-2 text-[11px] text-emerald-700 font-semibold bg-emerald-50 py-2 rounded-xl border border-emerald-200">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
      <span>Transaksi Sah &amp; Terdaftar di Kas PDAM</span>
    </div>

    <div class="flex items-center gap-3">
      <button type="button" onclick="window.print()" class="flex-1 py-2.5 rounded-xl text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
        <span>Cetak Bukti</span>
      </button>
      <button type="button" onclick="closeReceiptModal(); showProfileToast('E-Kuitansi berhasil diunduh dalam format PDF!');" class="flex-1 py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors flex items-center justify-center gap-1.5 shadow-sm cursor-pointer">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
        <span>Unduh PDF</span>
      </button>
    </div>
  </div>
</div>

<!-- ======================================================== -->
<!-- 4. MODAL: SERTIFIKAT AKADEMI AIR                         -->
<!-- ======================================================== -->
<div id="certificateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
  <div class="bg-white rounded-3xl max-w-lg w-full p-7 space-y-6 border border-slate-200 shadow-2xl relative text-center">
    <button type="button" onclick="closeCertificateModal()" class="absolute top-5 right-5 p-1.5 rounded-full hover:bg-slate-100 text-slate-400 hover:text-slate-700 transition-colors cursor-pointer">
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>

    <div class="border-4 border-double border-amber-300 p-6 rounded-2xl bg-amber-50/20 space-y-4">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center shadow-xs">
        <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
      </div>
      <h3 class="text-lg font-black text-slate-900">Smart Water Academy Payakumbuh</h3>
      <p class="text-xs text-slate-600">Diberikan kepada warga berdedikasi:</p>
      <p id="certUserName" class="text-base font-extrabold text-[#0284C7]"><?= e($currentUser['name'] ?? '') ?></p>
      <p class="text-xs text-slate-600 leading-relaxed">
        Telah berhasil menyelesaikan seluruh modul pembelajaran dan lulus ujian kuis
        <strong id="certCourseTitle">"-"</strong> dengan nilai pencapaian
        <strong id="certScore" class="text-emerald-700 font-mono">100%</strong>.
      </p>
      <div class="pt-2 flex items-center justify-between text-[10px] text-slate-400 border-t border-amber-200/60">
        <span id="certCode" class="font-mono">-</span>
        <span id="certDate">Oktober 2026</span>
      </div>
    </div>

    <button type="button" onclick="closeCertificateModal(); showProfileToast('Sertifikat berhasil disimpan!');" class="w-full py-2.5 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors flex items-center justify-center gap-2 cursor-pointer">
      <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
      <span>Simpan Sertifikat Digital</span>
    </button>
  </div>
</div>

<!-- ======================================================== -->
<!-- 5. MODAL: KONFIRMASI KELUAR AKUN                         -->
<!-- ======================================================== -->
<div id="logoutConfirmModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-xs">
  <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 border border-slate-200 shadow-2xl text-center">
    <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 flex items-center justify-center">
      <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
    </div>
    <h3 class="text-base font-bold text-slate-900">Keluar dari Akun?</h3>
    <p class="text-xs text-slate-500 leading-relaxed">
      Anda akan keluar dari sesi pelanggan saat ini. Anda dapat masuk kembali kapan saja dengan email Anda.
    </p>
    <div class="flex items-center gap-3 pt-2">
      <button type="button" onclick="closeLogoutModal()" class="flex-1 py-2.5 rounded-xl text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 transition-colors cursor-pointer">Batal</button>
      <a href="index.php?action=logout" class="flex-1 py-2.5 rounded-xl text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 transition-colors shadow-sm text-center">Ya, Keluar</a>
    </div>
  </div>
</div>



  <?php else: ?>
    <!-- ======================================================== -->
    <!-- GUEST PROFILE FALLBACK (BELUM MASUK AKUN - App.tsx)      -->
    <!-- ======================================================== -->
    <main class="flex-1">
      <div class="max-w-xl mx-auto px-4 py-16 text-center space-y-6">
        <div class="w-16 h-16 mx-auto rounded-3xl bg-gradient-to-tr from-cyan-600 to-sky-400 flex items-center justify-center text-white shadow-lg shadow-cyan-600/20">
          <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        </div>

        <div class="space-y-2">
          <h2 class="text-2xl font-extrabold text-slate-900 tracking-tight">Halaman Pribadi Pelanggan</h2>
          <p class="text-xs text-slate-500 max-w-md mx-auto leading-relaxed">
            Akses laporan resmi kubikasi air, nomor meteran, kategori tarif, riwayat pengaduan resmi, serta progres literasi konservasi air Kota Payakumbuh.
          </p>
        </div>

        <div class="grid grid-cols-2 gap-3 text-left max-w-md mx-auto pt-2">
          <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-xs space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-800 block">Data Pelanggan</span>
            <p class="text-xs font-semibold text-slate-800">ID Meteran &amp; Tarif PDAM</p>
            <p class="text-[11px] text-slate-400">Sinkronisasi data resmi sambungan</p>
          </div>
          <div class="p-3.5 rounded-2xl bg-white border border-slate-200/90 shadow-xs space-y-1">
            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-800 block">Laporan Air</span>
            <p class="text-xs font-semibold text-slate-800">Kubikasi &amp; E-Kuitansi</p>
            <p class="text-[11px] text-slate-400">Pantau tagihan &amp; hemat air</p>
          </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 pt-3">
          <a href="<?= url('/login') ?>" class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-sm">
            Masuk ke Akun Anda
          </a>
          <a href="<?= url('/daftar') ?>" class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold text-cyan-900 bg-cyan-50 hover:bg-cyan-100 border border-cyan-200 transition-colors">
            Daftar Akun Baru
          </a>
        </div>
      </div>
    </main>
  <?php endif; ?>

  <!-- Site Footer -->
  <?php
$SHARED_FOOTER_VARIANT = 'user';
require $legacySharedPath . '/footer.php';
?>

  <!-- TirtaBot AI Chatbot Widget -->
  <?php require $legacySharedPath . '/chatbot.php'; ?>

  <script>
    window.__USER_PROFILE__ = <?= json_encode($currentUser, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.__USER_COMPLAINTS__ = <?= json_encode($userComplaints, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
  </script>
  <script src="../../assets/js/pages/user.js"></script>
</body>
</html>
