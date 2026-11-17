<?php
/**
 * TirtaBot AI Chatbot Floating Trigger & Chat Window
 * Sesuai persis dengan src/components/chat/AiChatbot.tsx
 */
$QUICK_PROMPTS = [
    '💧 Cara cek kebocoran pipa tersembunyi?',
    '📊 Berapa tarif air Tirta Sago per kubik?',
    '📱 Bagaimana cara kerja AI-OCR meteran?',
    '🌱 Tips hemat air mudah di rumah?',
    '📝 Cara membuat laporan pengaduan pipa bocor?',
];
?>
<!-- 1. FLOATING BUTTON TRIGGER -->
<div id="tirtabot-trigger-wrap" class="fixed bottom-6 right-6 z-50 flex items-center gap-2">
  <button
    type="button"
    onclick="openTirtaBot()"
    class="group relative flex items-center justify-center w-14 h-14 rounded-full bg-gradient-to-tr from-cyan-600 via-sky-600 to-cyan-400 text-white shadow-xl shadow-cyan-600/35 hover:shadow-cyan-600/50 hover:scale-105 active:scale-95 transition-all duration-200 focus:outline-hidden focus:ring-4 focus:ring-cyan-300 cursor-pointer"
    aria-label="Buka Chat AI TirtaBot"
  >
    <!-- Glowing ring animation -->
    <span class="absolute -inset-1 rounded-full bg-cyan-400 opacity-30 group-hover:opacity-60 blur-sm transition-opacity"></span>

    <div class="relative flex items-center justify-center">
      <!-- Bot Icon -->
      <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M12 8V4H8"/>
        <rect width="16" height="12" x="4" y="8" rx="2"/>
        <path d="M2 14h2"/>
        <path d="M20 14h2"/>
        <path d="M15 13v2"/>
        <path d="M9 13v2"/>
      </svg>
      <span class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-400 border-2 border-white"></span>
    </div>

    <!-- Unread badge -->
    <span
      id="tirtabot-unread-badge"
      class="hidden absolute -top-1 -right-1 w-5 h-5 bg-rose-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center border-2 border-white shadow-xs"
    >0</span>
  </button>
</div>

<!-- 2. CHAT WINDOW / MODAL -->
<div
  id="tirtabot-window"
  class="hidden fixed z-50 transition-all duration-300 ease-out bottom-4 right-4 sm:bottom-6 sm:right-6 w-[calc(100vw-2rem)] sm:w-[420px] max-w-[440px] h-[580px] max-h-[85vh]"
>
  <div class="flex flex-col h-full bg-white rounded-3xl border border-slate-200/80 shadow-2xl shadow-slate-900/20 overflow-hidden">
    
    <!-- Header -->
    <div class="px-4 py-3.5 bg-gradient-to-r from-slate-900 via-sky-950 to-slate-900 text-white flex items-center justify-between shrink-0 border-b border-white/10">
      <div class="flex items-center gap-3">
        <div class="relative">
          <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-cyan-500 to-sky-400 flex items-center justify-center text-white shadow-sm">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 8V4H8"/>
              <rect width="16" height="12" x="4" y="8" rx="2"/>
              <path d="M2 14h2"/>
              <path d="M20 14h2"/>
              <path d="M15 13v2"/>
              <path d="M9 13v2"/>
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

      <div class="flex items-center gap-1 text-slate-300">
        <button
          type="button"
          onclick="clearTirtaBotHistory()"
          title="Hapus riwayat chat"
          class="p-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors cursor-pointer"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 6h18"/>
            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
            <line x1="10" x2="10" y1="11" y2="17"/>
            <line x1="14" x2="14" y1="11" y2="17"/>
          </svg>
        </button>
        <button
          type="button"
          onclick="toggleMinimizeTirtaBot()"
          id="tirtabot-min-btn"
          title="Perkecil"
          class="p-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors cursor-pointer"
        >
          <svg id="tirtabot-icon-minimize" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="4 14 10 14 10 20"/>
            <polyline points="20 10 14 10 14 4"/>
            <line x1="14" x2="21" y1="10" y2="3"/>
            <line x1="3" x2="10" y1="21" y2="14"/>
          </svg>
          <svg id="tirtabot-icon-maximize" class="w-4 h-4 hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="15 3 21 3 21 9"/>
            <polyline points="9 21 3 21 3 15"/>
            <line x1="21" x2="14" y1="3" y2="10"/>
            <line x1="3" x2="10" y1="21" y2="14"/>
          </svg>
        </button>
        <button
          type="button"
          onclick="closeTirtaBot()"
          title="Tutup chat"
          class="p-1.5 rounded-lg hover:bg-white/10 hover:text-white transition-colors cursor-pointer"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 6 6 18"/>
            <path d="m6 6 12 12"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- Minimized Bar only -->
    <div
      id="tirtabot-minimized-bar"
      onclick="toggleMinimizeTirtaBot()"
      class="hidden p-3 bg-slate-50 text-slate-600 text-xs font-medium cursor-pointer hover:bg-slate-100 flex items-center justify-between"
    >
      <span>Klik untuk melanjutkan chat...</span>
      <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
      </svg>
    </div>

    <!-- Full Chat Body -->
    <div id="tirtabot-body-wrap" class="flex-1 flex flex-col min-h-0">
      <!-- Chat Messages Body -->
      <div id="tirtabot-messages" class="flex-1 p-4 overflow-y-auto space-y-3.5 bg-slate-50/60">
        <!-- Subtle info notice -->
        <div class="p-2.5 rounded-xl bg-cyan-50/70 border border-cyan-100/80 text-[11px] text-cyan-900 flex items-start gap-2">
          <svg class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          </svg>
          <p class="leading-snug">
            Tanyakan apa saja seputar tips hemat air, deteksi kebocoran pipa, estimasi tarif Tirta Sago, atau tiket layanan!
          </p>
        </div>
        <!-- Dynamic messages injected via JS -->
      </div>

      <!-- Quick Prompts Carousel -->
      <div class="px-3 py-2 bg-white border-t border-slate-100 overflow-x-auto no-scrollbar flex items-center gap-1.5 shrink-0">
        <?php foreach ($QUICK_PROMPTS as $prompt): ?>
          <button
            type="button"
            onclick="sendTirtaBotMessage('<?= e($prompt) ?>')"
            class="tirtabot-quick-btn whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-medium bg-slate-100 hover:bg-cyan-50 hover:text-cyan-800 text-slate-600 border border-slate-200 transition-colors shrink-0 disabled:opacity-50 cursor-pointer"
          >
            <?= e($prompt) ?>
          </button>
        <?php endforeach; ?>
      </div>

      <!-- Input Form -->
      <form
        id="tirtabot-form"
        onsubmit="handleTirtaBotSubmit(event)"
        class="p-3 bg-white border-t border-slate-200 flex items-center gap-2 shrink-0"
      >
        <input
          id="tirtabot-input"
          type="text"
          placeholder="Tulis pertanyaan seputar air di Payakumbuh..."
          class="flex-1 px-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-hidden focus:ring-2 focus:ring-cyan-500/20 focus:border-cyan-500 transition-all text-slate-800 placeholder:text-slate-400"
        />
        <button
          id="tirtabot-send-btn"
          type="submit"
          class="w-10 h-10 rounded-xl bg-cyan-600 hover:bg-cyan-500 disabled:bg-slate-200 text-white disabled:text-slate-400 flex items-center justify-center transition-all shrink-0 shadow-sm cursor-pointer"
          aria-label="Kirim Pesan"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M14.536 21.686a.5.5 0 0 0 .937-.024l6.5-19a.496.496 0 0 0-.635-.635l-19 6.5a.5.5 0 0 0-.024.937l7.93 3.18a2 2 0 0 1 1.112 1.11z"/>
            <path d="m21.854 2.147-10.94 10.939"/>
          </svg>
        </button>
      </form>
    </div>

  </div>
</div>

<script>
(function() {
  if (window.tirtabotInitialized) return;
  window.tirtabotInitialized = true;

  const chatWindow = document.getElementById('tirtabot-window');
  const triggerWrap = document.getElementById('tirtabot-trigger-wrap');
  const messagesBox = document.getElementById('tirtabot-messages');
  const chatInput = document.getElementById('tirtabot-input');
  const sendBtn = document.getElementById('tirtabot-send-btn');
  const minimizedBar = document.getElementById('tirtabot-minimized-bar');
  const bodyWrap = document.getElementById('tirtabot-body-wrap');
  const iconMin = document.getElementById('tirtabot-icon-minimize');
  const iconMax = document.getElementById('tirtabot-icon-maximize');
  const chatApiUrl = "<?= url('/api/chat.php') ?>";

  let chatHistory = [];
  let isLoading = false;

  window.openTirtaBot = function() {
    if (triggerWrap) triggerWrap.classList.add('hidden');
    if (chatWindow) chatWindow.classList.remove('hidden');
    if (chatInput) setTimeout(() => chatInput.focus(), 150);
  };

  window.closeTirtaBot = function() {
    if (chatWindow) chatWindow.classList.add('hidden');
    if (triggerWrap) triggerWrap.classList.remove('hidden');
  };

  window.toggleMinimizeTirtaBot = function() {
    if (!chatWindow) return;
    const isMin = minimizedBar && !minimizedBar.classList.contains('hidden');
    if (isMin) {
      if (minimizedBar) minimizedBar.classList.add('hidden');
      if (bodyWrap) bodyWrap.classList.remove('hidden');
      if (iconMin) iconMin.classList.remove('hidden');
      if (iconMax) iconMax.classList.add('hidden');
      chatWindow.classList.remove('h-auto');
      chatWindow.classList.add('h-[580px]');
    } else {
      if (minimizedBar) minimizedBar.classList.remove('hidden');
      if (bodyWrap) bodyWrap.classList.add('hidden');
      if (iconMin) iconMin.classList.add('hidden');
      if (iconMax) iconMax.classList.remove('hidden');
      chatWindow.classList.remove('h-[580px]');
      chatWindow.classList.add('h-auto');
    }
  };

  window.clearTirtaBotHistory = function() {
    if (messagesBox) {
      const infoNotice = messagesBox.querySelector('.p-2.5');
      messagesBox.innerHTML = '';
      if (infoNotice) messagesBox.appendChild(infoNotice);
    }
    chatHistory = [];
  };

  window.sendTirtaBotMessage = async function(overrideText) {
    const text = (overrideText || (chatInput ? chatInput.value : '')).trim();
    if (!text || isLoading) return;

    if (!overrideText && chatInput) chatInput.value = '';

    appendBubble('user', text);
    isLoading = true;
    if (sendBtn) sendBtn.disabled = true;

    const loadingId = 'loading-' + Date.now();
    appendLoadingBubble(loadingId);

    try {
      const res = await fetch(chatApiUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history: chatHistory.slice(-6) })
      });
      const data = await res.json();
      removeBubble(loadingId);
      const reply = data.reply || 'Halo Sanak! TirtaBot siap membantu kebutuhan informasi air PDAM Kota Payakumbuh.';
      appendBubble('bot', reply);
      chatHistory.push({ role: 'user', content: text }, { role: 'model', content: reply });
    } catch (err) {
      removeBubble(loadingId);
      appendBubble('bot', 'Halo Sanak, untuk informasi mendesak terkait kebocoran pipa atau gangguan air, Anda dapat langsung menghubungi Call Center Tirta Sago di 0752-92044 atau WhatsApp 0812-7389-4902.');
    } finally {
      isLoading = false;
      if (sendBtn) sendBtn.disabled = false;
    }
  };

  window.handleTirtaBotSubmit = function(e) {
    e.preventDefault();
    window.sendTirtaBotMessage();
  };

  function appendBubble(role, text) {
    if (!messagesBox) return;
    const isUser = role === 'user';
    const div = document.createElement('div');
    div.className = `flex gap-2.5 ${isUser ? 'justify-end' : 'justify-start'} animate-fade-zoom`;
    
    const formattedText = text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\n\n/g, '<br><br>')
      .replace(/\n/g, '<br>');

    div.innerHTML = `
      ${!isUser ? `
        <div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
          🤖
        </div>
      ` : ''}
      <div class="max-w-[82%] px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed ${isUser ? 'bg-cyan-600 text-white rounded-tr-xs' : 'bg-white border border-slate-200 text-slate-800 rounded-tl-xs shadow-2xs'}">
        ${formattedText}
        <span class="block text-[10px] ${isUser ? 'text-cyan-200' : 'text-slate-400'} pt-1 text-right">
          ${new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })}
        </span>
      </div>
    `;
    messagesBox.appendChild(div);
    messagesBox.scrollTop = messagesBox.scrollHeight;
  }

  function appendLoadingBubble(id) {
    if (!messagesBox) return;
    const div = document.createElement('div');
    div.id = id;
    div.className = 'flex gap-2.5 justify-start animate-pulse';
    div.innerHTML = `
      <div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center text-xs shrink-0 mt-0.5">
        🤖
      </div>
      <div class="px-3.5 py-2 rounded-2xl bg-white border border-slate-200 text-slate-400 text-xs flex items-center gap-1.5 shadow-2xs">
        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-ping"></span>
        <span>TirtaBot sedang mengetik...</span>
      </div>
    `;
    messagesBox.appendChild(div);
    messagesBox.scrollTop = messagesBox.scrollHeight;
  }

  function removeBubble(id) {
    const el = document.getElementById(id);
    if (el) el.remove();
  }
})();
</script>
