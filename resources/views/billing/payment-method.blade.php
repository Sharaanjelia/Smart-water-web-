@extends('layouts.app')

@section('title', 'Pilih Metode Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/bill') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Tagihan</span>
        </a>
        <span class="text-xs font-bold text-slate-400">Kanal Pembayaran (Tahap 10B)</span>
    </div>

    <!-- PEMBERITAHUAN KEAMANAN PEMBAYARAN -->
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 flex items-start gap-3 text-xs text-amber-800">
        <svg class="w-5 h-5 text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div class="space-y-0.5">
            <strong class="font-bold block text-amber-900">Pemberitahuan: Fitur Pembayaran Sedang Dipersiapkan</strong>
            <p class="text-amber-700 text-[11px] leading-relaxed">
                Sistem pembayaran resmi berbasis transfer manual dan verifikasi Admin PDAM akan diintegrasikan pada Tahap 10B. Seluruh metode pembayaran di bawah saat ini berstatus non-aktif.
            </p>
        </div>
    </div>

    <!-- HEADER TOTAL TAGIHAN -->
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 rounded-3xl p-6 text-white shadow-md flex items-center justify-between gap-4 border border-slate-800">
        <div>
            <span class="text-xs text-slate-400 uppercase font-bold tracking-wider block">Total yang harus dibayar</span>
            <div class="text-2xl sm:text-3xl font-mono font-black text-white mt-0.5">
                Rp 150.800
            </div>
            <span class="text-[11px] text-cyan-300 font-mono">Invoice: INV-PYK-2025-04-1000 &bull; April 2025</span>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-cyan-500/10 border border-cyan-500/20 flex items-center justify-center text-cyan-400 shrink-0 shadow-inner">
            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
        </div>
    </div>

    <!-- DAFTAR PILIHAN METODE PEMBAYARAN (Radio Option Cards) -->
    <form id="paymentForm" onsubmit="handlePaymentSubmit(event)" class="space-y-3">
        
        <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider px-1">
            Pilihan Metode Pembayaran (Non-Aktif / Tahap 10B)
        </h3>

        <!-- OPSI 1: Saldo Dompet Smart Water (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all relative group shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="wallet" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">Saldo Dompet Smart Water</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Non-Aktif
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Potong langsung dari deposit restitusi rekening air Anda.
                        </p>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-slate-300 text-slate-600 flex items-center justify-center shrink-0 font-black text-sm">
                    👛
                </div>
            </div>
        </label>

        <!-- Opsi 2: Xendit Checkout (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="xendit_checkout" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">Xendit Checkout Multi-Payment</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Segera Hadir
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            QRIS, Semua Bank (BCA, Mandiri, BRI, BNI), OVO, DANA dalam satu pop-up cepat.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 font-bold text-xs">
                    ⚡
                </div>
            </div>
        </label>

        <!-- Opsi 3: QRIS (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="qris" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">QRIS</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Segera Hadir
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pindai QR code via BCA, Mandiri, GoPay, OVO, DANA, ShopeePay, Bank Nagari.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 font-bold text-xs">
                    QR
                </div>
            </div>
        </label>

        <!-- Opsi 4: BCA Virtual Account (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="bca_va" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">BCA Virtual Account</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Segera Hadir
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Transfer otomatis via KlikBCA, m-BCA, atau mesin ATM BCA 24 jam.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 font-black text-xs font-mono">
                    BCA
                </div>
            </div>
        </label>

        <!-- Opsi 5: Mandiri Virtual Account (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="mandiri_va" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">Mandiri Virtual Account</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Segera Hadir
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Transfer melalui aplikasi Livin' by Mandiri atau ATM Mandiri.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 font-black text-xs font-mono">
                    MDR
                </div>
            </div>
        </label>

        <!-- Opsi 6: E-Wallet (Non-Aktif) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-slate-50/70 opacity-75 cursor-not-allowed transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="ewallet" disabled class="mt-1 w-4 h-4 text-slate-400 cursor-not-allowed" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-700">E-Wallet (OVO / DANA / LinkAja)</strong>
                            <span class="px-2 py-0.5 rounded-full bg-slate-200 text-slate-600 text-[10px] font-extrabold uppercase tracking-wide">
                                Segera Hadir
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pembayaran otomatis langsung terpotong dari saldo aplikasi dompet digital Anda.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shrink-0 font-black text-xs">
                    EW
                </div>
            </div>
        </label>

        <!-- Box Keamanan & Enkripsi -->
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-xs text-slate-500">
            <svg class="w-4 h-4 text-slate-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Transaksi aman terenkripsi 256-bit SSL, berlisensi resmi Bank Indonesia & PCI-DSS Tier 1.</span>
        </div>

        <!-- Tombol Eksekusi Dinonaktifkan Sesuai Ketentuan Task 10A.1 -->
        <div class="pt-2">
            <button
                type="button"
                disabled
                id="submitPayBtn"
                class="w-full py-4 px-6 rounded-2xl bg-slate-100 border border-slate-300 text-slate-600 font-extrabold text-sm flex items-center justify-center gap-2 cursor-not-allowed shadow-none"
                title="Fitur pembayaran sedang dipersiapkan untuk Tahap 10B"
            >
                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span id="submitPayText">Fitur pembayaran sedang dipersiapkan.</span>
            </button>
        </div>

    </form>

</div>

<script>
function handlePaymentSubmit(e) {
    e.preventDefault();
    alert('Fitur pembayaran sedang dipersiapkan.');
    return false;
}
</script>
@endsection
