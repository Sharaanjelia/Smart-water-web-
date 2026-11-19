@php
if (!headers_sent()) {
    header('Location: ' . url('/user' . (request()->getQueryString() ? '?' . request()->getQueryString() : '')), true, 302);
    exit;
}
@endphp
<script>
    window.location.replace("{{ url('/user' . (request()->getQueryString() ? '?' . request()->getQueryString() : '')) }}");
</script>
@extends('layouts.app')

@section('title', 'Profil Pelanggan')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/beranda') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali</span>
        </a>
        <h2 class="text-sm font-extrabold text-slate-800">Profil Saya</h2>
        <div class="w-12"></div>
    </div>

    <!-- KARTU IDENTITAS AKUN PELANGGAN -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
        <!-- Avatar Foto -->
        <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-[#0284C7] to-cyan-400 p-1 shadow-md shrink-0">
            <div class="w-full h-full rounded-full bg-white flex items-center justify-center font-black text-2xl text-[#0284C7] uppercase">
                {{ strtoupper(substr($currentUser['fullName'] ?? ($currentUser['name'] ?? 'B'), 0, 2)) }}
            </div>
        </div>

        <div class="space-y-1.5 flex-1">
            <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
                {{ $currentUser['fullName'] ?? 'Budi Santoso' }}
            </h1>
            <p class="text-xs font-mono font-bold text-slate-500">
                ID Pelanggan: <span class="text-slate-900">{{ $currentUser['customerId'] ?? '1234567890' }}</span> • No. Meter: <span class="text-slate-900">{{ $currentUser['meterNumber'] ?? '01000' }}</span>
            </p>
            <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 pt-1">
                <span class="px-3 py-1 rounded-full bg-sky-50 text-[#0284C7] border border-sky-200 font-bold text-xs">
                    {{ $currentUser['zone'] ?? 'Zona DIST-A Sektor Utara' }}
                </span>
                <span class="px-3 py-1 rounded-full bg-slate-100 text-slate-700 font-semibold text-xs">
                    Golongan 2A (Rumah Tangga)
                </span>
            </div>
        </div>
    </div>

    <!-- GRUP MENU NAVIGASI 1 -->
    <div class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs divide-y divide-slate-100 text-xs">
        
        <!-- Item 1: Dompet / Saldo Operasional -->
        <a href="{{ url('/wallet') }}" class="p-4.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
                    </svg>
                </div>
                <div>
                    <strong class="text-sm font-bold text-slate-900 block">Dompet & Saldo Operasional</strong>
                    <span class="text-slate-500 text-[11px]">Saldo restitusi kelebihan bayar & penarikan dana</span>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span data-swc="wallet-balance" class="font-mono font-bold text-slate-900 text-xs">Rp 2.500.000</span>
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </div>
        </a>

        <!-- Item 2: Kondisi Layanan Air -->
        <a href="{{ url('/water-condition') }}" class="p-4.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-sky-50 text-[#0284C7] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                    </svg>
                </div>
                <div>
                    <strong class="text-sm font-bold text-slate-900 block">Kondisi Layanan Air</strong>
                    <span class="text-slate-500 text-[11px]">Tekanan pipa zona & ketersediaan tandon utama</span>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>

        <!-- Item 3: Pengaturan Aplikasi -->
        <a href="{{ url('/settings') }}" class="p-4.5 flex items-center justify-between hover:bg-slate-50 transition-colors">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </div>
                <div>
                    <strong class="text-sm font-bold text-slate-900 block">Pengaturan Aplikasi</strong>
                    <span class="text-slate-500 text-[11px]">Notifikasi, ganti PIN, dan preferensi bahasa</span>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>

    </div>

    <!-- GRUP MENU NAVIGASI 2 -->
    <div class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs divide-y divide-slate-100 text-xs">
        
        <!-- Item 1: Pusat Bantuan & FAQ -->
        <button
            type="button"
            onclick="showHelpModal()"
            class="w-full p-4.5 flex items-center justify-between hover:bg-slate-50 transition-colors cursor-pointer text-left"
        >
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                    </svg>
                </div>
                <div>
                    <strong class="text-sm font-bold text-slate-900 block">Pusat Bantuan & Call Center</strong>
                    <span class="text-slate-500 text-[11px]">Telepon darurat kebocoran pipa 0752-92044 / 0812-7389-4902</span>
                </div>
            </div>
            <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </button>

        <!-- Item 2: Keluar dari Akun (Dialog Logout) -->
        <button
            type="button"
            onclick="showLogoutModal()"
            class="w-full p-4.5 flex items-center justify-between hover:bg-rose-50/50 transition-colors cursor-pointer text-left text-rose-600"
        >
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
                    </svg>
                </div>
                <div>
                    <strong class="text-sm font-bold text-rose-600 block">Keluar dari Akun</strong>
                    <span class="text-rose-400 text-[11px]">Akhiri sesi pelanggan pada browser ini</span>
                </div>
            </div>
            <svg class="w-4 h-4 text-rose-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </button>

    </div>

</div>

<!-- Modal Logout Confirmation -->
<div id="logoutModal" class="hidden fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 space-y-4 text-center shadow-2xl">
        <div class="w-12 h-12 mx-auto rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/>
            </svg>
        </div>
        <div class="space-y-1">
            <h3 class="text-base font-extrabold text-slate-900">Keluar dari Akun?</h3>
            <p class="text-xs text-slate-500">Anda akan keluar dari sesi pelanggan saat ini. Anda dapat masuk kembali kapan saja.</p>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="button" onclick="hideLogoutModal()" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-700 hover:bg-slate-100">
                Batal
            </button>
            <a href="{{ url('/logout') }}" class="flex-1 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition-colors">
                Ya, Keluar
            </a>
        </div>
    </div>
</div>

<script>
function showLogoutModal() {
    document.getElementById('logoutModal').classList.remove('hidden');
}
function hideLogoutModal() {
    document.getElementById('logoutModal').classList.add('hidden');
}
function showHelpModal() {
    alert('Call Center PDAM Tirta Sago: 0752-92044 atau WhatsApp Layanan: 0812-7389-4902. Siap melayani laporan 24 jam.');
}
</script>
@endsection
