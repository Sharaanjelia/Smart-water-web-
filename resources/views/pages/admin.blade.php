<?php
/**
 * Command Center Admin & Operator SCADA - PDAM Tirta Sago Kota Payakumbuh
 * Versi PHP Standalone lengkap dari src/components/admin/AdminDashboardView.tsx
 */

$complaints         = $adminState['complaints'] ?? [];
$announcements      = $adminState['announcements'] ?? [];
$reservoirs         = $adminState['reservoirs'] ?? [];
$meterVerifications = $adminState['meterVerifications'] ?? [];
$auditLogs          = $adminState['auditLogs'] ?? [];

$totalComplaintsCount     = count($complaints);
$newComplaintsCount       = count(array_filter($complaints, fn($c) => ($c['status'] ?? '') === 'BARU'));
$inProgressComplaints     = count(array_filter($complaints, fn($c) => in_array($c['status'] ?? '', ['DIPROSES', 'MENUNGGU TINDAK LANJUT'], true)));
$resolvedComplaintsCount  = count(array_filter($complaints, fn($c) => ($c['status'] ?? '') === 'SELESAI'));
$pendingMeterVerification = count(array_filter($meterVerifications, fn($m) => ($m['status'] ?? '') === 'menunggu_validasi'));
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
  <link rel="stylesheet" href="../../assets/css/pages/admin.css" />
</head>
<body class="bg-[#F4F7FB] text-slate-800 antialiased font-sans">

  <!-- Toast Notification -->
  <div id="adminToast" class="hidden fixed bottom-6 right-6 z-50 bg-slate-900 text-white px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-3 border border-slate-700 animate-slide-down text-xs font-semibold">
    <svg class="w-4 h-4 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <span id="adminToastMsg">Aksi berhasil diproses.</span>
  </div>

  <!-- Mobile Backdrop Overlay -->
  <div id="adminSidebarBackdrop" onclick="closeAdminSidebar()" class="hidden fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs lg:hidden"></div>

  <div class="min-h-screen flex">

    <!-- ======================================================== -->
    <!-- 1. SIDEBAR NAVIGASI (DESKTOP & MOBILE DRAWER)            -->
    <!-- ======================================================== -->
    <aside id="adminSidebar" class="fixed inset-y-0 left-0 z-50 w-64 xl:w-72 bg-[#0C1E3C] text-slate-300 flex flex-col border-r border-slate-800 transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static lg:h-screen lg:sticky lg:top-0 shadow-2xl lg:shadow-none select-none">
      <!-- Brand Header -->
      <div class="p-5 border-b border-slate-800 flex items-center justify-between gap-3">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-sky-400 to-[#0284C7] flex items-center justify-center text-white shadow-md">
            <svg class="w-5 h-5 fill-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
            </svg>
          </div>
          <div class="leading-tight">
            <span class="font-extrabold tracking-tight text-sm text-white block">
              TIRTA SAGO <span class="text-sky-400 font-semibold">SCADA</span>
            </span>
            <span class="text-[10px] text-slate-400 font-mono">
              Command Center PDAM
            </span>
          </div>
        </div>

        <button type="button" onclick="closeAdminSidebar()" class="lg:hidden p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 cursor-pointer" aria-label="Tutup Menu">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <!-- System Online Status -->
      <div class="px-4 pt-3 pb-1">
        <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900/70 border border-slate-800 text-[11px]">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            <span class="font-bold text-emerald-300">SCADA Online</span>
          </div>
          <span class="font-mono text-[10px] text-slate-400">4/4 Instalasi</span>
        </div>
      </div>

      <!-- Admin Profile Box -->
      <div class="px-4 py-2">
        <div class="p-3 rounded-2xl bg-slate-900/50 border border-slate-800/80 flex items-center gap-3">
          <div class="w-9 h-9 rounded-full bg-sky-500/20 border border-sky-400/40 flex items-center justify-center text-sky-300 text-xs font-bold shrink-0">
            HP
          </div>
          <div class="min-w-0 flex-1 leading-tight text-left">
            <span class="font-bold text-white text-xs block truncate"><?= e($adminUser['name']) ?></span>
            <span class="text-[10px] text-sky-400 font-medium block truncate"><?= e($adminUser['staffRole']) ?></span>
            <span class="text-[9px] text-slate-400 font-mono block"><?= e($adminUser['staffId']) ?></span>
          </div>
        </div>
      </div>

      <!-- Navigation Menu List -->
      <div class="flex-1 px-3 py-2 space-y-1 overflow-y-auto">
        <div class="px-3 pb-1.5 pt-2 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
          Menu Operasional
        </div>

        <button type="button" data-tab="ikhtisar" onclick="switchAdminTab('ikhtisar')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all cursor-pointer bg-[#0284C7] text-white shadow-md shadow-sky-600/25 font-bold">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
            <span class="truncate">Ikhtisar &amp; KPI</span>
          </div>
        </button>

        <button type="button" data-tab="tiket" onclick="switchAdminTab('tiket')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span class="truncate">Manajemen Tiket Aduan</span>
          </div>
          <span id="sidebarBadgeTiket" class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 bg-rose-500 text-white <?= $newComplaintsCount > 0 ? '' : 'hidden' ?>"><?= $newComplaintsCount ?></span>
        </button>

        <button type="button" data-tab="meteran" onclick="switchAdminTab('meteran')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
            <span class="truncate">Validasi Stand AI-OCR</span>
          </div>
          <span id="sidebarBadgeMeteran" class="px-2 py-0.5 rounded-full text-[10px] font-bold shrink-0 bg-amber-500 text-white <?= $pendingMeterVerification > 0 ? '' : 'hidden' ?>"><?= $pendingMeterVerification ?></span>
        </button>

        <button type="button" data-tab="pengumuman" onclick="switchAdminTab('pengumuman')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
            <span class="truncate">Pengumuman Layanan</span>
          </div>
        </button>

        <button type="button" data-tab="reservoir" onclick="switchAdminTab('reservoir')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
            <span class="truncate">SCADA Reservoir &amp; Kualitas</span>
          </div>
        </button>

        <button type="button" data-tab="pelanggan" onclick="switchAdminTab('pelanggan')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            <span class="truncate">Master Pelanggan &amp; Tagihan</span>
          </div>
        </button>

        <button type="button" data-tab="audit" onclick="switchAdminTab('audit')" class="admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80">
          <div class="flex items-center gap-3 truncate">
            <svg class="admin-nav-icon w-4 h-4 shrink-0 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
            <span class="truncate">Audit Trail</span>
          </div>
        </button>
      </div>

      <!-- Bottom Utility Actions -->
      <div class="p-3 border-t border-slate-800 space-y-1">
        <a href="<?= url('/beranda') ?>" class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors cursor-pointer">
          <svg class="w-4 h-4 text-sky-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
          <span>Lihat Portal Warga</span>
        </a>

        <button type="button" onclick="syncAdminData()" class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-white hover:bg-slate-800/80 transition-colors cursor-pointer">
          <svg class="w-4 h-4 text-emerald-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
          <span>Sinkronisasi Data</span>
        </button>

        <a href="<?= url('/login?mode=admin-login') ?>" class="w-full flex items-center gap-2.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-rose-400 hover:text-rose-300 hover:bg-rose-950/40 transition-colors cursor-pointer">
          <svg class="w-4 h-4 text-rose-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
          <span>Keluar Akun Admin</span>
        </a>
      </div>
    </aside>

    <!-- ======================================================== -->
    <!-- 2. RIGHT MAIN CONTENT AREA                               -->
    <!-- ======================================================== -->
    <div class="flex-1 flex flex-col min-w-0 bg-[#F4F7FB] min-h-screen">
      <!-- Top Header Bar -->
      <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200 h-16 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4 shadow-2xs">
        <div class="flex items-center gap-3">
          <button type="button" onclick="openAdminSidebar()" class="lg:hidden p-2 rounded-xl text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer" aria-label="Buka Menu Sidebar">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
          </button>

          <div>
            <div class="flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
              <span>Portal Admin</span>
              <span>/</span>
              <span id="adminBreadcrumbLabel" class="text-[#0284C7] font-bold">Ikhtisar &amp; KPI</span>
            </div>
            <h1 id="adminHeaderTitle" class="text-base sm:text-lg font-black text-slate-900 tracking-tight leading-tight">
              Ikhtisar &amp; Telemetri Real-time
            </h1>
          </div>
        </div>

        <div class="flex items-center gap-3 text-xs">
          <span class="hidden sm:inline-flex items-center gap-1.5 text-slate-500 font-medium bg-slate-100 px-3 py-1.5 rounded-xl">
            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
            <span>Payakumbuh, 24 Oktober 2026</span>
          </span>

          <button type="button" onclick="syncAdminData()" title="Sinkronisasi Data" class="p-2 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/><path d="M8 16H3v5"/></svg>
          </button>

          <a href="<?= url('/beranda') ?>" class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-sky-50 text-[#0284C7] hover:bg-sky-100 font-bold border border-sky-200 transition-colors cursor-pointer">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
            <span>Portal Warga</span>
          </a>

          <div class="h-5 w-px bg-slate-200 hidden sm:block"></div>

          <div class="w-8 h-8 rounded-full bg-[#0C1E3C] text-sky-300 border border-slate-700 flex items-center justify-center font-bold text-xs shadow-xs">
            HP
          </div>
        </div>
      </header>

      <!-- Main Content Area -->
      <main class="p-4 sm:p-6 lg:p-8 space-y-6 max-w-7xl w-full mx-auto flex-1">

        <!-- ======================================================== -->
        <!-- TAB 1: IKHTISAR & KPI REAL-TIME                          -->
        <!-- ======================================================== -->
        <section id="tab-ikhtisar" class="admin-tab-panel space-y-6">
          <!-- Header Banner -->
          <div class="bg-gradient-to-r from-[#0C1E3C] via-[#0369A1] to-[#0284C7] rounded-3xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2 z-10 max-w-2xl">
              <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-sky-200 text-xs font-semibold border border-white/15">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                <span>Pusat Kendali Operasional Terpadu PDAM Tirta Sago</span>
              </div>
              <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                Selamat Datang, <?= e($adminUser['name']) ?>
              </h2>
              <p class="text-xs sm:text-sm text-sky-100/90 leading-relaxed">
                Semua sistem distribusi air Payakumbuh terpantau normal. Debit hulu Mata Air Sikabu 142.5 L/detik, 4 instalasi reservoir dalam kondisi prima, dan <span id="ikhtisarPendingMeterText"><?= $pendingMeterVerification ?></span> laporan meteran memerlukan validasi.
              </p>
            </div>

            <div class="z-10 flex flex-wrap gap-2.5 shrink-0">
              <button type="button" onclick="openAddAnnouncementModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-white text-[#0C1E3C] hover:bg-sky-50 transition-colors shadow-sm flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                <span>Buat Pengumuman</span>
              </button>
              <button type="button" onclick="switchAdminTab('tiket')" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-sky-500/30 hover:bg-sky-500/40 text-white border border-white/20 transition-colors flex items-center gap-2 cursor-pointer">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                <span>Tinjau <span id="ikhtisarBannerNewTicketCount"><?= $newComplaintsCount ?></span> Tiket Baru</span>
              </button>
            </div>
          </div>

          <!-- 6 KPI Cards Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <!-- Card 1 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Tiket Pengaduan Warga</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span id="kpiTotalComplaints" class="text-3xl font-black font-mono text-slate-900"><?= $totalComplaintsCount ?></span>
                <span class="text-xs text-slate-500">Tiket Terdaftar</span>
              </div>
              <div class="grid grid-cols-3 gap-2 pt-2 border-t border-slate-100 text-[11px] font-semibold text-center">
                <div class="p-1.5 rounded-lg bg-rose-50 text-rose-700">
                  <span id="kpiNewComplaints" class="block font-bold"><?= $newComplaintsCount ?></span>
                  <span class="text-[10px] text-rose-500">Baru</span>
                </div>
                <div class="p-1.5 rounded-lg bg-amber-50 text-amber-700">
                  <span id="kpiInProgressComplaints" class="block font-bold"><?= $inProgressComplaints ?></span>
                  <span class="text-[10px] text-amber-500">Diproses</span>
                </div>
                <div class="p-1.5 rounded-lg bg-emerald-50 text-emerald-700">
                  <span id="kpiResolvedComplaints" class="block font-bold"><?= $resolvedComplaintsCount ?></span>
                  <span class="text-[10px] text-emerald-500">Selesai</span>
                </div>
              </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Validasi Stand Meteran</span>
                <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span id="kpiTotalMeters" class="text-3xl font-black font-mono text-slate-900"><?= count($meterVerifications) ?></span>
                <span class="text-xs text-slate-500">Total Pembacaan</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-xs">
                <span class="text-amber-600 font-semibold flex items-center gap-1">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <span id="kpiPendingMeters"><?= $pendingMeterVerification ?> Menunggu Validasi</span>
                </span>
                <button type="button" onclick="switchAdminTab('meteran')" class="text-[#0284C7] font-bold hover:underline cursor-pointer">
                  Verifikasi &rarr;
                </button>
              </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Debit Distribusi Aktif</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-slate-900">357.5</span>
                <span class="text-xs text-slate-500">Liter / Detik</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                <span>Hulu Sikabu: 142.5 L/s</span>
                <span class="text-emerald-600 font-semibold flex items-center gap-1">
                  <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/></svg>
                  <span>Stabil (+2.1%)</span>
                </span>
              </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Level Cadangan Reservoir</span>
                <span class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-slate-900">80.7%</span>
                <span class="text-xs text-slate-500">Rata-Rata 4 Lokasi</span>
              </div>
              <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                <div class="bg-[#0284C7] h-full rounded-full" style="width: 80.7%"></div>
              </div>
            </div>

            <!-- Card 5 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Pelanggan Terdaftar</span>
                <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-slate-900">42.850</span>
                <span class="text-xs text-slate-500">Sambungan Rumah (SR)</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                <span>Cakupan: 5 Kecamatan Payakumbuh</span>
                <span class="text-emerald-600 font-semibold">96.8% Aktif</span>
              </div>
            </div>

            <!-- Card 6 -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 shadow-2xs space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-500">Baku Mutu Air (Permenkes)</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
              </div>
              <div class="flex items-baseline gap-2">
                <span class="text-3xl font-black font-mono text-slate-900">0.8</span>
                <span class="text-xs text-slate-500">NTU (Standar &lt; 5.0)</span>
              </div>
              <div class="flex items-center justify-between pt-2 border-t border-slate-100 text-[11px] text-slate-500">
                <span>pH Rata-rata: 7.2 (Ideal)</span>
                <span class="text-emerald-600 font-semibold">Memenuhi Syarat</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions & Recent Activity Dual Grid -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                    <span>Tiket Pengaduan Masuk Terkini</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">Klik tiket untuk melakukan disposisi dan penanganan regu teknisi.</p>
                </div>
                <button type="button" onclick="switchAdminTab('tiket')" class="text-xs font-bold text-[#0284C7] hover:underline cursor-pointer">
                  Lihat Semua (<span id="ikhtisarAllTicketsCount"><?= $totalComplaintsCount ?></span>) &rarr;
                </button>
              </div>
              <div id="ikhtisarRecentTickets" class="divide-y divide-slate-100"></div>
            </div>

            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
              <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                  <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                    <span>Status Instalasi Reservoir</span>
                  </h3>
                  <p class="text-xs text-slate-500 mt-0.5">Telemetri SCADA ketinggian air &amp; debit.</p>
                </div>
                <button type="button" onclick="switchAdminTab('reservoir')" class="text-xs font-bold text-[#0284C7] hover:underline cursor-pointer">
                  Detail SCADA &rarr;
                </button>
              </div>
              <div id="ikhtisarReservoirs" class="space-y-3"></div>
            </div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 2: MANAJEMEN TIKET ADUAN & DISPATCHER                -->
        <!-- ======================================================== -->
        <section id="tab-tiket" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
              <div>
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                  <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span>Manajemen Tiket Pengaduan &amp; Disposisi Lapangan</span>
                </h2>
                <p class="text-xs text-slate-500 mt-1">
                  Kelola dan delegasikan tiket aduan warga Kota Payakumbuh secara transparan dan terdata.
                </p>
              </div>
              <div class="flex items-center gap-2 text-xs">
                <span id="tiketTotalBadge" class="font-bold text-slate-700 bg-sky-50 px-3 py-1.5 rounded-xl border border-sky-100">
                  Total: <?= $totalComplaintsCount ?> Tiket
                </span>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
              <div class="sm:col-span-5 relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input id="ticketSearchInput" type="text" oninput="filterTickets()" placeholder="Cari ID tiket, nama pelapor, kendala, atau alamat..." class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100" />
              </div>

              <div class="sm:col-span-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                <select id="ticketStatusSelect" onchange="filterTickets()" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 focus:outline-hidden focus:border-[#0284C7]">
                  <option value="semua">Semua Status Tiket</option>
                  <option value="BARU">BARU (Belum Ditangani)</option>
                  <option value="DIPROSES">DIPROSES (Regu Menuju Lokasi)</option>
                  <option value="MENUNGGU TINDAK LANJUT">MENUNGGU TINDAK LANJUT</option>
                  <option value="SELESAI">SELESAI (Tuntas)</option>
                </select>
              </div>

              <div class="sm:col-span-3">
                <select id="ticketDistrictSelect" onchange="filterTickets()" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 focus:outline-hidden focus:border-[#0284C7]">
                  <option value="semua">Semua Kecamatan</option>
                  <?php foreach ($DISTRICTS_PAYAKUMBUH as $d): ?>
                    <option value="<?= e($d) ?>"><?= e($d) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <div class="lg:col-span-5 bg-white rounded-3xl border border-slate-200/90 p-4 shadow-2xs space-y-3 max-h-[750px] overflow-y-auto">
              <div class="px-2 py-1 text-xs font-bold text-slate-500 uppercase tracking-wider flex items-center justify-between">
                <span>Daftar Tiket (<span id="filteredTicketCount"><?= $totalComplaintsCount ?></span>)</span>
                <button id="ticketResetFilterBtn" type="button" onclick="resetTicketFilters()" class="hidden text-[#0284C7] lowercase hover:underline cursor-pointer">
                  Reset filter
                </button>
              </div>
              <div id="ticketListContainer" class="space-y-3"></div>
            </div>

            <div id="ticketDetailPanel" class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-6"></div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 3: VALIDASI STAND METERAN & AI-OCR                   -->
        <!-- ======================================================== -->
        <section id="tab-meteran" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
              <div class="space-y-1">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                  <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 14 4-4"/><path d="M3.34 19a10 10 0 1 1 17.32 0"/></svg>
                  <span>Verifikasi Stand Meteran &amp; Deteksi AI-OCR</span>
                </h2>
                <p class="text-xs text-slate-500">
                  Validasi angka register fisik meteran air hasil jepretan kamera mobile AI-OCR dan Lapor Mandiri warga sebelum dimasukkan ke sistem rekening billing.
                </p>
              </div>

              <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 text-xs font-bold flex items-center gap-1.5">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                  <span id="meterPendingHeaderText"><?= $pendingMeterVerification ?> Menunggu Validasi</span>
                </span>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <th class="p-3.5 rounded-l-xl">Pelanggan / ID Meter</th>
                    <th class="p-3.5">Sumber &amp; Tanggal</th>
                    <th class="p-3.5">Stand Bulan Lalu</th>
                    <th class="p-3.5">Stand Terbaca</th>
                    <th class="p-3.5">Konsumsi (m³)</th>
                    <th class="p-3.5">AI-OCR Confidence</th>
                    <th class="p-3.5">Status</th>
                    <th class="p-3.5 rounded-r-xl text-right">Aksi Operator</th>
                  </tr>
                </thead>
                <tbody id="meterTableBody" class="divide-y divide-slate-100"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 4: PUBLIKASI PENGUMUMAN LAYANAN                      -->
        <!-- ======================================================== -->
        <section id="tab-pengumuman" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
              <div class="space-y-1">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                  <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
                  <span>Pusat Publikasi Pengumuman Layanan &amp; Pemeliharaan</span>
                </h2>
                <p class="text-xs text-slate-500">
                  Kirim pengumuman resmi ke portal pelanggan terkait jadwal perbaikan pipa, flushing sedimen, atau peringatan darurat.
                </p>
              </div>

              <button type="button" onclick="openAddAnnouncementModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold bg-[#0284C7] hover:bg-[#0369A1] text-white shadow-xs transition-colors flex items-center gap-2 cursor-pointer self-start sm:self-auto">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" x2="12" y1="5" y2="19"/><line x1="5" x2="19" y1="12" y2="12"/></svg>
                <span>Tambah Pengumuman Baru</span>
              </button>
            </div>

            <div id="announcementListContainer" class="space-y-4"></div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 5: SCADA RESERVOIR & TELEMETRI KUALITAS AIR          -->
        <!-- ======================================================== -->
        <section id="tab-reservoir" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-4">
              <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-2.48a2 2 0 0 0-1.93 1.46l-2.35 8.36a.25.25 0 0 1-.48 0L9.24 2.18a.25.25 0 0 0-.48 0l-2.35 8.36A2 2 0 0 1 4.49 12H2"/></svg>
                <span>Sistem SCADA &amp; Telemetri Sensor Kualitas Air Payakumbuh</span>
              </h2>
              <p class="text-xs text-slate-500 mt-1">
                Pemantauan real-time ketinggian penampungan, debit transmisi, tekanan pipa distribusi, kekeruhan (NTU), pH, dan pembukaan katup motorik.
              </p>
            </div>
            <div id="reservoirCardsGrid" class="grid grid-cols-1 md:grid-cols-2 gap-6"></div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 6: MASTER DATA PELANGGAN & TAGIHAN                   -->
        <!-- ======================================================== -->
        <section id="tab-pelanggan" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
              <div class="space-y-1">
                <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                  <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                  <span>Register Master Pelanggan &amp; Rekening Tagihan</span>
                </h2>
                <p class="text-xs text-slate-500">
                  Database sambungan rumah tangga, status pembayaran tagihan bulan berjalan, dan riwayat pemakaian air PDAM Tirta Sago.
                </p>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
              <div class="sm:col-span-8 relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                <input id="customerSearchInput" type="text" oninput="filterCustomers()" placeholder="Cari nama pelanggan, nomor meteran, atau alamat..." class="w-full pl-10 pr-3.5 py-2.5 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-[#0284C7]" />
              </div>
              <div class="sm:col-span-4">
                <select id="customerDistrictSelect" onchange="filterCustomers()" class="w-full py-2.5 px-3 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700">
                  <option value="semua">Semua Kecamatan</option>
                  <?php foreach ($DISTRICTS_PAYAKUMBUH as $d): ?>
                    <option value="<?= e($d) ?>"><?= e($d) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="overflow-x-auto">
              <table class="w-full text-left text-xs">
                <thead>
                  <tr class="border-b border-slate-200 bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider">
                    <th class="p-3.5 rounded-l-xl">Nama Pelanggan</th>
                    <th class="p-3.5">No. Meteran</th>
                    <th class="p-3.5">Kategori Tarif</th>
                    <th class="p-3.5">Kecamatan / Alamat</th>
                    <th class="p-3.5">Konsumsi Okt 2026</th>
                    <th class="p-3.5">Tagihan Terkini</th>
                    <th class="p-3.5">Status Pembayaran</th>
                    <th class="p-3.5 rounded-r-xl text-right">Aksi</th>
                  </tr>
                </thead>
                <tbody id="customerTableBody" class="divide-y divide-slate-100"></tbody>
              </table>
            </div>
          </div>
        </section>

        <!-- ======================================================== -->
        <!-- TAB 7: AUDIT TRAIL & LOG AKTIVITAS                       -->
        <!-- ======================================================== -->
        <section id="tab-audit" class="admin-tab-panel hidden space-y-6">
          <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-2xs space-y-4">
            <div class="border-b border-slate-100 pb-4">
              <h2 class="text-xl font-extrabold text-slate-900 tracking-tight flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                <span>Audit Trail &amp; Log Aktivitas Sistem Dispatcher</span>
              </h2>
              <p class="text-xs text-slate-500 mt-1">
                Catatan jejak audit integritas dan transparansi setiap tindakan penanganan tiket, pengaturan katup reservoir, dan validasi meteran.
              </p>
            </div>
            <div id="auditLogsContainer" class="divide-y divide-slate-100 text-xs"></div>
          </div>
        </section>

      </main>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL: KOREKSI STAND METERAN                             -->
  <!-- ======================================================== -->
  <div id="meterCorrectionModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl space-y-4 border border-slate-100">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h3 id="meterModalTitle" class="text-base font-bold text-slate-900">Koreksi Stand Meter</h3>
        <button type="button" onclick="closeMeterCorrectionModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <form onsubmit="handleCorrectMeterSubmit(event)" class="space-y-4 text-xs">
        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-700 space-y-1">
          <p><strong>Nomor Meter:</strong> <span id="meterModalNumber" class="font-mono">-</span></p>
          <p><strong>Stand Bulan Lalu:</strong> <span id="meterModalLastStand" class="font-mono">-</span></p>
          <p><strong>Hasil Bacaan AI:</strong> <span id="meterModalAiStand" class="font-mono">-</span></p>
        </div>

        <div class="space-y-1">
          <label class="font-semibold text-slate-700 block">Angka Stand Fisik Terkoreksi (m³)</label>
          <input id="meterCorrectedInput" type="number" step="0.1" required class="w-full p-2.5 rounded-xl border border-slate-200 font-mono text-base font-bold text-slate-900" />
        </div>

        <div class="space-y-1">
          <label class="font-semibold text-slate-700 block">Catatan Koreksi Operator</label>
          <textarea id="meterCorrectionNote" rows="2" placeholder="Contoh: Dial nomor terakhir dikoreksi dari 2 menjadi 0 setelah inspeksi foto resolusi tinggi..." class="w-full p-2.5 rounded-xl border border-slate-200"></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button type="button" onclick="closeMeterCorrectionModal()" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 hover:bg-slate-200 font-semibold cursor-pointer">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold cursor-pointer">Simpan &amp; Verifikasi</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ======================================================== -->
  <!-- MODAL: TAMBAH PENGUMUMAN LAYANAN                         -->
  <!-- ======================================================== -->
  <div id="addAnnouncementModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/50 backdrop-blur-xs overflow-y-auto">
    <div class="bg-white rounded-3xl max-w-xl w-full p-6 sm:p-7 shadow-2xl space-y-4 my-8 border border-slate-100">
      <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
          <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
          <span>Buat Pengumuman Layanan Baru</span>
        </h3>
        <button type="button" onclick="closeAddAnnouncementModal()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 cursor-pointer">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <form id="addAnnouncementForm" onsubmit="handleCreateAnnouncementSubmit(event)" class="space-y-3.5 text-xs">
        <div class="space-y-1">
          <label class="font-semibold text-slate-700 block">Judul Pengumuman</label>
          <input id="annTitleInput" type="text" placeholder="Contoh: Pembersihan Berkala Bak Prasedimentasi Sikabu" class="w-full p-2.5 rounded-xl border border-slate-200" required />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
          <div class="space-y-1">
            <label class="font-semibold text-slate-700 block">Kategori</label>
            <select id="annCategorySelect" class="w-full p-2.5 rounded-xl border border-slate-200 bg-white">
              <option value="pemeliharaan">Pemeliharaan Terjadwal</option>
              <option value="gangguan_darurat">Gangguan Darurat / Pipa Pecah</option>
              <option value="kualitas_air">Kualitas Air / Kekeruhan</option>
              <option value="info_layanan">Informasi Pelayanan Umum</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="font-semibold text-slate-700 block">Estimasi Normalisasi</label>
            <input id="annEstimatedInput" type="text" value="Hari ini, 18:00 WIB" placeholder="Contoh: 18 Oktober 2026, 17:00 WIB" class="w-full p-2.5 rounded-xl border border-slate-200" />
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="font-semibold text-slate-700 block">Kecamatan Terdampak</label>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 bg-slate-50 p-3 rounded-xl border border-slate-200">
            <?php foreach ($DISTRICTS_PAYAKUMBUH as $idx => $dist): ?>
              <label class="inline-flex items-center gap-2 text-[11px] font-medium text-slate-700 cursor-pointer">
                <input type="checkbox" value="<?= e($dist) ?>" <?= $idx === 0 ? 'checked' : '' ?> class="ann-district-cb rounded text-[#0284C7]" />
                <span><?= e($dist) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="space-y-1">
          <label class="font-semibold text-slate-700 block">Deskripsi Detail</label>
          <textarea id="annDescriptionInput" rows="3" placeholder="Jelaskan jenis pekerjaan dan implikasi penurunan debit air..." class="w-full p-2.5 rounded-xl border border-slate-200" required></textarea>
        </div>

        <div class="space-y-1">
          <label class="font-semibold text-slate-700 block">Saran Aksi bagi Warga</label>
          <input id="annAdviceInput" type="text" placeholder="Contoh: Tampung air bersih secukupnya sebelum pukul 09:00 WIB" class="w-full p-2.5 rounded-xl border border-slate-200" />
        </div>

        <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
          <button type="button" onclick="closeAddAnnouncementModal()" class="px-4 py-2 rounded-xl text-slate-600 bg-slate-100 font-semibold cursor-pointer">Batal</button>
          <button type="submit" class="px-5 py-2 rounded-xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold cursor-pointer">Publikasikan Pengumuman</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    window.__ADMIN_STATE__ = <?= json_encode($adminState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.__ADMIN_CUSTOMERS__ = <?= json_encode($ADMIN_DEMO_CUSTOMERS, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>;
    window.__ADMIN_USER_NAME__ = <?= json_encode($adminUser['name'], JSON_UNESCAPED_UNICODE) ?>;
  </script>
  <script src="../../assets/js/pages/admin.js"></script>
</body>
</html>
