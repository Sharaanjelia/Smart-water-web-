<?php
$header = array_merge([
    'handler' => '',
    'logoHref' => url('/beranda'),
    'activeNav' => true,
    'showPoints' => false,
    'points' => 0,
    'pointsId' => false,
    'user' => $currentUser ?? null,
    'userNameFallback' => 'Warga Payakumbuh',
    'showAdmin' => false,
    'showAdminMobile' => false,
    'showLogout' => false,
    'profileLink' => false,
    'profileActive' => false,
    'directLoginLink' => null,
    'mobileAuthMode' => 'guest',
    'mobileUserMenu' => false,
    'mobileProfileAlways' => false,
    'homeDataTarget' => false,
], $SHARED_HEADER_OPTIONS ?? []);

// Ambil identitas pengguna aktif dari parameter atau session PHP / Laravel
$headerUser = $header['user'] ?? ($_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null));
$isGuest = empty($headerUser);
$headerIsAdmin = !$isGuest && (!empty($headerUser['isAdmin']) || ($headerUser['role'] ?? '') === 'admin');

if ($isGuest) {
    $activeRole = 'guest';
} elseif ($headerIsAdmin) {
    $activeRole = 'admin';
} else {
    $activeRole = 'customer';
}

// Konfigurasi Navigasi Berdasarkan 3 Peran: Guest (Publik), Customer (Warga), Admin (Petugas)
if ($activeRole === 'admin') {
    $headerLogoHref = url('/admin-dashboard');
    $NAV_ITEMS = [
        ['id' => 'admin-dashboard',      'label' => 'Dashboard Petugas'],
        ['id' => 'water-monitoring',     'label' => 'SCADA Terpusat'],
        ['id' => 'reservoir-detail',     'label' => 'Tandon Reservoir'],
        ['id' => 'distribution-network', 'label' => 'Jaringan Pipa'],
        ['id' => 'layanan',              'label' => 'Pengaduan Tiket'],
    ];
} elseif ($activeRole === 'customer') {
    $headerLogoHref = url('/beranda');
    $NAV_ITEMS = [
        ['id' => 'beranda',         'label' => 'Beranda'],
        ['id' => 'layanan',         'label' => 'Pusat Layanan'],
        ['id' => 'ai-ocr',          'label' => 'AI OCR'],
        ['id' => 'akademi',         'label' => 'Akademi Air'],
        ['id' => 'tentang',         'label' => 'Tentang PDAM'],
        ['id' => 'tantangan',       'label' => 'Tantangan Air'],
    ];
} else {
    // Mode Tamu / Publik (Belum Login)
    $headerLogoHref = url('/beranda');
    $NAV_ITEMS = [
        ['id' => 'beranda',         'label' => 'Beranda'],
        ['id' => 'layanan',         'label' => 'Pusat Layanan'],
        ['id' => 'ai-ocr',          'label' => 'AI OCR'],
        ['id' => 'akademi',         'label' => 'Akademi Air'],
        ['id' => 'tentang',         'label' => 'Tentang PDAM'],
        ['id' => 'tantangan',       'label' => 'Tantangan Air'],
    ];
}

$navRoutes = [
    'beranda'              => url('/beranda'),
    'layanan'              => url('/pusat-layanan'),
    'ai-ocr'               => url('/report-meter'),
    'report-meter'         => url('/report-meter'),
    'meter-reading'        => url('/report-meter'),
    'bill'                 => url('/bill'),
    'water-condition'      => url('/water-condition'),
    'notification'         => url('/notification'),
    'admin-dashboard'      => url('/admin-dashboard'),
    'water-monitoring'     => url('/water-monitoring'),
    'reservoir-detail'     => url('/reservoir-detail'),
    'distribution-network' => url('/distribution-network'),
    'akademi'              => url('/akademi-air'),
    'tantangan'            => url('/tantangan'),
    'tentang'              => url('/tentang'),
    'profil'               => url('/profile'),
    'profile'              => url('/profile'),
    'wallet'               => url('/wallet'),
    'settings'             => url('/settings'),
    'admin'                => url('/admin-dashboard'),
    'auth'                 => url('/login'),
];
?>
<header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between gap-4 sm:gap-6">
    
    <!-- Brand Logo -->
    <div class="flex items-center gap-3">
      <?php renderBrandLogo('md', false, $headerLogoHref); ?>
      <?php if ($activeRole === 'admin'): ?>
        <span class="hidden xl:inline-flex items-center px-2 py-0.5 rounded-md bg-slate-900 border border-slate-700 text-cyan-400 text-[10px] font-mono font-bold tracking-wider">
          COMMAND CENTER
        </span>
      <?php endif; ?>
    </div>

    <!-- Menu Navigasi Utama -->
    <nav class="hidden md:flex items-center gap-5 lg:gap-7 text-sm font-medium text-slate-600">
      <?php foreach ($NAV_ITEMS as $item): ?>
        <?php $isActive = $header['activeNav'] && (($currentTab ?? '') === $item['id'] || ($item['id'] === 'ai-ocr' && in_array($currentTab ?? '', ['ai-ocr', 'report-meter', 'scanning-meter', 'meter-result', 'meter-confirmation'], true))); ?>
        <a
          href="<?= $navRoutes[$item['id']] ?? url('/' . $item['id']) ?>"
          class="whitespace-nowrap shrink-0 py-1 transition-colors duration-150 border-b-2 text-sm cursor-pointer <?= $isActive
            ? 'border-cyan-600 text-cyan-900 font-bold'
            : 'border-transparent hover:text-slate-900 hover:border-slate-300' ?>"
        >
          <?= e($item['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <!-- Bar Aksi Kanan (Tergantung Peran: Guest, Customer, atau Admin) -->
    <div class="flex items-center gap-2.5 sm:gap-3 shrink-0">

      <?php if ($activeRole === 'guest'): ?>
        <!-- ============================================== -->
        <!-- AKSI MODE GUEST / TAMU (BELUM LOGIN)           -->
        <!-- ============================================== -->
        <a
          href="<?= url('/login') ?>"
          class="inline-flex items-center gap-1.5 px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-cyan-600 to-sky-600 hover:from-cyan-500 hover:to-sky-500 shadow-sm hover:shadow-md transition-all cursor-pointer"
          title="Masuk sebagai Pelanggan Smart Water"
        >
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
            <polyline points="10 17 15 12 10 7"/>
            <line x1="15" x2="3" y1="12" y2="12"/>
          </svg>
          <span>Masuk Pelanggan</span>
        </a>

      <?php elseif ($activeRole === 'customer'): ?>
        <!-- ============================================== -->
        <!-- AKSI MODE CUSTOMER / WARGA (SETELAH LOGIN)     -->
        <!-- ============================================== -->
        
        <!-- Tombol Lonceng Notifikasi dengan Counter Badge -->
        <a
          href="<?= url('/notification') ?>"
          class="relative p-2 rounded-xl text-slate-500 hover:text-slate-900 hover:bg-slate-100 transition-colors cursor-pointer"
          title="2 Notifikasi Rekening & Layanan Baru"
          aria-label="Pusat Notifikasi"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
          <span id="headerNotifBadge" data-swc="notif-badge" class="absolute top-1 right-1 flex items-center justify-center min-w-4 h-4 px-1 rounded-full bg-rose-500 text-[10px] font-bold text-white shadow-xs">
            2
          </span>
        </a>

        <?php
          $currentDisplayName = $headerUser['name'] ?? $headerUser['fullName'] ?? 'Budi Santoso';
          $currentInitial = strtoupper(substr($currentDisplayName, 0, 1) ?: 'B');
        ?>

        <!-- Dropdown Menu Profil Warga Terpadu -->
        <div class="relative" id="userProfileDropdownWrap">
          <button
            type="button"
            id="userProfileDropdownBtn"
            onclick="toggleUserDropdown(event)"
            class="flex items-center gap-2 py-1 pl-1 pr-2.5 rounded-full border border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50 transition-all cursor-pointer shadow-2xs group"
            aria-expanded="false"
            aria-label="Menu Pengguna"
          >
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-cyan-600 to-sky-400 text-white shadow-xs flex items-center justify-center text-xs font-bold shrink-0">
              <?= e($currentInitial) ?>
            </div>
            <div class="hidden lg:flex flex-col text-left">
              <span class="text-xs font-bold text-slate-800 leading-tight truncate max-w-[125px]">
                <?= e($currentDisplayName) ?>
              </span>
              <span class="text-[10px] font-mono text-slate-400 leading-none">
                Pelanggan PDAM
              </span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-600 transition-transform duration-200" id="userDropdownChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m6 9 6 6 6-6"/>
            </svg>
          </button>

          <!-- Popover Menu Dropdown Pelanggan -->
          <div
            id="userProfileDropdownMenu"
            class="hidden absolute right-0 mt-2 w-72 rounded-2xl bg-white border border-slate-200 shadow-xl z-50 overflow-hidden animate-fade-zoom"
          >
            <div class="p-4 bg-slate-50/80 border-b border-slate-100">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white shadow-xs flex items-center justify-center text-sm font-bold shrink-0">
                  <?= e($currentInitial) ?>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold text-slate-900 truncate"><?= e($currentDisplayName) ?></p>
                  <p class="text-[11px] text-slate-500 font-mono truncate"><?= e($headerUser['email'] ?? 'budi.santoso@warga.payakumbuh.go.id') ?></p>
                  <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-cyan-50 text-cyan-800 border border-cyan-200 text-[10px] font-semibold">
                    <?= 'Meter: ' . e($headerUser['meterNumber'] ?? '04-01000') ?>
                  </div>
                </div>
              </div>

              <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-xs">
                <span class="text-slate-500 font-medium">Saldo Dompet:</span>
                <span id="headerWalletBalance" data-swc="wallet-balance" class="font-black text-emerald-600 font-mono">Rp 2.500.000</span>
              </div>
            </div>

            <div class="p-2 space-y-0.5 text-xs text-slate-700">
              <a href="<?= url('/profile') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <span class="font-medium">Profil &amp; Data Pelanggan</span>
              </a>
              <a href="<?= url('/wallet') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span class="font-medium">Dompet Saldo &amp; Restitusi</span>
              </a>
              <a href="<?= url('/settings') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span class="font-medium">Pengaturan Notifikasi &amp; PIN</span>
              </a>
            </div>

            <!-- Tombol Logout Warga -->
            <div class="p-2 border-t border-slate-100">
              <a
                href="<?= url('/logout') ?>"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition-colors"
              >
                <svg class="w-4 h-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                <span>Keluar dari Akun (Logout)</span>
              </a>
            </div>
          </div>
        </div>

      <?php else: ?>
        <!-- ============================================== -->
        <!-- AKSI MODE ADMIN / PETUGAS (SETELAH LOGIN)      -->
        <!-- ============================================== -->
        
        <!-- Live Telemetry Badge -->
        <div class="hidden sm:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-900 border border-slate-700 text-cyan-400 text-[11px] font-mono font-semibold">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          <span>SCADA NORMAL &middot; 3.2 BAR</span>
        </div>

        <?php
          $currentDisplayName = $headerUser['name'] ?? $headerUser['fullName'] ?? 'Ahmad Pratama';
          $currentInitial = strtoupper(substr($currentDisplayName, 0, 1) ?: 'A');
        ?>

        <!-- Dropdown Menu Profil Petugas -->
        <div class="relative" id="userProfileDropdownWrap">
          <button
            type="button"
            id="userProfileDropdownBtn"
            onclick="toggleUserDropdown(event)"
            class="flex items-center gap-2 py-1 pl-1 pr-2.5 rounded-full border border-slate-700 bg-slate-900 hover:bg-slate-800 text-white transition-all cursor-pointer shadow-xs group"
            aria-expanded="false"
            aria-label="Menu Petugas"
          >
            <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-slate-900 to-slate-800 text-cyan-400 border border-slate-700 flex items-center justify-center text-xs font-bold shrink-0">
              <?= e($currentInitial) ?>
            </div>
            <div class="hidden lg:flex flex-col text-left">
              <span class="text-xs font-bold text-slate-100 leading-tight truncate max-w-[135px]">
                <?= e($currentDisplayName) ?>
              </span>
              <span class="text-[10px] font-mono text-cyan-400 leading-none">
                Petugas SCADA
              </span>
            </div>
            <svg class="w-3.5 h-3.5 text-slate-400 group-hover:text-slate-200 transition-transform duration-200" id="userDropdownChevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m6 9 6 6 6-6"/>
            </svg>
          </button>

          <!-- Popover Menu Dropdown Petugas -->
          <div
            id="userProfileDropdownMenu"
            class="hidden absolute right-0 mt-2 w-72 rounded-2xl bg-white border border-slate-200 shadow-xl z-50 overflow-hidden animate-fade-zoom"
          >
            <div class="p-4 bg-slate-900 text-white border-b border-slate-800">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-slate-800 border border-slate-700 text-cyan-400 flex items-center justify-center text-sm font-bold shrink-0">
                  <?= e($currentInitial) ?>
                </div>
                <div class="min-w-0 flex-1">
                  <p class="text-xs font-bold text-white truncate"><?= e($currentDisplayName) ?></p>
                  <p class="text-[11px] text-slate-400 font-mono truncate"><?= e($headerUser['email'] ?? 'ahmad.pratama@tirtasago.id') ?></p>
                  <div class="inline-flex items-center gap-1 mt-1 px-2 py-0.5 rounded-full bg-slate-800 text-cyan-300 border border-slate-700 text-[10px] font-mono font-semibold">
                    <?= e($headerUser['staffId'] ?? 'NIP-198804-004') ?>
                  </div>
                </div>
              </div>
            </div>

            <div class="p-2 space-y-0.5 text-xs text-slate-700">
              <a href="<?= url('/admin-dashboard') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                <span class="font-medium">Dashboard Petugas</span>
              </a>
              <a href="<?= url('/water-monitoring') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-sky-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/></svg>
                <span class="font-medium">SCADA Telemetri Terpusat</span>
              </a>
              <a href="<?= url('/settings') ?>" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-100 transition-colors">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                <span class="font-medium">Pengaturan Akun Dinas</span>
              </a>
            </div>

            <!-- Tombol Logout Petugas -->
            <div class="p-2 border-t border-slate-100">
              <a
                href="<?= url('/logout?to=admin') ?>"
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-rose-600 hover:bg-rose-50 font-bold text-xs transition-colors"
              >
                <svg class="w-4 h-4 text-rose-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                <span>Keluar Portal Petugas (Logout)</span>
              </a>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Tombol Mobile Drawer -->
      <button
        type="button"
        id="mobileMenuToggleBtn"
        onclick="toggleMobileMenu()"
        class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 cursor-pointer"
        aria-label="Toggle Navigation Menu"
        aria-expanded="false"
      >
        <svg id="mobileMenuIconOpen" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>
        </svg>
        <svg id="mobileMenuIconClose" class="w-5 h-5 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile Drawer Menu -->
  <div id="mobileMenuDrawer" class="hidden md:hidden border-t border-slate-200 bg-white px-4 pt-3 pb-5 space-y-2 animate-slide-down shadow-lg">
    <?php foreach ($NAV_ITEMS as $item): ?>
      <?php $isActive = $header['activeNav'] && (($currentTab ?? '') === $item['id'] || ($item['id'] === 'ai-ocr' && in_array($currentTab ?? '', ['ai-ocr', 'report-meter', 'scanning-meter', 'meter-result', 'meter-confirmation'], true))); ?>
      <a
        href="<?= $navRoutes[$item['id']] ?? url('/' . $item['id']) ?>"
        class="block w-full text-left px-3 py-2.5 rounded-lg text-sm font-medium transition-colors cursor-pointer <?= $isActive
          ? 'bg-cyan-50 text-cyan-900 font-bold'
          : 'text-slate-700 hover:bg-slate-50' ?>"
      >
        <?= e($item['label']) ?>
      </a>
    <?php endforeach; ?>

    <div class="pt-3 border-t border-slate-100 space-y-1.5">
      <?php if ($activeRole === 'guest'): ?>
        <a
          href="<?= url('/login') ?>"
          class="w-full text-center py-2.5 px-4 rounded-xl text-xs font-bold text-white bg-cyan-600 hover:bg-cyan-700 flex items-center justify-center gap-2 shadow-xs"
        >
          <span>Masuk Pelanggan</span>
        </a>
      <?php elseif ($activeRole === 'customer'): ?>
        <a
          href="<?= url('/profile') ?>"
          class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
          <span>Profil &amp; Data Pelanggan</span>
        </a>
        <a
          href="<?= url('/wallet') ?>"
          class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50 flex items-center justify-between cursor-pointer"
        >
          <span class="flex items-center gap-2">
            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
            <span>Dompet Saldo</span>
          </span>
          <span class="text-xs font-bold text-emerald-600 font-mono">Rp 2.500.000</span>
        </a>
        <a
          href="<?= url('/logout') ?>"
          class="w-full text-left px-3 py-2 rounded-lg text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-semibold"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
          </svg>
          <span>Keluar (Logout)</span>
        </a>
      <?php else: ?>
        <a
          href="<?= url('/admin-dashboard') ?>"
          class="w-full text-left px-3 py-2 rounded-lg text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
          <span>Dashboard Petugas</span>
        </a>
        <a
          href="<?= url('/logout?to=admin') ?>"
          class="w-full text-left px-3 py-2 rounded-lg text-sm text-rose-600 hover:bg-rose-50 flex items-center gap-2 font-semibold"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
          </svg>
          <span>Keluar Portal Petugas (Logout)</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</header>

<script>
function toggleUserDropdown(event) {
  event.stopPropagation();
  const menu = document.getElementById('userProfileDropdownMenu');
  const chevron = document.getElementById('userDropdownChevron');
  const btn = document.getElementById('userProfileDropdownBtn');
  if (!menu) return;
  const isHidden = menu.classList.contains('hidden');
  if (isHidden) {
    menu.classList.remove('hidden');
    chevron?.classList.add('rotate-180');
    btn?.setAttribute('aria-expanded', 'true');
  } else {
    menu.classList.add('hidden');
    chevron?.classList.remove('rotate-180');
    btn?.setAttribute('aria-expanded', 'false');
  }
}

document.addEventListener('click', function (e) {
  const wrap = document.getElementById('userProfileDropdownWrap');
  const menu = document.getElementById('userProfileDropdownMenu');
  const chevron = document.getElementById('userDropdownChevron');
  const btn = document.getElementById('userProfileDropdownBtn');
  if (wrap && !wrap.contains(e.target)) {
    menu?.classList.add('hidden');
    chevron?.classList.remove('rotate-180');
    btn?.setAttribute('aria-expanded', 'false');
  }
});

function toggleMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const iconOpen = document.getElementById('mobileMenuIconOpen');
  const iconClose = document.getElementById('mobileMenuIconClose');
  if (drawer) {
    drawer.classList.toggle('hidden');
    if (iconOpen && iconClose) {
      iconOpen.classList.toggle('hidden');
      iconClose.classList.toggle('hidden');
    }
  }
}
</script>

<?php if (empty($GLOBALS['SWC_STORE_INCLUDED'])): $GLOBALS['SWC_STORE_INCLUDED'] = true; ?>
<script src="<?= asset('assets/js/swc-store.js') ?>"></script>
<?php endif; ?>
