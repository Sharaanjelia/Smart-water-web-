@extends('layouts.app')

@section('title', 'Pengaturan Aplikasi')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/user') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Profil</span>
        </a>
        <h2 class="text-sm font-extrabold text-slate-800">Pengaturan</h2>
        <div class="w-12"></div>
    </div>

    <!-- PENGATURAN NOTIFIKASI -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
            Notifikasi & Pengingat
        </h3>

        <div class="space-y-4 text-xs">
            <!-- Switch 1 -->
            <div class="flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <strong class="font-bold text-slate-900 block text-[13px]">Pengingat Tagihan Terbit</strong>
                    <p class="text-slate-500">Kirim notifikasi otomatis saat lembar rekening bulanan terbit di tanggal 1.</p>
                </div>
                <input type="checkbox" checked class="w-5 h-5 rounded text-[#0284C7] focus:ring-sky-200 cursor-pointer" />
            </div>

            <!-- Switch 2 -->
            <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-100">
                <div class="space-y-0.5">
                    <strong class="font-bold text-slate-900 block text-[13px]">Jadwal Catat Meter Mandiri</strong>
                    <p class="text-slate-500">Pengingat berkala tanggal 20-25 setiap bulan untuk memotret fisik odometer.</p>
                </div>
                <input type="checkbox" checked class="w-5 h-5 rounded text-[#0284C7] focus:ring-sky-200 cursor-pointer" />
            </div>
        </div>
    </div>

    <!-- KEAMANAN & AKUN -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
            Keamanan Akun
        </h3>

        <div class="space-y-4 text-xs">
            <!-- Switch Biometrik -->
            <div class="flex items-center justify-between gap-4">
                <div class="space-y-0.5">
                    <strong class="font-bold text-slate-900 block text-[13px]">Kunci Keamanan Biometrik / WebAuthn</strong>
                    <p class="text-slate-500">Minta sidik jari / PIN Windows Hello saat otorisasi pembayaran rekening.</p>
                </div>
                <input type="checkbox" class="w-5 h-5 rounded text-[#0284C7] focus:ring-sky-200 cursor-pointer" />
            </div>

            <!-- Ganti PIN -->
            <div class="flex items-center justify-between gap-4 pt-2 border-t border-slate-100">
                <div class="space-y-0.5">
                    <strong class="font-bold text-slate-900 block text-[13px]">Ganti PIN Keamanan</strong>
                    <p class="text-slate-500">Perbarui 6 digit PIN akun Anda melalui verifikasi OTP nomor HP.</p>
                </div>
                <a
                    href="{{ url('/forgot-password') }}"
                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs"
                >
                    Ubah PIN
                </a>
            </div>
        </div>
    </div>

    <!-- PREFERENSI BAHASA -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
            Preferensi Tampilan & Bahasa
        </h3>

        <div class="flex items-center justify-between gap-4 text-xs">
            <div>
                <strong class="font-bold text-slate-900 block text-[13px]">Bahasa Aplikasi</strong>
                <p class="text-slate-500">Pilih bahasa pengantar antarmuka portal.</p>
            </div>
            <select class="px-3.5 py-2 rounded-xl border border-slate-200 bg-[#F8FAFC] text-slate-900 font-semibold focus:outline-none focus:border-[#0284C7]">
                <option value="id" selected>Bahasa Indonesia</option>
                <option value="en">English (US)</option>
            </select>
        </div>
    </div>

    <!-- TENTANG APLIKASI -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3 text-xs">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider border-b border-slate-100 pb-2">
            Tentang Smart Water
        </h3>

        <div class="space-y-2 text-slate-500">
            <div class="flex justify-between">
                <span>Versi Portal</span>
                <span class="font-mono font-bold text-slate-800">v1.0.0+1 Web Edition</span>
            </div>
            <div class="flex justify-between">
                <span>Pengembang</span>
                <span class="font-semibold text-slate-800">Perumda Air Minum Tirta Sago Payakumbuh</span>
            </div>
            <div class="pt-2 flex items-center gap-4 text-[#0284C7] font-semibold">
                <a href="{{ url('/tentang') }}" class="hover:underline">Syarat & Ketentuan Layanan</a>
                <span>•</span>
                <a href="{{ url('/tentang') }}" class="hover:underline">Kebijakan Privasi Data PDAM</a>
            </div>
        </div>
    </div>

</div>
@endsection
