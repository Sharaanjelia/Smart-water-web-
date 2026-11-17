@extends('layouts.admin')

@section('title', 'Jaringan Pipa Distribusi PDAM')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/water-monitoring') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Konsol SCADA</span>
        </a>
        <span class="text-xs font-mono font-bold text-slate-400">JARINGAN TRANSMISI KOTA</span>
    </div>

    <!-- Header Pengantar -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Pilih Sektor Distribusi untuk Analisis Pipa
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
            Pengawasan telemetri hidrolika dan stabilitas tekanan pipa air bersih per wilayah pelayanan di Kota Payakumbuh.
        </p>
    </div>

    <!-- DAFTAR KARTU SEKTOR JARINGAN (DIST-A, DIST-B, DIST-C) -->
    @php
    $networkSectors = [
        [
            'id' => 'DIST-A',
            'code' => 'DIST-A-MTR-01',
            'name' => 'Sektor Distribusi Utara (DIST-A)',
            'status' => 'NORMAL',
            'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'coverage' => 'Mencakup Kelurahan Balai Nan Duo, Tan Malaka, Koto Nan Ompek, dan sekitarnya (~1.250 Pelanggan).',
            'pipeDiameter' => '300 mm (Ductile Iron)',
            'pressure' => 2.8,
            'flow' => 18.5,
        ],
        [
            'id' => 'DIST-B',
            'code' => 'DIST-B-MTR-02',
            'name' => 'Sektor Distribusi Timur (DIST-B)',
            'status' => 'NORMAL',
            'badgeColor' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'coverage' => 'Mencakup Kompleks Perumahan Tiakar Indah, Payobasung, dan Payakumbuh Timur (~1.840 Pelanggan).',
            'pipeDiameter' => '350 mm (Steel HDPE)',
            'pressure' => 2.6,
            'flow' => 22.0,
        ],
        [
            'id' => 'DIST-C',
            'code' => 'DIST-C-MTR-03',
            'name' => 'Sektor Distribusi Selatan (DIST-C)',
            'status' => 'WASPADAI',
            'badgeColor' => 'bg-amber-50 text-amber-800 border-amber-200',
            'coverage' => 'Mencakup Wilayah Limbukan, Sawah Padang, dan Payakumbuh Selatan (~920 Pelanggan).',
            'pipeDiameter' => '250 mm (HDPE)',
            'pressure' => 2.2,
            'flow' => 15.1,
        ],
    ];
    @endphp

    <div class="space-y-4">
        @foreach($networkSectors as $ns)
            <a
                href="{{ url('/distribution-detail?sector=' . $ns['id']) }}"
                class="block bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs hover:border-sky-300 transition-all hover:scale-[1.01] group"
            >
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-sky-50 text-[#0284C7] border border-sky-200 flex items-center justify-center shrink-0">
                            <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="3"/><circle cx="19" cy="6" r="2"/><circle cx="5" cy="6" r="2"/><circle cx="12" cy="19" r="2"/>
                                <line x1="12" x2="12" y1="15" y2="17"/><line x1="10" x2="6.5" y1="10" y2="7.5"/><line x1="14" x2="17.5" y1="10" y2="7.5"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <div class="flex items-center gap-2">
                                <h3 class="text-base sm:text-lg font-black text-slate-900 group-hover:text-[#0284C7] transition-colors">
                                    {{ $ns['name'] }}
                                </h3>
                            </div>
                            <span class="font-mono text-xs text-slate-400 block">{{ $ns['code'] }} • Pipa {{ $ns['pipeDiameter'] }}</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 self-start sm:self-auto">
                        <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $ns['badgeColor'] }}">
                            {{ $ns['status'] }}
                        </span>
                        <div class="w-8 h-8 rounded-full bg-slate-100 group-hover:bg-[#0284C7] group-hover:text-white flex items-center justify-center text-slate-500 transition-colors">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <p class="text-xs text-slate-600 pt-3 leading-relaxed">
                    {{ $ns['coverage'] }}
                </p>

                <!-- Baris Metrik Mini -->
                <div class="pt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
                    <div class="p-3 rounded-2xl bg-slate-50">
                        <span class="text-slate-400 text-[10px] block">Tekanan Pipa</span>
                        <strong class="text-sm font-mono font-bold text-slate-900">{{ $ns['pressure'] }} Bar</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50">
                        <span class="text-slate-400 text-[10px] block">Debit Pasokan</span>
                        <strong class="text-sm font-mono font-bold text-slate-900">{{ $ns['flow'] }} L/s</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50">
                        <span class="text-slate-400 text-[10px] block">Pipa Transmisi</span>
                        <strong class="text-xs font-bold text-slate-700">{{ $ns['pipeDiameter'] }}</strong>
                    </div>
                    <div class="p-3 rounded-2xl bg-slate-50">
                        <span class="text-slate-400 text-[10px] block">Sensor Telemetri</span>
                        <strong class="text-xs font-bold text-emerald-700">Online 24/7</strong>
                    </div>
                </div>
            </a>
        @endforeach
    </div>

</div>
@endsection
