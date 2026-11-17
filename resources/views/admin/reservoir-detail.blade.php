@extends('layouts.admin')

@section('title', 'Detail Reservoir Utama IPA Batang Agam')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/water-monitoring') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Konsol SCADA</span>
        </a>
        <span class="text-xs font-mono font-bold text-slate-400">DETAIL HIDROLIKA FASILITAS</span>
    </div>

    <!-- KARTU STATUS & LOKASI RESERVOIR -->
    <div class="bg-gradient-to-r from-[#0284C7] via-cyan-600 to-sky-600 rounded-3xl p-6 sm:p-7 text-white shadow-md shadow-sky-600/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-sky-400/30">
        <div class="space-y-1">
            <span class="text-[10px] text-cyan-100 font-mono font-bold uppercase tracking-[0.2em] block">
                INSTALASI PENGOLAHAN AIR (IPA)
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-white">
                Reservoir Utama IPA Batang Agam
            </h1>
            <p class="text-xs text-cyan-50">
                Lokasi: Jl. Raya Sikabu, Kec. Payakumbuh Barat • Sensor IoT Updated: {{ now()->format('H:i:s') }} WIB
            </p>
        </div>

        <div>
            <span class="inline-flex items-center gap-1.5 px-4 py-2 rounded-2xl bg-white/20 text-white border border-white/30 backdrop-blur-xs font-black text-xs shadow-xs">
                <svg class="w-4 h-4 text-emerald-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>NORMAL (92% Kapasitas)</span>
            </span>
        </div>
    </div>

    <!-- VISUALIZER TANGKI AIR BESAR (Tinggi 200px) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs flex flex-col items-center justify-center space-y-4">
        <div class="w-full max-w-sm h-[200px] rounded-3xl border-4 border-slate-800 bg-slate-900 relative overflow-hidden shadow-2xl flex flex-col justify-end p-3">
            <!-- Mistar Ketinggian Air -->
            <div class="absolute left-3 top-3 bottom-3 w-6 flex flex-col justify-between text-[10px] font-mono text-cyan-300 z-20">
                <span>5.0m</span>
                <span>3.75m</span>
                <span>2.5m</span>
                <span>1.25m</span>
            </div>

            <!-- Gelombang Air -->
            <div class="w-full h-[92%] bg-gradient-to-t from-[#0284C7] to-cyan-400 rounded-2xl relative shadow-lg flex items-center justify-center">
                <div class="text-center text-white z-10 space-y-1">
                    <span class="text-4xl font-black font-mono block drop-shadow-md">4.6 Meter</span>
                    <span class="text-xs uppercase font-bold tracking-wider text-cyan-100">Ketinggian Muka Air (92%)</span>
                </div>
            </div>
        </div>

        <p class="text-xs text-slate-500 font-medium text-center max-w-md">
            Sensor ultrasonik mendeteksi ketinggian air tandon stabil pada elevasi 4.6 meter dari total kapasitas tangki 5.0 meter.
        </p>
    </div>

    <!-- 4 KOTAK PARAMETER HIDROLIKA REAL-TIME -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Kotak 1: Air Masuk (Inflow) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span class="uppercase tracking-wider">Air Masuk (Inflow)</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="5" y2="19"/><polyline points="19 12 12 19 5 12"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-mono font-black text-slate-900 tracking-tight">
                120 <span class="text-xs font-semibold text-slate-500">L/s</span>
            </div>
            <p class="text-[11px] text-slate-400">Debit intake dari Mata Air Sikabu</p>
        </div>

        <!-- Kotak 2: Air Keluar (Outflow) -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span class="uppercase tracking-wider">Air Keluar (Outflow)</span>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" x2="12" y1="19" y2="5"/><polyline points="5 12 12 5 19 12"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-mono font-black text-slate-900 tracking-tight">
                115 <span class="text-xs font-semibold text-slate-500">L/s</span>
            </div>
            <p class="text-[11px] text-slate-400">Didistribusikan ke 3 sektor</p>
        </div>

        <!-- Kotak 3: Ketinggian Muka Air -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span class="uppercase tracking-wider">Ketinggian Muka Air</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
            </div>
            <div class="text-2xl font-mono font-black text-slate-900 tracking-tight">
                4.6 <span class="text-xs font-semibold text-slate-500">/ 5.0 m</span>
            </div>
            <p class="text-[11px] text-slate-400">Sensor Ultrasonik Terkalibrasi</p>
        </div>

        <!-- Kotak 4: Status Pompa Distribusi -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-2">
            <div class="flex items-center justify-between text-xs font-bold text-slate-500">
                <span class="uppercase tracking-wider">Pompa Distribusi</span>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                    </svg>
                </div>
            </div>
            <div class="text-sm font-mono font-black text-emerald-700 tracking-tight">
                3 Pompa Aktif
            </div>
            <p class="text-[11px] text-slate-400">1 Pompa Cadangan Siap Pakai</p>
        </div>

    </div>

</div>
@endsection
