<?php
/**
 * Halaman Utama Tentang Ekosistem - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 * Sesuai persis dengan src/components/about/AboutView.tsx
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
    <link rel="stylesheet" href="../../assets/css/pages/tentang.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">

      <!-- Top Header -->
      <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleAboutNav',
    'logoHref' => url('/beranda'),
    'activeNav' => true,
    'user' => $_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null),
];
require $legacySharedPath . '/header.php';
?>

      <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-12">
          <!-- Hero -->
          
<?php
/**
 * Hero Section - Halaman Tentang (sesuai AboutView.tsx baris 22-36)
 */
?>
<div class="bg-gradient-to-r from-slate-900 via-cyan-950 to-slate-900 text-white rounded-3xl p-6 sm:p-12 relative overflow-hidden">
  <div class="relative z-10 max-w-2xl space-y-4">
    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-cyan-400/20 text-cyan-300 text-xs font-semibold border border-cyan-300/30">
      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/>
        <path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/>
        <path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/>
      </svg>
      <span>Arsitektur Ekosistem Digital</span>
    </div>
    <h1 class="text-3xl sm:text-5xl font-extrabold tracking-tight text-white leading-tight">
      Smart Water Kota Payakumbuh
    </h1>
    <p class="text-sm sm:text-base text-slate-300 leading-relaxed">
      Menghubungkan masyarakat, teknologi kecerdasan buatan, dan Perumda Air Minum Tirta Sago untuk pengelolaan sumber daya air yang transparan, efisien, dan berkelanjutan.
    </p>
  </div>
</div>



          <!-- 3 Pillars of Ecosystem -->
          
<?php
/**
 * 3 Pillars of Ecosystem Section - Halaman Tentang (sesuai AboutView.tsx baris 38-149)
 */
?>
<div class="space-y-6">
  <div class="text-center max-w-2xl mx-auto space-y-2">
    <span class="text-xs font-bold uppercase tracking-wider text-cyan-800">
      Trilogi Ekosistem Smart Water
    </span>
    <h2 class="text-2xl font-bold text-slate-900">
      Tiga Komponen yang Saling Melengkapi
    </h2>
    <p class="text-xs text-slate-500">
      Sistem dirancang dengan pembagian peran yang tegas dan harmonis antara aplikasi bergerak di lapangan, portal komunitas warga, dan pusat operasi utilitas.
    </p>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <?php foreach ($ECOSYSTEM_PILLARS as $pillar): ?>
      <?php $isHighlight = !empty($pillar['highlight']); ?>
      <div class="<?= $isHighlight
        ? 'bg-white rounded-2xl border-2 border-cyan-600 p-6 space-y-4 shadow-md relative'
        : 'bg-white rounded-2xl border border-slate-200 p-6 space-y-4 shadow-xs' ?>">

        <?php if ($pillar['icon'] === 'smartphone'): ?>
          <div class="w-12 h-12 rounded-xl bg-cyan-50 text-cyan-700 flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>
            </svg>
          </div>
        <?php elseif ($pillar['icon'] === 'globe'): ?>
          <div class="w-12 h-12 rounded-xl bg-cyan-600 text-white flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/><path d="M2 12h20"/>
            </svg>
          </div>
        <?php else: ?>
          <div class="w-12 h-12 rounded-xl bg-slate-100 text-slate-800 flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/>
            </svg>
          </div>
        <?php endif; ?>

        <div>
          <span class="text-[10px] font-bold uppercase tracking-wider block <?= $pillar['icon'] === 'server' ? 'text-slate-500' : 'text-cyan-800' ?>">
            <?= e($pillar['badge']) ?>
          </span>
          <h3 class="text-base font-bold text-slate-900">
            <?= e($pillar['title']) ?>
          </h3>
        </div>

        <p class="text-xs text-slate-600 leading-relaxed">
          <?= e($pillar['description']) ?>
        </p>

        <ul class="text-xs text-slate-500 space-y-1.5 pt-2 border-t border-slate-100">
          <?php foreach ($pillar['features'] as $feat): ?>
            <li class="flex items-center gap-2">
              <svg class="w-3.5 h-3.5 shrink-0 <?= $pillar['icon'] === 'globe' ? 'text-emerald-600' : ($pillar['icon'] === 'server' ? 'text-slate-600' : 'text-cyan-600') ?>" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
              </svg>
              <span><?= e($feat) ?></span>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </div>
</div>



          <!-- Payakumbuh Commitment CTA -->
          
<?php
/**
 * Payakumbuh Commitment Section - Halaman Tentang (sesuai AboutView.tsx baris 151-173)
 */
?>
<div class="bg-slate-50 rounded-3xl border border-slate-200 p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6">
  <div class="space-y-2 max-w-xl">
    <div class="flex items-center gap-2 text-cyan-800 text-xs font-bold uppercase">
      <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
        <path d="m9 12 2 2 4-4"/>
      </svg>
      <span>Komitmen Konservasi Batang Agam</span>
    </div>
    <h3 class="text-xl font-bold text-slate-900">
      Air Bersih dari Kaki Gunung Sago untuk Seluruh Warga
    </h3>
    <p class="text-xs text-slate-600 leading-relaxed">
      Menjaga keberlanjutan air bukan sekadar tugas teknis pemerintah, melainkan tanggung jawab kolektif setiap rumah tangga. Bergabunglah dalam gerakan hemat air hari ini.
    </p>
  </div>

  <a
    href="<?= url('/tantangan') ?>"
    class="px-5 py-3 rounded-xl bg-slate-900 text-white hover:bg-slate-800 text-xs font-bold flex items-center gap-2 transition-colors shrink-0 shadow-sm cursor-pointer"
  >
    <span>Mulai Tantangan Komunitas</span>
    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
    </svg>
  </a>
</div>


        </div>
      </main>

      <!-- Footer -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <script src="../../assets/js/pages/tentang.js"></script>
  </body>
</html>
