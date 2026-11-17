/**
 * Interactive Scripts untuk Halaman Tantangan Hemat Air PHP Standalone
 * Smart Water Kota Payakumbuh (Sesuai ChallengeView.tsx)
 */

const CHALLENGES = Array.isArray(window.SWC_CHALLENGES) ? window.SWC_CHALLENGES : [];
const BADGES = Array.isArray(window.SWC_BADGES) ? window.SWC_BADGES : [];
let challengeProgress = window.SWC_CHALLENGE_PROGRESS || {
  points: 380,
  activeChallenges: ['challenge-7days'],
  completedMissions: ['m-7d-1', 'm-7d-2'],
  badges: ['badge-sago-guardian'],
};

let selectedChallengeId = CHALLENGES[0]?.id || 'challenge-7days';
let toastTimeoutId = null;

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

function handleChallengeNav(tab) {
  if (tab === 'tantangan') {
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  const target = ROUTE_MAP[tab] || '/' + tab;
  window.location.href = getAppBaseUrl(target);
}

function openAuthNoticeModal() {
  window.location.href = getAppBaseUrl('/login');
}

function openInfoModal(title, desc, href) {
  const modal = document.getElementById('challenge-nav-modal');
  if (modal) modal.classList.add('hidden');
}

function closeNavModal() {
  document.getElementById('challenge-nav-modal')?.classList.add('hidden');
}

// ---------------------------------------------------------------------------
// 2. TOAST & CONFETTI FEEDBACK
// ---------------------------------------------------------------------------
function showToast(msg) {
  const toast = document.getElementById('challenge-toast');
  const textEl = document.getElementById('challenge-toast-text');
  if (!toast || !textEl) return;

  textEl.textContent = msg;
  toast.classList.remove('hidden');

  if (toastTimeoutId) clearTimeout(toastTimeoutId);
  toastTimeoutId = setTimeout(() => {
    toast.classList.add('hidden');
  }, 3500);
}

function fireSimpleConfetti(starsMode = false) {
  const colors = starsMode
    ? ['#f59e0b', '#fbbf24', '#06b6d4', '#10b981']
    : ['#06b6d4', '#0284c7', '#10b981', '#38bdf8'];
  const count = starsMode ? 32 : 20;
  for (let i = 0; i < count; i++) {
    const el = document.createElement('div');
    el.style.position = 'fixed';
    el.style.zIndex = '9999';
    el.style.left = (25 + Math.random() * 50) + 'vw';
    el.style.top = (25 + Math.random() * 25) + 'vh';
    el.style.width = starsMode ? '10px' : '8px';
    el.style.height = starsMode ? '10px' : '8px';
    el.style.borderRadius = starsMode ? '2px' : '50%';
    el.style.background = colors[i % colors.length];
    el.style.pointerEvents = 'none';
    el.style.transition = 'transform 900ms ease-out, opacity 900ms ease-out';
    document.body.appendChild(el);
    requestAnimationFrame(() => {
      const dx = (Math.random() - 0.5) * 260;
      const dy = (Math.random() - 0.7) * 220;
      el.style.transform = `translate(${dx}px, ${dy}px) rotate(${Math.random() * 360}deg)`;
      el.style.opacity = '0';
    });
    setTimeout(() => el.remove(), 950);
  }
}

// ---------------------------------------------------------------------------
// 3. CHALLENGE SELECTION, ENROLL & COMPLETE MISSION
// ---------------------------------------------------------------------------
function selectChallenge(challengeId) {
  selectedChallengeId = challengeId;
  renderChallengesUI();
}

async function handleEnrollChallenge() {
  const active = CHALLENGES.find((c) => c.id === selectedChallengeId) || CHALLENGES[0];
  if (!active) return;

  if ((challengeProgress.activeChallenges || []).includes(active.id)) return;

  try {
    const res = await fetch(getAppBaseUrl('/api/challenges.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'enroll', challengeId: active.id }),
    });
    const data = await res.json();
    if (data.ok && data.progress) {
      challengeProgress = data.progress;
      renderChallengesUI();
      showToast(data.message || `Berhasil bergabung ke: ${active.title}!`);
      fireSimpleConfetti(false);
    }
  } catch (e) {
    challengeProgress.activeChallenges = [...(challengeProgress.activeChallenges || []), active.id];
    renderChallengesUI();
    showToast(`Berhasil bergabung ke: ${active.title}!`);
    fireSimpleConfetti(false);
  }
}

async function handleCompleteMission(missionId, xp) {
  const active = CHALLENGES.find((c) => c.id === selectedChallengeId) || CHALLENGES[0];
  if (!active) return;

  if ((challengeProgress.completedMissions || []).includes(missionId)) {
    showToast('Misi ini telah diselesaikan sebelumnya!');
    return;
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/challenges.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'complete_mission',
        challengeId: active.id,
        missionId,
      }),
    });
    const data = await res.json();
    if (data.ok && data.progress) {
      challengeProgress = data.progress;
      renderChallengesUI();
      showToast(data.message || `Misi selesai! +${xp} XP berhasil ditambahkan ke profil.`);
      fireSimpleConfetti(Boolean(data.allDone));
      return;
    }
  } catch (e) {
    // Fallback client-side state update if opened statically
  }

  challengeProgress.completedMissions = [...(challengeProgress.completedMissions || []), missionId];
  challengeProgress.points = Number(challengeProgress.points || 0) + Number(xp || 0);
  if (!(challengeProgress.activeChallenges || []).includes(active.id)) {
    challengeProgress.activeChallenges = [...(challengeProgress.activeChallenges || []), active.id];
  }
  const allDone = active.missions.every((m) => challengeProgress.completedMissions.includes(m.id));
  if (allDone && !(challengeProgress.badges || []).includes(active.badgeRewardId)) {
    challengeProgress.badges = [...(challengeProgress.badges || []), active.badgeRewardId];
    const badge = BADGES.find((b) => b.id === active.badgeRewardId);
    showToast(`Luar biasa! Seluruh misi tuntas. Anda mendapatkan Badge: ${badge?.name || 'Baru'}!`);
    fireSimpleConfetti(true);
  } else {
    showToast(`Misi selesai! +${xp} XP berhasil ditambahkan ke profil.`);
    fireSimpleConfetti(false);
  }
  if (challengeProgress.points >= 500 && !(challengeProgress.badges || []).includes('badge-eco-champion')) {
    challengeProgress.badges.push('badge-eco-champion');
  }
  renderChallengesUI();
}

function renderChallengesUI() {
  const pointsHeader = document.getElementById('header-points-value');
  if (pointsHeader) {
    pointsHeader.textContent = String(challengeProgress.points ?? 380);
  }

  const listEl = document.getElementById('challenges-list');
  const detailEl = document.getElementById('challenge-detail-card');
  if (!listEl || !detailEl || CHALLENGES.length === 0) return;

  const active = CHALLENGES.find((c) => c.id === selectedChallengeId) || CHALLENGES[0];
  const completedMissions = challengeProgress.completedMissions || [];
  const activeChallenges = challengeProgress.activeChallenges || [];
  const userBadges = challengeProgress.badges || [];

  // Render Left Column Cards
  listEl.innerHTML = CHALLENGES.map((challenge) => {
    const isSelected = challenge.id === active.id;
    const completedCount = challenge.missions.filter((m) => completedMissions.includes(m.id)).length;
    const progressPct = Math.round((completedCount / challenge.missions.length) * 100);

    return `
      <button
        type="button"
        onclick="selectChallenge('${escapeHtml(challenge.id)}')"
        class="w-full text-left p-4 rounded-2xl border transition-all duration-150 cursor-pointer ${
          isSelected
            ? 'bg-white border-cyan-600 shadow-md ring-1 ring-cyan-600'
            : 'bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50'
        }"
      >
        <div class="flex items-center justify-between text-xs text-slate-500 mb-1">
          <span class="font-semibold text-cyan-800 text-[11px]">
            ${challenge.durationDays} Hari &middot; ${escapeHtml(challenge.difficulty)}
          </span>
          <span class="font-mono font-bold text-slate-900 bg-slate-100 px-2 py-0.5 rounded text-[11px]">
            +${challenge.totalXp} XP
          </span>
        </div>

        <h4 class="text-sm font-bold text-slate-900 leading-snug">
          ${escapeHtml(challenge.title)}
        </h4>

        <p class="text-xs text-slate-500 mt-1 line-clamp-2">
          ${escapeHtml(challenge.tagline)}
        </p>

        <div class="mt-3 pt-2 border-t border-slate-100 space-y-1">
          <div class="flex justify-between text-[10px] text-slate-500">
            <span>Progres Misi</span>
            <span class="font-semibold text-slate-700">
              ${completedCount}/${challenge.missions.length} Selesai (${progressPct}%)
            </span>
          </div>
          <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
            <div class="bg-cyan-600 h-full rounded-full transition-all" style="width: ${progressPct}%"></div>
          </div>
        </div>
      </button>
    `;
  }).join('');

  // Render Right Column Detail
  const isEnrolled = activeChallenges.includes(active.id);
  const badge = BADGES.find((b) => b.id === active.badgeRewardId);
  const isBadgeUnlocked = userBadges.includes(active.badgeRewardId);

  const enrollControl = isEnrolled
    ? `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200 shrink-0">
        <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
        <span>Sedang Berjalan</span>
      </span>`
    : `<button
        type="button"
        onclick="handleEnrollChallenge()"
        class="px-4 py-2 rounded-xl text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 transition-colors shadow-xs cursor-pointer shrink-0"
      >
        Gabung Tantangan Ini
      </button>`;

  const missionsHtml = active.missions.map((mission, index) => {
    const isCompleted = completedMissions.includes(mission.id);
    return `
      <div class="p-4 rounded-xl border transition-all flex flex-col sm:flex-row sm:items-center justify-between gap-3 ${
        isCompleted
          ? 'bg-slate-50 border-slate-200/90 text-slate-500'
          : 'bg-white border-slate-200 hover:border-cyan-300'
      }">
        <div class="flex items-start gap-3">
          <span class="w-6 h-6 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-xs font-bold text-slate-700 shrink-0 mt-0.5">
            ${index + 1}
          </span>
          <div class="space-y-1">
            <h4 class="text-xs font-bold leading-snug ${isCompleted ? 'text-slate-500 line-through' : 'text-slate-900'}">
              ${escapeHtml(mission.title)}
            </h4>
            <p class="text-xs text-slate-500 leading-relaxed">
              ${escapeHtml(mission.description)}
            </p>
            <div class="flex items-center gap-2 text-[10px] text-slate-400 pt-0.5">
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
              <span>Estimasi: ${mission.estimatedMinutes} menit</span>
              <span>&middot;</span>
              <span class="font-mono text-cyan-800 font-semibold">+${mission.xp} XP</span>
            </div>
          </div>
        </div>

        <div class="shrink-0 self-end sm:self-center">
          ${isCompleted
            ? `<span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
                <span>Selesai</span>
              </span>`
            : `<button
                type="button"
                onclick="handleCompleteMission('${escapeHtml(mission.id)}', ${mission.xp})"
                class="px-3.5 py-1.5 rounded-lg text-xs font-semibold text-white bg-cyan-600 hover:bg-cyan-700 transition-colors shadow-xs cursor-pointer"
              >
                Tandai Selesai
              </button>`
          }
        </div>
      </div>
    `;
  }).join('');

  detailEl.innerHTML = `
    <!-- Header of Active Challenge -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
      <div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <span>Periode: ${active.durationDays} Hari</span>
          <span>&middot;</span>
          <span>${active.participantsCount} Warga Bergabung</span>
        </div>
        <h2 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">
          ${escapeHtml(active.title)}
        </h2>
        <p class="text-xs text-slate-600 mt-1">
          ${escapeHtml(active.goal)}
        </p>
      </div>
      ${enrollControl}
    </div>

    <!-- Badge Reward Callout -->
    <div class="p-4 rounded-xl bg-cyan-50/70 border border-cyan-200/80 flex items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-xl flex items-center justify-center ${
          isBadgeUnlocked ? 'bg-amber-100 text-amber-700' : 'bg-slate-200 text-slate-400'
        }">
          <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/></svg>
        </div>
        <div>
          <span class="text-[10px] uppercase font-bold text-cyan-800 tracking-wider block">
            Hadiah Badge Penyelesaian
          </span>
          <strong class="text-xs text-slate-900 block">
            ${escapeHtml(badge?.name || 'Badge Komunitas')}
          </strong>
          <span class="text-[11px] text-slate-500">
            ${escapeHtml(badge?.requirement || '')}
          </span>
        </div>
      </div>

      <div class="text-right shrink-0">
        ${isBadgeUnlocked
          ? `<span class="text-xs font-bold text-emerald-700 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>
              <span>Terkoleksi</span>
            </span>`
          : `<span class="text-xs font-medium text-slate-400 flex items-center gap-1">
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
              <span>Terkunci</span>
            </span>`
        }
      </div>
    </div>

    <!-- Missions Checklist -->
    <div class="space-y-3 pt-2">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
        <h3 class="text-sm font-bold text-slate-900">
          Daftar Misi Aksi (${active.missions.length} Misi)
        </h3>
        <span class="text-xs text-slate-400">
          Klik centang setelah Anda melakukan aksi nyata di rumah
        </span>
      </div>
      <div class="space-y-3">${missionsHtml}</div>
    </div>

    <!-- Scientific & Rigorous Evaluation Note -->
    <div class="p-3.5 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-600 flex items-start gap-2.5">
      <svg class="w-4 h-4 text-cyan-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
      <p class="leading-relaxed">
        <strong>Catatan Evaluasi Penghematan:</strong> Perhitungan dampak penghematan air komunitas dihitung berdasarkan bobot misi aksi yang diselesaikan. Penghematan volume aktual pada tagihan rekening bulanan tetap diverifikasi secara obyektif melalui riwayat foto pembacaan meteran AI-OCR di aplikasi mobile.
      </p>
    </div>
  `;
}

// ---------------------------------------------------------------------------
// 4. TIRTABOT AI CHATBOT WIDGET
// ---------------------------------------------------------------------------
const CHAT_STORAGE_KEY = 'smartwater_tirtabot_history_php';
let chatMessages = [];
let isChatOpen = false;
let isChatMinimized = false;
let isChatLoading = false;

function getDefaultGreeting() {
  return {
    id: 'msg-welcome',
    role: 'model',
    content: 'Halo! Saya **TirtaBot**, asisten cerdas Komunitas Smart Water Kota Payakumbuh.\n\nAda yang bisa saya bantu seputar program Tantangan Hemat Air, poin XP, atau tips penghematan air di rumah?',
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
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

function formatBotMarkdown(text) {
  let safe = escapeHtml(text);
  safe = safe.replace(/\*\*(.*?)\*\*/g, '<strong class="font-bold">$1</strong>');
  safe = safe.replace(/\*(.*?)\*/g, '<em class="italic">$1</em>');
  safe = safe.replace(/`([^`]+)`/g, '<code class="px-1 py-0.5 rounded bg-slate-100 text-cyan-800 font-mono text-[10px]">$1</code>');
  return safe.replace(/\n/g, '<br>');
}

function renderChatMessages() {
  const container = document.getElementById('tirtabot-messages');
  if (!container) return;
  container.querySelectorAll('.tirtabot-msg-row, #tirtabot-loading-row').forEach((n) => n.remove());

  chatMessages.forEach((msg) => {
    const isUser = msg.role === 'user';
    const row = document.createElement('div');
    row.className = `tirtabot-msg-row flex flex-col max-w-[85%] ${isUser ? 'ml-auto items-end' : 'mr-auto items-start'}`;
    const bubbleClass = isUser
      ? 'bg-gradient-to-br from-cyan-600 to-sky-600 text-white rounded-tr-sm'
      : 'bg-white text-slate-700 border border-slate-200/80 rounded-tl-sm';
    const contentHtml = isUser ? escapeHtml(msg.content).replace(/\n/g, '<br>') : formatBotMarkdown(msg.content);

    row.innerHTML = `
      <div class="px-3.5 py-2.5 rounded-2xl text-xs leading-relaxed shadow-xs ${bubbleClass}">
        <div class="space-y-1 break-words">${contentHtml}</div>
      </div>
      <span class="text-[9px] text-slate-400 mt-1 px-1">${escapeHtml(msg.timestamp || '')}</span>
    `;
    container.appendChild(row);
  });

  if (isChatLoading) {
    const loadingRow = document.createElement('div');
    loadingRow.id = 'tirtabot-loading-row';
    loadingRow.className = 'flex items-center gap-2 max-w-[75%] mr-auto';
    loadingRow.innerHTML = `
      <div class="px-3.5 py-2.5 rounded-2xl rounded-tl-sm bg-white border border-slate-200 shadow-xs flex items-center gap-2 text-xs text-slate-500">
        <span class="flex gap-1">
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:0ms"></span>
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:150ms"></span>
          <span class="w-1.5 h-1.5 rounded-full bg-cyan-500 animate-bounce" style="animation-delay:300ms"></span>
        </span>
        <span class="text-[11px] font-medium">TirtaBot sedang mengetik...</span>
      </div>
    `;
    container.appendChild(loadingRow);
  }
  container.scrollTop = container.scrollHeight;
}

function openTirtaBot() {
  isChatOpen = true;
  isChatMinimized = false;
  document.getElementById('tirtabot-trigger-wrap')?.classList.add('hidden');
  document.getElementById('tirtabot-window')?.classList.remove('hidden');
  updateMinimizedState();
  renderChatMessages();
  setTimeout(() => document.getElementById('tirtabot-input')?.focus(), 150);
}

function closeTirtaBot() {
  isChatOpen = false;
  document.getElementById('tirtabot-window')?.classList.add('hidden');
  document.getElementById('tirtabot-trigger-wrap')?.classList.remove('hidden');
}

function toggleMinimizeTirtaBot() {
  isChatMinimized = !isChatMinimized;
  updateMinimizedState();
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
  saveChatHistory();
  renderChatMessages();
}

function handleTirtaBotSubmit(event) {
  event.preventDefault();
  const input = document.getElementById('tirtabot-input');
  if (!input) return;
  const text = input.value.trim();
  if (text) sendTirtaBotMessage(text);
}

async function sendTirtaBotMessage(text) {
  if (!text || isChatLoading) return;
  const input = document.getElementById('tirtabot-input');
  if (input) input.value = '';

  const historySnapshot = chatMessages.map((m) => ({ role: m.role, content: m.content }));
  chatMessages.push({
    id: 'msg-user-' + Date.now(),
    role: 'user',
    content: text,
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  });
  saveChatHistory();
  isChatLoading = true;
  renderChatMessages();

  try {
    const res = await fetch(getAppBaseUrl('/api/chat.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, history: historySnapshot }),
    });
    const data = await res.json();
    chatMessages.push({
      id: 'msg-bot-' + Date.now(),
      role: 'model',
      content: data.reply || 'Maaf, terjadi gangguan saat memproses pertanyaan Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } catch (e) {
    chatMessages.push({
      id: 'msg-err-' + Date.now(),
      role: 'model',
      content: '⚠️ Koneksi ke server PHP gagal. Jalankan folder ini melalui server PHP lokal Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } finally {
    isChatLoading = false;
    saveChatHistory();
    renderChatMessages();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderChallengesUI();
  loadChatHistory();
  renderChatMessages();
});
