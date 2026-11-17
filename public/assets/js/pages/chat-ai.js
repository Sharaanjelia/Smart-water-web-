/**
 * Interactive Scripts untuk Halaman Chat AI TirtaBot PHP Standalone
 * Smart Water Kota Payakumbuh (Sesuai persis AiChatbot.tsx + Embedded Chat Workspace)
 */

const CHAT_STORAGE_KEY = 'swc_tirtabot_chat_v1_php';
const INITIAL_WELCOME = window.SWC_INITIAL_WELCOME || 'Halo Sanak Warga Payakumbuh! Saya TirtaBot AI.';

let chatMessages = [];
let isChatOpen = false;
let isChatMinimized = false;
let isChatLoading = false;
let unreadCount = 0;

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ---------------------------------------------------------------------------
// 1. HEADER MOBILE MENU & NAVIGATION MODAL
// ---------------------------------------------------------------------------
function toggleMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const openIcon = document.getElementById('mobileMenuIconOpen');
  const closeIcon = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  if (!drawer) return;
  const isHidden = drawer.classList.contains('hidden');
  if (isHidden) {
    drawer.classList.remove('hidden');
    openIcon?.classList.add('hidden');
    closeIcon?.classList.remove('hidden');
    btn?.setAttribute('aria-expanded', 'true');
  } else {
    closeMobileMenu();
  }
}

function closeMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const openIcon = document.getElementById('mobileMenuIconOpen');
  const closeIcon = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  drawer?.classList.add('hidden');
  openIcon?.classList.remove('hidden');
  closeIcon?.classList.add('hidden');
  btn?.setAttribute('aria-expanded', 'false');
}

const ROUTE_MAP = {
  beranda: '/beranda',
  layanan: '/pusat-layanan',
  'pusat-layanan': '/pusat-layanan',
  akademi: '/akademi-air',
  'akademi-air': '/akademi-air',
  tantangan: '/tantangan',
  tentang: '/tentang',
  profil: '/user',
  user: '/user',
  admin: '/admin',
  auth: '/login',
  login: '/login',
};

function getAppBaseUrl(path) {
  const pathname = window.location.pathname;
  if (pathname.includes('/smart-water')) {
    return '/smart-water' + path;
  }
  return path;
}

function handleChatNav(tab) {
  const target = ROUTE_MAP[tab] || '/' + tab;
  window.location.href = getAppBaseUrl(target);
}

function openAuthNoticeModal() {
  window.location.href = getAppBaseUrl('/login');
}

function openInfoModal(title, desc, href) {
  const modal = document.getElementById('chat-nav-modal');
  if (modal) modal.classList.add('hidden');
}

function closeNavModal() {
  document.getElementById('chat-nav-modal')?.classList.add('hidden');
}

// ---------------------------------------------------------------------------
// 2. MARKDOWN PARSER (1:1 WITH AiChatbot.tsx formatBotContent + parseBold)
// ---------------------------------------------------------------------------
function parseInlineStyles(text) {
  let safe = escapeHtml(text);
  safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>');
  safe = safe.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 rounded bg-slate-100 text-cyan-800 font-mono text-[10px]">$1</code>');
  return safe;
}

function formatBotContentHtml(content) {
  const lines = String(content || '').split('\n');
  return lines.map((line) => {
    const trimmed = line.trim();
    if (!trimmed) {
      return '<div class="h-2"></div>';
    }
    if (line.startsWith('### ')) {
      return `<h4 class="font-bold text-slate-900 mt-2 mb-1 text-xs">${escapeHtml(line.replace('### ', ''))}</h4>`;
    }
    if (line.startsWith('## ')) {
      return `<h3 class="font-extrabold text-slate-900 mt-2 mb-1 text-sm">${escapeHtml(line.replace('## ', ''))}</h3>`;
    }
    if (trimmed.startsWith('- ') || trimmed.startsWith('* ')) {
      const itemText = trimmed.replace(/^[-*]\s+/, '');
      return `
        <div class="flex items-start gap-1.5 ml-1 my-0.5">
          <span class="text-cyan-600 font-bold text-xs mt-0.5">&bull;</span>
          <span class="text-slate-700 leading-relaxed text-xs">${parseInlineStyles(itemText)}</span>
        </div>
      `;
    }
    const numMatch = trimmed.match(/^(\d+)\.\s+/);
    if (numMatch) {
      const number = numMatch[1];
      const itemText = trimmed.replace(/^\d+\.\s+/, '');
      return `
        <div class="flex items-start gap-1.5 ml-1 my-0.5">
          <span class="text-cyan-700 font-bold text-[11px] min-w-[14px]">${number}.</span>
          <span class="text-slate-700 leading-relaxed text-xs">${parseInlineStyles(itemText)}</span>
        </div>
      `;
    }
    return `<p class="text-slate-700 leading-relaxed text-xs my-0.5">${parseInlineStyles(line)}</p>`;
  }).join('');
}

// ---------------------------------------------------------------------------
// 3. STATE & RENDERER (SYNCED BETWEEN MAIN WORKSPACE & FLOATING WIDGET)
// ---------------------------------------------------------------------------
function getDefaultGreeting() {
  return {
    id: 'welcome-1',
    role: 'model',
    content: INITIAL_WELCOME,
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    source: 'gemini',
  };
}

function loadChatHistory() {
  try {
    const saved = localStorage.getItem(CHAT_STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        chatMessages = parsed;
        return;
      }
    }
  } catch (e) {}
  chatMessages = [getDefaultGreeting()];
}

function saveChatHistory() {
  try {
    localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(chatMessages));
  } catch (e) {}
}

function renderMessageListInto(container) {
  if (!container) return;
  container.querySelectorAll('.tirtabot-msg-row, .tirtabot-loading-row').forEach((n) => n.remove());

  chatMessages.forEach((msg) => {
    const isUser = msg.role === 'user';
    const row = document.createElement('div');
    row.className = `tirtabot-msg-row flex gap-2.5 ${isUser ? 'justify-end' : 'justify-start'}`;

    const avatarHtml = !isUser
      ? `<div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center shrink-0 mt-1 shadow-xs">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
        </div>`
      : '';

    const bubbleClass = isUser
      ? 'bg-slate-900 text-white rounded-br-xs'
      : 'bg-white text-slate-800 border border-slate-200/90 rounded-bl-xs';

    const innerHtml = isUser
      ? `<p class="leading-relaxed whitespace-pre-wrap">${escapeHtml(msg.content)}</p>`
      : `<div class="space-y-1">${formatBotContentHtml(msg.content)}</div>`;

    const aiBadgeHtml = !isUser && msg.source === 'gemini'
      ? `<span class="font-mono text-[8px] px-1 rounded bg-slate-100 text-slate-500">AI</span>`
      : '';

    row.innerHTML = `
      ${avatarHtml}
      <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 shadow-xs text-xs ${bubbleClass}">
        ${innerHtml}
        <div class="text-[9px] mt-1 flex items-center gap-1.5 text-slate-400 ${isUser ? 'justify-end' : 'justify-start'}">
          <span>${escapeHtml(msg.timestamp || '')}</span>
          ${aiBadgeHtml}
        </div>
      </div>
    `;
    container.appendChild(row);
  });

  if (isChatLoading) {
    const loadingRow = document.createElement('div');
    loadingRow.className = 'tirtabot-loading-row flex items-center gap-2 text-slate-500 text-xs';
    loadingRow.innerHTML = `
      <div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center shrink-0 shadow-xs">
        <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/></svg>
      </div>
      <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-bl-xs border border-slate-200 flex items-center gap-1.5 shadow-xs">
        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:200ms"></span>
        <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:400ms"></span>
        <span class="text-[11px] text-slate-400 ml-1">TirtaBot sedang berpikir...</span>
      </div>
    `;
    container.appendChild(loadingRow);
  }

  container.scrollTop = container.scrollHeight;
}

function renderAllChats() {
  renderMessageListInto(document.getElementById('main-tirtabot-messages'));
  renderMessageListInto(document.getElementById('tirtabot-messages'));
  updateUnreadBadge();
}

function updateUnreadBadge() {
  const badge = document.getElementById('tirtabot-unread-badge');
  if (!badge) return;
  if (unreadCount > 0 && (!isChatOpen || isChatMinimized)) {
    badge.textContent = String(unreadCount);
    badge.classList.remove('hidden');
  } else {
    badge.classList.add('hidden');
  }
}

// Floating window controls (match includes/chatbot.php IDs)
function openTirtaBot() {
  isChatOpen = true;
  isChatMinimized = false;
  unreadCount = 0;
  document.getElementById('tirtabot-trigger-wrap')?.classList.add('hidden');
  document.getElementById('tirtabot-window')?.classList.remove('hidden');
  updateMinimizedState();
  renderAllChats();
  setTimeout(() => document.getElementById('tirtabot-input')?.focus(), 150);
}

function closeTirtaBot() {
  isChatOpen = false;
  document.getElementById('tirtabot-window')?.classList.add('hidden');
  document.getElementById('tirtabot-trigger-wrap')?.classList.remove('hidden');
  updateUnreadBadge();
}

function toggleMinimizeTirtaBot() {
  isChatMinimized = !isChatMinimized;
  if (!isChatMinimized) unreadCount = 0;
  updateMinimizedState();
  updateUnreadBadge();
}

function updateMinimizedState() {
  const win = document.getElementById('tirtabot-window');
  const minBar = document.getElementById('tirtabot-minimized-bar');
  const bodyWrap = document.getElementById('tirtabot-body-wrap');
  const iconMin = document.getElementById('tirtabot-icon-minimize');
  const iconMax = document.getElementById('tirtabot-icon-maximize');
  if (!win) return;

  if (isChatMinimized) {
    win.classList.remove('h-[580px]', 'max-h-[85vh]');
    win.classList.add('h-14');
    minBar?.classList.remove('hidden');
    bodyWrap?.classList.add('hidden');
    iconMin?.classList.add('hidden');
    iconMax?.classList.remove('hidden');
  } else {
    win.classList.remove('h-14');
    win.classList.add('h-[580px]', 'max-h-[85vh]');
    minBar?.classList.add('hidden');
    bodyWrap?.classList.remove('hidden');
    iconMin?.classList.remove('hidden');
    iconMax?.classList.add('hidden');
  }
}

function clearTirtaBotHistory() {
  chatMessages = [getDefaultGreeting()];
  localStorage.removeItem(CHAT_STORAGE_KEY);
  renderAllChats();
}

function handleMainChatSubmit(event) {
  event.preventDefault();
  const input = document.getElementById('main-tirtabot-input');
  if (!input) return;
  const text = input.value.trim();
  if (text) sendTirtaBotMessage(text);
}

function handleTirtaBotSubmit(event) {
  event.preventDefault();
  const input = document.getElementById('tirtabot-input');
  if (!input) return;
  const text = input.value.trim();
  if (text) sendTirtaBotMessage(text);
}

async function sendTirtaBotMessage(text) {
  const cleaned = String(text || '').trim();
  if (!cleaned || isChatLoading) return;

  const mainInput = document.getElementById('main-tirtabot-input');
  const floatInput = document.getElementById('tirtabot-input');
  if (mainInput) mainInput.value = '';
  if (floatInput) floatInput.value = '';

  const historySnapshot = chatMessages.slice(-6).map((m) => ({
    role: m.role,
    content: m.content,
  }));

  chatMessages.push({
    id: 'user-' + Date.now(),
    role: 'user',
    content: cleaned,
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  });

  saveChatHistory();
  isChatLoading = true;
  renderAllChats();

  try {
    const res = await fetch(getAppBaseUrl('/api/chat.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        message: cleaned,
        history: historySnapshot,
      }),
    });

    if (!res.ok) {
      throw new Error('HTTP Error ' + res.status);
    }

    const data = await res.json();
    chatMessages.push({
      id: 'bot-' + Date.now(),
      role: 'model',
      content: data.reply || 'Maaf, saya sedang tidak dapat merespons saat ini. Silakan coba kembali sesaat lagi.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
      source: data.source || 'gemini',
    });

    if (!isChatOpen || isChatMinimized) {
      unreadCount++;
    }
  } catch (error) {
    chatMessages.push({
      id: 'bot-err-' + Date.now(),
      role: 'model',
      content: 'Halo Sanak, maaf terjadi gangguan koneksi sementara ke server AI. Mohon periksa jaringan atau jalankan melalui server PHP lokal Anda (`php -S localhost:8080`).',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
      source: 'fallback',
    });
  } finally {
    isChatLoading = false;
    saveChatHistory();
    renderAllChats();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  loadChatHistory();
  renderAllChats();
});
