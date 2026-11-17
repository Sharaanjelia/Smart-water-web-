@extends('layouts.app')

@section('title', 'Instruksi Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/payment-method') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Ubah Metode</span>
        </a>
        <span class="text-xs font-bold text-slate-400">Menunggu Pembayaran</span>
    </div>

    <!-- KARTU HITUNG MUNDUR (Timer Countdown 15 Menit) -->
    <div class="p-4 rounded-2xl bg-[#FFFBEB] border border-[#FDE68A] flex items-center justify-between text-xs text-amber-900 shadow-2xs">
        <div class="flex items-center gap-2.5">
            <svg class="w-5 h-5 text-amber-600 animate-spin" style="animation-duration: 6s;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
            </svg>
            <span class="font-semibold">Selesaikan pembayaran dalam waktu:</span>
        </div>
        <div id="countdownTimer" class="font-mono font-black text-base text-amber-950 px-2.5 py-0.5 rounded-lg bg-amber-200/60">
            14:59
        </div>
    </div>

    <!-- NOMOR REKENING / VIRTUAL ACCOUNT BOX -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kanal Pembayaran</span>
                <strong class="text-sm font-black text-slate-900">
                    {{ strtoupper(str_replace('_', ' ', $method ?? 'bca_va')) }}
                </strong>
            </div>
            <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 border border-blue-200 font-bold text-[10px]">
                Otomatis Terverifikasi
            </span>
        </div>

        <!-- Nomor VA Besar -->
        <div class="p-4.5 rounded-2xl bg-[#F8FAFC] border border-slate-200 space-y-1.5">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                Nomor Virtual Account / Kode Bayar
            </span>
            <div class="flex items-center justify-between gap-3">
                <span id="vaNumber" class="text-xl sm:text-2xl font-mono font-black text-slate-900 tracking-wider">
                    {{ $virtualAccount ?? '8808 1234567890 01' }}
                </span>
                <button
                    type="button"
                    onclick="copyToClipboard('{{ $virtualAccount ?? '8808 1234567890 01' }}', 'Nomor Virtual Account berhasil disalin!')"
                    class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shrink-0"
                >
                    <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                    </svg>
                    <span>Salin</span>
                </button>
            </div>
            <p class="text-[11px] text-slate-500 pt-1">
                Nama Penerima: <strong class="text-slate-800">PDAM Smart Water - Budi Santoso</strong>
            </p>
        </div>

        <!-- Total Pembayaran Tepat -->
        <div class="p-4.5 rounded-2xl bg-[#F8FAFC] border border-slate-200 flex items-center justify-between gap-3">
            <div>
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Jumlah Transfer Tepat</span>
                <span class="text-2xl font-mono font-black text-[#0284C7]">
                    Rp {{ number_format($totalAmount ?? 150800, 0, ',', '.') }}
                </span>
            </div>
            <button
                type="button"
                onclick="copyToClipboard('{{ $totalAmount ?? 150800 }}', 'Jumlah transfer berhasil disalin!')"
                class="px-3 py-1.5 rounded-xl border border-slate-300 hover:bg-slate-100 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer shrink-0"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/>
                </svg>
                <span>Salin Jumlah</span>
            </button>
        </div>
        <p class="text-[11px] text-slate-400">
            Pastikan Anda mentransfer tepat hingga digit terakhir agar otomatis diverifikasi tanpa konfirmasi manual.
        </p>

    </div>

    <!-- PANDUAN CARA PEMBAYARAN (Tabs Accordion) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2">
            Panduan Pembayaran
        </h4>

        <div class="flex items-center gap-2 border-b border-slate-100 pb-2 text-xs">
            <button type="button" onclick="switchGuide('mbanking')" id="guideBtn1" class="px-3 py-1.5 rounded-lg font-bold text-[#0284C7] bg-sky-50 cursor-pointer">
                m-Banking
            </button>
            <button type="button" onclick="switchGuide('atm')" id="guideBtn2" class="px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                Mesin ATM
            </button>
            <button type="button" onclick="switchGuide('ibanking')" id="guideBtn3" class="px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                Internet Banking
            </button>
        </div>

        <div id="guideContent" class="text-xs text-slate-600 space-y-2 leading-relaxed">
            <ol class="list-decimal pl-5 space-y-1.5">
                <li>Buka aplikasi m-Banking Anda dan lakukan login.</li>
                <li>Pilih menu <strong>Transfer</strong> &gt; <strong>Virtual Account</strong>.</li>
                <li>Masukkan nomor Virtual Account: <code class="font-mono text-slate-900 font-bold">{{ $virtualAccount ?? '8808 1234567890 01' }}</code>.</li>
                <li>Periksa detail rekening, nama penerima, dan nominal pembayaran (<strong>Rp 150.800</strong>).</li>
                <li>Masukkan PIN m-Banking Anda untuk menyelesaikan transaksi.</li>
            </ol>
        </div>
    </div>

    <!-- TOMBOL PENGECEKAN STATUS PEMBAYARAN -->
    <div class="space-y-2 pt-2">
        <button
            type="button"
            id="confirmPayBtn"
            onclick="confirmPayment()"
            class="w-full py-3.5 px-6 rounded-2xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-extrabold text-sm shadow-md shadow-sky-600/20 flex items-center justify-center gap-2 transition-all cursor-pointer"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span id="confirmPayText">Saya Sudah Membayar (Verifikasi Rekening)</span>
        </button>
        <p class="text-[11px] text-center text-slate-400">
            Sistem payment gateway otomatis memverifikasi mutasi bank dalam 1–3 detik.
        </p>
    </div>

</div>

<script>
// Timer Countdown 15 Menit
let totalSeconds = 15 * 60 - 1;
const timerDisplay = document.getElementById('countdownTimer');

const interval = setInterval(() => {
    if (totalSeconds <= 0) {
        clearInterval(interval);
        timerDisplay.textContent = '00:00';
        return;
    }
    const mins = Math.floor(totalSeconds / 60);
    const secs = totalSeconds % 60;
    timerDisplay.textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    totalSeconds--;
}, 1000);

function copyToClipboard(text, msg) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(() => alert(msg));
    } else {
        alert(msg);
    }
}

function switchGuide(type) {
    const content = document.getElementById('guideContent');
    const b1 = document.getElementById('guideBtn1');
    const b2 = document.getElementById('guideBtn2');
    const b3 = document.getElementById('guideBtn3');

    b1.className = 'px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer';
    b2.className = 'px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer';
    b3.className = 'px-3 py-1.5 rounded-lg font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer';

    if (type === 'mbanking') {
        b1.className = 'px-3 py-1.5 rounded-lg font-bold text-[#0284C7] bg-sky-50 cursor-pointer';
        content.innerHTML = `
            <ol class="list-decimal pl-5 space-y-1.5">
                <li>Buka aplikasi m-Banking Anda dan lakukan login.</li>
                <li>Pilih menu <strong>Transfer</strong> &gt; <strong>Virtual Account</strong>.</li>
                <li>Masukkan nomor Virtual Account: <code class="font-mono text-slate-900 font-bold">8808 1234567890 01</code>.</li>
                <li>Periksa detail rekening, nama penerima, dan nominal pembayaran (<strong>Rp 150.800</strong>).</li>
                <li>Masukkan PIN m-Banking Anda untuk menyelesaikan transaksi.</li>
            </ol>`;
    } else if (type === 'atm') {
        b2.className = 'px-3 py-1.5 rounded-lg font-bold text-[#0284C7] bg-sky-50 cursor-pointer';
        content.innerHTML = `
            <ol class="list-decimal pl-5 space-y-1.5">
                <li>Masukkan kartu ATM dan PIN Anda di mesin ATM.</li>
                <li>Pilih menu <strong>Transaksi Lainnya</strong> &gt; <strong>Transfer</strong> &gt; <strong>Ke Rekening Virtual Account</strong>.</li>
                <li>Masukkan kode bayar Virtual Account: <code class="font-mono text-slate-900 font-bold">8808 1234567890 01</code>.</li>
                <li>Konfirmasi kesesuaian nama pelanggan dan total tagihan.</li>
                <li>Simpan struk bukti transaksi Anda.</li>
            </ol>`;
    } else {
        b3.className = 'px-3 py-1.5 rounded-lg font-bold text-[#0284C7] bg-sky-50 cursor-pointer';
        content.innerHTML = `
            <ol class="list-decimal pl-5 space-y-1.5">
                <li>Buka situs Internet Banking bank Anda.</li>
                <li>Pilih menu <strong>Pembayaran Tagihan / Virtual Account</strong>.</li>
                <li>Input nomor VA <code class="font-mono text-slate-900 font-bold">8808 1234567890 01</code>.</li>
                <li>Otorisasi menggunakan token atau OTP SMS Anda.</li>
            </ol>`;
    }
}

function checkPaymentStatus() {
    confirmPayment();
}

function confirmPayment() {
    const btn = document.getElementById('confirmPayBtn');
    const txt = document.getElementById('confirmPayText');
    if (btn) btn.disabled = true;
    if (txt) txt.textContent = 'Memverifikasi Pembayaran...';

    const method = "{{ strtoupper(str_replace('_', ' ', $method ?? 'bca_va')) }}";
    if (window.SWC) {
        window.SWC.payBill('INV-PYK-2025-04-1000', method);
    }
    setTimeout(() => {
        window.location.href = "{{ url('/payment-success') }}?method=" + encodeURIComponent(method);
    }, 600);
}
</script>
@endsection
