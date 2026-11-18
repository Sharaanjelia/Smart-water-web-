/**
 * Interactive Scripts untuk Halaman Login & Daftar PHP Standalone
 * Smart Water Kota Payakumbuh (Sesuai AuthPage.tsx - 4 Mode Lengkap + Frosted Glass)
 */

let currentAuthMode = window.SWC_INITIAL_MODE || 'login';
let currentUser = window.SWC_CURRENT_USER || null;

function escapeHtml(str) {
  return String(str ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;');
}

// ---------------------------------------------------------------------------
// 1. AUTH MODE SWITCHER (login | register | forgot-password | admin-login)
// ---------------------------------------------------------------------------
function switchAuthMode(mode) {
  if (mode === 'admin-login') {
    window.location.href = getAppBaseUrl('/admin-login');
    return;
  }
  currentAuthMode = mode;
  ['login', 'register', 'forgot-password'].forEach((m) => {
    const panel = document.getElementById('auth-mode-' + m);
    const tabBtn = document.getElementById('tab-btn-' + m);
    if (panel) {
      if (m === mode) {
        panel.classList.remove('hidden');
      } else {
        panel.classList.add('hidden');
      }
    }
    if (tabBtn) {
      if (m === mode) {
        tabBtn.className = 'py-2.5 px-2 rounded-xl transition-all cursor-pointer bg-white text-slate-900 shadow-xs font-bold';
      } else {
        tabBtn.className = 'py-2.5 px-2 rounded-xl transition-all cursor-pointer text-slate-500 hover:text-slate-900 font-semibold truncate';
      }
    }
  });
  hideAllErrors();
  if (mode !== 'forgot-password') {
    document.getElementById('forgot-form-wrap')?.classList.remove('hidden');
    document.getElementById('forgot-success-wrap')?.classList.add('hidden');
  }
}

function hideAllErrors() {
  ['login-error', 'reg-error', 'forgot-error', 'admin-error'].forEach((id) => {
    const el = document.getElementById(id);
    if (el) el.classList.add('hidden');
  });
}

function showError(boxId, textId, message) {
  const box = document.getElementById(boxId);
  const textEl = document.getElementById(textId);
  if (box && textEl) {
    textEl.textContent = message;
    box.classList.remove('hidden');
  }
}

function togglePasswordVisibility(inputId, eyeOpenId, eyeClosedId) {
  const input = document.getElementById(inputId);
  const openIcon = document.getElementById(eyeOpenId);
  const closedIcon = document.getElementById(eyeClosedId);
  if (!input) return;

  if (input.type === 'password') {
    input.type = 'text';
    openIcon?.classList.add('hidden');
    closedIcon?.classList.remove('hidden');
  } else {
    input.type = 'password';
    openIcon?.classList.remove('hidden');
    closedIcon?.classList.add('hidden');
  }
}

// ---------------------------------------------------------------------------
// 2. LOGGED-IN BANNER UPDATE & TOAST NOTIFICATION
// ---------------------------------------------------------------------------
function getAppBaseUrl(path) {
  const pathname = window.location.pathname;
  if (pathname.includes('/smart-water/public')) {
    return '/smart-water/public' + path;
  }
  if (pathname.includes('/smart-water')) {
    return '/smart-water' + path;
  }
  return path;
}

function showToastNotification(message, type = 'success') {
  let toast = document.getElementById('swc-toast-notice');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'swc-toast-notice';
    document.body.appendChild(toast);
  }

  const isSuccess = type === 'success';
  toast.className = isSuccess
    ? 'fixed top-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold border border-emerald-300/80 bg-emerald-600 text-white backdrop-blur-md transition-all duration-300'
    : 'fixed top-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-2xl shadow-2xl text-sm font-semibold border border-sky-300/80 bg-sky-600 text-white backdrop-blur-md transition-all duration-300';

  toast.innerHTML = `
    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
      ${isSuccess ? '<path d="M20 6L9 17l-5-5"/>' : '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>'}
    </svg>
    <span>${escapeHtml(message)}</span>
  `;
  toast.style.opacity = '1';
  toast.style.transform = 'translateY(0)';

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-10px)';
  }, 2200);
}

function renderSessionBanner() {
  const banner = document.getElementById('auth-session-banner');
  if (!banner) return;
  if (!currentUser) {
    banner.classList.add('hidden');
    return;
  }

  const nameEl = document.getElementById('session-user-name');
  const roleEl = document.getElementById('session-user-role');
  const metaEl = document.getElementById('session-user-meta');
  const primaryLinkEl = document.getElementById('session-primary-link');
  if (nameEl) nameEl.textContent = currentUser.name || currentUser.fullName || 'Warga Payakumbuh';
  if (roleEl) roleEl.textContent = currentUser.isAdmin ? 'Petugas / Admin PDAM Tirta Sago' : (currentUser.role || 'Pelanggan Smart Water');
  if (metaEl) metaEl.textContent = `${currentUser.email || ''} · ${currentUser.meterNumber || ''}`;
  if (primaryLinkEl) {
    if (currentUser.isAdmin) {
      primaryLinkEl.setAttribute('href', getAppBaseUrl('/admin-dashboard'));
      primaryLinkEl.innerHTML = 'Buka Panel Admin SCADA &rarr;';
    } else {
      primaryLinkEl.setAttribute('href', getAppBaseUrl('/report-meter'));
      primaryLinkEl.innerHTML = 'Buka Portal Pelanggan &rarr;';
    }
  }

  banner.classList.remove('hidden');
}

// ---------------------------------------------------------------------------
// 3. FORM HANDLERS (PHP API auth.php)
// ---------------------------------------------------------------------------
async function handleLoginSubmit(event) {
  event.preventDefault();
  hideAllErrors();

  const identifier = document.getElementById('login-identifier')?.value.trim() || '';
  const password = document.getElementById('login-password')?.value || '';

  if (!identifier) {
    showError('login-error', 'login-error-text', 'Silakan masukkan alamat email atau nomor telepon Anda.');
    return;
  }
  if (!password) {
    showError('login-error', 'login-error-text', 'Silakan masukkan kata sandi akun Anda.');
    return;
  }

  const btn = document.getElementById('login-submit-btn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memverifikasi...';
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'login', identifier, password }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      showError('login-error', 'login-error-text', data.error || 'Gagal masuk ke akun.');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Masuk ke Akun';
      }
      return;
    }
    currentUser = data.user;
    renderSessionBanner();
    showToastNotification('Berhasil masuk! Mengalihkan ke sistem...', 'success');
    
    const targetUrl = data.redirect || (currentUser.isAdmin ? getAppBaseUrl('/admin-dashboard') : getAppBaseUrl('/report-meter'));
    setTimeout(() => {
      window.location.href = targetUrl;
    }, 600);
  } catch (e) {
    showError('login-error', 'login-error-text', 'Gagal menghubungi server PHP. Pastikan server PHP lokal aktif.');
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Masuk ke Akun';
    }
  }
}

async function handleGoogleSignIn() {
  hideAllErrors();
  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'google_signin' }),
    });
    const data = await res.json();
    if (data.ok && data.user) {
      currentUser = data.user;
      renderSessionBanner();
      showToastNotification('Masuk dengan Google berhasil! Mengalihkan...', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || getAppBaseUrl('/report-meter');
      }, 600);
    }
  } catch (e) {
    showError('login-error', 'login-error-text', 'Simulasi Google Sign-In memerlukan server PHP aktif.');
  }
}

async function handleRegisterSubmit(event) {
  event.preventDefault();
  hideAllErrors();

  const name = document.getElementById('reg-name')?.value.trim() || '';
  const email = document.getElementById('reg-email')?.value.trim() || '';
  const phone = document.getElementById('reg-phone')?.value.trim() || '';
  const password = document.getElementById('reg-password')?.value || '';
  const confirmPassword = document.getElementById('reg-confirm-password')?.value || '';
  const agreeTerms = Boolean(document.getElementById('reg-agree-terms')?.checked);

  if (!name) {
    showError('reg-error', 'reg-error-text', 'Nama lengkap wajib diisi.');
    return;
  }
  if (!email || !email.includes('@')) {
    showError('reg-error', 'reg-error-text', 'Alamat email valid wajib diisi.');
    return;
  }
  if (!password || password.length < 6) {
    showError('reg-error', 'reg-error-text', 'Kata sandi minimal 6 karakter.');
    return;
  }
  if ($password !== confirmPassword && password !== confirmPassword) {
    showError('reg-error', 'reg-error-text', 'Konfirmasi kata sandi tidak cocok.');
    return;
  }
  if (!agreeTerms) {
    showError('reg-error', 'reg-error-text', 'Anda wajib menyetujui Syarat & Ketentuan dan Kebijakan Privasi.');
    return;
  }

  const btn = document.getElementById('reg-submit-btn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Mendaftarkan Akun...';
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        action: 'register',
        name,
        email,
        phone,
        password,
        confirmPassword,
        agreeTerms,
      }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      showError('reg-error', 'reg-error-text', data.error || 'Gagal mendaftar.');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Daftar Sekarang';
      }
      return;
    }
    currentUser = data.user;
    renderSessionBanner();
    showToastNotification('Pendaftaran berhasil! Selamat datang di Smart Water.', 'success');
    setTimeout(() => {
      window.location.href = data.redirect || getAppBaseUrl('/report-meter');
    }, 600);
  } catch (e) {
    showError('reg-error', 'reg-error-text', 'Gagal menghubungi server PHP. Pastikan server PHP lokal aktif.');
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Daftar Sekarang';
    }
  }
}

async function handleForgotSubmit(event) {
  event.preventDefault();
  hideAllErrors();

  const email = document.getElementById('forgot-email')?.value.trim() || '';
  if (!email || !email.includes('@')) {
    showError('forgot-error', 'forgot-error-text', 'Silakan masukkan alamat email yang valid.');
    return;
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'forgot_password', email }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      showError('forgot-error', 'forgot-error-text', data.error || 'Gagal mengirim tautan reset.');
      return;
    }
    const targetEmailEl = document.getElementById('forgot-success-email');
    if (targetEmailEl) targetEmailEl.textContent = email;
    document.getElementById('forgot-form-wrap')?.classList.add('hidden');
    document.getElementById('forgot-success-wrap')?.classList.remove('hidden');
    showToastNotification('Instruksi pemulihan berhasil dikirim.', 'info');
  } catch (e) {
    showError('forgot-error', 'forgot-error-text', 'Gagal menghubungi server PHP.');
  }
}

async function handleAdminLoginSubmit(event) {
  event.preventDefault();
  hideAllErrors();

  const identifier = document.getElementById('admin-identifier')?.value.trim() || '';
  const password = document.getElementById('admin-password')?.value || '';

  if (!identifier) {
    showError('admin-error', 'admin-error-text', 'Masukkan email dinas atau NIP petugas Anda.');
    return;
  }
  if (!password) {
    showError('admin-error', 'admin-error-text', 'Masukkan kata sandi akun petugas.');
    return;
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_login', identifier, password }),
    });
    const data = await res.json();
    if (!res.ok || !data.ok) {
      showError('admin-error', 'admin-error-text', data.error || 'Gagal masuk portal admin.');
      return;
    }
    currentUser = data.user;
    renderSessionBanner();
    showToastNotification('Login Petugas PDAM berhasil! Membuka SCADA...', 'success');
    setTimeout(() => {
      window.location.href = data.redirect || getAppBaseUrl('/admin-dashboard');
    }, 600);
  } catch (e) {
    showError('admin-error', 'admin-error-text', 'Gagal menghubungi server PHP.');
  }
}

async function handleInstantCustomerDemo() {
  hideAllErrors();
  const idInput = document.getElementById('login-identifier');
  const pwInput = document.getElementById('login-password');
  if (idInput) idInput.value = 'budi.santoso@warga.payakumbuh.go.id';
  if (pwInput) pwInput.value = 'Pelanggan123!';

  const btn = document.getElementById('login-submit-btn');
  if (btn) {
    btn.disabled = true;
    btn.textContent = 'Memverifikasi...';
  }

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'login', identifier: 'budi.santoso@warga.payakumbuh.go.id', password: 'Pelanggan123!' }),
    });
    const data = await res.json();
    if (data.ok && data.user) {
      currentUser = data.user;
      renderSessionBanner();
      showToastNotification('Berhasil masuk sebagai Budi Santoso!', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || getAppBaseUrl('/report-meter');
      }, 500);
    } else {
      showError('login-error', 'login-error-text', data.error || 'Gagal masuk akun.');
      if (btn) {
        btn.disabled = false;
        btn.textContent = 'Masuk';
      }
    }
  } catch (e) {
    showError('login-error', 'login-error-text', 'Gagal memproses verifikasi autentikasi.');
    if (btn) {
      btn.disabled = false;
      btn.textContent = 'Masuk';
    }
  }
}

async function handleInstantAdminDemo() {
  hideAllErrors();
  const idInput = document.getElementById('admin-identifier');
  const pwInput = document.getElementById('admin-password');
  if (idInput) idInput.value = 'ahmad.pratama@tirtasago.id';
  if (pwInput) pwInput.value = 'Petugas123!';

  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'admin_login', identifier: 'ahmad.pratama@tirtasago.id', password: 'Petugas123!' }),
    });
    const data = await res.json();
    if (data.ok && data.user) {
      currentUser = data.user;
      renderSessionBanner();
      showToastNotification('Login Petugas PDAM berhasil! Membuka SCADA...', 'success');
      setTimeout(() => {
        window.location.href = data.redirect || getAppBaseUrl('/admin-dashboard');
      }, 600);
    } else {
      showError('admin-error', 'admin-error-text', data.error || 'Gagal masuk portal petugas.');
    }
  } catch (e) {
    showError('admin-error', 'admin-error-text', 'Gagal menghubungi server autentikasi.');
  }
}

async function handleLogoutSession() {
  try {
    const res = await fetch(getAppBaseUrl('/api/auth.php'), {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ action: 'logout' }),
    });
    const data = await res.json();
    currentUser = null;
    renderSessionBanner();
    showToastNotification('Anda telah keluar dari akun.', 'info');
    setTimeout(() => {
      window.location.href = (data && data.redirect) ? data.redirect : getAppBaseUrl('/login');
    }, 500);
  } catch (e) {
    window.location.href = getAppBaseUrl('/logout');
  }
}

// ---------------------------------------------------------------------------
// 4. LEGAL / HELP MODAL
// ---------------------------------------------------------------------------
function openLegalModal(title) {
  const modal = document.getElementById('auth-legal-modal');
  const titleEl = document.getElementById('auth-legal-modal-title');
  if (!modal || !titleEl) return;
  titleEl.textContent = title;
  modal.classList.remove('hidden');
}

function closeLegalModal() {
  document.getElementById('auth-legal-modal')?.classList.add('hidden');
}

// ---------------------------------------------------------------------------
// 5. TIRTABOT AI CHATBOT WIDGET
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
    content: 'Halo! Saya **TirtaBot**, asisten cerdas Komunitas Smart Water Kota Payakumbuh.\n\nAda yang bisa saya bantu seputar proses masuk, pendaftaran akun pelanggan, atau reset password?',
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
  switchAuthMode(currentAuthMode);
  renderSessionBanner();
  loadChatHistory();
  renderChatMessages();
});
