/**
 * Interaktivitas Halaman Profil / User Dashboard - Smart Water Kota Payakumbuh
 * Sinkron 100% dengan src/components/profile/ProfileView.tsx + TirtaBot AI Chatbot
 */

let userState = window.__USER_PROFILE__ || null;
let userComplaints = window.__USER_COMPLAINTS__ || [];
let activeProfileTab = 'beranda';
let complaintFilter = 'semua';
let hasUploadedMeterPhoto = false;
let toastTimer = null;

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatRupiah(val) {
  return 'Rp ' + Number(val || 0).toLocaleString('id-ID');
}

function showProfileToast(msg) {
  const el = document.getElementById('profileToast');
  const txt = document.getElementById('profileToastMsg');
  if (!el || !txt) return;
  txt.textContent = msg;
  el.classList.remove('hidden');
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    el.classList.add('hidden');
  }, 3500);
}

/* ================= NAVIGASI ANTAR HALAMAN PHP ================= */
const USER_ROUTE_MAP = {
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
  chatai: '/chat-ai',
};

function getAppBaseUrl(path) {
  const pathname = window.location.pathname;
  if (pathname.includes('/smart-water')) {
    return '/smart-water' + path;
  }
  return path;
}

function handleUserSiteNav(tab) {
  const target = USER_ROUTE_MAP[tab] || '/' + tab;
  window.location.href = getAppBaseUrl(target);
}

function toggleMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const iconOpen = document.getElementById('mobileMenuIconOpen');
  const iconClose = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  if (!drawer) return;
  const hidden = drawer.classList.contains('hidden');
  drawer.classList.toggle('hidden', !hidden);
  iconOpen?.classList.toggle('hidden', hidden);
  iconClose?.classList.toggle('hidden', !hidden);
  btn?.setAttribute('aria-expanded', String(hidden));
}

function closeMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const iconOpen = document.getElementById('mobileMenuIconOpen');
  const iconClose = document.getElementById('mobileMenuIconClose');
  if (drawer) drawer.classList.add('hidden');
  iconOpen?.classList.remove('hidden');
  iconClose?.classList.add('hidden');
}

/* ================= 7 TAB PROFIL SIDEBAR ================= */
function switchProfileTab(tab) {
  activeProfileTab = tab;

  document.querySelectorAll('.profile-tab-panel').forEach((p) => {
    p.classList.add('hidden');
  });
  const target = document.getElementById('profile-tab-' + tab);
  if (target) target.classList.remove('hidden');

  document.querySelectorAll('.profile-nav-btn').forEach((btn) => {
    const isTarget = btn.getAttribute('data-ptab') === tab;
    if (isTarget) {
      btn.className = 'profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs transition-all cursor-pointer bg-[#E3EFFB] text-[#0284C7] font-bold shadow-2xs';
    } else {
      btn.className = 'profile-nav-btn w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all cursor-pointer text-slate-600 hover:text-slate-900 hover:bg-slate-50';
    }
  });

  if (window.history && window.history.replaceState) {
    try {
      const url = new URL(window.location.href);
      url.searchParams.set('tab', tab);
      window.history.replaceState({}, '', url.toString());
    } catch (e) {}
  }

  const topEl = document.getElementById('profile-content-top');
  if (topEl) topEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

/* ================= SIMULATOR TAGIHAN GOLONGAN 2A ================= */
function calculateGol2ABill(volume) {
  const v = Math.max(0, Number(volume) || 0);
  const b1M3 = Math.round(Math.min(v, 10) * 10) / 10;
  const b1Cost = Math.round(b1M3 * 5000);

  const b2M3 = Math.round(Math.max(0, Math.min(v - 10, 10)) * 10) / 10;
  const b2Cost = Math.round(b2M3 * 6200);

  const b3M3 = Math.round(Math.max(0, v - 20) * 10) / 10;
  const b3Cost = Math.round(b3M3 * 7800);

  return {
    b1M3, b1Cost,
    b2M3, b2Cost,
    b3M3, b3Cost,
    total: b1Cost + b2Cost + b3Cost
  };
}

function updateBillSimulator(val) {
  const m3 = parseFloat(val) || 0;
  const sim = calculateGol2ABill(m3);

  const elVol = document.getElementById('simVolumeValue');
  const elLit = document.getElementById('simLitersValue');
  const elB1  = document.getElementById('simBlok1Value');
  const elB2  = document.getElementById('simBlok2Value');
  const elB3  = document.getElementById('simBlok3Value');
  const elTot = document.getElementById('simTotalEstimate');

  if (elVol) elVol.textContent = m3 + ' m³';
  if (elLit) elLit.textContent = '(' + Math.round(m3 * 1000).toLocaleString('id-ID') + ' Liter)';
  if (elB1)  elB1.textContent = sim.b1M3 + ' m³ = ' + formatRupiah(sim.b1Cost);
  if (elB2)  elB2.textContent = sim.b2M3 + ' m³ = ' + formatRupiah(sim.b2Cost);
  if (elB3)  elB3.textContent = sim.b3M3 + ' m³ = ' + formatRupiah(sim.b3Cost);
  if (elTot) elTot.textContent = formatRupiah(sim.total);
}

/* ================= SELF-METER READING ================= */
function toggleMeterPhotoUpload() {
  hasUploadedMeterPhoto = !hasUploadedMeterPhoto;
  const btn = document.getElementById('meterPhotoBtn');
  const lbl = document.getElementById('meterPhotoLabel');
  if (btn && lbl) {
    if (hasUploadedMeterPhoto) {
      btn.className = 'flex-1 py-2.5 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer bg-emerald-50 border-emerald-300 text-emerald-800';
      lbl.textContent = 'Foto Dilampirkan ✓';
    } else {
      btn.className = 'flex-1 py-2.5 px-3 rounded-xl border text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer bg-slate-50 border-slate-200 text-slate-700 hover:bg-slate-100';
      lbl.textContent = 'Unggah Foto';
    }
  }
  showProfileToast(hasUploadedMeterPhoto ? 'Foto angka meteran berhasil dilampirkan!' : 'Foto meteran dihapus.');
}

async function submitSelfMeterReading(e) {
  e.preventDefault();
  const standVal = parseFloat(document.getElementById('selfStandInput')?.value || '0') || 0;
  try {
    const res = await fetch(getAppBaseUrl('/api/profile.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'save_meter_reading',
        standMeterCurrent: standVal,
        hasUploadedPhoto
      })
    });
    const data = await res.json();
    if (!data.ok) {
      showProfileToast(data.message || 'Gagal mencatat stand meteran.');
      return;
    }
    userState = data.user;
    const r = userState.personalWaterReport;

    // Sinkronkan tampilan metrik secara real-time
    document.querySelectorAll('.bind-current-m3').forEach((el) => el.textContent = r.currentMonthM3);
    document.querySelectorAll('.bind-current-bill').forEach((el) => el.textContent = formatRupiah(r.currentBillEstimateIdr));
    document.querySelectorAll('.bind-stand-current').forEach((el) => el.textContent = r.standMeterCurrent);
    const diffEl = document.getElementById('standDiffText');
    if (diffEl) diffEl.textContent = 'Bulan Lalu: ' + r.standMeterLastMonth + ' m³ (Selisih: +' + Math.round((r.standMeterCurrent - r.standMeterLastMonth) * 10) / 10 + ' m³)';

    const simSlider = document.getElementById('billSimSlider');
    if (simSlider) {
      simSlider.value = r.currentMonthM3;
      updateBillSimulator(r.currentMonthM3);
    }

    showProfileToast(data.message);
  } catch (err) {
    showProfileToast('Koneksi gagal saat menyimpan stand meteran.');
  }
}

/* ================= EDIT DATA KONTAK ================= */
function openEditContactModal() {
  document.getElementById('editContactModal')?.classList.remove('hidden');
}

function closeEditContactModal() {
  document.getElementById('editContactModal')?.classList.add('hidden');
}

async function submitEditContact(e) {
  e.preventDefault();
  const phone = (document.getElementById('editPhoneInput')?.value || '').trim();
  const email = (document.getElementById('editEmailInput')?.value || '').trim();
  const address = (document.getElementById('editAddressInput')?.value || '').trim();

  try {
    const res = await fetch(getAppBaseUrl('/api/profile.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'update_contact', phone, email, address })
    });
    const data = await res.json();
    if (!data.ok) {
      showProfileToast(data.message || 'Gagal memperbarui kontak.');
      return;
    }
    userState = data.user;
    document.querySelectorAll('.bind-user-phone').forEach((el) => el.textContent = userState.phone);
    document.querySelectorAll('.bind-user-email').forEach((el) => el.textContent = userState.email);
    document.querySelectorAll('.bind-user-address').forEach((el) => el.textContent = userState.address);
    closeEditContactModal();
    showProfileToast(data.message);
  } catch (err) {
    showProfileToast('Koneksi gagal saat menyimpan kontak.');
  }
}

/* ================= PENGADUAN PELANGGAN ================= */
function filterProfileComplaints(filter) {
  complaintFilter = filter;
  ['semua', 'proses', 'selesai'].forEach((f) => {
    const btn = document.getElementById('cmpFilter-' + f);
    if (btn) {
      btn.className = f === filter
        ? 'px-3 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer bg-[#0284C7] text-white'
        : 'px-3 py-1.5 rounded-lg font-semibold transition-colors cursor-pointer text-slate-600 hover:bg-slate-100';
    }
  });
  renderProfileComplaints();
}

function renderProfileComplaints() {
  const box = document.getElementById('profileComplaintsContainer');
  if (!box) return;

  const filtered = (userComplaints || []).filter((t) => {
    if (complaintFilter === 'proses') return t.status === 'BARU' || t.status === 'DIPROSES' || t.status === 'MENUNGGU TINDAK LANJUT';
    if (complaintFilter === 'selesai') return t.status === 'SELESAI';
    return true;
  });

  if (filtered.length === 0) {
    box.innerHTML = `
      <div class="text-center py-10 space-y-2">
        <svg class="w-8 h-8 text-slate-300 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <p class="text-xs text-slate-400">Tidak ada tiket pengaduan pada filter ini.</p>
      </div>
    `;
    return;
  }

  box.innerHTML = filtered.map((t) => {
    const badgeCls = t.status === 'SELESAI'
      ? 'text-emerald-700 bg-emerald-50 border-emerald-200'
      : 'text-amber-700 bg-amber-50 border-amber-200';
    return `
      <div class="p-4 rounded-2xl border border-slate-100 bg-slate-50 space-y-2 text-xs hover:border-sky-200 transition-colors">
        <div class="flex items-center justify-between">
          <span class="font-mono font-bold text-[#0284C7]">${escapeHtml(t.id)}</span>
          <span class="font-bold px-2 py-0.5 rounded border text-[10px] ${badgeCls}">${escapeHtml(t.status)}</span>
        </div>
        <h4 class="font-bold text-slate-900">${escapeHtml(t.title)}</h4>
        <p class="text-slate-600 text-[11px] leading-relaxed">${escapeHtml(t.description)}</p>
        <div class="text-[10px] text-slate-400 pt-1 flex flex-wrap items-center justify-between gap-2 border-t border-slate-200/50">
          <span>Diajukan: ${escapeHtml(t.createdAt)}</span>
          <span>Lokasi: ${escapeHtml(t.address)}</span>
        </div>
      </div>
    `;
  }).join('');
}

function openComplaintModal() {
  document.getElementById('complaintModal')?.classList.remove('hidden');
}

function closeComplaintModal() {
  document.getElementById('complaintModal')?.classList.add('hidden');
}

async function submitProfileComplaint(e) {
  e.preventDefault();
  const category = document.getElementById('cmpCategorySelect')?.value || 'pipa_bocor';
  const district = document.getElementById('cmpDistrictSelect')?.value || 'Payakumbuh Barat';
  const title = (document.getElementById('cmpTitleInput')?.value || '').trim();
  const address = (document.getElementById('cmpAddressInput')?.value || '').trim();
  const description = (document.getElementById('cmpDescInput')?.value || '').trim();

  try {
    const res = await fetch(getAppBaseUrl('/api/profile.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'create_complaint',
        category,
        district,
        title,
        address,
        description
      })
    });
    const data = await res.json();
    if (!data.ok) {
      showProfileToast(data.message || 'Gagal mengajukan pengaduan.');
      return;
    }
    userComplaints = data.complaints || [];
    document.querySelectorAll('.bind-complaints-count').forEach((el) => el.textContent = userComplaints.length);
    const filterAllBtn = document.getElementById('cmpFilter-semua');
    if (filterAllBtn) filterAllBtn.textContent = 'Semua (' + userComplaints.length + ')';

    document.getElementById('profileComplaintForm')?.reset();
    closeComplaintModal();
    renderProfileComplaints();
    showProfileToast(data.message);
  } catch (err) {
    showProfileToast('Koneksi gagal saat mengirim pengaduan.');
  }
}

/* ================= E-KUITANSI, SERTIFIKAT & LOGOUT ================= */
function openReceiptModal(period, m3, amountIdr, date, invoiceNo) {
  document.getElementById('rcInvoice').textContent = invoiceNo;
  document.getElementById('rcPeriod').textContent = period;
  document.getElementById('rcM3').textContent = m3 + ' m³';
  document.getElementById('rcDate').textContent = date;
  document.getElementById('rcAmount').textContent = formatRupiah(amountIdr);
  document.getElementById('receiptModal')?.classList.remove('hidden');
}

function closeReceiptModal() {
  document.getElementById('receiptModal')?.classList.add('hidden');
}

function openCertificateModal(courseTitle, score, date, code) {
  document.getElementById('certCourseTitle').textContent = '"' + courseTitle + '"';
  document.getElementById('certScore').textContent = score + '%';
  document.getElementById('certDate').textContent = date;
  document.getElementById('certCode').textContent = code;
  document.getElementById('certificateModal')?.classList.remove('hidden');
}

function closeCertificateModal() {
  document.getElementById('certificateModal')?.classList.add('hidden');
}

function openLogoutModal() {
  document.getElementById('logoutConfirmModal')?.classList.remove('hidden');
}

function closeLogoutModal() {
  document.getElementById('logoutConfirmModal')?.classList.add('hidden');
}

/* ================= PRIVASI & KEAMANAN ================= */
async function savePrivacySettings() {
  const showInLeaderboard = Boolean(document.getElementById('privShowLeaderboard')?.checked);
  const waNotifications   = Boolean(document.getElementById('privWaNotif')?.checked);
  const twoFactorAuth     = Boolean(document.getElementById('privTwoFactor')?.checked);

  try {
    const res = await fetch(getAppBaseUrl('/api/profile.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'save_privacy',
        privacySettings: {
          showInLeaderboard,
          waNotifications,
          twoFactorAuth,
          shareConservationStats: true,
          emailDigest: true
        }
      })
    });
    const data = await res.json();
    if (data.ok) {
      showProfileToast(data.message);
    } else {
      showProfileToast(data.message || 'Gagal menyimpan pengaturan.');
    }
  } catch (err) {
    showProfileToast('Koneksi gagal saat menyimpan pengaturan privasi.');
  }
}

/* ================= TIRTABOT AI FLOATING WIDGET ================= */
const CHAT_STORAGE_KEY = 'swc_tirtabot_chat_history';
let chatMessages = [];
let isChatOpen = false;
let isChatMinimized = false;
let isChatLoading = false;

function getDefaultGreeting() {
  return {
    id: 'greeting-1',
    role: 'model',
    content: 'Halo! 👋 Saya **TirtaBot AI**, asisten edukasi dan layanan Smart Water Kota Payakumbuh.\n\nSaya bisa membantu Anda:\n• Menghitung perkiraan tarif tagihan air Tirta Sago\n• Cara cek kebocoran pipa rumah tangga (uji statis & toren)\n• Tips hemat air harian & tantangan mingguan\n• Panduan menggunakan pelaporan gangguan atau AI-OCR\n\nAda yang ingin Anda tanyakan hari ini?',
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
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
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
  });
  saveChatHistory();
  isChatLoading = true;
  renderChatMessages();

  try {
    const res = await fetch(getAppBaseUrl('/api/chat.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ message: text, history: historySnapshot })
    });
    const data = await res.json();
    chatMessages.push({
      id: 'msg-bot-' + Date.now(),
      role: 'model',
      content: data.reply || 'Maaf, terjadi gangguan saat memproses pertanyaan Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    });
  } catch (e) {
    chatMessages.push({
      id: 'msg-err-' + Date.now(),
      role: 'model',
      content: '⚠️ Koneksi ke server PHP gagal. Jalankan folder ini melalui server PHP lokal Anda.',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
    });
  } finally {
    isChatLoading = false;
    saveChatHistory();
    renderChatMessages();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderProfileComplaints();
  loadChatHistory();
  renderChatMessages();

  try {
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || window.location.hash.replace('#', '');
    if (initialTab && ['beranda', 'info', 'laporan', 'pengaduan', 'belajar', 'privasi', 'lencana'].includes(initialTab)) {
      switchProfileTab(initialTab);
    }
  } catch (e) {
    // URL parsing fallback
  }
});
