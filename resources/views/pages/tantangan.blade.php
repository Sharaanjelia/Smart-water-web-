<?php
/**
 * Halaman Utama Tantangan Hemat Air - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 * Sesuai persis dengan src/components/challenge/ChallengeView.tsx
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
    <link rel="stylesheet" href="../../assets/css/pages/tantangan.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">

      <!-- Top Header -->
      <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleChallengeNav',
    'logoHref' => url('/beranda'),
    'activeNav' => true,
    'user' => $_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null),
];
require $legacySharedPath . '/header.php';
?>

      <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
          
          <!-- Toast Notification -->
          <div
            id="challenge-toast"
            class="hidden fixed bottom-6 right-24 z-50 bg-slate-900 text-white px-4 py-3 rounded-xl shadow-xl flex items-center gap-2.5 text-xs font-semibold animate-fade-zoom"
          >
            <svg class="w-4 h-4 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
              <path d="M5 3v4"/><path d="M19 17v4"/><path d="M3 5h4"/><path d="M17 19h4"/>
            </svg>
            <span id="challenge-toast-text"></span>
          </div>

          <!-- Main Challenge Layout: 2 Columns -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            
            <!-- Left Column: Challenges List (4 Cols) -->
            <div class="lg:col-span-4 space-y-3">
              <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 px-1">
                Daftar Program Tantangan
              </h3>
              <div id="challenges-list" class="space-y-3"></div>
            </div>

            <!-- Right Column: Active Challenge Detail & Interactive Mission Checklist (8 Cols) -->
            <div
              id="challenge-detail-card"
              class="lg:col-span-8 bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-6"
            ></div>

          </div>
        </div>
      </main>

      <!-- Footer -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <script>
      window.SWC_CHALLENGES = <?= json_encode($WATER_CHALLENGES, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      window.SWC_BADGES = <?= json_encode($BADGE_DEFINITIONS, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      window.SWC_CHALLENGE_PROGRESS = <?= json_encode($challengeProgress, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="../../assets/js/pages/tantangan.js"></script>
  </body>
</html>
