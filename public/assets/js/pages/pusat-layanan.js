/**
 * Interactive Scripts untuk Halaman Pusat Layanan PHP Standalone
 * Smart Water Kota Payakumbuh
 */

// ---------------------------------------------------------------------------
// 1. STATE TIKET PENGADUAN (Diisi dari konfigurasi PHP pusat)
// ---------------------------------------------------------------------------
let complaintsState = Array.isArray(window.SWC_COMPLAINTS) ? window.SWC_COMPLAINTS : [];
let activeTrackerTicketId = null;
let selectedPhotoBase64 = null;

const WORKFLOW_STEPS = ['BARU', 'DIPROSES', 'MENUNGGU TINDAK LANJUT', 'SELESAI'];

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

function getCardPillClass(status) {
  switch (status) {
    case 'BARU':
      return 'bg-[#e0f2fe] text-[#0369a1]';
    case 'DIPROSES':
      return 'bg-[#fef3c7] text-[#b45309]';
    case 'MENUNGGU TINDAK LANJUT':
      return 'bg-[#f3e8ff] text-[#7e22ce]';
    default:
      return 'bg-[#dcfce7] text-[#15803d]';
  }
}

function getModalBadgeClass(status) {
  switch (status) {
    case 'BARU':
      return 'bg-blue-50 text-blue-700 border-blue-200';
    case 'DIPROSES':
      return 'bg-amber-50 text-amber-700 border-amber-200';
    case 'MENUNGGU TINDAK LANJUT':
      return 'bg-purple-50 text-purple-700 border-purple-200';
    default:
      return 'bg-emerald-50 text-emerald-700 border-emerald-200';
  }
}

// ---------------------------------------------------------------------------
// 2. MOBILE MENU & MODAL NAVIGASI SUB-HALAMAN
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
    if (openIcon) openIcon.classList.add('hidden');
    if (closeIcon) closeIcon.classList.remove('hidden');
    if (btn) btn.setAttribute('aria-expanded', 'true');
  } else {
    closeMobileMenu();
  }
}

function closeMobileMenu() {
  const drawer = document.getElementById('mobileMenuDrawer');
  const openIcon = document.getElementById('mobileMenuIconOpen');
  const closeIcon = document.getElementById('mobileMenuIconClose');
  const btn = document.getElementById('mobileMenuToggleBtn');
  if (drawer) drawer.classList.add('hidden');
  if (openIcon) openIcon.classList.remove('hidden');
  if (closeIcon) closeIcon.classList.add('hidden');
  if (btn) btn.setAttribute('aria-expanded', 'false');
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

function handleLayananNav(tab) {
  if (tab === 'layanan') {
    closeCreateComplaintPage();
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }
  const target = ROUTE_MAP[tab] || '/' + tab;
  window.location.href = getAppBaseUrl(target);
}

function openAuthNoticeModal() {
  window.location.href = getAppBaseUrl('/login');
}

function openNavModal(title, desc, href) {
  const modal = document.getElementById('layanan-nav-modal');
  if (modal) modal.classList.add('hidden');
}

function closeNavModal() {
  const modal = document.getElementById('layanan-nav-modal');
  if (modal) modal.classList.add('hidden');
}

// ---------------------------------------------------------------------------
// 3. PELACAKAN TIKET CEPAT & PENGADUAN SAYA CARD SYNC
// ---------------------------------------------------------------------------
function scrollToTracker() {
  closeCreateComplaintPage();
  const el = document.getElementById('pelacakan-cepat-tiket');
  if (el) {
    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }
  setTimeout(() => {
    const input = document.getElementById('ticket-search-input');
    if (input) input.focus();
  }, 450);
}

function syncMyComplaintsCard(ticket) {
  if (!ticket) return;

  const countEl = document.getElementById('my-complaints-count');
  const cardIdEl = document.getElementById('my-ticket-card-id');
  const cardStatusEl = document.getElementById('my-ticket-card-status');
  const cardTitleEl = document.getElementById('my-ticket-card-title');
  const cardDescEl = document.getElementById('my-ticket-card-desc');
  const cardDistrictEl = document.getElementById('my-ticket-card-district');

  const sampleBtn = document.getElementById('quick-sample-ticket-btn');
  const sampleStatus = document.getElementById('quick-sample-ticket-status');

  // Hitung tiket non-default atau minimal 1 sesuai ServiceCenterView.tsx
  const customCount = Math.max(1, complaintsState.length - 2);
  if (countEl) countEl.textContent = String(customCount);

  if (cardIdEl) cardIdEl.textContent = ticket.id;
  if (cardStatusEl) {
    cardStatusEl.textContent = ticket.status;
    cardStatusEl.className = `px-2.5 py-0.5 rounded-full font-bold text-[10px] uppercase tracking-wider ${getCardPillClass(ticket.status)}`;
  }
  if (cardTitleEl) cardTitleEl.textContent = ticket.title;
  if (cardDescEl) cardDescEl.textContent = ticket.description;
  if (cardDistrictEl) cardDistrictEl.textContent = ticket.district;

  if (sampleBtn) {
    sampleBtn.textContent = ticket.id;
    sampleBtn.setAttribute('onclick', `openTicketTrackerById('${escapeHtml(ticket.id)}', true)`);
  }
  if (sampleStatus) {
    sampleStatus.textContent = ticket.status;
    sampleStatus.className = `px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wider uppercase ${getCardPillClass(ticket.status)}`;
  }
}

function handleSearchTicket(event) {
  event.preventDefault();
  const input = document.getElementById('ticket-search-input');
  const errEl = document.getElementById('ticket-search-error');
  if (!input || !errEl) return;

  errEl.classList.add('hidden');
  errEl.textContent = '';

  const query = input.value.trim().toUpperCase();
  if (!query) {
    errEl.textContent = 'Masukkan nomor tiket Anda (contoh: TK-PYK-2026-8942)';
    errEl.classList.remove('hidden');
    return;
  }

  const found = complaintsState.find((c) => String(c.id).toUpperCase() === query);
  if (!found) {
    errEl.textContent = `Tiket dengan nomor "${query}" tidak ditemukan. Periksa kembali format tiket Anda.`;
    errEl.classList.remove('hidden');
    return;
  }

  openTicketTrackerModal(found);
}

function openTicketTrackerById(ticketId, fillSearchInput = false) {
  const cleanId = String(ticketId || '').trim().toUpperCase();
  if (fillSearchInput) {
    const input = document.getElementById('ticket-search-input');
    const errEl = document.getElementById('ticket-search-error');
    if (input) input.value = cleanId;
    if (errEl) errEl.classList.add('hidden');
  }
  const found = complaintsState.find((c) => String(c.id).toUpperCase() === cleanId);
  if (found) {
    openTicketTrackerModal(found);
  }
}

function openTicketTrackerModal(ticket) {
  activeTrackerTicketId = ticket.id;
  renderTicketTrackerModal(ticket);
  const modal = document.getElementById('ticket-tracker-modal');
  if (modal) modal.classList.remove('hidden');
}

function closeTicketTrackerModal() {
  const modal = document.getElementById('ticket-tracker-modal');
  if (modal) modal.classList.add('hidden');
  activeTrackerTicketId = null;
}

function renderTicketTrackerModal(ticket) {
  const idEl = document.getElementById('tracker-modal-id');
  const statusEl = document.getElementById('tracker-modal-status');
  const titleEl = document.getElementById('tracker-modal-title');
  const stepsEl = document.getElementById('tracker-modal-steps');
  const userEl = document.getElementById('tracker-modal-username');
  const distEl = document.getElementById('tracker-modal-district');
  const addrEl = document.getElementById('tracker-modal-address');
  const createdEl = document.getElementById('tracker-modal-created');
  const phoneEl = document.getElementById('tracker-modal-phone');
  const updatedEl = document.getElementById('tracker-modal-updated');
  const descEl = document.getElementById('tracker-modal-desc');
  const photoWrap = document.getElementById('tracker-modal-photo-wrap');
  const photoImg = document.getElementById('tracker-modal-photo');
  const updatesEl = document.getElementById('tracker-modal-updates');
  const simBtn = document.getElementById('tracker-modal-sim-btn');
  const simLabel = document.getElementById('tracker-modal-sim-label');
  const doneBadge = document.getElementById('tracker-modal-done-badge');

  if (idEl) idEl.textContent = ticket.id;
  if (statusEl) {
    statusEl.textContent = ticket.status;
    statusEl.className = `text-[11px] font-semibold px-2 py-0.5 rounded border ${getModalBadgeClass(ticket.status)}`;
  }
  if (titleEl) titleEl.textContent = ticket.title;

  const currentStepIdx = WORKFLOW_STEPS.indexOf(ticket.status);
  if (stepsEl) {
    stepsEl.innerHTML = WORKFLOW_STEPS.map((st, idx) => {
      const isPassed = idx <= currentStepIdx;
      const isCurrent = idx === currentStepIdx;
      const circleCls = isPassed
        ? isCurrent
          ? 'bg-cyan-600 text-white ring-2 ring-cyan-200'
          : 'bg-emerald-600 text-white'
        : 'bg-slate-200 text-slate-400';
      const labelCls = isCurrent ? 'text-cyan-900 font-bold' : 'text-slate-500';
      const innerIcon = isPassed
        ? '<svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/></svg>'
        : String(idx + 1);

      return `
        <div class="flex flex-col items-center text-center">
          <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold transition-all mb-1 ${circleCls}">
            ${innerIcon}
          </div>
          <span class="text-[10px] leading-tight font-medium ${labelCls}">${escapeHtml(st)}</span>
        </div>
      `;
    }).join('');
  }

  if (userEl) userEl.textContent = ticket.userName || '-';
  if (distEl) distEl.textContent = ticket.district || '-';
  if (addrEl) addrEl.textContent = ticket.address || '-';
  if (createdEl) createdEl.textContent = ticket.createdAt || '-';
  if (phoneEl) phoneEl.textContent = ticket.userPhone || '-';
  if (updatedEl) updatedEl.textContent = ticket.updatedAt || '-';
  if (descEl) descEl.textContent = ticket.description || '-';

  if (photoWrap && photoImg) {
    if (ticket.imageUrl) {
      photoImg.src = ticket.imageUrl;
      photoWrap.classList.remove('hidden');
    } else {
      photoImg.src = '';
      photoWrap.classList.add('hidden');
    }
  }

  if (updatesEl) {
    const updates = Array.isArray(ticket.updates) ? ticket.updates : [];
    updatesEl.innerHTML = updates.map((up) => `
      <div class="p-3.5 rounded-xl bg-white border border-slate-200 space-y-1">
        <div class="flex items-center justify-between text-[11px]">
          <span class="font-bold text-slate-900">${escapeHtml(up.actor)}</span>
          <span class="text-slate-400 font-mono">${escapeHtml(up.timestamp)}</span>
        </div>
        <p class="text-xs text-slate-600 leading-relaxed">${escapeHtml(up.note)}</p>
        <span class="inline-block text-[10px] font-semibold px-2 py-0.5 rounded border mt-1 ${getModalBadgeClass(up.status)}">
          Status: ${escapeHtml(up.status)}
        </span>
      </div>
    `).join('');
  }

  if (ticket.status !== 'SELESAI') {
    const nextLabel = WORKFLOW_STEPS[currentStepIdx + 1] || 'SELESAI';
    if (simBtn) simBtn.classList.remove('hidden');
    if (simLabel) simLabel.textContent = `Simulasi Update Operator (${nextLabel})`;
    if (doneBadge) doneBadge.classList.add('hidden');
  } else {
    if (simBtn) simBtn.classList.add('hidden');
    if (doneBadge) doneBadge.classList.remove('hidden');
  }
}

async function simulateOperatorProgress() {
  if (!activeTrackerTicketId) return;
  const simBtn = document.getElementById('tracker-modal-sim-btn');
  if (simBtn) simBtn.disabled = true;

  try {
    const res = await fetch(getAppBaseUrl('/api/complaints.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'simulate_progress',
        id: activeTrackerTicketId,
      }),
    });

    const data = await res.json();
    if (res.ok && data.ticket) {
      if (Array.isArray(data.complaints)) {
        complaintsState = data.complaints;
      } else {
        complaintsState = complaintsState.map((c) => (c.id === data.ticket.id ? data.ticket : c));
      }
      renderTicketTrackerModal(data.ticket);

      const cardIdEl = document.getElementById('my-ticket-card-id');
      if (cardIdEl && cardIdEl.textContent.trim() === data.ticket.id) {
        syncMyComplaintsCard(data.ticket);
      }
    }
  } catch (err) {
    console.error('Gagal simulasi operator:', err);
  } finally {
    if (simBtn) simBtn.disabled = false;
  }
}

// ---------------------------------------------------------------------------
// 4. DEDICATED COMPLAINT FORM PAGE
// ---------------------------------------------------------------------------
function openCreateComplaintPage() {
  const mainEl = document.getElementById('service-center-main');
  const formWrap = document.getElementById('complaint-form-page-wrapper');
  const formScreen = document.getElementById('complaint-form-screen');
  const successScreen = document.getElementById('complaint-success-screen');
  const errBox = document.getElementById('complaint-form-error');

  if (mainEl) mainEl.classList.add('hidden');
  if (formWrap) formWrap.classList.remove('hidden');
  if (formScreen) formScreen.classList.remove('hidden');
  if (successScreen) successScreen.classList.add('hidden');
  if (errBox) errBox.classList.add('hidden');

  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function closeCreateComplaintPage() {
  const mainEl = document.getElementById('service-center-main');
  const formWrap = document.getElementById('complaint-form-page-wrapper');
  if (formWrap) formWrap.classList.add('hidden');
  if (mainEl) mainEl.classList.remove('hidden');
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectComplaintCategory(catId) {
  const hiddenInput = document.getElementById('complaint-category-input');
  if (hiddenInput) hiddenInput.value = catId;

  document.querySelectorAll('.complaint-cat-btn').forEach((btn) => {
    const isThis = btn.getAttribute('data-category-btn') === catId;
    const badge = btn.querySelector('.cat-check-badge');
    if (isThis) {
      btn.className = 'complaint-cat-btn p-4 rounded-2xl border text-left transition-all relative flex flex-col justify-between gap-2 cursor-pointer border-[#0284C7] bg-sky-50/70 ring-2 ring-sky-200 shadow-xs';
      if (badge) badge.classList.remove('hidden');
    } else {
      btn.className = 'complaint-cat-btn p-4 rounded-2xl border text-left transition-all relative flex flex-col justify-between gap-2 cursor-pointer border-slate-200 hover:border-slate-300 bg-white hover:bg-slate-50/50';
      if (badge) badge.classList.add('hidden');
    }
  });
}

function handleComplaintPhotoPreview(event) {
  const file = event.target.files && event.target.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onloadend = () => {
    selectedPhotoBase64 = typeof reader.result === 'string' ? reader.result : null;
    const wrap = document.getElementById('complaint-photo-preview-wrap');
    const img = document.getElementById('complaint-photo-preview-img');
    if (img && selectedPhotoBase64) img.src = selectedPhotoBase64;
    if (wrap) wrap.classList.remove('hidden');
  };
  reader.readAsDataURL(file);
}

function clearComplaintPhotoPreview() {
  selectedPhotoBase64 = null;
  const input = document.getElementById('complaint-photo-input');
  const wrap = document.getElementById('complaint-photo-preview-wrap');
  const img = document.getElementById('complaint-photo-preview-img');
  if (input) input.value = '';
  if (img) img.src = '';
  if (wrap) wrap.classList.add('hidden');
}

async function handleSubmitComplaint(event) {
  event.preventDefault();

  const errBox = document.getElementById('complaint-form-error');
  const errText = document.getElementById('complaint-form-error-text');
  const submitBtn = document.getElementById('complaint-submit-btn');

  const category = (document.getElementById('complaint-category-input')?.value || 'pipa_bocor').trim();
  const district = (document.getElementById('complaint-district-input')?.value || 'Payakumbuh Barat').trim();
  const userPhone = (document.getElementById('complaint-phone-input')?.value || '').trim();
  const address = (document.getElementById('complaint-address-input')?.value || '').trim();
  const title = (document.getElementById('complaint-title-input')?.value || '').trim();
  const description = (document.getElementById('complaint-desc-input')?.value || '').trim();

  if (errBox) errBox.classList.add('hidden');

  if (!title || !description || !address || !userPhone) {
    if (errText) errText.textContent = 'Mohon lengkapi seluruh kolom yang bertanda wajib (*), termasuk nomor kontak dan alamat patokan.';
    if (errBox) errBox.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
    return;
  }

  if (submitBtn) submitBtn.disabled = true;

  try {
    const res = await fetch(getAppBaseUrl('/api/complaints.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'create',
        category,
        district,
        userPhone,
        address,
        title,
        description,
        imageUrl: selectedPhotoBase64,
      }),
    });

    const data = await res.json();
    if (!res.ok || !data.ticket) {
      throw new Error(data.error || 'Gagal mengirim pengaduan.');
    }

    if (Array.isArray(data.complaints)) {
      complaintsState = data.complaints;
    } else {
      complaintsState = [data.ticket, ...complaintsState];
    }

    // Update Pengaduan Saya card & quick badge
    syncMyComplaintsCard(data.ticket);

    // Populate Success Screen
    const formScreen = document.getElementById('complaint-form-screen');
    const successScreen = document.getElementById('complaint-success-screen');
    const distOut = document.getElementById('success-district-text');
    const idOut = document.getElementById('success-ticket-id');
    const catOut = document.getElementById('success-ticket-category');
    const locOut = document.getElementById('success-ticket-location');
    const copiedNote = document.getElementById('success-copied-note');

    if (distOut) distOut.textContent = data.ticket.district;
    if (idOut) idOut.textContent = data.ticket.id;
    if (catOut) catOut.textContent = String(data.ticket.category || '').replace('_', ' ');
    if (locOut) locOut.textContent = `${data.ticket.district}, ${data.ticket.address}`;
    if (copiedNote) copiedNote.classList.add('hidden');

    // Reset form fields for next usage
    document.getElementById('complaint-submit-form')?.reset();
    selectComplaintCategory('pipa_bocor');
    clearComplaintPhotoPreview();

    if (formScreen) formScreen.classList.add('hidden');
    if (successScreen) successScreen.classList.remove('hidden');
    window.scrollTo({ top: 0, behavior: 'smooth' });
  } catch (err) {
    if (errText) errText.textContent = err.message || 'Terjadi kesalahan saat menyimpan pengaduan.';
    if (errBox) errBox.classList.remove('hidden');
  } finally {
    if (submitBtn) submitBtn.disabled = false;
  }
}

function copySuccessTicketId() {
  const idEl = document.getElementById('success-ticket-id');
  const noteEl = document.getElementById('success-copied-note');
  if (!idEl) return;
  const ticketId = idEl.textContent.trim();

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(ticketId);
  }
  if (noteEl) {
    noteEl.classList.remove('hidden');
    setTimeout(() => noteEl.classList.add('hidden'), 2000);
  }
}

// ---------------------------------------------------------------------------
// 5. FILTER WILAYAH PENGUMUMAN GANGGUAN
// ---------------------------------------------------------------------------
function toggleDistrictDropdown() {
  const menu = document.getElementById('district-dropdown-menu');
  const chevron = document.getElementById('district-dropdown-chevron');
  if (!menu) return;

  const isHidden = menu.classList.contains('hidden');
  if (isHidden) {
    menu.classList.remove('hidden');
    if (chevron) chevron.classList.add('rotate-180');
  } else {
    closeDistrictDropdown();
  }
}

function closeDistrictDropdown() {
  const menu = document.getElementById('district-dropdown-menu');
  const chevron = document.getElementById('district-dropdown-chevron');
  if (menu) menu.classList.add('hidden');
  if (chevron) chevron.classList.remove('rotate-180');
}

function selectDistrictFilter(selectedValue) {
  const labelEl = document.getElementById('district-dropdown-label');
  if (labelEl) {
    labelEl.textContent = selectedValue === 'all' ? 'Pilih Wilayah' : selectedValue;
  }

  // Update active styles on dropdown buttons
  document.querySelectorAll('.district-opt-btn').forEach((btn) => {
    const val = btn.getAttribute('data-district-option');
    const checkIcon = btn.querySelector('.district-check');
    if (val === selectedValue) {
      btn.className = 'district-opt-btn w-full text-left px-4 py-2.5 text-xs transition-colors flex items-center justify-between cursor-pointer bg-[#d9effd] text-[#0284c7] font-bold';
      if (checkIcon) checkIcon.classList.remove('hidden');
    } else {
      btn.className = 'district-opt-btn w-full text-left px-4 py-2.5 text-xs transition-colors flex items-center justify-between cursor-pointer text-slate-700 hover:bg-slate-50 font-medium';
      if (checkIcon) checkIcon.classList.add('hidden');
    }
  });

  // Filter announcement cards
  const cards = document.querySelectorAll('[data-announcement-card]');
  let visibleCount = 0;

  cards.forEach((card) => {
    const districtsRaw = card.getAttribute('data-districts') || '';
    const districts = districtsRaw.split('|');
    const match = selectedValue === 'all' || districts.includes(selectedValue);
    if (match) {
      card.classList.remove('hidden');
      visibleCount++;
    } else {
      card.classList.add('hidden');
    }
  });

  const emptyEl = document.getElementById('announcements-empty');
  if (emptyEl) {
    if (visibleCount === 0) emptyEl.classList.remove('hidden');
    else emptyEl.classList.add('hidden');
  }

  closeDistrictDropdown();
}

// Tutup dropdown saat klik di luar area filter
document.addEventListener('mousedown', (event) => {
  const wrapper = document.getElementById('district-filter-wrapper');
  if (wrapper && !wrapper.contains(event.target)) {
    closeDistrictDropdown();
  }
});

// ---------------------------------------------------------------------------
// 6. FAQ ACCORDION (6 PERTANYAAN)
// ---------------------------------------------------------------------------
let currentOpenFaqIndex = 0;

function toggleFaqItem(idx) {
  const targetBody = document.querySelector(`[data-faq-body="${idx}"]`);
  const targetChevron = document.querySelector(`[data-faq-chevron="${idx}"]`);
  if (!targetBody) return;

  const isCurrentlyOpen = !targetBody.classList.contains('hidden');

  // Tutup semua item FAQ terlebih dahulu
  document.querySelectorAll('[data-faq-body]').forEach((body) => {
    body.classList.add('hidden');
  });
  document.querySelectorAll('[data-faq-chevron]').forEach((chev) => {
    chev.classList.remove('rotate-180');
  });

  if (!isCurrentlyOpen) {
    targetBody.classList.remove('hidden');
    if (targetChevron) targetChevron.classList.add('rotate-180');
    currentOpenFaqIndex = idx;
  } else {
    currentOpenFaqIndex = null;
  }
}

// ---------------------------------------------------------------------------
// 7. TIRTABOT AI CHATBOT WIDGET
// ---------------------------------------------------------------------------
const STORAGE_KEY = 'smartwater_tirtabot_history_php';
let chatMessages = [];
let isChatOpen = false;
let isChatMinimized = false;
let isChatLoading = false;

function getDefaultGreeting() {
  return {
    id: 'msg-welcome',
    role: 'model',
    content: 'Halo! Saya **TirtaBot**, asisten cerdas Komunitas Smart Water Kota Payakumbuh.\n\nAda yang bisa saya bantu seputar pelaporan gangguan pipa, pelacakan tiket, tarif air Tirta Sago, atau tips hemat air hari ini?',
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  };
}

function loadChatHistory() {
  try {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) {
      const parsed = JSON.parse(saved);
      if (Array.isArray(parsed) && parsed.length > 0) {
        chatMessages = parsed;
        return;
      }
    }
  } catch (e) {
    // ignore storage errors
  }
  chatMessages = [getDefaultGreeting()];
}

function saveChatHistory() {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(chatMessages));
  } catch (e) {
    // ignore
  }
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

  const existingDynamic = container.querySelectorAll('.tirtabot-msg-row, #tirtabot-loading-row');
  existingDynamic.forEach((node) => node.remove());

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
  const triggerWrap = document.getElementById('tirtabot-trigger-wrap');
  const win = document.getElementById('tirtabot-window');
  const badge = document.getElementById('tirtabot-unread-badge');

  isChatOpen = true;
  isChatMinimized = false;

  if (triggerWrap) triggerWrap.classList.add('hidden');
  if (win) win.classList.remove('hidden');
  if (badge) badge.classList.add('hidden');

  updateMinimizedState();
  renderChatMessages();

  setTimeout(() => {
    const input = document.getElementById('tirtabot-input');
    if (input) input.focus();
  }, 150);
}

function closeTirtaBot() {
  const triggerWrap = document.getElementById('tirtabot-trigger-wrap');
  const win = document.getElementById('tirtabot-window');

  isChatOpen = false;
  if (win) win.classList.add('hidden');
  if (triggerWrap) triggerWrap.classList.remove('hidden');
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
    if (minBar) minBar.classList.remove('hidden');
    if (bodyWrap) bodyWrap.classList.add('hidden');
    if (iconMin) iconMin.classList.add('hidden');
    if (iconMax) iconMax.classList.remove('hidden');
  } else {
    win.classList.remove('h-14');
    win.classList.add('h-[580px]', 'max-h-[85vh]');
    if (minBar) minBar.classList.add('hidden');
    if (bodyWrap) bodyWrap.classList.remove('hidden');
    if (iconMin) iconMin.classList.remove('hidden');
    if (iconMax) iconMax.classList.add('hidden');
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
  if (!text) return;
  sendTirtaBotMessage(text);
}

async function sendTirtaBotMessage(text) {
  if (!text || isChatLoading) return;

  const input = document.getElementById('tirtabot-input');
  if (input) input.value = '';

  const userMsg = {
    id: 'msg-user-' + Date.now(),
    role: 'user',
    content: text,
    timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
  };

  const historySnapshot = chatMessages.map((m) => ({ role: m.role, content: m.content }));
  chatMessages.push(userMsg);
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
    const replyContent = data.reply || 'Maaf, terjadi gangguan saat memproses pertanyaan Anda.';

    chatMessages.push({
      id: 'msg-bot-' + Date.now(),
      role: 'model',
      content: replyContent,
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } catch (err) {
    chatMessages.push({
      id: 'msg-err-' + Date.now(),
      role: 'model',
      content: '⚠️ Koneksi ke server PHP gagal. Pastikan Anda menjalankan folder ini melalui server PHP (XAMPP/Laragon/php -S localhost:8000).',
      timestamp: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }),
    });
  } finally {
    isChatLoading = false;
    saveChatHistory();
    renderChatMessages();
  }
}

// ---------------------------------------------------------------------------
// INITIALIZE ON PAGE LOAD
// ---------------------------------------------------------------------------
document.addEventListener('DOMContentLoaded', () => {
  loadChatHistory();
  renderChatMessages();
});
