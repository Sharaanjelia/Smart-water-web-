/**
 * JavaScript Halaman Beranda - Smart Water Kota Payakumbuh (Versi PHP)
 */

(function () {
  // =========================================================
  // 1. HERO DYNAMIC ROTATING BACKGROUNDS (6s interval + pause)
  // =========================================================
  let currentBgIndex = 0;
  let isPaused = false;
  const heroSection = document.getElementById('hero-section');
  const slides = document.querySelectorAll('[data-hero-slide]');

  function updateHeroSlide(nextIndex) {
    if (!slides.length) return;
    currentBgIndex = nextIndex % slides.length;
    slides.forEach((slide, idx) => {
      const img = slide.querySelector('.hero-slide-img');
      if (idx === currentBgIndex) {
        slide.classList.add('opacity-100', 'z-10');
        slide.classList.remove('opacity-0', 'z-0');
        if (img) {
          img.classList.add('scale-105');
          img.classList.remove('scale-100');
        }
      } else {
        slide.classList.remove('opacity-100', 'z-10');
        slide.classList.add('opacity-0', 'z-0');
        if (img) {
          img.classList.remove('scale-105');
          img.classList.add('scale-100');
        }
      }
    });
  }

  if (heroSection && slides.length > 1) {
    heroSection.addEventListener('mouseenter', () => {
      isPaused = true;
    });
    heroSection.addEventListener('mouseleave', () => {
      isPaused = false;
    });

    setInterval(() => {
      if (!isPaused) {
        updateHeroSlide(currentBgIndex + 1);
      }
    }, 6000);
  }

  // =========================================================
  // 2. MOBILE HEADER DRAWER
  // =========================================================
  const mobileDrawer = document.getElementById('mobileMenuDrawer');
  const iconOpen = document.getElementById('mobileMenuIconOpen');
  const iconClose = document.getElementById('mobileMenuIconClose');
  const toggleBtn = document.getElementById('mobileMenuToggleBtn');

  window.toggleMobileMenu = function () {
    if (!mobileDrawer) return;
    const isHidden = mobileDrawer.classList.contains('hidden');
    if (isHidden) {
      mobileDrawer.classList.remove('hidden');
      if (iconOpen) iconOpen.classList.add('hidden');
      if (iconClose) iconClose.classList.remove('hidden');
      if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'true');
    } else {
      window.closeMobileMenu();
    }
  };

  window.closeMobileMenu = function () {
    if (!mobileDrawer) return;
    mobileDrawer.classList.add('hidden');
    if (iconOpen) iconOpen.classList.remove('hidden');
    if (iconClose) iconClose.classList.add('hidden');
    if (toggleBtn) toggleBtn.setAttribute('aria-expanded', 'false');
  };

  // =========================================================
  // 3. NAVIGASI LANGSUNG HALAMAN (DIRECT ROUTING)
  // =========================================================
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

  window.handleBerandaNav = function (tab) {
    if (tab === 'beranda') {
      window.scrollTo({ top: 0, behavior: 'smooth' });
      return;
    }
    const target = ROUTE_MAP[tab] || '/' + tab;
    window.location.href = getAppBaseUrl(target);
  };

  window.openAuthNoticeModal = function () {
    window.location.href = getAppBaseUrl('/login');
  };

  window.closeNavModal = function () {
    const modal = document.getElementById('beranda-nav-modal');
    if (modal) modal.classList.add('hidden');
  };

  // =========================================================
  // 4. TIRTABOT AI CHATBOT WIDGET LOGIC
  // =========================================================
  const STORAGE_KEY = 'swc_tirtabot_chat_v1';
  const INITIAL_MESSAGES = [
    {
      id: 'welcome-1',
      role: 'model',
      content:
        'Halo Sanak Warga Payakumbuh! 👋 \n\nSaya **TirtaBot AI**, asisten cerdas Smart Water Kota Payakumbuh yang ditenagai oleh kecerdasan buatan.\n\nAda yang bisa saya bantu seputar penghematan air, cek pipa bocor, tarif kubikasi Tirta Sago, atau cara lapor pengaduan hari ini?',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
      source: 'gemini',
    },
  ];

  let chatMessages = [];
  let isChatOpen = false;
  let isChatMinimized = false;
  let isChatLoading = false;
  let unreadCount = 0;

  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    chatMessages = saved ? JSON.parse(saved) : [...INITIAL_MESSAGES];
  } catch (e) {
    chatMessages = [...INITIAL_MESSAGES];
  }

  const triggerWrap = document.getElementById('tirtabot-trigger-wrap');
  const chatWindow = document.getElementById('tirtabot-window');
  const minimizedBar = document.getElementById('tirtabot-minimized-bar');
  const bodyWrap = document.getElementById('tirtabot-body-wrap');
  const messagesBox = document.getElementById('tirtabot-messages');
  const chatInput = document.getElementById('tirtabot-input');
  const sendBtn = document.getElementById('tirtabot-send-btn');
  const iconMin = document.getElementById('tirtabot-icon-minimize');
  const iconMax = document.getElementById('tirtabot-icon-maximize');
  const unreadBadge = document.getElementById('tirtabot-unread-badge');

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
  }

  function parseBold(text) {
    const escaped = escapeHtml(text);
    return escaped.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold text-slate-900">$1</strong>');
  }

  function formatBotContent(content) {
    const lines = String(content).split('\n');
    return lines
      .map((line) => {
        if (!line.trim()) return '<div class="h-2"></div>';
        if (line.startsWith('### ')) {
          return `<h4 class="font-bold text-slate-900 mt-2 mb-1 text-xs">${escapeHtml(line.replace('### ', ''))}</h4>`;
        }
        if (line.startsWith('## ')) {
          return `<h3 class="font-extrabold text-slate-900 mt-2 mb-1 text-sm">${escapeHtml(line.replace('## ', ''))}</h3>`;
        }
        if (line.trim().startsWith('- ') || line.trim().startsWith('* ')) {
          const itemText = line.trim().replace(/^[-*]\s+/, '');
          return `<div class="flex items-start gap-1.5 ml-1 my-0.5"><span class="text-cyan-600 font-bold text-xs mt-0.5">•</span><span class="text-slate-700 leading-relaxed text-xs">${parseBold(itemText)}</span></div>`;
        }
        const numMatch = line.trim().match(/^(\d+)\.\s+/);
        if (numMatch) {
          const itemText = line.trim().replace(/^\d+\.\s+/, '');
          return `<div class="flex items-start gap-1.5 ml-1 my-0.5"><span class="text-cyan-700 font-bold text-[11px] min-w-[14px]">${numMatch[1]}.</span><span class="text-slate-700 leading-relaxed text-xs">${parseBold(itemText)}</span></div>`;
        }
        return `<p class="text-slate-700 leading-relaxed text-xs my-0.5">${parseBold(line)}</p>`;
      })
      .join('');
  }

  function renderChatMessages() {
    if (!messagesBox) return;

    // Preserve first info notice element
    const noticeHtml = `
      <div class="p-2.5 rounded-xl bg-cyan-50/70 border border-cyan-100/80 text-[11px] text-cyan-900 flex items-start gap-2">
        <svg class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
        </svg>
        <p class="leading-snug">
          Tanyakan apa saja seputar tips hemat air, deteksi kebocoran pipa, estimasi tarif Tirta Sago, atau tiket layanan!
        </p>
      </div>`;

    const msgsHtml = chatMessages
      .map((msg) => {
        const isUser = msg.role === 'user';
        return `
        <div class="flex gap-2.5 ${isUser ? 'justify-end' : 'justify-start'}">
          ${
            !isUser
              ? `<div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center shrink-0 mt-1 shadow-xs">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/><path d="M2 14h2"/><path d="M20 14h2"/><path d="M15 13v2"/><path d="M9 13v2"/>
                  </svg>
                </div>`
              : ''
          }
          <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 shadow-xs text-xs ${
            isUser
              ? 'bg-slate-900 text-white rounded-br-xs'
              : 'bg-white text-slate-800 border border-slate-200/90 rounded-bl-xs'
          }">
            ${
              isUser
                ? `<p class="leading-relaxed whitespace-pre-wrap">${escapeHtml(msg.content)}</p>`
                : `<div class="space-y-1">${formatBotContent(msg.content)}</div>`
            }
            <div class="text-[9px] mt-1 flex items-center gap-1.5 text-slate-400 ${
              isUser ? 'justify-end' : 'justify-start'
            }">
              <span>${escapeHtml(msg.timestamp || '')}</span>
              ${
                !isUser && msg.source === 'gemini'
                  ? '<span class="font-mono text-[8px] px-1 rounded bg-slate-100 text-slate-500">AI</span>'
                  : ''
              }
            </div>
          </div>
        </div>`;
      })
      .join('');

    const loadingHtml = isChatLoading
      ? `<div class="flex items-center gap-2 text-slate-500 text-xs">
          <div class="w-7 h-7 rounded-lg bg-cyan-600 text-white flex items-center justify-center shrink-0 shadow-xs">
            <svg class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 8V4H8"/><rect width="16" height="12" x="4" y="8" rx="2"/>
            </svg>
          </div>
          <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-bl-xs border border-slate-200 flex items-center gap-1.5 shadow-xs">
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce [animation-delay:0.2s]"></span>
            <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce [animation-delay:0.4s]"></span>
            <span class="text-[11px] text-slate-400 ml-1">TirtaBot sedang berpikir...</span>
          </div>
        </div>`
      : '';

    messagesBox.innerHTML = noticeHtml + msgsHtml + loadingHtml;
    messagesBox.scrollTop = messagesBox.scrollHeight;
  }

  function saveChatMessages() {
    try {
      localStorage.setItem(STORAGE_KEY, JSON.stringify(chatMessages));
    } catch (e) {
      // Ignore storage errors
    }
  }

  function updateUnreadBadge() {
    if (!unreadBadge) return;
    if (unreadCount > 0) {
      unreadBadge.textContent = String(unreadCount);
      unreadBadge.classList.remove('hidden');
    } else {
      unreadBadge.classList.add('hidden');
    }
  }

  window.openTirtaBot = function () {
    isChatOpen = true;
    isChatMinimized = false;
    unreadCount = 0;
    updateUnreadBadge();
    if (triggerWrap) triggerWrap.classList.add('hidden');
    if (chatWindow) {
      chatWindow.classList.remove('hidden', 'w-72');
      chatWindow.classList.add(
        'bottom-4',
        'right-4',
        'sm:bottom-6',
        'sm:right-6',
        'w-[calc(100vw-2rem)]',
        'sm:w-[420px]',
        'max-w-[440px]',
        'h-[580px]',
        'max-h-[85vh]'
      );
    }
    if (minimizedBar) minimizedBar.classList.add('hidden');
    if (bodyWrap) bodyWrap.classList.remove('hidden');
    if (iconMin) iconMin.classList.remove('hidden');
    if (iconMax) iconMax.classList.add('hidden');
    renderChatMessages();
    setTimeout(() => chatInput && chatInput.focus(), 180);
  };

  window.closeTirtaBot = function () {
    isChatOpen = false;
    if (chatWindow) chatWindow.classList.add('hidden');
    if (triggerWrap) triggerWrap.classList.remove('hidden');
  };

  window.toggleMinimizeTirtaBot = function () {
    isChatMinimized = !isChatMinimized;
    if (!chatWindow) return;

    if (isChatMinimized) {
      chatWindow.classList.remove(
        'bottom-4',
        'right-4',
        'sm:bottom-6',
        'sm:right-6',
        'w-[calc(100vw-2rem)]',
        'sm:w-[420px]',
        'max-w-[440px]',
        'h-[580px]',
        'max-h-[85vh]'
      );
      chatWindow.classList.add('bottom-6', 'right-6', 'w-72');
      if (minimizedBar) minimizedBar.classList.remove('hidden');
      if (bodyWrap) bodyWrap.classList.add('hidden');
      if (iconMin) iconMin.classList.add('hidden');
      if (iconMax) iconMax.classList.remove('hidden');
    } else {
      unreadCount = 0;
      updateUnreadBadge();
      chatWindow.classList.remove('w-72');
      chatWindow.classList.add(
        'bottom-4',
        'right-4',
        'sm:bottom-6',
        'sm:right-6',
        'w-[calc(100vw-2rem)]',
        'sm:w-[420px]',
        'max-w-[440px]',
        'h-[580px]',
        'max-h-[85vh]'
      );
      if (minimizedBar) minimizedBar.classList.add('hidden');
      if (bodyWrap) bodyWrap.classList.remove('hidden');
      if (iconMin) iconMin.classList.remove('hidden');
      if (iconMax) iconMax.classList.add('hidden');
      renderChatMessages();
    }
  };

  window.clearTirtaBotHistory = function () {
    chatMessages = [...INITIAL_MESSAGES];
    localStorage.removeItem(STORAGE_KEY);
    renderChatMessages();
  };

  window.sendTirtaBotMessage = async function (textOverride) {
    const rawText = textOverride !== undefined ? textOverride : chatInput ? chatInput.value : '';
    const text = String(rawText).trim();
    if (!text || isChatLoading) return;

    const userMessage = {
      id: 'user-' + Date.now(),
      role: 'user',
      content: text,
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    };

    chatMessages.push(userMessage);
    if (chatInput && textOverride === undefined) chatInput.value = '';
    isChatLoading = true;
    if (sendBtn) sendBtn.disabled = true;
    saveChatMessages();
    renderChatMessages();

    try {
      const history = chatMessages.slice(-6).map((m) => ({
        role: m.role,
        content: m.content,
      }));

      const res = await fetch(getAppBaseUrl('/api/chat.php'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message: text, history }),
      });

      if (!res.ok) throw new Error('HTTP ' + res.status);
      const data = await res.json();

      chatMessages.push({
        id: 'bot-' + Date.now(),
        role: 'model',
        content:
          data.reply ||
          'Maaf, saya sedang tidak dapat merespons saat ini. Silakan coba kembali sesaat lagi.',
        timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        source: data.source || 'gemini',
      });
    } catch (err) {
      chatMessages.push({
        id: 'bot-err-' + Date.now(),
        role: 'model',
        content:
          'Halo Sanak, maaf terjadi gangguan koneksi sementara ke server PHP AI. Mohon pastikan dijalankan via PHP server (XAMPP/Laragon/php -S).',
        timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
        source: 'fallback',
      });
    } finally {
      isChatLoading = false;
      if (sendBtn) sendBtn.disabled = false;
      if (!isChatOpen || isChatMinimized) {
        unreadCount += 1;
        updateUnreadBadge();
      }
      saveChatMessages();
      renderChatMessages();
    }
  };

  window.handleTirtaBotSubmit = function (e) {
    e.preventDefault();
    window.sendTirtaBotMessage();
  };

  // =========================================================
  // 5. PARAGON-STYLE INTERACTIVE WATER USAGE CALCULATOR
  // =========================================================
  window.calculateWaterUsage = function () {
    const peopleInput = document.getElementById('calcPeople');
    const habitInput = document.getElementById('calcHabit');
    if (!peopleInput || !habitInput) return;

    const people = parseInt(peopleInput.value, 10) || 4;
    const habit = parseFloat(habitInput.value) || 1.0;

    const basePerPerson = 4.2;
    const usage = Math.round((people * basePerPerson * habit) * 10) / 10;

    let cost = 0;
    if (usage <= 10) {
      cost = usage * 1850;
    } else if (usage <= 20) {
      cost = (10 * 1850) + ((usage - 10) * 2450);
    } else {
      cost = (10 * 1850) + (10 * 2450) + ((usage - 20) * 3200);
    }
    cost += 12000;

    const savedM3 = Math.round((usage * 0.28) * 10) / 10;
    const savedRupiah = Math.round(savedM3 * 2450);

    const peopleValEl = document.getElementById('calcPeopleVal');
    const usageEl = document.getElementById('calcUsageResult');
    const billEl = document.getElementById('calcBillResult');
    const savedEl = document.getElementById('calcSavedResult');
    const tipEl = document.getElementById('calcTipResult');

    if (peopleValEl) peopleValEl.textContent = people + ' Jiwa';
    if (usageEl) usageEl.textContent = usage.toLocaleString('id-ID') + ' m³';
    if (billEl) billEl.textContent = 'Rp ' + Math.round(cost).toLocaleString('id-ID');
    if (savedEl) savedEl.textContent = 'Hemat Rp ' + savedRupiah.toLocaleString('id-ID') + '/bln';

    if (tipEl) {
      if (habit > 1.1) {
        tipEl.textContent = 'Tip Cerdas: Pemakaian air Anda tergolong tinggi. Mengurangi durasi mandi 3 menit dan menutup keran saat menyabun dapat memangkas tagihan hingga Rp ' + savedRupiah.toLocaleString('id-ID') + '/bulan!';
      } else if (habit < 0.9) {
        tipEl.textContent = 'Luar Biasa! Pola penggunaan air keluarga Anda sudah sangat efisien dan berkontribusi langsung pada kelestarian Mata Air Sikabu.';
      } else {
        tipEl.textContent = 'Tip Cerdas: Pasang aerator pada keran wastafel dan rutin lakukan uji kebocoran statis tengah malam sebulan sekali.';
      }
    }
  };

  window.askTirtaBotQuick = function (question) {
    if (window.openTirtaBot) {
      window.openTirtaBot();
    } else {
      const trigger = document.getElementById('tirtabot-trigger-btn');
      if (trigger) trigger.click();
    }
    setTimeout(() => {
      const input = document.getElementById('tirtabot-input');
      if (input) {
        input.value = question;
        const send = document.getElementById('tirtabot-send-btn');
        if (send) send.click();
      }
    }, 250);
  };

  document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('calcPeople')) {
      window.calculateWaterUsage();
    }
  });

  renderChatMessages();
})();
