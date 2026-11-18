@extends('layouts.app')

@section('title', 'Rincian Lembar Tagihan Rekening')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ url('/bill') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Tagihan</span>
        </a>
        <div class="flex items-center gap-2">
            @if(isset($bill) && $bill)
                @if($bill->isSimulation())
                    <span class="px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-extrabold uppercase tracking-wider">
                        SIMULASI / DEMO
                    </span>
                @else
                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-extrabold uppercase tracking-wider">
                        TAGIHAN RESMI
                    </span>
                @endif
            @endif
            <span class="text-xs font-mono font-bold text-slate-400">FAKTUR ELEKTRONIK</span>
        </div>
    </div>

    @if(isset($bill) && $bill)
        <!-- HEADER GRADIENT RINGKASAN TAGIHAN (#0062D6 ke #0284C7) -->
        <div class="bg-gradient-to-r from-[#0062D6] to-[#0284C7] rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-sky-600/20 space-y-4 relative overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/20 pb-4">
                <div>
                    <span class="text-[11px] font-bold text-sky-200 uppercase tracking-wider block">Periode Rekening</span>
                    <h2 class="text-xl sm:text-2xl font-black text-white">
                        {{ \Carbon\Carbon::hasFormat($bill->billing_period, 'Y-m') ? \Carbon\Carbon::createFromFormat('Y-m', $bill->billing_period)->translatedFormat('F Y') : $bill->billing_period }}
                    </h2>
                    <span class="font-mono text-xs text-sky-100 opacity-90">{{ $bill->bill_number }}</span>
                </div>
                <div>
                    @if($bill->status === 'PAID')
                        <span id="detailStatusBadge" class="px-3.5 py-1 rounded-full bg-emerald-500/90 backdrop-blur-md text-white text-xs font-black tracking-wider border border-emerald-300">
                            LUNAS
                        </span>
                    @elseif($bill->status === 'PENDING_VERIFICATION')
                        <span id="detailStatusBadge" class="px-3.5 py-1 rounded-full bg-amber-500/90 backdrop-blur-md text-white text-xs font-black tracking-wider border border-amber-300">
                            MENUNGGU VERIFIKASI
                        </span>
                    @else
                        <span id="detailStatusBadge" class="px-3.5 py-1 rounded-full bg-white/20 backdrop-blur-md text-white text-xs font-black tracking-wider border border-white/30">
                            BELUM BAYAR
                        </span>
                    @endif
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-2">
                <div>
                    <span class="text-xs text-sky-100 block">Total Tagihan Bersih:</span>
                    <span class="text-3xl sm:text-4xl font-mono font-black text-white tracking-tight">
                        Rp {{ number_format($bill->total_amount, 0, ',', '.') }}
                    </span>
                </div>
                <span class="text-xs text-sky-100 font-medium">
                    Batas Jatuh Tempo: <strong class="text-white">{{ $bill->due_date ? \Carbon\Carbon::parse($bill->due_date)->translatedFormat('d F Y') : '-' }}</strong>
                </span>
            </div>
        </div>

        <!-- KARTU 1: DATA SAMBUNGAN PELANGGAN -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
                <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                <span>Data Sambungan Pelanggan</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div>
                    <span class="text-slate-400 block text-[11px]">Nama Pelanggan</span>
                    <strong class="text-slate-900 font-bold text-sm">{{ $bill->user?->name ?? 'Pelanggan PDAM' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">ID Pelanggan (Nomor Sambungan)</span>
                    <strong class="text-slate-900 font-mono font-bold">{{ $bill->customer_id ?? ($bill->user?->customerProfile?->customer_id ?? '-') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Nomor Seri Meter</span>
                    <strong class="text-slate-900 font-mono font-bold">{{ $bill->meter_number ?? ($bill->user?->customerProfile?->meter_number ?? '-') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block text-[11px]">Zona Distribusi</span>
                    <strong class="text-slate-900 font-bold">{{ $bill->meterReading?->zone ?? ($bill->user?->customerProfile?->zone ?? 'Wilayah Layanan Payakumbuh') }}</strong>
                </div>
                <div class="sm:col-span-2">
                    <span class="text-slate-400 block text-[11px]">Golongan Tarif</span>
                    <strong class="text-slate-900 font-bold">{{ $bill->tariff_category ?? '2A - Rumah Tangga Menengah' }}</strong>
                </div>
            </div>
        </div>

        <!-- KARTU 2: CATATAN METERAN AIR -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
                <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
                <span>Catatan Meteran Air</span>
            </div>

            <div class="grid grid-cols-3 gap-3 text-center text-xs">
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <span class="text-slate-400 block text-[11px]">Meter Lalu</span>
                    <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format($bill->previous_reading, 0, ',', '.') }} m³</span>
                </div>
                <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 space-y-1">
                    <span class="text-slate-400 block text-[11px]">Meter Kini</span>
                    <span class="font-mono font-bold text-slate-800 text-sm">{{ number_format($bill->current_reading, 0, ',', '.') }} m³</span>
                </div>
                <div class="p-3 rounded-2xl bg-sky-50 border border-sky-100 space-y-1">
                    <span class="text-[#0284C7] block text-[11px] font-bold">Pemakaian</span>
                    <span class="font-mono font-black text-[#0284C7] text-base">{{ number_format($bill->consumption, 1, ',', '.') }} m³</span>
                </div>
            </div>
        </div>

        <!-- KARTU 3: RINCIAN PERHITUNGAN BIAYA -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
            <div class="flex items-center gap-2 text-xs font-bold text-slate-800 border-b border-slate-100 pb-3">
                <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="3" rx="2"/><line x1="8" x2="16" y1="8" y2="8"/><line x1="8" x2="16" y1="12" y2="12"/><line x1="8" x2="12" y1="16" y2="16"/>
                </svg>
                <span>Rincian Perhitungan Biaya Rekening</span>
            </div>

            <div class="space-y-2.5 text-xs">
                @if($bill->details && $bill->details->isNotEmpty())
                    @foreach($bill->details as $item)
                        <div class="flex justify-between text-slate-600">
                            <span>{{ $item->description }}</span>
                            <span class="font-mono font-bold text-slate-900">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="flex justify-between text-slate-600">
                        <span>Pemakaian Air ({{ number_format($bill->consumption, 1) }} m³)</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($bill->water_charge, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Administrasi & Beban</span>
                        <span class="font-mono font-bold text-slate-900">Rp {{ number_format($bill->admin_fee, 0, ',', '.') }}</span>
                    </div>
                    @if($bill->penalty_fee > 0)
                    <div class="flex justify-between text-rose-600">
                        <span>Denda Keterlambatan</span>
                        <span class="font-mono font-bold text-rose-600">Rp {{ number_format($bill->penalty_fee, 0, ',', '.') }}</span>
                    </div>
                    @endif
                @endif
                <div class="border-t border-slate-200 pt-3 flex justify-between items-baseline">
                    <strong class="text-sm font-black text-slate-900">Total Tagihan Rekening</strong>
                    <span class="text-xl font-mono font-black text-[#0284C7]">Rp {{ number_format($bill->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <!-- Tombol Aksi Pembayaran / Status Tagihan -->
        <div class="pt-2" id="detailCtaWrapper">
            @if($bill->isSimulation())
                <div class="w-full py-4 px-6 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span>Tagihan Simulasi — Pembayaran Resmi Tidak Dapat Dilakukan</span>
                </div>
            @elseif($bill->status === 'PAID')
                <a
                    id="detailCtaBtn"
                    href="{{ url('/payment-success') }}?bill_id={{ $bill->id }}"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-sm shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                    <span>Lihat Bukti Kuitansi Pembayaran Lunas</span>
                </a>
            @elseif($bill->status === 'PENDING_VERIFICATION')
                <div class="w-full py-4 px-6 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 font-bold text-xs sm:text-sm text-center flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 text-amber-600 shrink-0 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"/>
                        <path d="M12 2a10 10 0 0 1 10 10"/>
                    </svg>
                    <span>Bukti Pembayaran Sedang Diverifikasi oleh Petugas PDAM</span>
                </div>
            @else
                <a
                    id="detailCtaBtn"
                    href="{{ url('/payment-method') }}?bill_id={{ $bill->id }}"
                    class="w-full py-4 px-6 rounded-2xl bg-gradient-to-r from-[#0284C7] to-[#0369A1] hover:from-[#0369A1] hover:to-[#075985] text-white font-extrabold text-sm shadow-lg shadow-sky-600/25 flex items-center justify-center gap-2 transition-all cursor-pointer"
                >
                    <span id="detailCtaText">Lanjut ke Pembayaran (Rp {{ number_format($bill->total_amount, 0, ',', '.') }})</span>
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m9 18 6-6-6-6"/>
                    </svg>
                </a>
            @endif
        </div>

    @else
        <!-- FALLBACK DEMO / TIDAK ADA TAGIHAN TERPILIH -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-8 text-center space-y-4">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-sky-50 text-[#0284C7] flex items-center justify-center">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
            </div>
            <div class="space-y-1">
                <h3 class="font-extrabold text-slate-800 text-base">Tidak Ada Lembar Tagihan</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Saat ini tidak ada data lembar tagihan yang dipilih atau akun Anda belum memiliki tagihan aktif.
                </p>
            </div>
            <div>
                <a href="{{ url('/bill') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#0284C7] text-white text-xs font-bold hover:bg-[#0369A1] transition-colors">
                    Kembali ke Halaman Tagihan
                </a>
            </div>
        </div>
    @endif

</div>
@endsection
