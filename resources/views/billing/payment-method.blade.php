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
        <span class="text-xs font-bold text-slate-400">Pembayaran Terverifikasi Otomatis</span>
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
            Pilih Metode Pembayaran
        </h3>

        <!-- OPSI UTAMA #1: Saldo Dompet Smart Water (Instan & Bebas Biaya) -->
        <label class="block p-4 sm:p-5 rounded-2xl border-2 border-emerald-500 bg-emerald-50/50 hover:bg-emerald-50 cursor-pointer transition-all relative group shadow-xs">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="wallet" checked class="mt-1 w-4 h-4 text-emerald-600 focus:ring-emerald-300" />
                    <div class="space-y-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <strong class="text-sm font-bold text-slate-900">Saldo Dompet Smart Water</strong>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-extrabold uppercase tracking-wide border border-emerald-300">
                                Instan & Bebas Admin
                            </span>
                        </div>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Potong langsung dari deposit restitusi rekening air Anda.
                        </p>
                        <div class="pt-1 flex items-center gap-2 text-xs">
                            <span class="text-slate-500">Saldo Tersedia:</span>
                            <span id="displayWalletBalance" class="font-mono font-bold text-emerald-700 bg-white px-2 py-0.5 rounded-md border border-emerald-200">
                                Rp 2.500.000
                            </span>
                        </div>
                    </div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-sm font-black text-sm">
                    👛
                </div>
            </div>
        </label>

        <!-- Opsi 2: Xendit Checkout (Instan) -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50/80 cursor-pointer transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="xendit_checkout" class="mt-1 w-4 h-4 text-[#0284C7] focus:ring-sky-200" />
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <strong class="text-sm font-bold text-slate-900">Xendit Checkout Multi-Payment</strong>
                            <span class="px-2 py-0.5 rounded-full bg-sky-100 text-[#0284C7] text-[10px] font-extrabold uppercase tracking-wide">
                                Pop-up
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            QRIS, Semua Bank (BCA, Mandiri, BRI, BNI), OVO, DANA dalam satu pop-up cepat.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-sky-50 border border-sky-200 flex items-center justify-center text-[#0284C7] shrink-0 font-bold text-xs">
                    ⚡
                </div>
            </div>
        </label>

        <!-- Opsi 3: QRIS Xendit -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50/80 cursor-pointer transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="qris" class="mt-1 w-4 h-4 text-[#0284C7] focus:ring-sky-200" />
                    <div class="space-y-1">
                        <strong class="text-sm font-bold text-slate-900 block">QRIS (Bebas Biaya Admin)</strong>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pindai QR code via BCA, Mandiri, GoPay, OVO, DANA, ShopeePay, Bank Nagari.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-700 shrink-0 font-bold text-xs">
                    QR
                </div>
            </div>
        </label>

        <!-- Opsi 4: BCA Virtual Account -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50/80 cursor-pointer transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="bca_va" class="mt-1 w-4 h-4 text-[#0284C7] focus:ring-sky-200" />
                    <div class="space-y-1">
                        <strong class="text-sm font-bold text-slate-900 block">BCA Virtual Account</strong>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Transfer otomatis via KlikBCA, m-BCA, atau mesin ATM BCA 24 jam.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-blue-50 border border-blue-200 flex items-center justify-center text-blue-700 shrink-0 font-black text-xs font-mono">
                    BCA
                </div>
            </div>
        </label>

        <!-- Opsi 5: Mandiri Virtual Account -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50/80 cursor-pointer transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="mandiri_va" class="mt-1 w-4 h-4 text-[#0284C7] focus:ring-sky-200" />
                    <div class="space-y-1">
                        <strong class="text-sm font-bold text-slate-900 block">Mandiri Virtual Account</strong>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Transfer melalui aplikasi Livin' by Mandiri atau ATM Mandiri.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-amber-50 border border-amber-200 flex items-center justify-center text-amber-700 shrink-0 font-black text-xs font-mono">
                    MDR
                </div>
            </div>
        </label>

        <!-- Opsi 6: E-Wallet -->
        <label class="block p-4 sm:p-5 rounded-2xl border border-slate-200 bg-white hover:border-sky-300 hover:bg-slate-50/80 cursor-pointer transition-all">
            <div class="flex items-start justify-between gap-3">
                <div class="flex items-start gap-3.5">
                    <input type="radio" name="method" value="ewallet" class="mt-1 w-4 h-4 text-[#0284C7] focus:ring-sky-200" />
                    <div class="space-y-1">
                        <strong class="text-sm font-bold text-slate-900 block">E-Wallet (OVO / DANA / LinkAja)</strong>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Pembayaran otomatis langsung terpotong dari saldo aplikasi dompet digital Anda.
                        </p>
                    </div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-purple-50 border border-purple-200 flex items-center justify-center text-purple-700 shrink-0 font-black text-xs">
                    EW
                </div>
            </div>
        </label>

        <!-- Box Keamanan & Enkripsi -->
        <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 flex items-center gap-3 text-xs text-slate-500">
            <svg class="w-4 h-4 text-emerald-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            </svg>
            <span>Transaksi aman terenkripsi 256-bit SSL, berlisensi resmi Bank Indonesia & PCI-DSS Tier 1.</span>
        </div>

        <!-- Tombol Eksekusi Lanjut -->
        <div class="pt-2">
            <button
                type="submit"
                id="submitPayBtn"
                class="w-full py-4 px-6 rounded-2xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-extrabold text-sm shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 transition-all cursor-pointer"
            >
                <span id="submitPayText">Lanjut Pembayaran (Rp 150.800)</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </button>
        </div>

    </form>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    if (window.SWC) {
        const wallet = window.SWC.getWallet();
        const el = document.getElementById('displayWalletBalance');
        if (el) {
            el.textContent = 'Rp ' + Number(wallet.balance).toLocaleString('id-ID');
        }
    }
});

function handlePaymentSubmit(e) {
    e.preventDefault();
    const form = e.target;
    const selectedMethod = form.querySelector('input[name="method"]:checked')?.value || 'wallet';
    const amount = 150800;
    const invNo = 'INV-PYK-2025-04-1000';

    if (selectedMethod === 'wallet') {
        const btn = document.getElementById('submitPayBtn');
        const txt = document.getElementById('submitPayText');
        btn.disabled = true;
        txt.textContent = 'Memproses Pembayaran Saldo Dompet...';

        setTimeout(() => {
            if (window.SWC) {
                const deductRes = window.SWC.deductWallet(amount, 'Pembayaran Tagihan Rekening Air April 2025 (' + invNo + ')');
                if (!deductRes.success) {
                    alert(deductRes.message);
                    btn.disabled = false;
                    txt.textContent = 'Lanjut Pembayaran (Rp 150.800)';
                    return;
                }
                window.SWC.payBill(invNo, 'Saldo Dompet Smart Water');
            }
            window.location.href = "{{ url('/payment-success') }}?method=" + encodeURIComponent('Saldo Dompet Smart Water') + "&amount=" + amount;
        }, 800);
    } else {
        window.location.href = "{{ url('/payment-instruction') }}?method=" + encodeURIComponent(selectedMethod) + "&amount=" + amount;
    }
}
</script>
@endsection
