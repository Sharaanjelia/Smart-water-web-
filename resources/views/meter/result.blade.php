@extends('layouts.app')

@section('title', 'Hasil Pembacaan AI-OCR')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header & Navigasi Kembali -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/scanning-meter') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Ulangi Pindai</span>
        </a>
        <h2 class="text-sm font-extrabold text-slate-800">Hasil Pembacaan AI</h2>
        <div class="w-12"></div>
    </div>

    <!-- Pratinjau Foto Meteran (Photo Preview Box, 200px) -->
    <div class="relative h-[200px] w-full rounded-2xl bg-slate-900 border border-slate-200 overflow-hidden shadow-sm flex flex-col items-center justify-center text-center p-4">
        <!-- Mock foto meteran / visual display -->
        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent z-10"></div>
        <div class="w-16 h-16 rounded-2xl bg-cyan-900/60 text-cyan-300 flex items-center justify-center mb-2 z-20">
            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                <circle cx="12" cy="13" r="4"/>
            </svg>
        </div>
        <span class="z-20 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500 text-white text-xs font-bold shadow-md">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            <span>Foto Berhasil Dipindai</span>
        </span>
    </div>

    <!-- KARTU HASIL AI (AI Detection Card) -->
    <div class="bg-[#F0FDF4] rounded-3xl border border-[#BBF7D0] p-6 text-center space-y-4 shadow-xs">
        
        <!-- Header Badge -->
        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-[#16A34A] text-xs font-bold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span>Angka Meter Terdeteksi AI-OCR</span>
        </div>

        <!-- Tampilan Visual Odometer (Skala 1.2x) -->
        <div class="py-2">
            <div class="inline-flex items-center justify-center gap-2 bg-slate-950 p-3.5 rounded-2xl border-2 border-slate-700 shadow-xl">
                @php $scannedDigits = str_split($scannedValue ?? '01019'); @endphp
                @foreach($scannedDigits as $idx => $dg)
                    <div class="w-11 h-16 bg-slate-900 rounded-xl border border-slate-800 flex items-center justify-center shadow-inner relative">
                        <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-[1px] bg-slate-800"></div>
                        <span class="font-mono font-black text-3xl {{ $idx >= 3 ? 'text-amber-400' : 'text-white' }}">
                            {{ $dg }}
                        </span>
                    </div>
                @endforeach
                <div class="pl-2 font-mono font-black text-base text-cyan-400">
                    m³
                </div>
            </div>
        </div>

        <!-- Teks Indikator Akurasi -->
        <div class="text-xs font-bold text-emerald-800">
            Tingkat Akurasi AI: <span class="font-mono text-sm text-emerald-900 font-extrabold">98.4%</span>
        </div>
    </div>

    <!-- PERBANDINGAN BACAAN (Komparasi Dua Kolom) -->
    <div class="bg-[#F8FAFC] rounded-2xl border border-slate-200 p-4 grid grid-cols-2 divide-x divide-slate-200 text-center">
        <div class="px-3 space-y-1">
            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block">Bacaan Lalu</span>
            <span class="font-mono text-lg font-bold text-slate-700 block">
                {{ $previousValue ?? '01000' }} m³
            </span>
        </div>
        <div class="px-3 space-y-1">
            <span class="text-[11px] font-bold text-[#0284C7] uppercase tracking-wider block">Bacaan Baru</span>
            <span class="font-mono text-lg font-black text-[#0284C7] block">
                {{ $scannedValue ?? '01019' }} m³
            </span>
        </div>
    </div>

    <!-- TOMBOL AKSI GANDA -->
    <div class="grid grid-cols-2 gap-3 pt-2">
        <a
            href="{{ url('/scanning-meter') }}"
            class="py-3 px-4 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
        >
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                <path d="M3 3v5h5"/>
                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                <path d="M16 21h5v-5"/>
            </svg>
            <span>Foto Ulang</span>
        </a>

        <a
            href="{{ url('/meter-confirmation?scanned=' . ($scannedValue ?? '01019')) }}"
            class="py-3 px-4 rounded-xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-sky-600/20 transition-all"
        >
            <span>Lanjutkan</span>
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6"/>
            </svg>
        </a>
    </div>

</div>
@endsection
