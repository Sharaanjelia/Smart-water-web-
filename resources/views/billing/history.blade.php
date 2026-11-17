@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Rekening')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between">
        <a href="{{ url('/bill') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Tagihan Aktif</span>
        </a>
        <button
            type="button"
            onclick="window.print()"
            class="px-3.5 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer"
        >
            <svg class="w-3.5 h-3.5 text-slate-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/>
            </svg>
            <span>Cetak Rekap</span>
        </button>
    </div>

    <!-- Judul & Deskripsi -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Riwayat Pembayaran Rekening
        </h1>
        <p class="text-xs sm:text-sm text-slate-500">
            Arsip lengkap seluruh transaksi pembayaran rekening air PDAM Kota Payakumbuh yang telah lunas terverifikasi.
        </p>
    </div>

    <!-- Daftar Kartu Riwayat Pembayaran -->
    @php
    $bills = [
        ['period' => 'Maret 2025', 'usage' => '18.2 m³', 'amount' => 144560, 'method' => 'BCA Virtual Account', 'paidAt' => '10 Maret 2025, 09:14 WIB', 'trx' => 'TRX-PYK-20250310-891'],
        ['period' => 'Februari 2025', 'usage' => '17.5 m³', 'amount' => 139100, 'method' => 'QRIS Bank Nagari', 'paidAt' => '08 Februari 2025, 14:22 WIB', 'trx' => 'TRX-PYK-20250208-412'],
        ['period' => 'Januari 2025', 'usage' => '19.0 m³', 'amount' => 150800, 'method' => 'Mandiri Virtual Account', 'paidAt' => '12 Januari 2025, 10:05 WIB', 'trx' => 'TRX-PYK-20250112-654'],
        ['period' => 'Desember 2024', 'usage' => '20.1 m³', 'amount' => 159380, 'method' => 'BCA Virtual Account', 'paidAt' => '09 Desember 2024, 16:30 WIB', 'trx' => 'TRX-PYK-20241209-119'],
        ['period' => 'November 2024', 'usage' => '16.8 m³', 'amount' => 133640, 'method' => 'DANA E-Wallet', 'paidAt' => '11 November 2024, 08:45 WIB', 'trx' => 'TRX-PYK-20241111-782'],
        ['period' => 'Oktober 2024', 'usage' => '15.4 m³', 'amount' => 122720, 'method' => 'BCA Virtual Account', 'paidAt' => '10 Oktober 2024, 11:12 WIB', 'trx' => 'TRX-PYK-20241010-330'],
    ];
    @endphp

    <div class="space-y-3" id="historyListWrapper">
        <!-- April 2025 card injected dynamically if paid -->
        @foreach($bills as $b)
            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-sky-300 transition-colors">
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-600 border border-emerald-200 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><polyline points="9 15 11 17 15 13"/>
                        </svg>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">{{ $b['period'] }}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200 text-[10px] font-bold">
                                Lunas
                            </span>
                        </div>
                        <p class="text-slate-500 font-mono text-xs">{{ $b['trx'] }}</p>
                        <p class="text-slate-400 text-[11px]">
                            Metode: <strong class="text-slate-700">{{ $b['method'] }}</strong> • Pemakaian: <strong class="text-slate-700">{{ $b['usage'] }}</strong> • Tanggal: {{ $b['paidAt'] }}
                        </p>
                    </div>
                </div>

                <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-2 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Pembayaran</span>
                        <span class="font-mono text-base sm:text-lg font-black text-slate-900">
                            Rp {{ number_format($b['amount'], 0, ',', '.') }}
                        </span>
                    </div>

                    <a
                        href="{{ url('/payment-success') }}"
                        class="px-3 py-1.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1 transition-colors"
                    >
                        <span>Lihat Bukti</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!window.SWC) return;
    const aprilBill = window.SWC.getBill('INV-PYK-2025-04-1000');
    if (aprilBill && aprilBill.status === 'PAID') {
        const wrapper = document.getElementById('historyListWrapper');
        if (wrapper) {
            const card = document.createElement('div');
            card.className = 'bg-white rounded-3xl border border-emerald-200/90 p-5 sm:p-6 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:border-emerald-300 transition-colors animate-fade-zoom';
            card.innerHTML = `
                <div class="flex items-start gap-4">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-100 text-emerald-600 border border-emerald-300 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                    </div>
                    <div class="space-y-1 text-xs">
                        <div class="flex items-center gap-2">
                            <h3 class="font-extrabold text-slate-900 text-sm sm:text-base">${aprilBill.period}</h3>
                            <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300 text-[10px] font-bold">
                                Baru Saja Lunas
                            </span>
                        </div>
                        <p class="text-slate-500 font-mono text-xs">${aprilBill.inv}</p>
                        <p class="text-slate-400 text-[11px]">
                            Metode: <strong class="text-slate-700">${aprilBill.method || 'Saldo Dompet Smart Water'}</strong> • Pemakaian: <strong class="text-slate-700">${aprilBill.usage}</strong> • Tanggal: ${aprilBill.paidAt || 'Hari ini'}
                        </p>
                    </div>
                </div>

                <div class="sm:text-right flex sm:flex-col items-center sm:items-end justify-between gap-2 border-t sm:border-t-0 border-slate-100 pt-3 sm:pt-0">
                    <div>
                        <span class="text-[10px] text-slate-400 uppercase font-bold block">Total Pembayaran</span>
                        <span class="font-mono text-base sm:text-lg font-black text-emerald-700">
                            Rp ${Number(aprilBill.amount).toLocaleString('id-ID')}
                        </span>
                    </div>

                    <a
                        href="{{ url('/payment-success') }}?method=${encodeURIComponent(aprilBill.method || 'Saldo Dompet Smart Water')}"
                        class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 text-xs font-bold flex items-center gap-1 transition-colors"
                    >
                        <span>Lihat Bukti</span>
                        <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </a>
                </div>
            `;
            wrapper.prepend(card);
        }
    }
});
</script>
@endsection
