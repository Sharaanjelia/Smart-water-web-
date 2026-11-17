/**
 * Interaktivitas Command Center Admin SCADA PDAM Tirta Sago Payakumbuh
 * Sinkron 100% dengan src/components/admin/AdminDashboardView.tsx
 */

let adminState = window.__ADMIN_STATE__ || {
  complaints: [],
  announcements: [],
  reservoirs: [],
  meterVerifications: [],
  auditLogs: []
};
const adminCustomers = window.__ADMIN_CUSTOMERS__ || [];
const adminUserName = window.__ADMIN_USER_NAME__ || 'Ir. Hendra Pratama, S.T.';

let activeTab = 'ikhtisar';
let selectedTicketId = null;
let selectedMeterId = null;
let toastTimer = null;

function esc(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

function formatRupiah(num) {
  return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
}

function showAdminToast(message) {
  const el = document.getElementById('adminToast');
  const txt = document.getElementById('adminToastMsg');
  if (!el || !txt) return;
  txt.textContent = message;
  el.classList.remove('hidden');
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    el.classList.add('hidden');
  }, 3500);
}

function getAppBaseUrl(path) {
  const pathname = window.location.pathname;
  if (pathname.includes('/smart-water')) {
    return '/smart-water' + path;
  }
  return path;
}

async function postAdminAction(payload) {
  try {
    const res = await fetch(getAppBaseUrl('/api/admin.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload)
    });
    const data = await res.json();
    if (!data.ok) {
      showAdminToast(data.message || 'Gagal memproses tindakan.');
      return null;
    }
    if (data.state) {
      adminState = data.state;
    }
    return data;
  } catch (err) {
    showAdminToast('Terjadi kendala koneksi ke server PHP.');
    return null;
  }
}

/* ================= SIDEBAR MOBILE DRAWER ================= */
function openAdminSidebar() {
  const aside = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('adminSidebarBackdrop');
  if (aside) {
    aside.classList.remove('-translate-x-full');
    aside.classList.add('translate-x-0');
  }
  if (backdrop) backdrop.classList.remove('hidden');
}

function closeAdminSidebar() {
  const aside = document.getElementById('adminSidebar');
  const backdrop = document.getElementById('adminSidebarBackdrop');
  if (aside) {
    aside.classList.add('-translate-x-full');
    aside.classList.remove('translate-x-0');
  }
  if (backdrop) backdrop.classList.add('hidden');
}

/* ================= TAB SWITCHING ================= */
const TAB_TITLES = {
  ikhtisar:   { crumb: 'Ikhtisar & KPI',             head: 'Ikhtisar & Telemetri Real-time' },
  tiket:      { crumb: 'Manajemen Tiket Aduan',      head: 'Manajemen Tiket Aduan & Disposisi' },
  meteran:    { crumb: 'Validasi Stand AI-OCR',      head: 'Validasi Stand Meteran AI-OCR' },
  pengumuman: { crumb: 'Pengumuman Layanan',         head: 'Pusat Publikasi Pengumuman Layanan' },
  reservoir:  { crumb: 'SCADA Reservoir & Kualitas', head: 'SCADA Telemetri Reservoir & Kualitas' },
  pelanggan:  { crumb: 'Master Pelanggan & Tagihan', head: 'Master Data Pelanggan & Tagihan' },
  audit:      { crumb: 'Audit Trail',                head: 'Audit Trail & Jejak Aktivitas' }
};

function switchAdminTab(tabId) {
  if (!TAB_TITLES[tabId]) return;
  activeTab = tabId;

  document.querySelectorAll('.admin-tab-panel').forEach((panel) => {
    panel.classList.add('hidden');
  });
  const activePanel = document.getElementById('tab-' + tabId);
  if (activePanel) activePanel.classList.remove('hidden');

  document.querySelectorAll('.admin-nav-btn').forEach((btn) => {
    const isTarget = btn.getAttribute('data-tab') === tabId;
    const iconEl = btn.querySelector('.admin-nav-icon');
    if (isTarget) {
      btn.className = 'admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all cursor-pointer bg-[#0284C7] text-white shadow-md shadow-sky-600/25 font-bold';
      if (iconEl) iconEl.classList.replace('text-sky-400', 'text-white');
    } else {
      btn.className = 'admin-nav-btn w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs transition-all font-semibold cursor-pointer text-slate-300 hover:text-white hover:bg-slate-800/80';
      if (iconEl) iconEl.classList.replace('text-white', 'text-sky-400');
    }
  });

  const crumbEl = document.getElementById('adminBreadcrumbLabel');
  const headEl = document.getElementById('adminHeaderTitle');
  if (crumbEl) crumbEl.textContent = TAB_TITLES[tabId].crumb;
  if (headEl) headEl.textContent = TAB_TITLES[tabId].head;

  closeAdminSidebar();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function getTicketStatusClass(status) {
  if (status === 'BARU') return 'bg-rose-100 text-rose-800';
  if (status === 'DIPROSES') return 'bg-amber-100 text-amber-800';
  if (status === 'MENUNGGU TINDAK LANJUT') return 'bg-blue-100 text-blue-800';
  return 'bg-emerald-100 text-emerald-800';
}

/* ================= MASTER RE-RENDER FUNCTIONS ================= */
function renderAllAdminSections() {
  const complaints = adminState.complaints || [];
  const meters = adminState.meterVerifications || [];
  const newCount = complaints.filter((c) => c.status === 'BARU').length;
  const inProgCount = complaints.filter((c) => c.status === 'DIPROSES' || c.status === 'MENUNGGU TINDAK LANJUT').length;
  const doneCount = complaints.filter((c) => c.status === 'SELESAI').length;
  const pendingMeters = meters.filter((m) => m.status === 'menunggu_validasi').length;

  // Badges sidebar
  const sbTicketBadge = document.getElementById('sidebarBadgeTiket');
  if (sbTicketBadge) {
    sbTicketBadge.textContent = newCount;
    sbTicketBadge.classList.toggle('hidden', newCount <= 0);
  }
  const sbMeterBadge = document.getElementById('sidebarBadgeMeteran');
  if (sbMeterBadge) {
    sbMeterBadge.textContent = pendingMeters;
    sbMeterBadge.classList.toggle('hidden', pendingMeters <= 0);
  }

  // Ikhtisar KPI counts
  setText('ikhtisarPendingMeterText', pendingMeters);
  setText('ikhtisarBannerNewTicketCount', newCount);
  setText('kpiTotalComplaints', complaints.length);
  setText('kpiNewComplaints', newCount);
  setText('kpiInProgressComplaints', inProgCount);
  setText('kpiResolvedComplaints', doneCount);
  setText('kpiTotalMeters', meters.length);
  setText('kpiPendingMeters', pendingMeters + ' Menunggu Validasi');
  setText('ikhtisarAllTicketsCount', complaints.length);
  setText('tiketTotalBadge', 'Total: ' + complaints.length + ' Tiket');
  setText('meterPendingHeaderText', pendingMeters + ' Menunggu Validasi');

  renderIkhtisarLists();
  renderTicketListAndDetail();
  renderMeterTable();
  renderAnnouncementList();
  renderReservoirCards();
  renderAuditLogs();
}

function setText(id, val) {
  const el = document.getElementById(id);
  if (el) el.textContent = String(val);
}

function renderIkhtisarLists() {
  const ticketBox = document.getElementById('ikhtisarRecentTickets');
  if (ticketBox) {
    const recent = (adminState.complaints || []).slice(0, 4);
    ticketBox.innerHTML = recent.map((ticket) => `
      <div
        onclick="inspectTicketFromOverview('${esc(ticket.id)}')"
        class="py-3 px-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer flex items-center justify-between gap-4"
      >
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-xs font-bold text-[#0284C7]">${esc(ticket.id)}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${getTicketStatusClass(ticket.status)}">
              ${esc(ticket.status)}
            </span>
            <span class="text-[11px] text-slate-400 hidden sm:inline">${esc(ticket.createdAt)}</span>
          </div>
          <h4 class="text-xs font-bold text-slate-900 line-clamp-1">${esc(ticket.title)}</h4>
          <p class="text-[11px] text-slate-500">${esc(ticket.userName)} · ${esc(ticket.district)}</p>
        </div>
        <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
      </div>
    `).join('');
  }

  const resBox = document.getElementById('ikhtisarReservoirs');
  if (resBox) {
    resBox.innerHTML = (adminState.reservoirs || []).map((res) => {
      const barColor = res.waterLevelPct >= 80 ? 'bg-emerald-500' : (res.waterLevelPct >= 60 ? 'bg-[#0284C7]' : 'bg-amber-500');
      const badgeCls = res.status === 'optimal' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';
      return `
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 space-y-2">
          <div class="flex items-center justify-between text-xs">
            <span class="font-bold text-slate-900 truncate max-w-[200px]">${esc(res.name)}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold ${badgeCls}">${res.waterLevelPct}%</span>
          </div>
          <div class="w-full bg-slate-200 h-2 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500 ${barColor}" style="width: ${res.waterLevelPct}%"></div>
          </div>
          <div class="flex items-center justify-between text-[10px] text-slate-500 font-mono">
            <span>Debit: ${res.inflowDebitLps} L/s</span>
            <span>Tekanan: ${res.pressureBar} Bar</span>
            <span>Katup: ${res.valveOpenPct}%</span>
          </div>
        </div>
      `;
    }).join('');
  }
}

function inspectTicketFromOverview(ticketId) {
  selectedTicketId = ticketId;
  switchAdminTab('tiket');
  renderTicketListAndDetail();
}

/* ================= TAB 2: TIKET ================= */
function filterTickets() {
  renderTicketListAndDetail();
}

function resetTicketFilters() {
  const s = document.getElementById('ticketSearchInput');
  const st = document.getElementById('ticketStatusSelect');
  const dt = document.getElementById('ticketDistrictSelect');
  if (s) s.value = '';
  if (st) st.value = 'semua';
  if (dt) dt.value = 'semua';
  renderTicketListAndDetail();
}

function selectAdminTicket(ticketId) {
  selectedTicketId = ticketId;
  renderTicketListAndDetail();
}

function renderTicketListAndDetail() {
  const searchVal = (document.getElementById('ticketSearchInput')?.value || '').trim().toLowerCase();
  const statusVal = document.getElementById('ticketStatusSelect')?.value || 'semua';
  const districtVal = document.getElementById('ticketDistrictSelect')?.value || 'semua';

  const complaints = adminState.complaints || [];
  const filtered = complaints.filter((t) => {
    const mStatus = statusVal === 'semua' || t.status === statusVal;
    const mDist = districtVal === 'semua' || t.district === districtVal;
    const mSearch = !searchVal ||
      t.id.toLowerCase().includes(searchVal) ||
      t.userName.toLowerCase().includes(searchVal) ||
      t.title.toLowerCase().includes(searchVal) ||
      t.address.toLowerCase().includes(searchVal);
    return mStatus && mDist && mSearch;
  });

  setText('filteredTicketCount', filtered.length);
  const resetBtn = document.getElementById('ticketResetFilterBtn');
  if (resetBtn) resetBtn.classList.toggle('hidden', statusVal === 'semua' && districtVal === 'semua' && !searchVal);

  const listBox = document.getElementById('ticketListContainer');
  if (listBox) {
    if (filtered.length === 0) {
      listBox.innerHTML = `
        <div class="text-center py-12 space-y-2">
          <svg class="w-8 h-8 text-slate-300 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <p class="text-xs text-slate-500 font-semibold">Tidak ada tiket pengaduan yang cocok.</p>
        </div>
      `;
    } else {
      listBox.innerHTML = filtered.map((t) => {
        const isSel = selectedTicketId === t.id;
        return `
          <div
            onclick="selectAdminTicket('${esc(t.id)}')"
            class="p-4 rounded-2xl border transition-all cursor-pointer space-y-2 text-xs ${
              isSel ? 'border-[#0284C7] bg-sky-50/50 ring-2 ring-sky-200 shadow-xs' : 'border-slate-100 hover:border-slate-300 bg-white'
            }"
          >
            <div class="flex items-center justify-between">
              <span class="font-mono font-bold text-[#0284C7]">${esc(t.id)}</span>
              <span class="px-2 py-0.5 rounded text-[10px] font-bold ${getTicketStatusClass(t.status)}">${esc(t.status)}</span>
            </div>
            <h4 class="font-bold text-slate-900 line-clamp-1">${esc(t.title)}</h4>
            <p class="text-slate-600 text-[11px] line-clamp-2 leading-relaxed">${esc(t.description)}</p>
            <div class="flex items-center justify-between text-[10px] text-slate-400 pt-1 border-t border-slate-100">
              <span>${esc(t.userName)}</span>
              <span>${esc(t.district)}</span>
            </div>
          </div>
        `;
      }).join('');
    }
  }

  const detailBox = document.getElementById('ticketDetailPanel');
  if (!detailBox) return;
  const sel = complaints.find((t) => t.id === selectedTicketId);

  if (!sel) {
    detailBox.innerHTML = `
      <div class="text-center py-24 space-y-3 text-slate-400">
        <svg class="w-12 h-12 mx-auto stroke-1 text-slate-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
        <h3 class="text-sm font-bold text-slate-600">Pilih Tiket dari Daftar di Sebelah Kiri</h3>
        <p class="text-xs text-slate-400 max-w-sm mx-auto">
          Klik salah satu tiket untuk meninjau data pelapor, koordinat keluhan, riwayat log, serta memperbarui status penanganan secara langsung.
        </p>
      </div>
    `;
    return;
  }

  detailBox.innerHTML = `
    <div class="space-y-6">
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
        <div class="space-y-1">
          <div class="flex items-center gap-2">
            <span class="font-mono text-base font-black text-[#0284C7]">${esc(sel.id)}</span>
            <span class="px-2.5 py-0.5 rounded text-xs font-bold ${getTicketStatusClass(sel.status)}">${esc(sel.status)}</span>
          </div>
          <h3 class="text-lg font-bold text-slate-900">${esc(sel.title)}</h3>
          <p class="text-xs text-slate-400">Diajukan pada: ${esc(sel.createdAt)}</p>
        </div>

        <button
          type="button"
          onclick="handleDeleteTicket('${esc(sel.id)}')"
          class="p-2 rounded-xl text-rose-500 hover:bg-rose-50 border border-rose-200 transition-colors self-start sm:self-auto cursor-pointer"
          title="Hapus Tiket"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
        </button>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs bg-slate-50 p-4 rounded-2xl border border-slate-100">
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Identitas Pelapor</span>
          <p class="font-bold text-slate-900">${esc(sel.userName)}</p>
          <p class="text-slate-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
            <span>${esc(sel.userPhone)}</span>
          </p>
        </div>
        <div class="space-y-1.5">
          <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Lokasi &amp; Alamat Aduan</span>
          <p class="font-semibold text-slate-800 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-rose-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
            <span>${esc(sel.district)}</span>
          </p>
          <p class="text-slate-600 text-[11px] leading-relaxed">${esc(sel.address)}</p>
        </div>
      </div>

      <div class="space-y-1.5 text-xs">
        <span class="font-bold text-slate-700 block">Deskripsi Permasalahan:</span>
        <p class="p-4 rounded-xl bg-slate-50 text-slate-800 leading-relaxed border border-slate-100">${esc(sel.description)}</p>
      </div>

      <div class="space-y-3 text-xs">
        <span class="font-bold text-slate-800 block">Riwayat Penanganan Resmi (${(sel.updates || []).length}):</span>
        <div class="space-y-2 max-h-48 overflow-y-auto">
          ${(sel.updates || []).map((up) => `
            <div class="p-3 rounded-xl bg-sky-50/50 border border-sky-100 space-y-1">
              <div class="flex items-center justify-between">
                <span class="font-bold text-[#0284C7]">${esc(up.actor)}</span>
                <span class="text-[10px] text-slate-400">${esc(up.timestamp)}</span>
              </div>
              <p class="text-slate-700 text-[11px]">${esc(up.note)}</p>
            </div>
          `).join('')}
        </div>
      </div>

      <form onsubmit="handleUpdateTicketSubmit(event, '${esc(sel.id)}')" class="pt-4 border-t border-slate-100 space-y-4">
        <h4 class="text-xs font-extrabold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
          <span>Eksekusi Tindakan &amp; Update Status</span>
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
          <div class="space-y-1">
            <label class="font-semibold text-slate-700 block">Ubah Status Tiket</label>
            <select id="dispatchStatusSelect" class="w-full p-2.5 rounded-xl border border-slate-200 font-bold bg-white focus:border-[#0284C7]">
              <option value="BARU" ${sel.status === 'BARU' ? 'selected' : ''}>BARU</option>
              <option value="DIPROSES" ${sel.status === 'DIPROSES' ? 'selected' : ''}>DIPROSES (Regu Teknisi Meluncur)</option>
              <option value="MENUNGGU TINDAK LANJUT" ${sel.status === 'MENUNGGU TINDAK LANJUT' ? 'selected' : ''}>MENUNGGU TINDAK LANJUT</option>
              <option value="SELESAI" ${sel.status === 'SELESAI' ? 'selected' : ''}>SELESAI (Tuntas &amp; Normal)</option>
            </select>
          </div>
          <div class="space-y-1">
            <label class="font-semibold text-slate-700 block">Petugas Pelaksana</label>
            <input id="dispatchActorInput" type="text" value="${esc(adminUserName)}" class="w-full p-2.5 rounded-xl border border-slate-200 bg-white" />
          </div>
        </div>

        <div class="space-y-1 text-xs">
          <label class="font-semibold text-slate-700 block">Catatan Penanganan Lapangan (Terlihat oleh Pelanggan)</label>
          <textarea id="dispatchNoteInput" rows="3" placeholder="Contoh: Petugas Regu Reaksi Cepat 02 telah berada di lokasi Jl. Tan Malaka. Penggantian kran valve 2 inch sedang dilakukan..." class="w-full p-3 rounded-xl border border-slate-200 bg-white focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100"></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-2">
          <button type="submit" class="px-6 py-2.5 rounded-xl text-xs font-bold text-white bg-[#0284C7] hover:bg-[#0369A1] transition-colors shadow-xs flex items-center gap-2 cursor-pointer">
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            <span>Simpan &amp; Kirim Update Resmi</span>
          </button>
        </div>
      </form>
    </div>
  `;
}

async function handleUpdateTicketSubmit(e, ticketId) {
  e.preventDefault();
  const status = document.getElementById('dispatchStatusSelect')?.value || 'DIPROSES';
  const actor = document.getElementById('dispatchActorInput')?.value || adminUserName;
  const note = document.getElementById('dispatchNoteInput')?.value || '';

  const res = await postAdminAction({
    action: 'update_ticket',
    ticketId,
    status,
    actor,
    note
  });
  if (res) {
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

async function handleDeleteTicket(ticketId) {
  const res = await postAdminAction({ action: 'delete_ticket', ticketId });
  if (res) {
    if (selectedTicketId === ticketId) selectedTicketId = null;
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

/* ================= TAB 3: METERAN AI-OCR ================= */
function renderMeterTable() {
  const tbody = document.getElementById('meterTableBody');
  if (!tbody) return;
  const list = adminState.meterVerifications || [];

  tbody.innerHTML = list.map((item) => {
    const srcLabel = item.source === 'mobile_ai_ocr' ? 'Mobile AI-OCR' : (item.source === 'web_mandiri' ? 'Web Mandiri' : 'Petugas Lapangan');
    const confCls = item.ocrConfidence >= 95 ? 'text-emerald-600' : 'text-amber-600';
    const statBadge = item.status === 'terverifikasi' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';
    const statTxt = item.status === 'terverifikasi' ? 'Terverifikasi' : 'Menunggu Validasi';

    const actionHtml = item.status === 'menunggu_validasi'
      ? `
        <button type="button" onclick="handleApproveMeter('${esc(item.id)}')" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-colors cursor-pointer inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span>Setujui</span>
        </button>
        <button type="button" onclick="openMeterCorrectionModal('${esc(item.id)}')" class="px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors cursor-pointer inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
          <span>Koreksi</span>
        </button>
      `
      : `<span class="text-[11px] text-slate-400 font-semibold">Telah Diverifikasi</span>`;

    return `
      <tr class="hover:bg-slate-50/80 transition-colors">
        <td class="p-3.5">
          <span class="font-bold text-slate-900 block">${esc(item.customerName)}</span>
          <span class="font-mono text-slate-500 text-[11px] block">${esc(item.meterNumber)}</span>
          <span class="text-[10px] text-slate-400">${esc(item.tariffCategory)}</span>
        </td>
        <td class="p-3.5">
          <span class="font-semibold text-slate-700 block">${esc(item.date)}</span>
          <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-50 text-[#0284C7] font-semibold inline-block mt-1">${esc(srcLabel)}</span>
        </td>
        <td class="p-3.5 font-mono text-slate-600">${item.lastMonthStand} m³</td>
        <td class="p-3.5 font-mono font-bold text-slate-900 text-sm">${item.recordedStand} m³</td>
        <td class="p-3.5 font-mono font-bold text-[#0284C7] text-sm">+${item.volumeM3} m³</td>
        <td class="p-3.5">
          <div class="flex items-center gap-1.5 font-mono font-semibold text-xs">
            <span class="${confCls}">${item.ocrConfidence}%</span>
          </div>
        </td>
        <td class="p-3.5">
          <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold ${statBadge}">${statTxt}</span>
        </td>
        <td class="p-3.5 text-right space-x-1.5">${actionHtml}</td>
      </tr>
    `;
  }).join('');
}

async function handleApproveMeter(meterId) {
  const res = await postAdminAction({ action: 'approve_meter', meterId });
  if (res) {
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

function openMeterCorrectionModal(meterId) {
  const item = (adminState.meterVerifications || []).find((m) => m.id === meterId);
  if (!item) return;
  selectedMeterId = meterId;

  setText('meterModalTitle', 'Koreksi Stand Meter: ' + item.customerName);
  setText('meterModalNumber', item.meterNumber);
  setText('meterModalLastStand', item.lastMonthStand + ' m³');
  setText('meterModalAiStand', item.recordedStand + ' m³ (' + item.ocrConfidence + '% confidence)');

  const inputStand = document.getElementById('meterCorrectedInput');
  const inputNote = document.getElementById('meterCorrectionNote');
  if (inputStand) inputStand.value = item.recordedStand;
  if (inputNote) inputNote.value = '';

  const modal = document.getElementById('meterCorrectionModal');
  if (modal) modal.classList.remove('hidden');
}

function closeMeterCorrectionModal() {
  const modal = document.getElementById('meterCorrectionModal');
  if (modal) modal.classList.add('hidden');
  selectedMeterId = null;
}

async function handleCorrectMeterSubmit(e) {
  e.preventDefault();
  if (!selectedMeterId) return;
  const correctedStand = parseFloat(document.getElementById('meterCorrectedInput')?.value || '0') || 0;
  const note = document.getElementById('meterCorrectionNote')?.value || '';

  const res = await postAdminAction({
    action: 'correct_meter',
    meterId: selectedMeterId,
    correctedStand,
    note
  });
  if (res) {
    closeMeterCorrectionModal();
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

/* ================= TAB 4: PENGUMUMAN ================= */
function renderAnnouncementList() {
  const box = document.getElementById('announcementListContainer');
  if (!box) return;
  const list = adminState.announcements || [];

  box.innerHTML = list.map((ann) => {
    const catCls =
      ann.category === 'pemeliharaan' ? 'bg-sky-100 text-sky-800' :
      ann.category === 'gangguan_darurat' ? 'bg-rose-100 text-rose-800' :
      ann.category === 'kualitas_air' ? 'bg-teal-100 text-teal-800' :
      'bg-indigo-100 text-indigo-800';
    const statCls =
      ann.status === 'aktif' ? 'bg-emerald-100 text-emerald-800' :
      ann.status === 'dalam_penanganan' ? 'bg-amber-100 text-amber-800' :
      'bg-slate-100 text-slate-700';

    return `
      <div class="p-5 rounded-2xl border border-slate-200/90 bg-white hover:border-sky-300 transition-all space-y-3">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-mono text-xs font-bold text-[#0284C7]">${esc(ann.id)}</span>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${catCls}">
              ${esc(String(ann.category || '').replace('_', ' '))}
            </span>
            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold ${statCls}">
              Status: ${esc(ann.status)}
            </span>
          </div>

          <div class="flex items-center gap-2">
            <button type="button" onclick="handleToggleAnnouncementStatus('${esc(ann.id)}')" class="px-3 py-1 rounded-lg text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors cursor-pointer">
              Ubah Status
            </button>
            <button type="button" onclick="handleDeleteAnnouncement('${esc(ann.id)}')" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 border border-rose-200 transition-colors cursor-pointer" title="Hapus">
              <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            </button>
          </div>
        </div>

        <h3 class="text-sm font-bold text-slate-900">${esc(ann.title)}</h3>
        <p class="text-xs text-slate-600 leading-relaxed">${esc(ann.description)}</p>

        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 text-xs text-slate-700 space-y-1">
          <p><strong>Wilayah Terdampak:</strong> ${esc((ann.districtsAffected || []).join(', '))}</p>
          <p><strong>Saran Warga:</strong> ${esc(ann.actionAdvice)}</p>
          <p><strong>Estimasi Normalisasi:</strong> ${esc(ann.estimatedResolution || 'Sesuai perkembangan teknis')}</p>
        </div>

        <div class="flex items-center justify-between text-[11px] text-slate-400 pt-1 border-t border-slate-100">
          <span>Diterbitkan: ${esc(ann.publishedAt)}</span>
          <span>Sumber: ${esc(ann.source)}</span>
        </div>
      </div>
    `;
  }).join('');
}

function openAddAnnouncementModal() {
  const modal = document.getElementById('addAnnouncementModal');
  if (modal) modal.classList.remove('hidden');
}

function closeAddAnnouncementModal() {
  const modal = document.getElementById('addAnnouncementModal');
  if (modal) modal.classList.add('hidden');
}

async function handleCreateAnnouncementSubmit(e) {
  e.preventDefault();
  const title = (document.getElementById('annTitleInput')?.value || '').trim();
  const category = document.getElementById('annCategorySelect')?.value || 'pemeliharaan';
  const estimatedResolution = (document.getElementById('annEstimatedInput')?.value || '').trim();
  const description = (document.getElementById('annDescriptionInput')?.value || '').trim();
  const actionAdvice = (document.getElementById('annAdviceInput')?.value || '').trim();

  const checkedDistricts = [];
  document.querySelectorAll('.ann-district-cb:checked').forEach((cb) => {
    checkedDistricts.push(cb.value);
  });

  if (!title || !description) {
    showAdminToast('Judul dan deskripsi pengumuman wajib diisi.');
    return;
  }

  const res = await postAdminAction({
    action: 'add_announcement',
    title,
    category,
    estimatedResolution,
    description,
    actionAdvice,
    districtsAffected: checkedDistricts.length > 0 ? checkedDistricts : ['Payakumbuh Barat']
  });
  if (res) {
    document.getElementById('addAnnouncementForm')?.reset();
    closeAddAnnouncementModal();
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

async function handleToggleAnnouncementStatus(annId) {
  const res = await postAdminAction({ action: 'toggle_announcement', annId });
  if (res) {
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

async function handleDeleteAnnouncement(annId) {
  const res = await postAdminAction({ action: 'delete_announcement', annId });
  if (res) {
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

/* ================= TAB 5: SCADA RESERVOIR ================= */
function renderReservoirCards() {
  const grid = document.getElementById('reservoirCardsGrid');
  if (!grid) return;
  const list = adminState.reservoirs || [];

  grid.innerHTML = list.map((res) => {
    const statBadge = res.status === 'optimal' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800';
    const tankGrad = res.waterLevelPct >= 80 ? 'bg-gradient-to-r from-sky-400 to-[#0284C7]' : 'bg-amber-500';
    return `
      <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-2xs space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <div>
            <h3 class="font-extrabold text-slate-900 text-sm">${esc(res.name)}</h3>
            <p class="text-[11px] text-slate-400 flex items-center gap-1 mt-0.5">
              <svg class="w-3 h-3 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
              <span>${esc(res.location)}</span>
            </p>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-bold ${statBadge}">Level: ${res.waterLevelPct}%</span>
        </div>

        <div class="space-y-1.5">
          <div class="flex items-center justify-between text-xs font-semibold">
            <span class="text-slate-600">Level Ketinggian Air</span>
            <span class="font-mono text-slate-900 font-bold">${res.waterLevelPct}% Kapasitas</span>
          </div>
          <div class="w-full bg-slate-100 h-3 rounded-full overflow-hidden p-0.5 border border-slate-200">
            <div class="h-full rounded-full transition-all duration-700 ${tankGrad}" style="width: ${res.waterLevelPct}%"></div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
          <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Debit Masuk / Keluar</span>
            <span class="font-mono font-bold text-slate-900 text-sm">${res.inflowDebitLps} / ${res.outflowDebitLps} <span class="text-[10px] text-slate-500">L/s</span></span>
          </div>
          <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Tekanan Jaringan</span>
            <span class="font-mono font-bold text-slate-900 text-sm">${res.pressureBar} <span class="text-[10px] text-slate-500">Bar</span></span>
          </div>
          <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold block uppercase">Kekeruhan (NTU)</span>
            <span class="font-mono font-bold text-emerald-700 text-sm">${res.turbidityNtu} NTU <span class="text-[10px] text-emerald-600">(Jernih)</span></span>
          </div>
          <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100">
            <span class="text-[10px] text-slate-400 font-semibold block uppercase">pH / Sisa Klorin</span>
            <span class="font-mono font-bold text-slate-900 text-sm">pH ${res.phLevel} · ${res.chlorineMgL} mg/L</span>
          </div>
        </div>

        <div class="p-4 rounded-2xl bg-sky-50/50 border border-sky-100 space-y-2">
          <div class="flex items-center justify-between text-xs">
            <label class="font-bold text-slate-700 flex items-center gap-1.5">
              <svg class="w-3.5 h-3.5 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="4" y1="21" y2="14"/><line x1="4" x2="4" y1="10" y2="3"/><line x1="12" x2="12" y1="21" y2="12"/><line x1="12" x2="12" y1="8" y2="3"/><line x1="20" x2="20" y1="21" y2="16"/><line x1="20" x2="20" y1="12" y2="3"/><line x1="2" x2="6" y1="14" y2="14"/><line x1="10" x2="14" y1="8" y2="8"/><line x1="18" x2="22" y1="16" y2="16"/></svg>
              <span>Bukaan Katup Motorik Transmisi</span>
            </label>
            <span id="valveLabel-${esc(res.id)}" class="font-mono font-black text-[#0284C7]">${res.valveOpenPct}% Terbuka</span>
          </div>
          <input
            type="range"
            min="0"
            max="100"
            value="${res.valveOpenPct}"
            oninput="previewValveValue('${esc(res.id)}', this.value)"
            onchange="commitValveChange('${esc(res.id)}', this.value)"
            class="w-full h-2 bg-slate-200 rounded-lg appearance-none cursor-pointer accent-[#0284C7]"
          />
          <div class="flex justify-between text-[10px] text-slate-400 font-mono">
            <span>0% (Tutup)</span>
            <span>50%</span>
            <span>100% (Maksimal)</span>
          </div>
        </div>
      </div>
    `;
  }).join('');
}

function previewValveValue(resId, val) {
  const el = document.getElementById('valveLabel-' + resId);
  if (el) el.textContent = val + '% Terbuka';
}

async function commitValveChange(resId, val) {
  const res = await postAdminAction({
    action: 'update_valve',
    resId,
    valveOpenPct: parseInt(val, 10)
  });
  if (res) {
    renderIkhtisarLists();
    renderAuditLogs();
    showAdminToast(res.message);
  }
}

/* ================= TAB 6: PELANGGAN ================= */
function filterCustomers() {
  const searchVal = (document.getElementById('customerSearchInput')?.value || '').trim().toLowerCase();
  const distVal = document.getElementById('customerDistrictSelect')?.value || 'semua';
  const tbody = document.getElementById('customerTableBody');
  if (!tbody) return;

  const filtered = adminCustomers.filter((usr) => {
    const mDist = distVal === 'semua' || usr.district === distVal;
    const mSearch = !searchVal ||
      usr.name.toLowerCase().includes(searchVal) ||
      usr.meterNumber.toLowerCase().includes(searchVal) ||
      usr.address.toLowerCase().includes(searchVal);
    return mDist && mSearch;
  });

  if (filtered.length === 0) {
    tbody.innerHTML = '<tr><td colspan="8" class="p-6 text-center text-slate-400 text-xs">Tidak ada data pelanggan yang sesuai pencarian.</td></tr>';
    return;
  }

  tbody.innerHTML = filtered.map((usr) => `
    <tr class="hover:bg-slate-50/80 transition-colors">
      <td class="p-3.5 font-bold text-slate-900">${esc(usr.name)}</td>
      <td class="p-3.5 font-mono font-bold text-[#0284C7]">${esc(usr.meterNumber)}</td>
      <td class="p-3.5 font-semibold text-slate-700">${esc(usr.tariffCategory)}</td>
      <td class="p-3.5 text-slate-600 max-w-[200px] truncate">${esc(usr.district)}</td>
      <td class="p-3.5 font-mono font-bold text-slate-900">${usr.currentMonthM3} m³</td>
      <td class="p-3.5 font-mono font-bold text-slate-900">${formatRupiah(usr.currentBillIdr)}</td>
      <td class="p-3.5">
        <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">
          <svg class="w-2.5 h-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
          <span>${esc(usr.lastBillStatus)}</span>
        </span>
      </td>
      <td class="p-3.5 text-right">
        <button type="button" onclick="handleDownloadSlip('${esc(usr.name)}')" class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#0284C7] bg-sky-50 hover:bg-sky-100 transition-colors cursor-pointer inline-flex items-center gap-1">
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
          <span>Slip Rekening</span>
        </button>
      </td>
    </tr>
  `).join('');
}

async function handleDownloadSlip(customerName) {
  const res = await postAdminAction({ action: 'download_slip', customerName });
  if (res) {
    renderAuditLogs();
    showAdminToast(res.message);
  }
}

/* ================= TAB 7: AUDIT TRAIL ================= */
function renderAuditLogs() {
  const box = document.getElementById('auditLogsContainer');
  if (!box) return;
  const logs = adminState.auditLogs || [];

  box.innerHTML = logs.map((log) => `
    <div class="py-3.5 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
      <div class="space-y-0.5">
        <div class="flex items-center gap-2 flex-wrap">
          <span class="font-mono text-[10px] text-slate-400">${esc(log.timestamp)}</span>
          <span class="font-bold text-[#0284C7]">${esc(log.actor)}</span>
          <span class="px-2 py-0.5 rounded font-mono text-[10px] font-bold bg-slate-100 text-slate-700">${esc(log.action)}</span>
        </div>
        <p class="text-slate-800 font-semibold">${esc(log.details)}</p>
      </div>
      <span class="font-mono text-[11px] text-slate-500 shrink-0">Target: ${esc(log.target)}</span>
    </div>
  `).join('');
}

/* ================= SYNC DATA ================= */
async function syncAdminData() {
  const res = await postAdminAction({ action: 'sync_data' });
  if (res) {
    renderAllAdminSections();
    showAdminToast(res.message);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  renderAllAdminSections();
  filterCustomers();
});
