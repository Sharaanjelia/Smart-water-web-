@extends('layouts.app')

@section('title', 'Rincian Lembar Tagihan Rekening')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ url('/bill') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Tagihan</span>
        </a>
        <span class="text-xs font-mono font-bold text-slate-400">FAKTUR ELEKTRONIK</span>
    </div>

    <!-- HEADER GRADIENT RINGKASAN TAGIHAN (#0062D6 ke #0284C7) -->
    <div class="bg-gradient-to-r from-[#0062D6] to-[#0284C7] rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-sky-600/20 space-y-4 relative overflow-hidden">
        <div class="flex items-center justify-between border-b border-white/20 pb-4">
            <div>
                <span class="text-[11px] font-bold text-sky-200 uppercase tracking-wider block">Periode Rekening</span>
                <h2 class="text-xl sm:text-2xl font-black text-white">April 2025</h2>
            </div>
            <span id="detailStatusBadge" class="px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-black tracking-wider border border-white/30">
                BELUM BAYAR
            </span>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
            <div>
                <span class="text-xs text-sky-100 block">Total Tagihan Bersih:</span>
                <span class="text-3xl sm:text-4xl font-mono font-black text-white tracking-tight">
                    Rp 150.800
                </span>
            </div>
            <span class="text-xs text-sky-100 font-medium">
                Batas Jatuh Tempo: <strong class="text-white">20 April 2025</strong>
            </span>
        </div>
    </div>

    <!-- KARTU 1: DATA SAMBUNGAN PELANGGAN -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
            <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            <span>Data Sambungan Pelanggan</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
            <div>
                <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                <strong class="text-slate-900 font-bold text-sm">Budi Santoso</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">ID Pelanggan (Nomor Sambungan)</span>
                <strong class="text-slate-900 font-mono font-bold">1234567890</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Nomor Seri Meter</span>
                <strong class="text-slate-900 font-mono font-bold">01000</strong>
            </div>
            <div>
                <span class="text-slate-400 block text-[11px]">Zona Distribusi</span>
                <strong class="text-slate-900 font-bold">DIST-A Sektor Utara</strong>
            </div>
            <div class="sm:col-span-2">
                <span class="text-slate-400 block text-[11px]">Golongan Tarif</span>
                <strong class="text-slate-900 font-bold">2A - Rumah Tangga Menengah (PDAM Tirta Sago)</strong>
            </div>
        </div>
    </div>

    <!-- KARTU 2: CATATAN METERAN AIR -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
            <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
            </svg>
            <span>Catatan Meteran Air</span>
        </div>

        <div class="grid grid-cols-3 gap-3 text-center text-xs">
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <span class="text-slate-400 block text-[11px]">Meter Lalu</span>
                <span class="font-mono font-bold text-slate-800 text-sm">00981 m³</span>
            </div>
            <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                <span class="text-slate-400 block text-[11px]">Meter Kini</span>
                <span class="font-mono font-bold text-slate-800 text-sm">01000 m³</span>
            </div>
            <div class="p-3 rounded-2xl bg-sky-50 border border-sky-100 space-y-1">
                <span class="text-[#0284C7] block text-[11px] font-bold">Pemakaian</span>
                <span class="font-mono font-black text-[#0284C7] text-base">19.0 m³</span>
            </div>
        </div>
    </div>

    <!-- KARTU 3: RINCIAN PERHITUNGAN BIAYA -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
            <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="3" rx="2"/><line x1="8" x2="16" y1="8" y2="8"/><line x1="8" x2="16" y1="12" y2="12"/><line x1="8" x2="12" y1="16" y2="16"/>
            </svg>
            <span>Rincian Perhitungan Biaya Rekening</span>
        </div>

        <div class="space-y-2.5 text-xs">
            <div class="flex justify-between text-slate-600">
                <span>Tarif Pemakaian Air (19.0 m³ × Rp 7.800)</span>
                <span class="font-mono font-bold text-slate-900">Rp 148.200</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Biaya Beban & Administrasi</span>
                <span class="font-mono font-bold text-slate-900">Rp 2.600</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Pemeliharaan Pipa & Kalibrasi</span>
                <span class="font-mono font-bold text-slate-900">Rp 0</span>
            </div>
            <div class="flex justify-between text-slate-600">
                <span>Denda Keterlambatan</span>
                <span class="font-mono font-bold text-emerald-700">Rp 0</span>
            </div>
            <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                <strong class="text-sm font-black text-slate-900">Total Tagihan Rekening</strong>
                <span class="text-xl font-mono font-black text-[#0284C7]">Rp 150.800</span>
            </div>
        </div>
    </div>

    <!-- Tombol Pembayaran / Bukti Lunas -->
    <div class="pt-2" id="detailCtaWrapper">
        <a
            id="detailCtaBtn"
            href="{{ url('/payment-method') }}"
            class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#0284C7] to-[#0369A1] hover:from-[#0369A1] hover:to-[#075985] text-white font-extrabold text-sm shadow-lg shadow-sky-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
        >
            <span id="detailCtaText">Lanjut ke Pembayaran (Rp 150.800)</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.SWC && window.SWC.isBillPaid('INV-PYK-2025-04-1000')) {
        const badge = document.getElementById('detailStatusBadge');
        const btn = document.getElementById('detailCtaBtn');
        const text = document.getElementById('detailCtaText');

        if (badge) {
            badge.className = 'px-3.5 py-1 rounded-full bg-emerald-500/90 backdrop-blur-md text-white text-xs font-black tracking-wider border border-emerald-300';
            badge.textContent = 'LUNAS (TERVERIFIKASI)';
        }
        if (btn) {
            btn.href = "{{ url('/payment-success') }}";
            btn.className = 'w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer';
        }
        if (text) {
            text.textContent = 'Lihat Bukti Kuitansi Pembayaran Lunas';
        }
    }
});
</script>
@endsection
