@extends('layouts.admin')

@section('title', 'Detail Sektor Distribusi & Tren Tekanan')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/distribution-network') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Jaringan Pipa</span>
        </a>
        <span class="text-xs font-mono font-bold text-slate-400">ANALISIS TELEMETRI 24 JAM</span>
    </div>

    <!-- HEADER SEKTOR SPESIFIK -->
    <div class="bg-gradient-to-r from-[#0284C7] via-cyan-600 to-sky-600 rounded-3xl p-6 sm:p-7 text-white shadow-md shadow-sky-600/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-sky-400/30">
        <div class="space-y-1">
            <span class="text-[10px] text-cyan-100 font-mono font-bold uppercase tracking-[0.2em] block">
                ZONA DISTRIBUSI PDAM
            </span>
            <h1 class="text-xl sm:text-2xl font-black text-white">
                {{ $sector === 'DIST-B' ? 'Sektor Distribusi Timur (DIST-B)' : ($sector === 'DIST-C' ? 'Sektor Distribusi Selatan (DIST-C)' : 'Sektor Distribusi Utara (DIST-A)') }}
            </h1>
            <p class="text-xs text-cyan-50">
                Pipa Transmisi: {{ $sector === 'DIST-B' ? '350 mm Steel HDPE • Melayani ~1.840 Sambungan' : ($sector === 'DIST-C' ? '250 mm HDPE • Melayani ~920 Sambungan' : '300 mm Ductile Iron • Melayani ~1.250 Sambungan') }} Pelanggan • Pembaruan: {{ now()->format('H:i') }} WIB
            </p>
        </div>

        <div>
            @if($sector === 'DIST-C')
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-2xl bg-white/20 text-white border border-white/30 backdrop-blur-xs font-bold text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-amber-300 animate-pulse"></span>
                    <span>STATUS WASPADAI</span>
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-2xl bg-white/20 text-white border border-white/30 backdrop-blur-xs font-bold text-xs shadow-xs">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>STATUS NORMAL</span>
                </span>
            @endif
        </div>
    </div>

    <!-- KARTU METRIK HIDROLIKA KEMBAR -->
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        
        <!-- Tekanan Real-time -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tekanan Air Real-Time</span>
                <div class="w-8 h-8 rounded-xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/><path d="m14 14-4-4"/><path d="M12 6v2"/><path d="M6 12h2"/><path d="M18 12h-2"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-mono font-black text-slate-900 tracking-tight">
                {{ $sector === 'DIST-B' ? '2.6' : ($sector === 'DIST-C' ? '2.2' : '2.8') }} <span class="text-xs font-semibold text-slate-500">Bar</span>
            </div>
            <p class="text-[11px] {{ $sector === 'DIST-C' ? 'text-amber-700 font-bold' : 'text-emerald-700 font-bold' }}">
                {{ $sector === 'DIST-C' ? '⚠️ Tekanan Rendah di Puncak Penggunaan' : '✓ Tekanan Ideal Rumah Tangga (2.0 - 3.5 Bar)' }}
            </p>
        </div>

        <!-- Debit Real-time -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Debit Aliran Real-Time</span>
                <div class="w-8 h-8 rounded-xl bg-cyan-50 text-cyan-600 flex items-center justify-center">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                    </svg>
                </div>
            </div>
            <div class="text-3xl font-mono font-black text-slate-900 tracking-tight">
                {{ $sector === 'DIST-B' ? '22.0' : ($sector === 'DIST-C' ? '15.1' : '18.5') }} <span class="text-xs font-semibold text-slate-500">L/s</span>
            </div>
            <p class="text-[11px] text-emerald-700 font-bold">✓ Aliran Kontinu Tanpa Sumbatan</p>
        </div>

    </div>

    <!-- GRAFIK RIWAYAT TREN TEKANAN 24 JAM (Pressure Trend Chart) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" x2="18" y1="20" y2="10"/><line x1="12" x2="12" y1="20" y2="4"/><line x1="6" x2="6" y1="20" y2="14"/>
                    </svg>
                    <span>Grafik Fluktuasi Tekanan Pipa 24 Jam</span>
                </h3>
                <p class="text-xs text-slate-500">
                    Pola pemakaian harian pelanggan pada jam beban puncak (Pagi 06:00-08:00 WIB & Sore 16:00-18:00 WIB).
                </p>
            </div>
            <span class="text-xs font-mono font-bold text-slate-500">Standar: 2.0 - 3.5 Bar</span>
        </div>

        @php
        $readings = [
            ['time' => '00:00', 'bar' => 3.2, 'pct' => 91, 'peak' => false],
            ['time' => '04:00', 'bar' => 3.1, 'pct' => 88, 'peak' => false],
            ['time' => '08:00 (Puncak Pagi)', 'bar' => 2.4, 'pct' => 68, 'peak' => true],
            ['time' => '12:00', 'bar' => 2.9, 'pct' => 82, 'peak' => false],
            ['time' => '16:00 (Puncak Sore)', 'bar' => 2.5, 'pct' => 71, 'peak' => true],
            ['time' => '20:00', 'bar' => 2.8, 'pct' => 80, 'peak' => false],
        ];
        @endphp

        <!-- Visual Bar Chart -->
        <div class="p-6 rounded-2xl bg-[#F8FAFC] border border-slate-200 space-y-4">
            <div class="grid grid-cols-6 gap-2 sm:gap-4 items-end h-48 pt-6">
                @foreach($readings as $r)
                    <div class="flex flex-col items-center gap-2 h-full justify-end group">
                        <span class="text-[10px] font-mono font-bold text-slate-700 block group-hover:text-[#0284C7]">
                            {{ $r['bar'] }} Bar
                        </span>
                        <div class="w-full max-w-[42px] bg-slate-200 rounded-xl overflow-hidden flex flex-col justify-end p-1 h-36">
                            <div
                                class="w-full rounded-lg transition-all duration-500 {{ $r['peak'] ? 'bg-amber-500' : 'bg-[#0284C7]' }}"
                                style="height: {{ $r['pct'] }}%;"
                            ></div>
                        </div>
                        <span class="text-[10px] text-slate-500 text-center font-mono leading-tight block">
                            {{ $r['time'] }}
                        </span>
                    </div>
                @endforeach
            </div>

            <!-- Keterangan Legend -->
            <div class="pt-3 border-t border-slate-200 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500">
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-[#0284C7]"></span>
                        <span>Tekanan Normal (Siang & Malam)</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-amber-500"></span>
                        <span>Beban Puncak Konsumsi Warga</span>
                    </div>
                </div>
                <span class="text-[11px] text-slate-400">Pompa booster otomatis aktif saat tekanan &lt; 2.2 Bar</span>
            </div>
        </div>

    </div>

</div>
@endsection
