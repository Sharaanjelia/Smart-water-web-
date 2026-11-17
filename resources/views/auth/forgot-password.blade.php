@extends('layouts.app')

@section('title', 'Lupa PIN Akun')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-6">
    <div class="w-full max-w-md mx-auto bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-6">
        
        <!-- Header & Navigasi Kembali -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <a href="{{ url('/login') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m15 18-6-6 6-6"/>
                </svg>
                <span>Kembali ke Login</span>
            </a>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Lupa PIN</span>
            <div class="w-12"></div>
        </div>

        <!-- Lingkaran Ikon & Tipografi Identitas -->
        <div class="text-center space-y-3 pt-2">
            <div class="w-20 h-20 mx-auto rounded-full bg-sky-50 flex items-center justify-center border border-sky-100 shadow-xs">
                <svg class="w-11 h-11 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                    <path d="M12 15v2"/>
                </svg>
            </div>
            <h1 class="text-[22px] font-black text-[#0F172A] tracking-tight">
                Pemulihan PIN Akun
            </h1>
            <p class="text-[13px] text-[#64748B] leading-relaxed max-w-xs mx-auto">
                Masukkan ID Pelanggan atau Nomor WhatsApp yang terdaftar untuk menerima kode verifikasi OTP reset PIN.
            </p>
        </div>

        <!-- Formulir Input & Trigger -->
        <form id="forgotForm" onsubmit="handleForgotSubmit(event)" class="space-y-4 pt-1">
            <div class="space-y-1.5">
                <label for="identifier" class="text-[13px] font-bold text-slate-700 block">
                    ID Pelanggan / Nomor WhatsApp
                </label>
                <div class="relative">
                    <div class="absolute left-3.5 top-1/2 -translate-y-1/2 text-[#0284C7]">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                            <circle cx="12" cy="7" r="4"/>
                        </svg>
                    </div>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        placeholder="Contoh: 1234567890 atau 0812..."
                        class="w-full pl-10 pr-4 py-3 rounded-xl border border-slate-200 bg-[#F8FAFC] text-slate-900 text-sm focus:outline-none focus:border-[#0284C7] focus:bg-white focus:ring-2 focus:ring-sky-100 transition-all font-mono"
                        required
                    />
                </div>
            </div>

            <!-- Pesan Notifikasi Sukses / Peringatan -->
            <div id="statusMessage" class="hidden p-3.5 rounded-xl text-xs font-semibold"></div>

            <button
                type="submit"
                id="submitBtn"
                class="w-full py-3.5 px-5 rounded-xl bg-gradient-to-r from-[#0284C7] to-[#0369A1] hover:from-[#0369A1] hover:to-[#075985] text-white font-bold text-sm shadow-md shadow-sky-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <span id="btnText">Kirim Kode Verifikasi</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" x2="11" y1="2" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>

        <!-- Call Center Info Card -->
        <div class="p-4 rounded-2xl bg-[#F1F5F9] border border-[#E2E8F0] flex items-start gap-3.5">
            <div class="w-9 h-9 rounded-xl bg-white text-[#0284C7] border border-slate-200 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/>
                    <path d="M18 9h2a2 2 0 0 1 2 2v11l-4-4h-6a2 2 0 0 1-2-2v-1"/>
                </svg>
            </div>
            <div class="text-xs space-y-0.5">
                <strong class="font-bold text-slate-800 text-[13px] block">Butuh Bantuan Langsung?</strong>
                <p class="text-slate-600 leading-relaxed">
                    Hubungi Call Center PDAM di <span class="font-bold text-slate-800">0752-92044</span> atau WhatsApp <span class="font-bold text-[#0284C7]">0812-7389-4902</span>.
                </p>
            </div>
        </div>

        <div class="pt-2 text-center text-xs text-slate-500 border-t border-slate-100 flex items-center justify-center gap-4">
            <a href="{{ url('/login') }}" class="font-bold text-[#0284C7] hover:underline">
                &larr; Masuk ke Akun
            </a>
            <span class="text-slate-300">&bull;</span>
            <a href="{{ url('/register') }}" class="font-semibold text-slate-600 hover:text-[#0284C7]">
                Daftar Pelanggan Baru &rarr;
            </a>
        </div>

    </div>
</div>

<script>
let isSent = false;

function handleForgotSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('identifier').value.trim();
    const msg = document.getElementById('statusMessage');
    const btnText = document.getElementById('btnText');

    if (!input) {
        msg.className = 'p-3.5 rounded-xl text-xs font-semibold bg-amber-50 border border-amber-200 text-amber-900 block';
        msg.textContent = 'Silakan masukkan ID Pelanggan atau nomor WhatsApp Anda.';
        return;
    }

    isSent = true;
    msg.className = 'p-3.5 rounded-xl text-xs font-semibold bg-emerald-50 border border-emerald-200 text-emerald-900 block';
    msg.innerHTML = '<strong>Instruksi Terkirim!</strong> Kode OTP pemulihan PIN telah dikirimkan via SMS/WhatsApp ke nomor yang terdaftar.';
    btnText.textContent = 'Kirim Ulang Kode OTP';
}
</script>
@endsection
