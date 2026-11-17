@extends('layouts.app')

@section('title', 'Notifikasi & Informasi')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/beranda') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="flex items-center gap-3">
            <button
                type="button"
                onclick="markAllAsRead()"
                class="text-xs font-bold text-[#0284C7] hover:underline cursor-pointer"
            >
                Tandai Semua Dibaca
            </button>
            <button
                type="button"
                onclick="window.SWC && window.SWC.resetDemoData()"
                class="text-[11px] font-mono text-slate-400 hover:text-rose-500 transition-colors cursor-pointer"
            >
                ↻ Reset Demo
            </button>
        </div>
    </div>

    <!-- Judul Halaman -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Notifikasi & Informasi
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Pemberitahuan terbitnya tagihan rekening air, validasi stand meter AI, dan info operasional pipa.
        </p>
    </div>

    <!-- DAFTAR NOTIFIKASI DINAMIS -->
    <div class="space-y-3" id="notifList">
        <!-- Dirender secara dinamis oleh JavaScript -->
    </div>

</div>

<script>
function renderNotifications() {
    if (!window.SWC) return;
    const notifs = window.SWC.getNotifications();
    const container = document.getElementById('notifList');

    if (!container) return;

    if (notifs.length === 0) {
        container.innerHTML = `
            <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 text-xs text-slate-400">
                Belum ada notifikasi saat ini.
            </div>
        `;
        return;
    }

    container.innerHTML = notifs.map(n => {
        let iconHtml = '';
        if (n.type === 'BILL') {
            iconHtml = `
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 border border-rose-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                </div>
            `;
        } else if (n.type === 'METER') {
            iconHtml = `
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m14 14-4-4"/><path d="M12 6v2"/><path d="M6 12h2"/><path d="M18 12h-2"/>
                    </svg>
                </div>
            `;
        } else {
            iconHtml = `
                <div class="w-10 h-10 rounded-2xl bg-sky-50 text-[#0284C7] border border-sky-200 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                    </svg>
                </div>
            `;
        }

        const linkUrl = n.link ? (n.link.startsWith('http') ? n.link : ("{{ url('/') }}" + n.link.replace(/^\//, ''))) : "{{ url('/bill') }}";

        return `
            <a
                href="${linkUrl}"
                class="block p-5 rounded-3xl border transition-all hover:scale-[1.01] ${!n.isRead ? 'bg-[#EFF6FF] border-sky-200 shadow-xs' : 'bg-white border-slate-200/90'}"
            >
                <div class="flex items-start gap-4">
                    ${iconHtml}
                    <div class="space-y-1 flex-1 text-xs">
                        <div class="flex items-center justify-between gap-2">
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">${n.title}</h3>
                            ${!n.isRead ? '<span class="w-2.5 h-2.5 rounded-full bg-[#0284C7] shrink-0" title="Belum dibaca"></span>' : ''}
                        </div>
                        <p class="text-slate-600 text-xs sm:text-[13px] leading-relaxed">${n.desc}</p>
                        <span class="text-slate-400 font-mono text-[11px] block pt-1">${n.time}</span>
                    </div>
                </div>
            </a>
        `;
    }).join('');
}

function markAllAsRead() {
    if (window.SWC) {
        window.SWC.markAllNotificationsRead();
    }
    renderNotifications();
}

document.addEventListener('DOMContentLoaded', () => {
    renderNotifications();
});

window.addEventListener('swc:state-change', () => {
    renderNotifications();
});
</script>
@endsection
