@extends('layouts.app')

@section('title', 'Tagihan Saya')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ url('/beranda') }}" class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Beranda</span>
        </a>
        <div class="flex items-center gap-3">
            @if(!($isLoggedIn ?? false))
            <button
                type="button"
                onclick="window.SWC && window.SWC.resetDemoData()"
                class="text-[11px] font-mono text-slate-400 hover:text-rose-500 transition-colors cursor-pointer"
                title="Reset status tagihan & saldo ke demo awal"
            >
                ↻ Reset Demo
            </button>
            @endif
            <a href="{{ url('/bill-history') }}" class="text-xs font-bold text-[#0284C7] hover:underline flex items-center gap-1">
                <span>Arsip Lengkap</span>
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m9 18 6-6-6-6"/>
                </svg>
            </a>
        </div>
    </div>

    <!-- Error Banner (Jika Fetch API Gagal) -->
    <div id="apiErrorBanner" class="hidden p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between gap-3 shadow-xs">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-rose-600 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span>Gagal memuat sinkronisasi tagihan terbaru dari server.</span>
        </div>
        <button type="button" onclick="window.location.reload()" class="px-3 py-1 rounded-lg bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 transition-colors cursor-pointer">
            Coba Lagi
        </button>
    </div>

    <!-- Judul Halaman -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Tagihan Rekening Air
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Kelola tagihan aktif tertunggak dan riwayat pembayaran rekening air PDAM Kota Payakumbuh.
        </p>
    </div>

    <!-- TAB SEGMENTASI (Tagihan Aktif vs Riwayat Tagihan) -->
    <div class="bg-slate-200/80 p-1 rounded-2xl flex items-center max-w-sm">
        <button
            type="button"
            id="tabActiveBtn"
            onclick="switchBillTab('active')"
            class="flex-1 py-2 px-3 rounded-xl font-bold text-xs text-white bg-[#0284C7] shadow-sm transition-all text-center cursor-pointer"
        >
            Tagihan Aktif (<span id="activeBillCount">{{ ($activeBill ?? null) ? 1 : 0 }}</span>)
        </button>
        <button
            type="button"
            id="tabHistoryBtn"
            onclick="switchBillTab('history')"
            class="flex-1 py-2 px-3 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all text-center cursor-pointer"
        >
            Riwayat Tagihan (<span id="historyBillCount">{{ ($paidBills ?? collect())->count() }}</span>)
        </button>
    </div>

    <!-- KONTEN TAB 1: TAGIHAN AKTIF -->
    <div id="contentActiveTab" class="space-y-4">
        
        <!-- KONDISI A: Kartu Faktur Tagihan Tertunggak (Tampil jika belum lunas) -->
        <div id="unpaidBillCard" class="{{ ($activeBill ?? null) ? '' : 'hidden' }} bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-xs space-y-6">
            @if($activeBill ?? null)
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
                <div class="space-y-1">
                    <div class="flex items-center gap-2">
                        <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">
                            Periode Rekening
                        </span>
                        @if($activeBill->isSimulation())
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 border border-amber-300 text-amber-900 text-[10px] font-black tracking-wide">
                                SIMULASI / DEMO
                            </span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-sky-100 border border-sky-300 text-[#0284C7] text-[10px] font-black tracking-wide">
                                TAGIHAN RESMI
                            </span>
                        @endif
                    </div>
                    <h2 class="text-xl sm:text-2xl font-black text-slate-900" id="billPeriodText">
                        {{ $activeBill->period_label }}
                    </h2>
                    <span class="text-xs text-slate-500 font-mono" id="billInvoiceText">
                        No. Invoice: {{ $activeBill->bill_number }}
                    </span>
                </div>

                <div class="flex items-center gap-2" id="billStatusBadgeWrapper">
                    @if($activeBill->status === 'UNPAID')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-rose-500 animate-pulse"></span>
                            <span>Belum Bayar</span>
                        </span>
                    @elseif($activeBill->status === 'PENDING_VERIFICATION')
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                            <span>Menunggu Verifikasi</span>
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Lunas</span>
                        </span>
                    @endif
                </div>
            </div>

            <!-- Nominal Besar & Jatuh Tempo -->
            <div class="bg-gradient-to-br from-slate-50 to-sky-50/40 rounded-2xl p-5 border border-sky-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Tagihan Tertunggak</span>
                    <div class="text-3xl sm:text-4xl font-mono font-black text-slate-900 tracking-tight mt-1" id="billTotalAmountText">
                        Rp {{ number_format($activeBill->total_amount, 0, ',', '.') }}
                    </div>
                </div>

                <div class="sm:text-right space-y-1">
                    <span class="text-xs font-semibold text-rose-700 bg-rose-100/70 px-2.5 py-1 rounded-lg inline-block" id="billDueDateText">
                        Jatuh tempo: {{ $activeBill->due_date ? \Carbon\Carbon::parse($activeBill->due_date)->locale('id')->isoFormat('D MMMM Y') : '-' }}
                    </span>
                    <p class="text-[11px] text-slate-500">
                        Bayar tepat waktu untuk menghindari denda keterlambatan.
                    </p>
                </div>
            </div>

            <!-- Rincian Komponen Biaya Ringkas -->
            <div class="space-y-2.5 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Pemakaian Air Bersih</span>
                    <span class="font-mono font-bold text-slate-800" id="billConsumptionText">{{ number_format($activeBill->consumption, 1) }} m³</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Administrasi & Beban Tetap</span>
                    <span class="font-mono font-bold text-slate-800" id="billAdminFeeText">Rp {{ number_format($activeBill->admin_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Biaya Pemeliharaan Meter</span>
                    <span class="font-mono font-bold text-slate-800" id="billMaintenanceFeeText">Rp {{ number_format($activeBill->maintenance_fee, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Denda Keterlambatan</span>
                    <span class="font-mono font-bold text-emerald-700" id="billPenaltyFeeText">Rp {{ number_format($activeBill->penalty_fee, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Dua Tombol Aksi -->
            <div class="pt-2 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-slate-100">
                <a
                    href="{{ url('/bill-detail') }}?id={{ $activeBill->id }}"
                    class="w-full sm:w-auto px-5 py-3 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-colors"
                >
                    <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/>
                    </svg>
                    <span>Rincian Lengkap</span>
                </a>

                @if($activeBill->status === 'PENDING_VERIFICATION')
                    <button
                        type="button"
                        disabled
                        class="w-full sm:w-auto px-6 py-3 rounded-xl bg-amber-500/80 text-white font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed"
                    >
                        <span>Bukti Menunggu Verifikasi Admin</span>
                    </button>
                @elseif($activeBill->isSimulation())
                    <div class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-[11px] font-semibold flex items-center justify-center gap-1.5 text-center" title="Tagihan simulasi tidak dapat dibayar">
                        <span>⚠️ Simulasi — Pembayaran Non-Aktif</span>
                    </div>
                @else
                    <button
                        type="button"
                        disabled
                        class="w-full sm:w-auto px-5 py-3 rounded-xl bg-slate-100 border border-slate-300 text-slate-600 font-bold text-xs flex items-center justify-center gap-2 cursor-not-allowed"
                        title="Fitur pembayaran sedang dipersiapkan untuk Tahap 10B"
                    >
                        <svg class="w-4 h-4 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span>Fitur pembayaran sedang dipersiapkan.</span>
                    </button>
                @endif
            </div>
            @endif
        </div>

        <!-- KONDISI B: Kartu Semua Tagihan Lunas (Tampil jika sudah lunas) -->
        <div id="allBillsPaidCard" class="{{ ($activeBill ?? null) ? 'hidden' : '' }} bg-gradient-to-br from-emerald-50/80 via-white to-teal-50/50 rounded-3xl border border-emerald-200/90 p-8 sm:p-10 shadow-xs text-center space-y-5 animate-fade-zoom">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-inner">
                <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            </div>

            <div class="space-y-1.5 max-w-md mx-auto">
                <h3 class="text-xl sm:text-2xl font-black text-slate-900">
                    Semua Tagihan Sudah Lunas 🎉
                </h3>
                <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                    Tidak ada tunggakan rekening air pada akun Anda saat ini. Seluruh tagihan telah diselesaikan.
                </p>
            </div>

            <div class="pt-2 flex flex-wrap items-center justify-center gap-3">
                @if(($paidBills ?? collect())->isNotEmpty())
                <a
                    href="{{ url('/payment-success') }}?bill_id={{ $paidBills->first()->id }}"
                    class="py-3 px-5 rounded-2xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center gap-2 shadow-md shadow-emerald-600/20 transition-all cursor-pointer"
                >
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>Lihat Kuitansi Pembayaran Terakhir</span>
                </a>
                @endif

                <button
                    type="button"
                    onclick="switchBillTab('history')"
                    class="py-3 px-5 rounded-2xl border border-slate-300 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center gap-2 transition-colors cursor-pointer"
                >
                    <span>Buka Riwayat Pelunasan &rarr;</span>
                </button>
            </div>
        </div>

    </div>

    <!-- KONTEN TAB 2: RIWAYAT TAGIHAN DENGAN SINKRONISASI REAKTIF -->
    <div id="contentHistoryTab" class="hidden space-y-3">
        <div id="historyListContainer" class="space-y-3">
            @if(($paidBills ?? collect())->isNotEmpty())
                @foreach($paidBills as $b)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-4.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs hover:border-sky-200 transition-colors">
                    <div class="flex items-start gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5 text-xs">
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-slate-900 text-sm">{{ $b->period_label }}</h3>
                                @if($b->isSimulation())
                                    <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 text-[10px] font-bold">Simulasi</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full bg-sky-50 text-sky-700 border border-sky-200 text-[10px] font-bold">Resmi</span>
                                @endif
                            </div>
                            <p class="text-slate-500 font-mono text-[11px]">{{ $b->bill_number }} • {{ $b->latestPayment?->payment_method ? ucwords(str_replace('_', ' ', $b->latestPayment->payment_method)) : 'Kanal Terverifikasi' }}</p>
                            <p class="text-slate-400 text-[10px]">Lunas pada: {{ $b->paid_at ? \Carbon\Carbon::parse($b->paid_at)->locale('id')->isoFormat('D MMMM Y, HH:mm') . ' WIB' : 'Telah diverifikasi' }} (Pemakaian: {{ number_format($b->consumption, 1) }} m³)</p>
                        </div>
                    </div>

                    <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-1 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                        <span class="font-mono font-bold text-slate-900 text-sm">
                            Rp {{ number_format($b->total_amount, 0, ',', '.') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                Lunas
                            </span>
                            <a href="{{ url('/payment-success') }}?bill_id={{ $b->id }}" class="text-[10px] text-[#0284C7] font-semibold hover:underline">
                                Kuitansi &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            @else
                <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 text-xs text-slate-400">
                    Belum ada riwayat tagihan yang tercatat.
                </div>
            @endif
        </div>
    </div>

</div>

<script>
const isCustomerLoggedIn = {{ ($isLoggedIn ?? false) ? 'true' : 'false' }};

function renderBillingView() {
    if (isCustomerLoggedIn) {
        // Pelanggan terautentikasi: data tagihan dirender langsung dari database MySQL oleh Blade.
        // Jangan timpa dengan data simulasi statis localStorage.
        return;
    }

    if (!window.SWC) return;

    const bills = window.SWC.getBills();
    const unpaid = bills.filter(b => b.status === 'UNPAID');
    const paid = bills.filter(b => b.status === 'PAID');

    // Update Counter Tab untuk Demo Non-Login
    const activeCountEl = document.getElementById('activeBillCount');
    const histCountEl = document.getElementById('historyBillCount');
    if (activeCountEl) activeCountEl.textContent = unpaid.length;
    if (histCountEl) histCountEl.textContent = paid.length;

    // Toggle Tampilan Tab Aktif untuk Demo Non-Login
    const unpaidCard = document.getElementById('unpaidBillCard');
    const allPaidCard = document.getElementById('allBillsPaidCard');

    if (unpaid.length === 0) {
        if (unpaidCard) unpaidCard.classList.add('hidden');
        if (allPaidCard) allPaidCard.classList.remove('hidden');
    } else {
        if (unpaidCard) unpaidCard.classList.remove('hidden');
        if (allPaidCard) allPaidCard.classList.add('hidden');
    }

    // Render Riwayat Tagihan Lunas Demo Non-Login
    const historyContainer = document.getElementById('historyListContainer');
    if (historyContainer) {
        if (paid.length === 0) {
            historyContainer.innerHTML = `
                <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 text-xs text-slate-400">
                    Belum ada riwayat tagihan yang tercatat.
                </div>
            `;
            return;
        }

        historyContainer.innerHTML = paid.map(b => `
            <div class="bg-white rounded-2xl border border-slate-200/90 p-4.5 sm:p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs hover:border-sky-200 transition-colors">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div class="space-y-0.5 text-xs">
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-900 text-sm">${b.period}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">Simulasi</span>
                        </div>
                        <p class="text-slate-500 font-mono text-[11px]">${b.inv} • ${b.method || 'Kanal Terverifikasi'}</p>
                        <p class="text-slate-400 text-[10px]">Lunas pada: ${b.paidAt || 'Telah diverifikasi'} (Pemakaian: ${b.usage})</p>
                    </div>
                </div>

                <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-1 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-100">
                    <span class="font-mono font-bold text-slate-900 text-sm">
                        Rp ${Number(b.amount).toLocaleString('id-ID')}
                    </span>
                    <div class="flex items-center gap-1.5">
                        <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                            Lunas
                        </span>
                        <a href="{{ url('/payment-success') }}?method=${encodeURIComponent(b.method || 'Kanal Online')}" class="text-[10px] text-[#0284C7] font-semibold hover:underline">
                            Kuitansi &rarr;
                        </a>
                    </div>
                </div>
            </div>
        `).join('');
    }
}

function switchBillTab(tab) {
    const activeBtn = document.getElementById('tabActiveBtn');
    const histBtn = document.getElementById('tabHistoryBtn');
    const activeContent = document.getElementById('contentActiveTab');
    const histContent = document.getElementById('contentHistoryTab');

    if (tab === 'active') {
        activeBtn.className = 'flex-1 py-2 px-3 rounded-xl font-bold text-xs text-white bg-[#0284C7] shadow-sm transition-all text-center cursor-pointer';
        histBtn.className = 'flex-1 py-2 px-3 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all text-center cursor-pointer';
        activeContent.classList.remove('hidden');
        histContent.classList.add('hidden');
    } else {
        histBtn.className = 'flex-1 py-2 px-3 rounded-xl font-bold text-xs text-white bg-[#0284C7] shadow-sm transition-all text-center cursor-pointer';
        activeBtn.className = 'flex-1 py-2 px-3 rounded-xl font-bold text-xs text-slate-600 hover:text-slate-900 transition-all text-center cursor-pointer';
        histContent.classList.remove('hidden');
        activeContent.classList.add('hidden');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    renderBillingView();
});

window.addEventListener('swc:state-change', () => {
    renderBillingView();
});
</script>
@endsection
