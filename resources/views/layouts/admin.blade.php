<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$currentUser = session('swc_user') ?? $_SESSION['swc_user'] ?? ($currentUser ?? null);
$currentTab = $currentTab ?? 'admin-dashboard';
$legacySharedPath = resource_path('views/shared');
require_once $legacySharedPath . '/brand_logo.php';

$authUser = Auth::user();
$isAdmin = $authUser ? $authUser->isAdmin() : false;
$isScada = $authUser ? $authUser->isScadaOperator() : false;
$isField = $authUser ? $authUser->isFieldOfficer() : false;
$isLegacyStaff = $authUser ? $authUser->hasRole('staff') : false;

$officerName = $authUser ? $authUser->name : ($currentUser['name'] ?? ($currentUser['fullName'] ?? 'Petugas PDAM'));
$staffProfile = $authUser?->staffProfile;

if ($isAdmin) {
    $headerRoleBadge = 'COMMAND CENTER • SUPER ADMINISTRATOR';
    $sidebarRoleLabel = 'Super Administrator';
    $officerNip = $staffProfile?->nip ?? ($currentUser['nip'] ?? 'NIP: TS-198402-089');
    $officerSubtitle = 'ID Petugas: ' . ($staffProfile?->nip ?? 'ADMIN-01') . ' • Divisi: Transmisi, Distribusi & SCADA';
} elseif ($isScada) {
    $headerRoleBadge = 'COMMAND CENTER • OPERATOR SCADA';
    $sidebarRoleLabel = 'Operator SCADA';
    $officerNip = $staffProfile?->nip ?? ($currentUser['nip'] ?? 'NIP: 198804-004');
    $officerSubtitle = 'ID Petugas: ' . ($staffProfile?->nip ?? 'SCADA-01') . ' • Divisi: Seksi Pengolahan Air & SCADA';
} elseif ($isField) {
    $headerRoleBadge = 'PORTAL OPERASIONAL • PETUGAS LAPANGAN & TRC';
    $sidebarRoleLabel = 'Petugas Lapangan / TRC';
    $officerNip = $staffProfile?->nip ?? 'Petugas Lapangan TRC';
    $officerSubtitle = 'ID Petugas: FIELD-TRC • Unit: Tim Reaksi Cepat PDAM Tirta Sago';
} else {
    $headerRoleBadge = 'COMMAND CENTER • OPERASIONAL PDAM';
    $sidebarRoleLabel = 'Staf Operasional';
    $officerNip = $staffProfile?->nip ?? ($currentUser['nip'] ?? 'Staf Internal');
    $officerSubtitle = 'Staf Operasional PDAM Tirta Sago';
}
?>
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Command Center SCADA') - Perumda Tirta Sago Payakumbuh</title>

  <!-- Google Fonts: Plus Jakarta Sans & JetBrains Mono -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;700;800&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Shared CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/shared.css') }}" />

  <style>
    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
    }
    .font-mono {
      font-family: 'JetBrains Mono', monospace;
    }
    /* Custom Scrollbar untuk Sidebar */
    aside::-webkit-scrollbar {
      width: 5px;
    }
    aside::-webkit-scrollbar-track {
      background: rgba(241, 245, 249, 0.8);
    }
    aside::-webkit-scrollbar-thumb {
      background: rgba(203, 213, 225, 0.8);
      border-radius: 9999px;
    }
    aside::-webkit-scrollbar-thumb:hover {
      background: rgba(148, 163, 184, 0.9);
    }
  </style>
  @stack('styles')
</head>
<body class="h-full bg-slate-50 text-slate-800 antialiased font-sans flex overflow-hidden">

  <!-- ============================================== -->
  <!-- 1. LEFT SIDEBAR (COMMAND CENTER NAVIGATION)    -->
  <!-- ============================================== -->
  <aside
    id="adminSidebar"
    class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-[#06304E] via-[#025A8B] to-[#0284C7] text-white border-r border-sky-400/20 flex flex-col justify-between transition-transform duration-300 ease-in-out -translate-x-full lg:translate-x-0 lg:static shrink-0 shadow-2xl lg:shadow-none select-none"
  >
    <!-- Bagian Atas: Brand & Node Identity -->
    <div class="p-5 border-b border-white/10 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-2xl bg-white/15 backdrop-blur-md shadow-md border border-white/25 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6 text-cyan-200" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
            </svg>
          </div>
          <div>
            <span class="font-black text-white text-base tracking-tight block">Smart Water</span>
            <span class="text-[10px] font-mono text-cyan-200 block -mt-0.5 font-bold tracking-wider">KOTA PAYAKUMBUH</span>
          </div>
        </div>

        <!-- Tombol Tutup Sidebar di Mobile -->
        <button
          type="button"
          onclick="toggleAdminSidebar()"
          class="lg:hidden p-1.5 rounded-lg text-white/70 hover:text-white hover:bg-white/10 cursor-pointer transition-colors"
          aria-label="Tutup Menu"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>

      <!-- SCADA Node Status Pill -->
      <div class="p-2.5 rounded-xl bg-white/10 backdrop-blur-xs border border-white/15 flex items-center justify-between text-[10px] font-mono">
        <div class="flex items-center gap-1.5 text-sky-100">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span class="font-medium">NODE: IPA-PYK-01</span>
        </div>
        <span class="text-cyan-200 font-bold">ONLINE</span>
      </div>
    </div>

    <!-- Bagian Tengah: Menu Navigasi Utama -->
    <nav class="flex-1 overflow-y-auto px-3.5 py-4 space-y-2 text-xs">
      
      @if($isAdmin || $isScada || $isLegacyStaff)
      <!-- 1.0 Dashboard Utama / Ringkasan Card -->
      <a
        id="sidebarNavOverview"
        href="{{ url('/admin-dashboard?tab=overview') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'overview') ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'overview') ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/>
        </svg>
        <span>Ringkasan Dashboard</span>
      </a>
      @endif

      @if($isAdmin || $isScada || $isLegacyStaff)
      <!-- 1.1 Dashboard & Validasi Meter -->
      <a
        id="sidebarNavVerify"
        href="{{ url('/admin-dashboard?tab=verify') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'verify') ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'verify') ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/>
        </svg>
        <span class="flex-1">Validasi Meter &amp; Kas</span>
        <span
          id="sidebarPendingBadge"
          data-swc="pending-meter-count"
          class="px-2 py-0.5 rounded-full text-[10px] font-mono font-black bg-cyan-400 text-slate-900 shadow-2xs"
          title="Antrean laporan meter AI-OCR yang menunggu verifikasi"
        >
          3
        </span>
      </a>
      @endif

      @if($isAdmin)
      <!-- 1.2 Manajemen Tagihan -->
      <a
        id="sidebarNavBills"
        href="{{ url('/admin-dashboard?tab=bills') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'bills') ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'bills') ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
        </svg>
        <span>Penerimaan Tagihan</span>
      </a>
      @endif

      @if($isAdmin || $isField || $isLegacyStaff)
      <!-- 1.3 Database Pelanggan -->
      <a
        id="sidebarNavCustomers"
        href="{{ url('/admin-dashboard?tab=customers') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'customers') ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'customers') ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        <span>Database Sambungan</span>
      </a>
      @endif

      <!-- Divider Halus -->
      <div class="my-2 border-t border-white/10"></div>

      @if($isAdmin || $isScada || $isLegacyStaff)
      <!-- 2.1 Konsol SCADA Terpusat -->
      <a
        href="{{ url('/water-monitoring') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ $currentTab === 'water-monitoring' ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ $currentTab === 'water-monitoring' ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
        </svg>
        <span>Monitoring SCADA Terpusat</span>
      </a>

      <!-- 2.2 Reservoir IPA Batang Agam -->
      <a
        href="{{ url('/reservoir-detail') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ $currentTab === 'reservoir-detail' ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ $currentTab === 'reservoir-detail' ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.29 7 12 12 20.71 7"/><line x1="12" x2="12" y1="22" y2="12"/>
        </svg>
        <span>Reservoir Batang Agam</span>
      </a>

      <!-- 2.3 Jaringan Pipa Distribusi -->
      <a
        href="{{ url('/distribution-network') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ $currentTab === 'distribution-network' ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ $currentTab === 'distribution-network' ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="3"/><circle cx="19" cy="6" r="2"/><circle cx="5" cy="6" r="2"/><circle cx="12" cy="19" r="2"/>
          <line x1="12" x2="12" y1="15" y2="17"/><line x1="10" x2="6.5" y1="10" y2="7.5"/><line x1="14" x2="17.5" y1="10" y2="7.5"/>
        </svg>
        <span>Jaringan Pipa Distribusi</span>
      </a>
      @endif

      <!-- Divider Halus -->
      <div class="my-2 border-t border-white/10"></div>

      @if($isAdmin || $isField || $isLegacyStaff)
      <!-- 3.1 Pengaduan Tiket -->
      <a
        href="{{ url('/pusat-layanan') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent transition-all duration-150 group"
      >
        <svg class="w-4 h-4 text-sky-200 group-hover:text-amber-300 shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/><path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/>
        </svg>
        <span>Pusat Tiket Gangguan</span>
      </a>
      @endif

      <!-- 3.2 Profil Petugas -->
      <a
        id="sidebarNavProfile"
        href="{{ url('/admin-dashboard?tab=profile') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-all duration-150 group {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'profile') ? 'bg-white/20 text-white font-bold border border-white/30 shadow-xs backdrop-blur-md' : 'text-sky-100/85 hover:text-white hover:bg-white/10 border border-transparent font-semibold' }}"
      >
        <svg class="w-4 h-4 {{ ($currentTab === 'admin-dashboard' && ($subTab ?? 'overview') === 'profile') ? 'text-cyan-200' : 'text-sky-200 group-hover:text-cyan-200' }} shrink-0 transition-colors duration-150" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <span>Profil Petugas</span>
      </a>

      <!-- 3.3 Beralih ke Tampilan Warga -->
      <a
        href="{{ url('/beranda') }}"
        class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl font-semibold text-sky-100/90 hover:text-white hover:bg-white/15 transition-all duration-150 border border-dashed border-white/25 mt-2"
        title="Buka tampilan sisi pelanggan / masyarakat"
      >
        <svg class="w-4 h-4 text-cyan-200 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" x2="3" y1="12" y2="12"/>
        </svg>
        <span>Buka Tampilan Warga</span>
      </a>

    </nav>

    <!-- Bagian Bawah: Profil Petugas & Logout -->
    <div class="p-3.5 border-t border-white/10 bg-black/15 space-y-2.5">
      <!-- Identitas Petugas Aktif (Klik untuk buka profil) -->
      <a
        href="{{ url('/admin-dashboard?tab=profile') }}"
        class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/10 transition-colors group cursor-pointer"
        title="Buka Profil Petugas"
      >
        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-400 to-sky-300 text-slate-900 flex items-center justify-center font-black text-sm shrink-0 shadow-xs">
          <?= strtoupper(substr(e($officerName), 0, 1)) ?>
        </div>
        <div class="min-w-0 flex-1">
          <p class="text-xs font-bold text-white group-hover:text-cyan-200 transition-colors truncate"><?= e($officerName) ?></p>
          <p class="text-[10px] font-mono text-cyan-200/90 font-semibold truncate"><?= e($officerNip) ?></p>
        </div>
        <svg class="w-4 h-4 text-white/50 group-hover:text-white transition-colors shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
      </a>

      <!-- Tombol Keluar Petugas -->
      <a
        href="{{ url('/logout?to=admin') }}"
        class="w-full py-2 px-3 rounded-xl bg-white/10 hover:bg-rose-600/90 text-white border border-white/15 hover:border-transparent text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer shadow-2xs backdrop-blur-xs"
      >
        <svg class="w-3.5 h-3.5 text-white/70" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
        <span>Keluar Portal Petugas</span>
      </a>
    </div>
  </aside>

  <!-- Mobile Backdrop Overlay -->
  <div
    id="sidebarBackdrop"
    onclick="toggleAdminSidebar()"
    class="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-xs hidden lg:hidden transition-opacity"
  ></div>

  <!-- ============================================== -->
  <!-- 2. MAIN CONTENT AREA WITH TOPBAR               -->
  <!-- ============================================== -->
  <div class="flex-1 flex flex-col min-w-0 h-full overflow-hidden bg-slate-50">
    
    <!-- TOPBAR OPERASIONAL PETUGAS -->
    <header class="h-16 bg-white/95 backdrop-blur-md border-b border-slate-200/90 px-4 sm:px-6 flex items-center justify-between shrink-0 z-20 shadow-2xs">
      
      <!-- Sisi Kiri: Hamburger Mobile & Context Title -->
      <div class="flex items-center gap-3">
        <button
          type="button"
          onclick="toggleAdminSidebar()"
          class="lg:hidden p-2 rounded-xl text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors cursor-pointer"
          aria-label="Buka Menu"
        >
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
        </button>

        <div class="hidden sm:block">
          <span class="text-[10px] font-mono text-cyan-700 font-bold uppercase tracking-wider block">
            <?= e($headerRoleBadge) ?>
          </span>
          <h2 class="text-xs sm:text-sm font-extrabold text-slate-900 tracking-tight">
            Perumda Air Minum Tirta Sago Kota Payakumbuh
          </h2>
        </div>
      </div>

      <!-- Sisi Kanan: Live Telemetry, Jam Digital, dan Reset Demo -->
      <div class="flex items-center gap-2.5 sm:gap-4">
        <!-- Live Jam WIB -->
        <div id="liveClock" class="hidden md:block font-mono text-xs font-bold text-slate-700 px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200">
          {{ now()->format('H:i:s') }} WIB
        </div>

        <!-- SCADA Telemetri Real-time Badge -->
        <div class="px-3 py-1.5 rounded-xl bg-emerald-50 border border-emerald-200 text-[11px] font-mono text-emerald-800 flex items-center gap-2 shadow-2xs font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span class="hidden sm:inline">SCADA IPA:</span>
          <span class="font-bold text-emerald-900">3.2 BAR (NORMAL)</span>
        </div>

        <!-- Tombol Reset Demo -->
        <button
          type="button"
          onclick="window.SWC && window.SWC.resetDemoData()"
          class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200/80 border border-slate-200 text-[11px] font-mono text-slate-700 hover:text-slate-900 transition-colors cursor-pointer"
          title="Reset status antrean verifikasi & tagihan ke simulasi awal"
        >
          ↻ Reset Demo
        </button>
      </div>

    </header>

    <!-- CONTENT WRAPPER -->
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8 space-y-6">
      
      <!-- Flash Message Notification -->
      @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-3 shadow-xs animate-fade-zoom">
          <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
          <span>{{ session('success') }}</span>
        </div>
      @endif

      @yield('content')
    </main>

  </div>

  <!-- Smart Water Client-Side Reactive Store (Firebase-Ready) -->
  <script src="{{ asset('assets/js/swc-store.js') }}"></script>

  <!-- Script Interaktivitas Layout Sidebar Admin -->
  <script>
    function toggleAdminSidebar() {
      const sidebar = document.getElementById('adminSidebar');
      const backdrop = document.getElementById('sidebarBackdrop');
      if (sidebar) {
        sidebar.classList.toggle('-translate-x-full');
      }
      if (backdrop) {
        backdrop.classList.toggle('hidden');
      }
    }

    // Live Digital Clock
    setInterval(() => {
      const clockEl = document.getElementById('liveClock');
      if (clockEl) {
        const now = new Date();
        clockEl.textContent = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }) + ' WIB';
      }
    }, 1000);
  </script>

  @stack('scripts')
</body>
</html>
