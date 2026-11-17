@extends('layouts.app')

@section('title', 'Kondisi Layanan Air Pelanggan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ url('/beranda') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
        <span class="text-xs font-mono font-bold text-slate-400">TELEMETRI IOT RESIDENSIAL</span>
    </div>

    <!-- Judul & Deskripsi -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Kondisi Layanan Air Pelanggan
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
            Transparansi kondisi suplai air, tekanan hidrolika pipa, dan debit pasokan real-time di zona sambungan rumah tangga Anda.
        </p>
    </div>

    <!-- BANNER STATUS ZONA KESELURUHAN (Gradasi Biru) -->
    <div class="bg-gradient-to-r from-[#0062D6] via-[#0284C7] to-cyan-600 rounded-3xl p-6 sm:p-7 text-white shadow-lg shadow-sky-600/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="space-y-1">
            <span class="text-[11px] font-bold text-sky-200 uppercase tracking-wider block">Zona Pelayanan Pelanggan</span>
            <h2 class="text-xl sm:text-2xl font-black text-white">
                Zona DIST-A Sektor Utara
            </h2>
            <p class="text-xs text-sky-100">
                Mencakup Kelurahan Balai Nan Duo, Tan Malaka, dan sekitarnya (Kec. Payakumbuh Barat).
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-emerald-500 text-white font-extrabold text-xs shadow-md">
                <span class="w-2.5 h-2.5 rounded-full bg-white animate-pulse"></span>
                <span>PASOKAN NORMAL</span>
            </span>
        </div>
    </div>

    <!-- KARTU METRIK HIDROLIKA (2 Kolom) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
        
        <!-- Kartu 1: Tekanan Air Pipa -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tekanan Air Pipa</span>
                <div class="w-9 h-9 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m14 14-4-4"/><path d="M12 6v2"/><path d="M6 12h2"/><path d="M18 12h-2"/>
                    </svg>
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-mono font-black text-slate-900 tracking-tight">2.8</span>
                <span class="text-sm font-bold text-slate-500 font-mono">Bar</span>
            </div>

            <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl w-fit">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Tekanan Stabil (Standar SNI: 2.0 - 3.5 Bar)</span>
            </div>
            <p class="text-[11px] text-slate-400 pt-1">
                Air mampu naik hingga lantai 2 rumah tanpa bantuan pompa tambahan.
            </p>
        </div>

        <!-- Kartu 2: Debit Aliran Air -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Debit Aliran Pasokan</span>
                <div class="w-9 h-9 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                    </svg>
                </div>
            </div>

            <div class="flex items-baseline gap-2">
                <span class="text-3xl sm:text-4xl font-mono font-black text-slate-900 tracking-tight">18.5</span>
                <span class="text-sm font-bold text-slate-500 font-mono">Liter / Detik</span>
            </div>

            <div class="flex items-center gap-1.5 text-xs font-bold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-xl w-fit">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>Lancar Mengalir</span>
            </div>
            <p class="text-[11px] text-slate-400 pt-1">
                Laju pasokan air dari pipa transmisi utama berjalan kontinu 24 jam.
            </p>
        </div>

    </div>

    <!-- VISUALIZER TANDON / RESERVOIR AIR (ReservoirTankVisual) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/><path d="M9 10a3 3 0 0 0 6 0"/>
                    </svg>
                    <span>Ketersediaan Cadangan Tandon Utama (Reservoir IPA)</span>
                </h3>
                <p class="text-xs text-slate-500">
                    Kapasitas air bersih pada reservoir pemasok zona Payakumbuh Barat.
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-[#0284C7] bg-sky-50 px-3 py-1 rounded-full border border-sky-200">
                Level: 82% Penuh
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <!-- Tangki Visual Silinder -->
            <div class="md:col-span-5 flex justify-center">
                <div class="w-44 h-56 rounded-3xl border-4 border-slate-800 bg-slate-900 relative overflow-hidden shadow-xl flex flex-col justify-end p-2">
                    <!-- Penanda Ketinggian Mistar -->
                    <div class="absolute left-2 top-2 bottom-2 w-3 flex flex-col justify-between text-[8px] font-mono text-slate-400 z-20">
                        <span>100%</span>
                        <span>75%</span>
                        <span>50%</span>
                        <span>25%</span>
                    </div>

                    <!-- Volume Air Bergelombang -->
                    <div class="w-full h-[82%] bg-gradient-to-t from-[#0284C7] to-cyan-400 rounded-2xl relative shadow-lg flex items-center justify-center transition-all duration-1000">
                        <div class="text-center text-white z-10 space-y-0.5">
                            <span class="text-3xl font-black font-mono block drop-shadow-sm">82%</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-cyan-100">Optimal</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Rincian Data Reservoir -->
            <div class="md:col-span-7 space-y-3 text-xs">
                <div class="p-4 rounded-2xl bg-[#F8FAFC] border border-slate-200 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nama Fasilitas</span>
                        <strong class="text-slate-900 font-bold">Reservoir Utama IPA Batang Agam</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Volume Saat Ini</span>
                        <strong class="text-slate-900 font-mono">12.300 m³</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Kapasitas Maksimal</span>
                        <strong class="text-slate-900 font-mono">15.000 m³</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Status Pompa Pengisi</span>
                        <span class="text-emerald-700 font-bold">3 Unit Beroperasi Normal</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-2xl bg-sky-50 border border-sky-100 flex items-start gap-2.5 text-sky-900 text-xs">
                    <svg class="w-4 h-4 text-[#0284C7] shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>
                    </svg>
                    <p>Cadangan air mencukupi kebutuhan seluruh zona hingga 48 jam ke depan tanpa kendala pasokan.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- KARTU PENGUMUMAN / INFO PEMELIHARAAN -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
            Pengumuman & Info Jaringan Sekitar
        </h3>
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex items-start gap-3.5 text-xs">
            <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><line x1="12" x2="12" y1="9" y2="13"/><line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
            </div>
            <div class="space-y-1">
                <strong class="font-bold text-slate-900 block text-xs">Tidak Ada Gangguan Pipa Terjadwal</strong>
                <p class="text-slate-500 leading-relaxed">
                    Seluruh katup jaringan transmisi di wilayah Payakumbuh Barat saat ini beroperasi penuh tanpa ada perbaikan darurat.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
