<?php
/**
 * Halaman Utama Chat AI (TirtaBot AI) - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 * Menampilkan ruang kerja chat AI interaktif penuh + floating popup TirtaBot (sesuai AiChatbot.tsx)
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
    <link rel="stylesheet" href="../../assets/css/pages/chat-ai.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">

      <!-- Top Header -->
      <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleChatNav',
    'activeNav' => false,
    'showPoints' => true,
    'points' => $userPoints,
    'mobileAuthMode' => 'always',
];
require $legacySharedPath . '/header.php';
?>

      <main class="flex-1">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
          
          <!-- Two-Column Workspace: Info & Quick Prompts + Live TirtaBot Chat Window -->
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">

            <!-- Left Column: TirtaBot Overview & Topik Populer (5 cols) -->
            <div class="lg:col-span-5 space-y-6">
              <div class="bg-gradient-to-br from-slate-900 via-sky-950 to-slate-900 text-white rounded-3xl p-6 sm:p-8 space-y-4 relative overflow-hidden shadow-md">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-cyan-400/20 border border-cyan-400/30 text-cyan-300 text-xs font-semibold">
                  <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
                  <span>Gemini 2.5 AI Assistant</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white leading-tight">
                  TirtaBot AI Kota Payakumbuh
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 leading-relaxed">
                  Asisten cerdas Smart Water yang siap menjawab pertanyaan seputar penghematan air rumah tangga, deteksi kebocoran tersembunyi, perhitungan tarif Perumda Tirta Sago, dan cara pelaporan tiket layanan.
                </p>

                <button
                  type="button"
                  onclick="openTirtaBot()"
                  class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-400 text-slate-950 text-xs font-bold transition-colors shadow-sm cursor-pointer"
                >
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                  <span>Buka Popup Melayang TirtaBot</span>
                </button>
              </div>

              <!-- Quick Topics Card -->
              <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-4 shadow-xs">
                <div class="flex items-center justify-between">
                  <h2 class="text-sm font-bold text-slate-900">Topik Pertanyaan Cepat</h2>
                  <span class="text-[11px] font-semibold text-cyan-700">Klik untuk tanya langsung</span>
                </div>

                <div class="space-y-2">
                  <?php foreach ($CHAT_QUICK_PROMPTS as $prompt): ?>
                    <button
                      type="button"
                      onclick="sendTirtaBotMessage('<?= e($prompt) ?>')"
                      class="w-full text-left px-3.5 py-2.5 rounded-xl bg-slate-50 hover:bg-cyan-50 border border-slate-200 hover:border-cyan-300 text-xs font-medium text-slate-700 hover:text-cyan-900 transition-colors flex items-center justify-between gap-2 cursor-pointer"
                    >
                      <span><?= e($prompt) ?></span>
                      <svg class="w-3.5 h-3.5 text-cyan-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <!-- Right Column: Full Live Interactive TirtaBot Window (7 cols, exact AiChatbot.tsx styling) -->
            <div class="lg:col-span-7">
              <div class="flex flex-col h-[620px] bg-white rounded-3xl border border-slate-200/90 shadow-xl shadow-slate-900/10 overflow-hidden">
                
                <!-- Chat Header -->
                <div class="px-5 py-4 bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white flex items-center justify-between shrink-0 border-b border-white/10">
                  <div class="flex items-center gap-3">
                    <div class="relative">
                      <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-sky-400 flex items-center justify-center text-white shadow-sm">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                          <path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>
                        </svg>
                      </div>
                      <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-400 border-2 border-slate-900"></span>
                    </div>
                    <div>
                      <div class="flex items-center gap-1.5">
                        <h3 class="text-xs font-bold text-white tracking-wide">
                          TirtaBot AI
                        </h3>
                        <span class="px-1.5 py-0.5 rounded-full bg-cyan-400/20 border border-cyan-400/30 text-[9px] font-semibold text-cyan-300">
                          Gemini 2.5
                        </span>
                      </div>
                      <p class="text-[10px] text-slate-300 flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Asisten Air Payakumbuh</span>
                      </p>
                    </div>
                  </div>

                  <button
                    type="button"
                    onclick="clearTirtaBotHistory()"
                    title="Hapus riwayat chat"
                    class="px-2.5 py-1.5 rounded-lg bg-white/10 hover:bg-white/20 text-xs font-semibold text-slate-200 hover:text-white transition-colors flex items-center gap-1.5 cursor-pointer"
                  >
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                    </svg>
                    <span>Reset Chat</span>
                  </button>
                </div>

                <!-- Messages Container -->
                <div id="main-tirtabot-messages" class="flex-1 p-5 overflow-y-auto space-y-3.5 bg-slate-50/60">
                  <div class="p-2.5 rounded-xl bg-cyan-50/70 border border-cyan-100/80 text-[11px] text-cyan-900 flex items-start gap-2">
                    <svg class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
                    </svg>
                    <p class="leading-snug">
                      Tanyakan apa saja seputar tips hemat air, deteksi kebocoran pipa, estimasi tarif Tirta Sago, atau tiket layanan!
                    </p>
                  </div>
                </div>

                <!-- Quick Prompts Strip -->
                <div class="px-3.5 py-2.5 bg-white border-t border-slate-100 overflow-x-auto no-scrollbar flex items-center gap-1.5 shrink-0">
                  <?php foreach ($CHAT_QUICK_PROMPTS as $prompt): ?>
                    <button
                      type="button"
                      onclick="sendTirtaBotMessage('<?= e($prompt) ?>')"
                      class="whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 hover:bg-cyan-50 hover:text-cyan-800 text-slate-600 border border-slate-200 transition-colors shrink-0 cursor-pointer"
                    >
                      <?= e($prompt) ?>
                    </button>
                  <?php endforeach; ?>
                </div>

                <!-- Input Form -->
                <form
                  onsubmit="handleMainChatSubmit(event)"
                  class="p-3.5 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0"
                >
                  <input
                    id="main-tirtabot-input"
                    type="text"
                    placeholder="Tulis pertanyaan seputar air di Payakumbuh..."
                    class="flex-1 px-4 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all text-slate-800 placeholder:text-slate-400"
                  />
                  <button
                    type="submit"
                    class="w-10 h-10 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white flex items-center justify-center transition-all shrink-0 shadow-sm cursor-pointer"
                    aria-label="Kirim Pesan"
                  >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                      <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/><path d="m21.854 2.147-10.94 10.939"/>
                    </svg>
                  </button>
                </form>

              </div>
            </div>

          </div>
        </div>
      </main>

      <!-- Footer -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- Floating TirtaBot AI Chatbot Widget (1:1 AiChatbot.tsx) -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <script>
      window.SWC_INITIAL_WELCOME = <?= json_encode($INITIAL_WELCOME_CONTENT, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    </script>
    <script src="../../assets/js/pages/chat-ai.js"></script>
  </body>
</html>
