@extends('layouts.admin')

@section('title', 'SCADA Monitoring Hidrolika Terpusat')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/admin-dashboard') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Portal Petugas</span>
        </a>
        <span class="text-xs font-mono font-bold text-cyan-700 bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            SCADA TELEMETRY IoT
        </span>
    </div>

    <!-- BANNER STATUS SISTEM HIDROLIKA -->
    <div class="bg-gradient-to-r from-[#0284C7] via-cyan-600 to-sky-600 rounded-3xl p-6 sm:p-7 text-white shadow-md shadow-sky-600/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-sky-400/30">
        <div class="space-y-1">
            <span class="text-[10px] text-cyan-100 font-mono font-bold uppercase tracking-[0.2em] block">
                PUSAT KONTROL SCADA AIR BERSIH
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-white">
                Sistem Monitoring Hidrolika Terpusat
            </h1>
            <p class="text-xs text-cyan-50">
                Pengawasan real-time Instalasi Pengolahan Air (IPA) Batang Agam dan 3 zona pipa transmisi utama Kota Payakumbuh.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white/20 text-white border border-white/30 backdrop-blur-xs font-extrabold text-xs shadow-xs">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>OPERASIONAL NORMAL • 3 SEKTOR TERHUBUNG</span>
            </span>
        </div>
    </div>

    <!-- PANEL TANDON UTAMA (RESERVOIR IPA) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <span class="text-[11px] font-bold text-[#0284C7] uppercase tracking-wider block">Fasilitas Penampungan Utama</span>
                <h3 class="text-base sm:text-lg font-black text-slate-900">Reservoir Utama IPA Batang Agam</h3>
            </div>
            <a
                href="{{ url('/reservoir-detail') }}"
                class="px-4 py-2 rounded-xl bg-sky-50 hover:bg-sky-100 text-[#0284C7] font-bold text-xs flex items-center gap-1.5 transition-colors border border-sky-200 self-start sm:self-auto"
            >
                <span>Rincian Hidrolika & Pompa</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-center">
            <!-- Visualizer Tangki -->
            <div class="md:col-span-4 flex justify-center">
                <div class="w-40 h-52 rounded-3xl border-4 border-slate-300 bg-slate-100 relative overflow-hidden shadow-md flex flex-col justify-end p-2">
                    <div class="w-full h-[83%] bg-gradient-to-t from-[#0284C7] to-cyan-400 rounded-2xl relative shadow-lg flex items-center justify-center">
                        <div class="text-center text-white z-10 space-y-0.5">
                            <span class="text-3xl font-black font-mono block">83%</span>
                            <span class="text-[10px] uppercase font-bold tracking-wider text-cyan-100">Kapasitas Aman</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Metrik Tangki -->
            <div class="md:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-slate-400 text-[11px] font-semibold block">Volume Air Tersedia</span>
                    <span class="font-mono font-black text-xl text-slate-900 block">12.500 m³</span>
                    <span class="text-[10px] text-slate-500 block">dari 15.000 m³ kapasitas</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-slate-400 text-[11px] font-semibold block">Laju Air Masuk (Inflow)</span>
                    <span class="font-mono font-black text-xl text-[#0284C7] block">120 L/s</span>
                    <span class="text-[10px] text-slate-500 block">Mata Air Sikabu</span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                    <span class="text-slate-400 text-[11px] font-semibold block">Laju Air Keluar (Outflow)</span>
                    <span class="font-mono font-black text-xl text-emerald-700 block">115 L/s</span>
                    <span class="text-[10px] text-slate-500 block">Dipompa ke pipa distribusi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- GRID JARINGAN PIPA SEKTOR (DIST-A, DIST-B, DIST-C) -->
    <div class="space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-900">
                Kondisi Hidrolika 3 Sektor Pipa Distribusi
            </h3>
            <a href="{{ url('/distribution-network') }}" class="text-xs font-bold text-[#0284C7] hover:underline flex items-center gap-1">
                <span>Jaringan Distribusi Pipa</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>

        @php
        $sectors = [
            ['id' => 'DIST-A', 'name' => 'Sektor Utara (DIST-A)', 'pressure' => 2.8, 'flow' => 18.5, 'status' => 'NORMAL', 'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'note' => 'Tekanan stabil normal di perumahan Balai Nan Duo.'],
            ['id' => 'DIST-B', 'name' => 'Sektor Timur (DIST-B)', 'pressure' => 2.6, 'flow' => 22.0, 'status' => 'NORMAL', 'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'note' => 'Pasokan lancar menuju zona Tiakar & Payakumbuh Timur.'],
            ['id' => 'DIST-C', 'name' => 'Sektor Selatan (DIST-C)', 'pressure' => 2.2, 'flow' => 15.1, 'status' => 'WASPADAI', 'badgeColor' => 'bg-amber-50 text-amber-800 border-amber-200', 'note' => 'Sedang pemantauan fluktuasi beban puncak jam malam.'],
        ];
        @endphp

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach($sectors as $sec)
                <a
                    href="{{ url('/distribution-detail?sector=' . $sec['id']) }}"
                    class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs hover:border-sky-300 transition-all flex flex-col justify-between space-y-4 group"
                >
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-mono text-xs font-bold text-slate-400">{{ $sec['id'] }}</span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $sec['badgeColor'] }}">
                                {{ $sec['status'] }}
                            </span>
                        </div>
                        <h4 class="font-extrabold text-slate-900 group-hover:text-[#0284C7] transition-colors text-sm">
                            {{ $sec['name'] }}
                        </h4>
                        <p class="text-[11px] text-slate-500 leading-relaxed">{{ $sec['note'] }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 grid grid-cols-2 gap-2 text-center text-xs">
                        <div class="p-2 rounded-xl bg-slate-50">
                            <span class="text-slate-400 block text-[10px]">Tekanan</span>
                            <span class="font-mono font-bold text-slate-900">{{ $sec['pressure'] }} Bar</span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50">
                            <span class="text-slate-400 block text-[10px]">Debit</span>
                            <span class="font-mono font-bold text-slate-900">{{ $sec['flow'] }} L/s</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>

    <!-- ALERT / PERINGATAN SISTEM AKTIF -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
        <div class="flex items-center gap-2 text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2">
            <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Status Integritas Jaringan Pipa Hidrolika</span>
        </div>
        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-900 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <div class="space-y-0.5">
                <strong class="font-bold text-emerald-950 block">Tidak Terdeteksi Anomali / Kebocoran Pipa Besar (Zero Major Leak)</strong>
                <p class="text-emerald-800">
                    Keseimbangan inflow reservoir (120 L/s) dan outflow distribusi (115 L/s) berada dalam batas deviasi aman 4.2%.
                </p>
            </div>
        </div>
    </div>

</div>
@endsection
