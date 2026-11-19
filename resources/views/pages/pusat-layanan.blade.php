<?php
/**
 * Halaman Utama Pusat Layanan - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 */
// Aksi demo logout via query string
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['swc_user']);
    header('Location: ' . $SWC_PAGE_URL);
    exit;
}

require_once $legacySharedPath . '/brand_logo.php';
?>

<?php
/**
 * Ilustrasi Vektor SVG Lengkap untuk Pusat Layanan
 * 1. FacilityWaterTowerIllustration
 * 2. SearchTicketIllustration
 * 3. CustomerSupportIllustration
 * Sesuai persis dengan ServiceCenterView.tsx baris 31-245
 */

if (!function_exists('renderFacilityWaterTowerIllustration')) {
function renderFacilityWaterTowerIllustration(): void
{
    ?>
    <div class="relative w-full max-w-[420px] h-[280px] sm:h-[320px] select-none pointer-events-none flex items-center justify-center">
      <svg viewBox="0 0 460 360" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-sm">
        <!-- Soft Sky & Hill Background -->
        <ellipse cx="230" cy="310" rx="210" ry="40" fill="#e2f1fd" />
        <path d="M40 320 C 120 280, 200 300, 320 290 C 380 285, 420 305, 450 320 Z" fill="#bfe1fa" opacity="0.6" />
        <path d="M10 335 C 100 310, 240 315, 340 305 C 400 300, 440 320, 460 340 L 460 360 L 0 360 Z" fill="#90caf9" opacity="0.4" />

        <!-- Modern Water Treatment Building (Left) -->
        <g>
          <rect x="50" y="318" width="170" height="12" rx="6" fill="#93c5fd" opacity="0.4" />
          <rect x="55" y="195" width="160" height="125" rx="10" fill="#ffffff" stroke="#93c5fd" stroke-width="2" />
          <rect x="55" y="195" width="160" height="14" rx="4" fill="#0284c7" />
          <rect x="65" y="218" width="140" height="4" rx="2" fill="#e0f2fe" />

          <rect x="70" y="232" width="38" height="42" rx="6" fill="#e0f2fe" stroke="#38bdf8" stroke-width="1.5" />
          <line x1="89" y1="232" x2="89" y2="274" stroke="#7dd3fc" stroke-width="1.5" />
          <line x1="70" y1="253" x2="108" y2="253" stroke="#7dd3fc" stroke-width="1.5" />

          <rect x="116" y="232" width="38" height="42" rx="6" fill="#e0f2fe" stroke="#38bdf8" stroke-width="1.5" />
          <line x1="135" y1="232" x2="135" y2="274" stroke="#7dd3fc" stroke-width="1.5" />
          <line x1="116" y1="253" x2="154" y2="253" stroke="#7dd3fc" stroke-width="1.5" />

          <rect x="162" y="240" width="36" height="80" rx="5" fill="#f0f9ff" stroke="#0284c7" stroke-width="1.5" />
          <rect x="166" y="246" width="28" height="38" rx="3" fill="#bae6fd" opacity="0.7" />
          <circle cx="191" cy="285" r="2.5" fill="#0284c7" />

          <path d="M40 310 L 55 310" stroke="#0284c7" stroke-width="6" stroke-linecap="round" />
          <circle cx="36" cy="310" r="5" fill="#0369a1" />
        </g>

        <!-- Elevated Water Tower (Right) -->
        <g>
          <ellipse cx="340" cy="330" rx="55" ry="12" fill="#7dd3fc" opacity="0.5" />

          <line x1="312" y1="180" x2="302" y2="330" stroke="#0284c7" stroke-width="6" stroke-linecap="round" />
          <line x1="368" y1="180" x2="378" y2="330" stroke="#0284c7" stroke-width="6" stroke-linecap="round" />
          <line x1="334" y1="180" x2="330" y2="330" stroke="#38bdf8" stroke-width="4" />
          <line x1="346" y1="180" x2="350" y2="330" stroke="#38bdf8" stroke-width="4" />

          <line x1="309" y1="225" x2="371" y2="225" stroke="#0284c7" stroke-width="3.5" />
          <line x1="305" y1="275" x2="375" y2="275" stroke="#0284c7" stroke-width="3.5" />
          <line x1="310" y1="185" x2="371" y2="225" stroke="#38bdf8" stroke-width="2.5" opacity="0.8" />
          <line x1="370" y1="185" x2="309" y2="225" stroke="#38bdf8" stroke-width="2.5" opacity="0.8" />
          <line x1="309" y1="225" x2="375" y2="275" stroke="#38bdf8" stroke-width="2.5" opacity="0.8" />
          <line x1="371" y1="225" x2="305" y2="275" stroke="#38bdf8" stroke-width="2.5" opacity="0.8" />

          <rect x="295" y="65" width="90" height="110" rx="14" fill="#0284c7" stroke="#0369a1" stroke-width="2.5" />
          <rect x="300" y="70" width="14" height="100" rx="4" fill="#38bdf8" opacity="0.4" />
          <rect x="370" y="70" width="10" height="100" rx="3" fill="#0369a1" opacity="0.5" />

          <path d="M290 68 L 340 32 L 390 68 Z" fill="#0369a1" stroke="#075985" stroke-width="2" stroke-linejoin="round" />
          <circle cx="340" cy="30" r="4" fill="#38bdf8" />

          <line x1="300" y1="80" x2="300" y2="160" stroke="#bae6fd" stroke-width="2" stroke-dasharray="3 3" />

          <circle cx="340" cy="98" r="15" fill="#ffffff" />
          <path d="M340 88 C 340 88 333 97 333 100 C 333 104 336 107 340 107 C 344 107 347 104 347 100 C 347 97 340 88 340 88 Z" fill="#0284c7" />

          <text x="340" y="128" text-anchor="middle" fill="#ffffff" font-size="11" font-weight="800" letter-spacing="0.05em">
            Tirta Sago
          </text>
          <rect x="316" y="136" width="48" height="3" rx="1.5" fill="#38bdf8" />
        </g>

        <!-- Floating Glossy Water Droplets -->
        <g>
          <path d="M250 80 C 250 80 240 98 240 106 C 240 112 244.5 117 250 117 C 255.5 117 260 112 260 106 C 260 98 250 80 250 80 Z" fill="#38bdf8" opacity="0.85" />
          <ellipse cx="247" cy="102" rx="2" ry="4" fill="#ffffff" opacity="0.75" />

          <path d="M225 125 C 225 125 218 138 218 144 C 218 148 221 152 225 152 C 229 152 232 148 232 144 C 232 138 225 125 225 125 Z" fill="#0ea5e9" opacity="0.7" />
          <ellipse cx="223" cy="140" rx="1.5" ry="3" fill="#ffffff" opacity="0.7" />

          <path d="M272 135 C 272 135 266 146 266 151 C 266 154.5 268.5 157.5 272 157.5 C 275.5 157.5 278 154.5 278 151 C 278 146 272 135 272 135 Z" fill="#0284c7" opacity="0.6" />
        </g>

        <!-- Landscaping Trees -->
        <circle cx="48" cy="318" r="18" fill="#4ade80" opacity="0.85" />
        <circle cx="34" cy="324" r="14" fill="#22c55e" />
        <circle cx="410" cy="326" r="20" fill="#4ade80" opacity="0.8" />
        <circle cx="426" cy="330" r="15" fill="#16a34a" />
      </svg>
    </div>
    <?php
}
}

if (!function_exists('renderSearchTicketIllustration')) {
function renderSearchTicketIllustration(): void
{
    ?>
    <div class="w-20 h-20 sm:w-24 sm:h-24 select-none pointer-events-none">
      <svg viewBox="0 0 100 100" fill="none" class="w-full h-full">
        <rect x="22" y="16" width="46" height="58" rx="6" fill="#e0f2fe" stroke="#bae6fd" stroke-width="1.5" transform="rotate(-6 45 45)" />
        <rect x="28" y="18" width="48" height="60" rx="6" fill="#ffffff" stroke="#93c5fd" stroke-width="1.5" />
        <line x1="36" y1="28" x2="62" y2="28" stroke="#38bdf8" stroke-width="3" stroke-linecap="round" />
        <line x1="36" y1="36" x2="68" y2="36" stroke="#e2e8f0" stroke-width="2.5" stroke-linecap="round" />
        <line x1="36" y1="43" x2="58" y2="43" stroke="#e2e8f0" stroke-width="2.5" stroke-linecap="round" />
        <line x1="36" y1="50" x2="52" y2="50" stroke="#e2e8f0" stroke-width="2.5" stroke-linecap="round" />

        <path d="M82 18 L 84 23 L 89 25 L 84 27 L 82 32 L 80 27 L 75 25 L 80 23 Z" fill="#38bdf8" opacity="0.8" />
        <path d="M16 46 L 17 50 L 21 51 L 17 52 L 16 56 L 15 52 L 11 51 L 15 50 Z" fill="#7dd3fc" opacity="0.7" />

        <g transform="translate(10, 5)">
          <circle cx="56" cy="46" r="18" fill="#ffffff" fill-opacity="0.4" stroke="#0284c7" stroke-width="4" />
          <path d="M46 38 C 50 34, 58 34, 64 38" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" />
          <path d="M69 59 L 85 76" stroke="#0369a1" stroke-width="6" stroke-linecap="round" />
          <path d="M72 62 L 82 73" stroke="#38bdf8" stroke-width="2" stroke-linecap="round" />
        </g>
      </svg>
    </div>
    <?php
}
}

if (!function_exists('renderCustomerSupportIllustration')) {
function renderCustomerSupportIllustration(): void
{
    ?>
    <div class="relative w-full max-w-[340px] h-[340px] flex items-center justify-center select-none pointer-events-none">
      <svg viewBox="0 0 340 340" fill="none" class="w-full h-full">
        <circle cx="170" cy="180" r="120" fill="#e0f2fe" opacity="0.6" />
        <ellipse cx="170" cy="285" rx="130" ry="35" fill="#bae6fd" opacity="0.4" />

        <g transform="translate(160, 48) rotate(-6)">
          <text x="0" y="0" text-anchor="middle" fill="#0369a1" font-size="16" font-weight="700" font-style="italic" letter-spacing="0.02em" font-family="cursive, sans-serif">
            Kami siap
          </text>
          <text x="14" y="22" text-anchor="middle" fill="#0284c7" font-size="18" font-weight="800" font-style="italic" letter-spacing="0.03em" font-family="cursive, sans-serif">
            membantu!
          </text>
        </g>

        <g transform="translate(68, 126)">
          <rect x="0" y="0" width="56" height="38" rx="12" fill="#0284c7" />
          <path d="M14 38 L 18 46 L 26 38 Z" fill="#0284c7" />
          <circle cx="18" cy="19" r="3" fill="#ffffff" />
          <circle cx="28" cy="19" r="3" fill="#ffffff" />
          <circle cx="38" cy="19" r="3" fill="#ffffff" />
        </g>

        <g>
          <path d="M120 280 C 120 235, 140 220, 170 220 C 200 220, 220 235, 220 280 Z" fill="#ffffff" stroke="#93c5fd" stroke-width="2" />
          <path d="M158 220 L 170 240 L 182 220 Z" fill="#e0f2fe" />
          <rect x="162" y="195" width="16" height="28" rx="4" fill="#fed7aa" />
          <ellipse cx="170" cy="165" rx="28" ry="32" fill="#fed7aa" />
          <path d="M142 165 C 142 135, 150 125, 170 125 C 190 125, 198 135, 198 165 C 194 150, 185 142, 170 142 C 155 142, 146 150, 142 165 Z" fill="#0c2340" />
          <path d="M140 160 C 136 175, 136 200, 148 215 C 146 195, 145 175, 146 160 Z" fill="#0c2340" />
          <path d="M200 160 C 204 175, 204 200, 192 215 C 194 195, 195 175, 194 160 Z" fill="#0c2340" />

          <path d="M158 165 Q 162 169 166 165" stroke="#9a3412" stroke-width="1.5" stroke-linecap="round" fill="none" />
          <path d="M174 165 Q 178 169 182 165" stroke="#9a3412" stroke-width="1.5" stroke-linecap="round" fill="none" />
          <path d="M164 180 Q 170 186 176 180" stroke="#c2410c" stroke-width="2" stroke-linecap="round" fill="none" />

          <path d="M140 165 C 140 120, 200 120, 200 165" stroke="#0284c7" stroke-width="4" stroke-linecap="round" fill="none" />
          <rect x="136" y="155" width="8" height="22" rx="4" fill="#0369a1" />
          <rect x="196" y="155" width="8" height="22" rx="4" fill="#0369a1" />
          <path d="M140 172 L 152 188 L 160 188" stroke="#0284c7" stroke-width="2.5" stroke-linecap="round" fill="none" />
          <circle cx="162" cy="188" r="3" fill="#38bdf8" />
        </g>

        <g>
          <path d="M80 300 C 60 270, 70 230, 100 240 C 90 260, 95 285, 80 300 Z" fill="#38bdf8" opacity="0.65" />
          <path d="M260 300 C 280 270, 270 230, 240 240 C 250 260, 245 285, 260 300 Z" fill="#0284c7" opacity="0.5" />
          <path d="M225 315 C 245 295, 260 305, 255 325 Z" fill="#7dd3fc" opacity="0.8" />
          <path d="M115 315 C 95 295, 80 305, 85 325 Z" fill="#0ea5e9" opacity="0.7" />
        </g>
      </svg>
    </div>
    <?php
}
}


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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
    <!-- Tailwind CSS v4 Browser Runtime agar seluruh utility class identik dengan versi React -->
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../../assets/css/shared.css" />
    <link rel="stylesheet" href="../../assets/css/pages/pusat-layanan.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">
      
      <!-- Top Header -->
      <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleLayananNav',
    'logoHref' => url('/beranda'),
    'activeNav' => true,
    'user' => $_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null),
];
require $legacySharedPath . '/header.php';
?>

      <!-- Main View: Halaman Pusat Layanan -->
      <main class="flex-1">
        <div id="service-center-main" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-10 space-y-12">
          <!-- 1. HERO SECTION -->
          
<?php
/**
 * 1. HERO SECTION — DUA KOLOM, BG GRADASI BIRU MUDA, ILUSTRASI MENARA TIRTA SAGO
 * Sesuai persis dengan ServiceCenterView.tsx baris 403-459
 */
?>
<section class="bg-gradient-to-r from-[#e7f3fd] via-[#eef6fe] to-[#f4f9fe] rounded-[32px] p-6 sm:p-10 lg:p-14 border border-sky-100 shadow-[0_4px_25px_-5px_rgba(2,132,199,0.05)] relative overflow-hidden">
  <!-- Subtle decorative background water wave -->
  <div class="absolute -bottom-10 right-0 w-96 h-48 bg-sky-200/20 rounded-full blur-2xl pointer-events-none"></div>

  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center relative z-10">
    <!-- Kolom Kiri: Informasi Utama -->
    <div class="lg:col-span-7 space-y-5">
      <div>
        <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-semibold text-[#0284c7] bg-[#d7eefd] border border-sky-200/60 shadow-2xs">
          Pusat Layanan Terpadu Payakumbuh
        </span>
      </div>

      <h1 class="text-3xl sm:text-4xl lg:text-[44px] font-extrabold text-[#0c2340] tracking-tight leading-[1.18]">
        Smart Water Service Center
      </h1>

      <p class="text-sm sm:text-base text-slate-500 max-w-xl leading-relaxed">
        Informasi gangguan pipa resmi, pelaporan pengaduan air cepat tanggap, dan pelacakan status penanganan teknis Perumda Air Minum Tirta Sago.
      </p>

      <!-- Dua Tombol Utama -->
      <div class="pt-3 flex flex-wrap items-center gap-3.5">
        <!-- Tombol Pertama: Biru Solid -->
        <button
          type="button"
          onclick="openCreateComplaintPage()"
          class="px-6 py-3.5 rounded-full text-xs sm:text-sm font-bold bg-[#1d82f5] hover:bg-[#156cd4] text-white transition-all shadow-md shadow-sky-400/30 flex items-center gap-2 cursor-pointer active:scale-95"
        >
          <!-- Megaphone -->
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m3 11 18-5v12L3 14v-3z"/>
            <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
          </svg>
          <span>Buat Pengaduan Gangguan Baru</span>
        </button>

        <!-- Tombol Kedua: Putih / Transparan Border Biru -->
        <button
          type="button"
          onclick="scrollToTracker()"
          class="px-6 py-3.5 rounded-full text-xs sm:text-sm font-semibold bg-white/90 hover:bg-white text-[#1d82f5] border border-[#1d82f5] transition-all shadow-xs flex items-center gap-2 cursor-pointer active:scale-95"
        >
          <!-- Search -->
          <svg class="w-4 h-4 text-[#1d82f5]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"/>
            <path d="m21 21-4.3-4.3"/>
          </svg>
          <span>Lacak Status Tiket Saya</span>
        </button>
      </div>
    </div>

    <!-- Kolom Kanan: Ilustrasi Fasilitas Air Bersih & Menara Tirta Sago -->
    <div class="lg:col-span-5 flex justify-center lg:justify-end">
      <?php renderFacilityWaterTowerIllustration(); ?>
    </div>
  </div>
</section>



          <!-- 2. SECTION PELACAKAN TIKET & PENGADUAN SAYA -->
          
<?php
/**
 * 2. SECTION PELACAKAN TIKET & PENGADUAN SAYA (2 KOLOM BERDAMPINGAN)
 * Sesuai persis dengan ServiceCenterView.tsx baris 461-615
 */
$myTickets = [];
if ($currentUser) {
    $myTickets = array_values(array_filter($complaints, fn($c) => ($c['userId'] ?? '') === ($currentUser['id'] ?? '')));
}

// Ticket to display on right card: use user's ticket or standard sample TK-PYK-2026-8942
$displayedTicket = $myTickets[0] ?? null;
if (!$displayedTicket) {
    foreach ($complaints as $c) {
        if ($c['id'] === 'TK-PYK-2026-8942') {
            $displayedTicket = $c;
            break;
        }
    }
}
if (!$displayedTicket) {
    $displayedTicket = $complaints[0];
}
$ticketCount = count($myTickets) > 0 ? count($myTickets) : 1;

if (!function_exists('getPillBadgeClassForCard')) {
    function getPillBadgeClassForCard(string $status): string
    {
        return match ($status) {
            'BARU'                   => 'bg-[#e0f2fe] text-[#0369a1]',
            'DIPROSES'               => 'bg-[#fef3c7] text-[#b45309]',
            'MENUNGGU TINDAK LANJUT' => 'bg-[#f3e8ff] text-[#7e22ce]',
            default                  => 'bg-[#dcfce7] text-[#15803d]',
        };
    }
}
?>
<section class="grid grid-cols-1 lg:grid-cols-2 gap-6">

  <!-- KOLOM KIRI: PELACAKAN CEPAT TIKET PENGADUAN (PASTEL BLUE CARD) -->
  <div
    id="pelacakan-cepat-tiket"
    class="bg-[#f0f7fe] rounded-[28px] border border-sky-100 p-6 sm:p-8 flex flex-col justify-between shadow-xs relative overflow-hidden min-h-[290px]"
  >
    <div class="space-y-4">
      <div class="flex items-start justify-between">
        <div class="space-y-1.5 max-w-sm">
          <div class="flex items-center gap-2.5">
            <div class="w-9 h-9 rounded-full bg-[#0284c7] text-white flex items-center justify-center shadow-xs">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"/>
                <path d="m21 21-4.3-4.3"/>
              </svg>
            </div>
            <h3 class="text-base sm:text-lg font-bold text-[#0c2340]">
              Pelacakan Cepat Tiket Pengaduan
            </h3>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed pt-1">
            Masukkan nomor tiket pengaduan resmi Anda (contoh: <span class="font-mono font-bold text-slate-700">TK-PYK-2026-8942</span>)
          </p>
        </div>

        <!-- Magnifying Glass & Ticket Illustration -->
        <div class="shrink-0 -mt-2 -mr-1">
          <?php renderSearchTicketIllustration(); ?>
        </div>
      </div>

      <!-- Input & Tombol Lacak Tiket -->
      <form id="ticket-search-form" onsubmit="handleSearchTicket(event)" class="flex gap-2 pt-1">
        <input
          id="ticket-search-input"
          type="text"
          placeholder="Masukkan nomor tiket..."
          class="flex-1 px-4 py-3 text-xs sm:text-sm bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-[#0284c7] text-slate-900 font-mono placeholder:text-slate-400 shadow-2xs"
        />
        <button
          type="submit"
          class="px-5 sm:px-6 py-3 rounded-xl text-xs sm:text-sm font-bold text-white bg-[#1d82f5] hover:bg-[#156cd4] transition-colors shadow-xs flex items-center gap-1.5 cursor-pointer shrink-0"
        >
          <span>Lacak Tiket</span>
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/>
            <path d="m12 5 7 7-7 7"/>
          </svg>
        </button>
      </form>

      <p id="ticket-search-error" class="hidden text-xs text-rose-600 font-medium"></p>
    </div>

    <!-- Tiket Akun Anda Pill Card -->
    <div class="mt-4 pt-3 border-t border-sky-100 flex items-center gap-2 text-xs">
      <div class="w-6 h-6 rounded-md bg-sky-100 text-[#0284c7] flex items-center justify-center shrink-0">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
          <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
          <path d="M10 9H8"/>
          <path d="M16 13H8"/>
          <path d="M16 17H8"/>
        </svg>
      </div>
      <span class="text-slate-500 font-medium">Tiket Akun Anda:</span>
      <button
        type="button"
        id="quick-sample-ticket-btn"
        onclick="openTicketTrackerById('<?= e($displayedTicket['id']) ?>', true)"
        class="font-mono font-bold text-slate-900 hover:text-[#0284c7] cursor-pointer"
      >
        <?= e($displayedTicket['id']) ?>
      </button>
      <span
        id="quick-sample-ticket-status"
        class="px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase <?= getPillBadgeClassForCard($displayedTicket['status']) ?>"
      >
        <?= e($displayedTicket['status']) ?>
      </span>
    </div>
  </div>

  <!-- KOLOM KANAN: PENGADUAN SAYA (PASTEL GREEN CARD) -->
  <div class="bg-[#f2faf5] rounded-[28px] border border-emerald-100 p-6 sm:p-8 flex flex-col justify-between shadow-xs relative overflow-hidden min-h-[290px]">
    <div>
      <!-- Header Pengaduan Saya -->
      <div class="flex items-center justify-between pb-4">
        <div class="flex items-center gap-2.5">
          <div class="w-9 h-9 rounded-xl bg-[#10b981] text-white flex items-center justify-center shadow-xs">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
              <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
              <path d="M10 9H8"/>
              <path d="M16 13H8"/>
              <path d="M16 17H8"/>
            </svg>
          </div>
          <h3 class="text-base sm:text-lg font-bold text-[#0c2340]">
            Pengaduan Saya (<span id="my-complaints-count"><?= $ticketCount ?></span>)
          </h3>
        </div>

        <button
          type="button"
          onclick="openCreateComplaintPage()"
          class="px-3.5 py-1.5 rounded-full text-xs font-semibold bg-white border border-emerald-300 text-[#059669] hover:bg-emerald-50 transition-colors shadow-2xs flex items-center gap-1 cursor-pointer"
        >
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/>
            <path d="M12 5v14"/>
          </svg>
          <span>Tambah Pengaduan</span>
        </button>
      </div>

      <!-- Kartu Informasi Pengaduan -->
      <div class="bg-white rounded-2xl border border-emerald-100/90 p-4 sm:p-5 shadow-xs space-y-2.5">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-2">
            <span id="my-ticket-card-id" class="px-2.5 py-0.5 rounded-full font-mono font-bold text-[11px] bg-[#e0f2fe] text-[#0369a1]">
              <?= e($displayedTicket['id']) ?>
            </span>
            <span id="my-ticket-card-status" class="px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider <?= getPillBadgeClassForCard($displayedTicket['status']) ?>">
              <?= e($displayedTicket['status']) ?>
            </span>
          </div>

          <button
            type="button"
            onclick="openTicketTrackerById(document.getElementById('my-ticket-card-id').textContent.trim())"
            aria-label="Detail tiket"
            class="text-slate-400 hover:text-slate-600 cursor-pointer"
          >
            <svg class="w-4 h-4 -rotate-90" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m6 9 6 6 6-6"/>
            </svg>
          </button>
        </div>

        <h4 id="my-ticket-card-title" class="text-sm font-bold text-[#0c2340] leading-snug">
          <?= e($displayedTicket['title']) ?>
        </h4>

        <p id="my-ticket-card-desc" class="text-xs text-slate-500 leading-relaxed line-clamp-2">
          <?= e($displayedTicket['description']) ?>
        </p>

        <div class="pt-2 flex items-center justify-between text-xs border-t border-slate-100">
          <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span id="my-ticket-card-district"><?= e($displayedTicket['district']) ?></span>
          </div>

          <button
            type="button"
            onclick="openTicketTrackerById(document.getElementById('my-ticket-card-id').textContent.trim())"
            class="font-semibold text-[#0284c7] hover:text-[#0369a1] flex items-center gap-1 cursor-pointer"
          >
            <span>Buka Timeline</span>
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14"/>
              <path d="m12 5 7 7-7 7"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div>

</section>



          <!-- 3. SECTION INFORMASI GANGGUAN & PEMELIHARAAN TERKONFIRMASI -->
          
<?php
/**
 * 3. SECTION INFORMASI GANGGUAN & PEMELIHARAAN TERKONFIRMASI
 * Sesuai persis dengan ServiceCenterView.tsx baris 617-867
 */
?>
<section class="space-y-6">
  <!-- Header Section dengan 1 Dropdown Filter Wilayah -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
      <h2 class="text-xl sm:text-2xl font-bold text-[#0c2340] tracking-tight">
        Informasi Gangguan &amp; Pemeliharaan Terkonfirmasi
      </h2>
      <p class="text-xs sm:text-sm text-slate-500 mt-1">
        Data resmi pemeliharaan jaringan pipa dan jadwal flushing instalasi air minum Kota Payakumbuh.
      </p>
    </div>

    <!-- Satu Dropdown Filter Wilayah -->
    <div class="relative shrink-0" id="district-filter-wrapper">
      <div class="flex items-center gap-2">
        <span class="text-xs text-slate-500 font-medium">Wilayah:</span>
        <button
          type="button"
          id="district-dropdown-btn"
          onclick="toggleDistrictDropdown()"
          class="px-4 py-2 text-xs font-semibold bg-white rounded-xl border border-sky-200 text-[#0c2340] hover:border-sky-400 flex items-center gap-3 shadow-xs cursor-pointer min-w-[180px] justify-between"
        >
          <span id="district-dropdown-label">Pilih Wilayah</span>
          <svg id="district-dropdown-chevron" class="w-3.5 h-3.5 text-slate-400 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m6 9 6 6 6-6"/>
          </svg>
        </button>
      </div>

      <!-- Dropdown Menu Mengambang -->
      <div
        id="district-dropdown-menu"
        class="hidden absolute right-0 top-full mt-1.5 w-64 bg-white rounded-2xl shadow-xl border border-sky-100 py-1.5 z-40 animate-fade-zoom"
      >
        <?php foreach ($DISTRICT_DROPDOWN_OPTIONS as $idx => $dist): ?>
          <?php $val = ($dist === 'Seluruh Kota Payakumbuh') ? 'all' : $dist; ?>
          <button
            type="button"
            data-district-option="<?= e($val) ?>"
            onclick="selectDistrictFilter('<?= e($val) ?>')"
            class="district-opt-btn w-full text-left px-4 py-2.5 text-xs transition-colors flex items-center justify-between cursor-pointer <?= $val === 'all'
              ? 'bg-[#d9effd] text-[#0284c7] font-bold'
              : 'text-slate-700 hover:bg-slate-50 font-medium' ?>"
          >
            <span><?= e($dist) ?></span>
            <svg class="district-check w-3.5 h-3.5 text-[#0284c7] <?= $val === 'all' ? '' : 'hidden' ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 6 9 17l-5-5"/>
            </svg>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Grid Kartu Informasi Gangguan -->
  <div id="announcements-grid" class="grid grid-cols-1 md:grid-cols-2 gap-6">

    <!-- KARTU 1 (PEMELIHARAAN - ORANGE THEME) -->
    <div
      data-announcement-card
      data-districts="Payakumbuh Barat|Payakumbuh Utara"
      class="bg-white rounded-3xl border border-orange-100 p-6 sm:p-7 shadow-xs hover:shadow-md transition-shadow space-y-4"
    >
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-orange-50 text-orange-600 flex items-center justify-center">
            <!-- Wrench -->
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
            </svg>
          </div>
          <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-orange-50 text-orange-600 border border-orange-200">
            pemeliharaan
          </span>
          <span class="text-slate-400 text-[11px]">2026-10-17 08:30 WIB</span>
        </div>
        <span class="px-3 py-1 rounded-full font-semibold text-xs bg-[#ffedd5] text-[#c2410c]">
          Dalam Penanganan
        </span>
      </div>

      <h3 class="text-base font-bold text-[#0c2340] leading-snug">
        Optimalisasi Jaringan Pipa Distribusi Utama Jl. Soekarno-Hatta
      </h3>

      <p class="text-xs text-slate-500 leading-relaxed">
        Pekerjaan interkoneksi pipa transmisi 250mm guna memperkuat tekanan aliran air ke area perumahan warga di Koto Nan Ampek dan Labuh Baru.
      </p>

      <!-- Dua Kolom: Wilayah Terdampak & Estimasi -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block">
            Wilayah Terdampak
          </span>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Payakumbuh Barat
            </span>
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Payakumbuh Utara
            </span>
          </div>
        </div>

        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block flex items-center gap-1">
            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
            </svg>
            <span>Estimasi Pemulihan</span>
          </span>
          <div class="flex items-center gap-1.5 text-[#0c2340] font-bold text-[11px]">
            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
            </svg>
            <span>17 Oktober 2026, 17:00 WIB</span>
          </div>
        </div>
      </div>

      <!-- Kotak Saran Tindakan Warga -->
      <div class="p-3.5 rounded-2xl bg-[#fffbf5] border border-orange-100 flex items-start gap-2.5 text-xs text-slate-600 leading-relaxed">
        <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
          <path d="M9 18h6"/><path d="M10 22h4"/>
        </svg>
        <div>
          <strong class="text-slate-800 font-semibold block mb-0.5">Saran Tindakan Warga:</strong>
          Masyarakat diimbau menampung air bersih secukupnya. Armada tangki darurat siaga di posko kelurahan jika dibutuhkan.
        </div>
      </div>
    </div>

    <!-- KARTU 2 (KUALITAS AIR - GREEN THEME) -->
    <div
      data-announcement-card
      data-districts="Payakumbuh Timur|Payakumbuh Selatan"
      class="bg-white rounded-3xl border border-emerald-100 p-6 sm:p-7 shadow-xs hover:shadow-md transition-shadow space-y-4"
    >
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
            <!-- Building2 -->
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
              <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
              <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
              <path d="M10 6h4"/><path d="M10 10h4"/><path d="M10 14h4"/><path d="M10 18h4"/>
            </svg>
          </div>
          <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-emerald-50 text-emerald-600 border border-emerald-200">
            kualitas air
          </span>
          <span class="text-slate-400 text-[11px]">2026-10-16 14:00 WIB</span>
        </div>
        <span class="px-3 py-1 rounded-full font-semibold text-xs bg-[#dcfce7] text-[#15803d]">
          Selesai
        </span>
      </div>

      <h3 class="text-base font-bold text-[#0c2340] leading-snug">
        Pembersihan Sedimen Bak Prasedimentasi Sumber Mata Air Sikabu
      </h3>

      <p class="text-xs text-slate-500 leading-relaxed">
        Pembersihan rutin material pasir dan lumpur pasca hujan intensitas tinggi di hulu Gunung Sago.
      </p>

      <!-- Dua Kolom: Wilayah Terdampak & Estimasi -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block">
            Wilayah Terdampak
          </span>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Payakumbuh Timur
            </span>
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Payakumbuh Selatan
            </span>
          </div>
        </div>

        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block flex items-center gap-1">
            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
            </svg>
            <span>Estimasi Pemulihan</span>
          </span>
          <div class="flex items-center gap-1.5 text-emerald-700 font-bold text-[11px]">
            <svg class="w-3.5 h-3.5 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Telah Selesai <span class="font-normal text-slate-500">(Tekanan Normal Kembali)</span></span>
          </div>
        </div>
      </div>

      <!-- Kotak Saran Tindakan Warga -->
      <div class="p-3.5 rounded-2xl bg-[#f7fdf9] border border-emerald-100 flex items-start gap-2.5 text-xs text-slate-600 leading-relaxed">
        <svg class="w-4 h-4 text-emerald-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
          <path d="M9 18h6"/><path d="M10 22h4"/>
        </svg>
        <div>
          <strong class="text-slate-800 font-semibold block mb-0.5">Saran Tindakan Warga:</strong>
          Jika air keran awal terlihat keruh sesaat, buka keran pembuangan selama 1-2 menit hingga jernih.
        </div>
      </div>
    </div>

    <!-- KARTU 3 (INFO LAYANAN - BLUE THEME) -->
    <div
      data-announcement-card
      data-districts="Lampasi Tigo Nagori (Latina)|Payakumbuh Barat"
      class="bg-white rounded-3xl border border-sky-100 p-6 sm:p-7 shadow-xs hover:shadow-md transition-shadow space-y-4"
    >
      <div class="flex items-center justify-between text-xs">
        <div class="flex items-center gap-2">
          <div class="w-7 h-7 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center">
            <!-- Megaphone -->
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m3 11 18-5v12L3 14v-3z"/>
              <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/>
            </svg>
          </div>
          <span class="px-2.5 py-0.5 rounded-full font-bold text-[11px] bg-sky-50 text-sky-600 border border-sky-200">
            info layanan
          </span>
          <span class="text-slate-400 text-[11px]">2026-10-15 09:00 WIB</span>
        </div>
        <span class="px-3 py-1 rounded-full font-semibold text-xs bg-[#e0f2fe] text-[#0369a1] flex items-center gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-[#0284c7]"></span>
          <span>Aktif</span>
        </span>
      </div>

      <h3 class="text-base font-bold text-[#0c2340] leading-snug">
        Imbauan Waspada Pipa Tersumbat &amp; Sosialisasi Pelaporan Cepat
      </h3>

      <p class="text-xs text-slate-500 leading-relaxed">
        Layanan respons cepat tim reaksi cepat (TRC) siaga 24 jam untuk menindaklanjuti laporan kebocoran air di jalan umum atau meteran pelanggan.
      </p>

      <!-- Dua Kolom: Wilayah Terdampak & Estimasi -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 text-xs">
        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block">
            Wilayah Terdampak
          </span>
          <div class="flex flex-wrap gap-1.5">
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Lampasi Tigo Nagori (Latina)
            </span>
            <span class="px-2.5 py-1 rounded-lg bg-[#e0f2fe] text-[#0369a1] text-[11px] font-medium">
              Payakumbuh Barat
            </span>
          </div>
        </div>

        <div class="space-y-1.5">
          <span class="text-[11px] font-bold text-slate-600 block flex items-center gap-1">
            <svg class="w-3 h-3 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
            </svg>
            <span>Estimasi Pemulihan</span>
          </span>
          <div class="flex items-center gap-1.5 text-slate-800 font-bold text-[11px]">
            <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/>
            </svg>
            <span>Berlaku Selama Musim Pancaroba</span>
          </div>
        </div>
      </div>

      <!-- Kotak Saran Tindakan Warga -->
      <div class="p-3.5 rounded-2xl bg-[#f0f9ff] border border-sky-100 flex items-start gap-2.5 text-xs text-slate-600 leading-relaxed">
        <svg class="w-4 h-4 text-sky-500 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/>
          <path d="M9 18h6"/><path d="M10 22h4"/>
        </svg>
        <div>
          <strong class="text-slate-800 font-semibold block mb-0.5">Saran Tindakan Warga:</strong>
          Gunakan fitur Buat Pengaduan di Smart Water untuk mendapatkan nomor tiket pelacakan otomatis.
        </div>
      </div>
    </div>

  </div>

  <!-- Empty State jika filter wilayah tidak ada pengumuman -->
  <div id="announcements-empty" class="hidden bg-white rounded-3xl border border-slate-200 p-8 text-center text-xs text-slate-500">
    Tidak ada pengumuman gangguan aktif untuk wilayah yang dipilih.
  </div>
</section>



          <!-- 4. SECTION FAQ & PUSAT BANTUAN -->
          
<?php
/**
 * 4. SECTION FAQ & PUSAT BANTUAN (DUA KOLOM DENGAN ILUSTRASI CS)
 * Sesuai persis dengan ServiceCenterView.tsx baris 869-940
 */
?>
<section class="bg-[#f2f8fe] rounded-[32px] border border-sky-100/90 p-6 sm:p-10 lg:p-12 relative overflow-hidden shadow-[0_4px_25px_-5px_rgba(2,132,199,0.04)]">
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center">

    <!-- Kolom Kiri: Accordion FAQ (7 Kolom Desktop) -->
    <div class="lg:col-span-7 space-y-6">
      <div>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-[#1d82f5] text-white flex items-center justify-center font-bold text-lg shadow-xs shrink-0">
            ?
          </div>
          <div>
            <h2 class="text-xl sm:text-2xl font-bold text-[#0c2340] tracking-tight">
              Pusat Bantuan &amp; Pertanyaan Sering Diajukan (FAQ)
            </h2>
          </div>
        </div>
        <p class="text-xs sm:text-sm text-slate-500 mt-2 sm:ml-13 leading-relaxed">
          Pelajari aturan pembacaan meteran, cara penagihan rekening, dan panduan pelaporan gangguan.
        </p>
      </div>

      <!-- Daftar Pertanyaan Accordion -->
      <div class="space-y-3" id="faq-accordion">
        <?php foreach ($FAQ_LIST as $idx => $faq): ?>
          <?php $isExpanded = ($idx === 0); ?>
          <div class="faq-item bg-white rounded-2xl border border-sky-100 overflow-hidden shadow-2xs transition-colors">
            <button
              type="button"
              onclick="toggleFaqItem(<?= $idx ?>)"
              aria-expanded="<?= $isExpanded ? 'true' : 'false' ?>"
              class="w-full p-4 sm:p-4.5 text-left flex items-center justify-between gap-3 cursor-pointer hover:bg-sky-50/20 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div class="w-5 h-5 rounded-full border border-sky-300 text-sky-600 flex items-center justify-center text-[10px] font-bold shrink-0">
                  !
                </div>
                <span class="text-xs sm:text-[13px] font-bold text-[#0c2340] leading-snug">
                  <?= e($faq['question']) ?>
                </span>
              </div>

              <svg
                data-faq-chevron="<?= $idx ?>"
                class="w-4 h-4 text-sky-500 shrink-0 transition-transform duration-200 <?= $isExpanded ? 'rotate-180' : '' ?>"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              >
                <path d="m6 9 6 6 6-6"/>
              </svg>
            </button>

            <div
              data-faq-body="<?= $idx ?>"
              class="<?= $isExpanded ? '' : 'hidden ' ?>px-5 pb-5 pt-1 text-xs text-slate-600 leading-relaxed border-t border-sky-50 bg-[#fafcff]"
            >
              <?= e($faq['answer']) ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Kolom Kanan: Ilustrasi Layanan Pelanggan (5 Kolom Desktop) -->
    <div class="lg:col-span-5 flex justify-center items-center">
      <?php renderCustomerSupportIllustration(); ?>
    </div>

  </div>
</section>


        </div>

        <!-- DEDICATED COMPLAINT FORM PAGE (Tampil saat Buat Pengaduan / + Tambah Pengaduan diklik) -->
        
<?php
/**
 * Dedicated Complaint Form Page & Success Screen
 * Sesuai persis dengan src/components/service/ComplaintFormPage.tsx
 */
?>
<div id="complaint-form-page-wrapper" class="hidden max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8 animate-fade-zoom">

  <!-- SCREEN A: FORMULIR PENGADUAN -->
  <div id="complaint-form-screen" class="space-y-8">
    <!-- Top Navigation Bar & Breadcrumb -->
    <div class="flex items-center justify-between gap-4">
      <button
        type="button"
        onclick="closeCreateComplaintPage()"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 hover:text-slate-900 bg-white border border-slate-200 hover:bg-slate-50 transition-colors shadow-2xs group cursor-pointer"
      >
        <svg class="w-4 h-4 text-slate-400 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 19-7-7 7-7"/>
          <path d="M19 12H5"/>
        </svg>
        <span>Kembali ke Pusat Layanan</span>
      </button>

      <div class="flex items-center gap-1.5 text-xs text-slate-400 font-medium">
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"/>
          <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"/>
          <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"/>
        </svg>
        <span>PDAM Tirta Sago Payakumbuh</span>
      </div>
    </div>

    <!-- Hero Header Banner -->
    <div class="rounded-3xl border border-sky-100/90 bg-gradient-to-r from-[#D7EEFD] via-[#E8F4FE] to-[#D5EDFC] p-6 sm:p-8 relative overflow-hidden shadow-xs">
      <div class="relative z-10 space-y-2 max-w-2xl">
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/80 border border-sky-200 text-[#0284C7] text-xs font-bold">
          <svg class="w-3.5 h-3.5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
          </svg>
          <span>Formulir Pengaduan Resmi Pelanggan</span>
        </div>

        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#0F2C59] tracking-tight">
          Lapor Gangguan &amp; Kebocoran Air
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
          Laporkan masalah kebocoran pipa, air keruh, mati air, atau kerusakan meteran secara langsung. Laporan Anda tercatat resmi dan dipantau oleh Tim Reaksi Cepat (TRC) Kota Payakumbuh.
        </p>

        <?php if ($currentUser): ?>
          <div class="pt-2 flex flex-wrap items-center gap-3 text-xs">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl bg-white/90 border border-sky-200 text-slate-800 font-medium">
              <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                <path d="m9 12 2 2 4-4"/>
              </svg>
              <span>Pelapor: <strong><?= e($currentUser['name']) ?></strong> (ID: <?= e($currentUser['meterNumber'] ?? 'PYK-001') ?>)</span>
            </span>
            <span class="text-slate-500 font-mono text-[11px]">
              No. HP: <?= e($currentUser['phone'] ?? '0812-7389-4902') ?>
            </span>
          </div>
        <?php else: ?>
          <div class="pt-2">
            <a
              href="<?= url('/login') ?>"
              class="text-xs font-bold text-[#0284C7] hover:underline flex items-center gap-1 cursor-pointer"
            >
              <span>Sudah punya akun pelanggan? Masuk untuk pengisian otomatis</span>
            </a>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Main Form Card Container -->
    <form id="complaint-submit-form" onsubmit="handleSubmitComplaint(event)" class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-9 shadow-sm space-y-8">
      <div id="complaint-form-error" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-3">
        <svg class="w-4 h-4 shrink-0 text-rose-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>
        <span id="complaint-form-error-text" class="font-medium"></span>
      </div>

      <!-- SECTION 1: PILIH KATEGORI GANGGUAN -->
      <div class="space-y-3">
        <div class="flex items-center justify-between">
          <label class="text-xs sm:text-sm font-bold text-slate-900 block">
            1. Pilih Kategori Masalah / Gangguan <span class="text-rose-500">*</span>
          </label>
          <span class="text-[11px] text-slate-400">Pilih salah satu yang paling sesuai</span>
        </div>

        <input type="hidden" id="complaint-category-input" value="pipa_bocor" />

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
          <?php foreach ($COMPLAINT_CATEGORIES as $idx => $cat): ?>
            <?php $isSelected = ($cat['id'] === 'pipa_bocor'); ?>
            <button
              type="button"
              data-category-btn="<?= e($cat['id']) ?>"
              onclick="selectComplaintCategory('<?= e($cat['id']) ?>')"
              class="complaint-cat-btn p-4 rounded-2xl border text-left transition-all relative flex flex-col justify-between gap-2 cursor-pointer <?= $isSelected
                ? 'border-[#0284C7] bg-sky-50/70 ring-2 ring-sky-200 shadow-xs'
                : 'border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50' ?>"
            >
              <div class="flex items-start justify-between gap-2">
                <span class="text-xs font-bold text-slate-900 leading-snug">
                  <?= e($cat['label']) ?>
                </span>
                <div class="cat-check-badge w-5 h-5 rounded-full bg-[#0284C7] text-white flex items-center justify-center shrink-0 <?= $isSelected ? '' : 'hidden' ?>">
                  <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 6 9 17l-5-5"/>
                  </svg>
                </div>
              </div>

              <p class="text-[11px] text-slate-500 leading-relaxed">
                <?= e($cat['desc']) ?>
              </p>

              <?php if (!empty($cat['badge'])): ?>
                <span class="self-start text-[10px] font-bold px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200">
                  <?= e($cat['badge']) ?>
                </span>
              <?php endif; ?>
            </button>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- SECTION 2: LOKASI & KONTAK -->
      <div class="space-y-4 pt-4 border-t border-slate-100">
        <label class="text-xs sm:text-sm font-bold text-slate-900 block">
          2. Wilayah Lokasi &amp; Kontak Pelapor <span class="text-rose-500">*</span>
        </label>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 block">
              Wilayah Kecamatan (Kota Payakumbuh)
            </label>
            <select
              id="complaint-district-input"
              class="w-full px-3.5 py-3 text-xs bg-white rounded-xl border border-slate-200 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 text-slate-900 font-medium"
            >
              <?php foreach ($DISTRICTS_PAYAKUMBUH as $d): ?>
                <option value="<?= e($d) ?>"><?= e($d) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="space-y-1.5">
            <label class="text-xs font-semibold text-slate-700 block">
              Nomor WhatsApp Aktif Pelapor
            </label>
            <div class="relative">
              <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
              </div>
              <input
                id="complaint-phone-input"
                type="tel"
                required
                placeholder="0812-xxxx-xxxx"
                value="<?= e($currentUser['phone'] ?? '0812-7389-4902') ?>"
                class="w-full pl-10 pr-3.5 py-3 text-xs bg-white rounded-xl border border-slate-200 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 text-slate-900 font-mono"
              />
            </div>
            <span class="text-[10px] text-slate-400 block">
              Petugas lapangan akan menghubungi nomor ini sebelum menuju lokasi.
            </span>
          </div>
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-700 block">
            Detail Alamat &amp; Titik Patokan Lokasi <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <div class="absolute top-3 left-3.5 pointer-events-none text-slate-400">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
                <circle cx="12" cy="10" r="3"/>
              </svg>
            </div>
            <textarea
              id="complaint-address-input"
              rows="2"
              required
              placeholder="Contoh: Jl. Soekarno Hatta No. 45, depan Toko Roti Sari Rasa, dekat simpang pasar..."
              class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-white rounded-xl border border-slate-200 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 text-slate-900 leading-relaxed"
            ><?= e($currentUser['address'] ?? '') ?></textarea>
          </div>
        </div>
      </div>

      <!-- SECTION 3: JUDUL & DESKRIPSI KRONOLOGI -->
      <div class="space-y-4 pt-4 border-t border-slate-100">
        <label class="text-xs sm:text-sm font-bold text-slate-900 block">
          3. Rincian &amp; Kronologi Gangguan <span class="text-rose-500">*</span>
        </label>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-700 block">
            Judul Ringkas Keluhan
          </label>
          <input
            id="complaint-title-input"
            type="text"
            required
            placeholder="Contoh: Pipa bocor menyembur cukup deras di bahu jalan aspal"
            class="w-full px-3.5 py-3 text-xs bg-white rounded-xl border border-slate-200 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 text-slate-900 font-medium"
          />
        </div>

        <div class="space-y-1.5">
          <label class="text-xs font-semibold text-slate-700 block">
            Deskripsi Kronologi &amp; Kondisi Kejadian
          </label>
          <textarea
            id="complaint-desc-input"
            rows="4"
            required
            placeholder="Jelaskan sejak jam berapa air tidak mengalir, kondisi semburan air, atau detail keluhan lainnya..."
            class="w-full px-3.5 py-3 text-xs bg-white rounded-xl border border-slate-200 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 text-slate-900 leading-relaxed"
          ></textarea>
        </div>
      </div>

      <!-- SECTION 4: UNGGAH FOTO BUKTI (OPSIONAL) -->
      <div class="space-y-3 pt-4 border-t border-slate-100">
        <label class="text-xs sm:text-sm font-bold text-slate-900 block">
          4. Foto Bukti Kejadian <span class="text-slate-400 font-normal">(Opsional)</span>
        </label>

        <div class="flex flex-col sm:flex-row sm:items-center gap-4">
          <label class="cursor-pointer px-4 py-3 rounded-2xl border-2 border-dashed border-sky-200 hover:border-[#0284C7] bg-sky-50/50 hover:bg-sky-50 text-xs text-slate-700 flex items-center justify-center gap-2.5 transition-all">
            <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
              <circle cx="12" cy="13" r="3"/>
            </svg>
            <span class="font-semibold">Unggah Foto Kejadian</span>
            <input
              id="complaint-photo-input"
              type="file"
              accept="image/*"
              onchange="handleComplaintPhotoPreview(event)"
              class="hidden"
            />
          </label>

          <div id="complaint-photo-preview-wrap" class="hidden relative w-24 h-24 rounded-2xl overflow-hidden border border-slate-200 shadow-2xs">
            <img id="complaint-photo-preview-img" src="" alt="Preview" class="w-full h-full object-cover" />
            <button
              type="button"
              onclick="clearComplaintPhotoPreview()"
              class="absolute top-1 right-1 bg-slate-900/80 text-white p-1 rounded-full hover:bg-rose-600 transition-colors cursor-pointer"
              title="Hapus foto"
            >
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
              </svg>
            </button>
          </div>

          <div class="text-[11px] text-slate-400 space-y-0.5">
            <p>Format yang didukung: JPG, PNG, WEBP (Maksimal 5MB).</p>
            <p>Lampirkan foto semburan air, meteran buram, atau genangan pipa.</p>
          </div>
        </div>
      </div>

      <!-- SECTION 5: TOMBOL TINDAKAN -->
      <div class="pt-6 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
        <button
          type="button"
          onclick="closeCreateComplaintPage()"
          class="w-full sm:w-auto px-5 py-3 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors cursor-pointer"
        >
          Batal
        </button>

        <button
          id="complaint-submit-btn"
          type="submit"
          class="w-full sm:w-auto px-7 py-3 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-all shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 disabled:opacity-50 cursor-pointer"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/>
            <path d="M14 2v4a2 2 0 0 0 2 2h4"/>
            <path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/>
          </svg>
          <span>Kirim Laporan Pengaduan Resmi</span>
        </button>
      </div>
    </form>
  </div>

  <!-- SCREEN B: SUCCESS SCREEN AFTER SUBMISSION -->
  <div id="complaint-success-screen" class="hidden py-2 space-y-8 animate-fade-zoom">
    <div class="bg-white rounded-3xl border border-emerald-200 p-8 sm:p-12 shadow-sm text-center space-y-6">
      <div class="w-20 h-20 mx-auto rounded-3xl bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 shadow-xs">
        <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
        </svg>
      </div>

      <div class="space-y-2 max-w-lg mx-auto">
        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold uppercase tracking-wider">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 6 9 17l-5-5"/>
          </svg>
          Pengaduan Berhasil Terkirim
        </span>
        <h2 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
          Laporan Anda Sedang Ditindaklanjuti
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
          Tiket pengaduan resmi Anda telah tercatat dalam sistem terpadu Perumda Air Minum Tirta Sago. Tim Reaksi Cepat (TRC) wilayah <strong id="success-district-text"></strong> telah diberi notifikasi tugas.
        </p>
      </div>

      <!-- Ticket Information Box -->
      <div class="max-w-md mx-auto p-5 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3">
        <div class="flex items-center justify-between pb-3 border-b border-slate-200/80">
          <span class="text-xs font-semibold text-slate-500">Nomor Tiket Pengaduan:</span>
          <div class="flex items-center gap-2">
            <span id="success-ticket-id" class="font-mono text-sm font-extrabold text-[#0284C7] bg-white px-2.5 py-1 rounded-lg border border-sky-200">
              TK-PYK-2026-0000
            </span>
            <button
              type="button"
              onclick="copySuccessTicketId()"
              class="p-1 text-slate-400 hover:text-[#0284C7] transition-colors cursor-pointer"
              title="Salin Nomor Tiket"
            >
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/>
                <path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
              </svg>
            </button>
          </div>
        </div>

        <p id="success-copied-note" class="hidden text-[11px] font-semibold text-emerald-600 text-right">
          Nomor tiket disalin ke clipboard!
        </p>

        <div class="grid grid-cols-2 gap-3 text-xs text-slate-600">
          <div>
            <span class="text-[11px] text-slate-400 block">Kategori Masalah:</span>
            <span id="success-ticket-category" class="font-semibold text-slate-800 capitalize">-</span>
          </div>
          <div>
            <span class="text-[11px] text-slate-400 block">Status Awal:</span>
            <span id="success-ticket-status" class="inline-flex items-center gap-1 font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-200 text-[10px]">
              BARU
            </span>
          </div>
          <div class="col-span-2">
            <span class="text-[11px] text-slate-400 block">Lokasi Dilaporkan:</span>
            <span id="success-ticket-location" class="font-medium text-slate-800 leading-tight block mt-0.5">-</span>
          </div>
        </div>
      </div>

      <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
        <button
          type="button"
          onclick="closeCreateComplaintPage()"
          class="w-full sm:w-auto px-6 py-3 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-sm flex items-center justify-center gap-2 cursor-pointer"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m12 19-7-7 7-7"/>
            <path d="M19 12H5"/>
          </svg>
          <span>Kembali ke Pusat Layanan</span>
        </button>
      </div>
    </div>
  </div>

</div>


      </main>

      <!-- Footer -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- Modal Pelacakan Tiket & Simulasi Operator -->
      
<?php
/**
 * Modal Pelacakan Tiket Pengaduan & Simulasi Update Operator
 * Sesuai persis dengan src/components/service/TicketTrackerModal.tsx
 */
?>
<div
  id="ticket-tracker-modal"
  class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
>
  <div class="bg-white rounded-2xl max-w-2xl w-full border border-slate-200 shadow-2xl overflow-hidden flex flex-col max-h-[92vh] animate-fade-zoom">
    
    <!-- Header -->
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
      <div>
        <div class="flex items-center gap-2">
          <span
            id="tracker-modal-id"
            class="font-mono font-bold text-xs text-cyan-800 bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200"
          >
            TK-PYK-2026-8942
          </span>
          <span
            id="tracker-modal-status"
            class="text-[11px] font-semibold px-2 py-0.5 rounded border bg-emerald-50 text-emerald-700 border-emerald-200"
          >
            SELESAI
          </span>
        </div>
        <h3 id="tracker-modal-title" class="text-base font-bold text-slate-900 mt-1">
          Memuat detail tiket...
        </h3>
      </div>

      <button
        type="button"
        onclick="closeTicketTrackerModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer"
        aria-label="Tutup pelacakan tiket"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6 6 18"/>
          <path d="m6 6 12 12"/>
        </svg>
      </button>
    </div>

    <!-- Content Body -->
    <div class="p-6 overflow-y-auto space-y-6">
      <!-- Visual Step Progression -->
      <div class="bg-slate-50 p-4 rounded-xl border border-slate-200 space-y-2">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">
          Tahapan Workflow Layanan
        </span>
        <div id="tracker-modal-steps" class="grid grid-cols-4 gap-2 pt-1">
          <!-- Rendered dynamically by layanan.js -->
        </div>
      </div>

      <!-- Ticket Information Details -->
      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
        <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-1.5">
          <span class="text-[10px] font-bold uppercase text-slate-400 block">
            Pelapor &amp; Wilayah
          </span>
          <p id="tracker-modal-username" class="text-slate-900 font-semibold">-</p>
          <div class="flex items-center gap-1.5 text-slate-500">
            <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
              <circle cx="12" cy="10" r="3"/>
            </svg>
            <span id="tracker-modal-district">-</span>
          </div>
          <p id="tracker-modal-address" class="text-slate-600 text-[11px] leading-tight">-</p>
        </div>

        <div class="p-3 bg-white rounded-xl border border-slate-200 space-y-1.5">
          <span class="text-[10px] font-bold uppercase text-slate-400 block">
            Waktu &amp; Kontak
          </span>
          <div class="flex items-center gap-1.5 text-slate-600">
            <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/>
              <polyline points="12 6 12 12 16 14"/>
            </svg>
            <span>Diajukan: <span id="tracker-modal-created">-</span></span>
          </div>
          <div class="flex items-center gap-1.5 text-slate-600">
            <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span id="tracker-modal-phone">-</span>
          </div>
          <p class="text-[11px] text-slate-400">
            Terakhir update: <span id="tracker-modal-updated">-</span>
          </p>
        </div>
      </div>

      <!-- Description -->
      <div class="p-4 bg-slate-50/70 rounded-xl border border-slate-200 space-y-1">
        <span class="text-[10px] font-bold uppercase text-slate-400 block">
          Deskripsi Laporan Awal
        </span>
        <p id="tracker-modal-desc" class="text-xs text-slate-700 leading-relaxed">-</p>
        <div id="tracker-modal-photo-wrap" class="hidden pt-2">
          <img
            id="tracker-modal-photo"
            src=""
            alt="Bukti foto"
            class="w-32 h-24 object-cover rounded-lg border border-slate-200"
          />
        </div>
      </div>

      <!-- Updates Timeline from Operator -->
      <div class="space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">
          Riwayat Tindak Lanjut Petugas
        </h4>
        <div id="tracker-modal-updates" class="space-y-2.5">
          <!-- Rendered dynamically by layanan.js -->
        </div>
      </div>
    </div>

    <!-- Footer with Operator Progress Simulation -->
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-center justify-between gap-3">
      <div class="text-[11px] text-slate-500 text-center sm:text-left">
        <span>Sinkronisasi langsung dengan sistem operasi Tirta Sago Payakumbuh</span>
      </div>

      <div class="flex items-center gap-2">
        <button
          id="tracker-modal-sim-btn"
          type="button"
          onclick="simulateOperatorProgress()"
          class="hidden px-3.5 py-2 rounded-xl text-xs font-semibold text-cyan-900 bg-cyan-100 hover:bg-cyan-200 border border-cyan-300 transition-colors flex items-center gap-1.5 cursor-pointer"
          title="Simulasikan respon operator ke status berikutnya"
        >
          <svg class="w-3.5 h-3.5 text-cyan-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
            <path d="M21 3v5h-5"/>
            <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
            <path d="M8 16H3v5"/>
          </svg>
          <span id="tracker-modal-sim-label">Simulasi Update Operator</span>
        </button>

        <span
          id="tracker-modal-done-badge"
          class="hidden text-xs font-semibold text-emerald-700 flex items-center gap-1"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
          </svg>
          <span>Pengaduan Selesai Ditangani</span>
        </span>

        <button
          type="button"
          onclick="closeTicketTrackerModal()"
          class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors cursor-pointer"
        >
          Tutup
        </button>
      </div>
    </div>

  </div>
</div>



      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <!-- Inject initial PHP Session complaints into JavaScript -->
    <script>
      window.SWC_COMPLAINTS = <?= json_encode($complaints, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="../../assets/js/pages/pusat-layanan.js"></script>
  </body>
</html>
