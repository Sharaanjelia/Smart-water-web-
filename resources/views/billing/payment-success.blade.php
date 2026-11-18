@extends('layouts.app')

@section('title', 'Pembayaran Berhasil')

@section('content')
<div class="max-w-md mx-auto py-6 space-y-6 text-center">

    <!-- Lingkaran Centang Hijau Besar (90px) -->
    <div class="w-[90px] h-[90px] mx-auto rounded-full bg-[#DCFCE7] border-2 border-emerald-300 flex items-center justify-center text-[#16A34A] shadow-lg shadow-emerald-500/10">
        <svg class="w-12 h-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
    </div>

    <!-- Teks Judul & Deskripsi -->
    <div class="space-y-1.5">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Pembayaran Berhasil!
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-sm mx-auto leading-relaxed">
            Terima kasih! Rekening air PDAM Anda bulan ini telah lunas tercatat secara resmi di sistem kas daerah.
        </p>
    </div>

    <!-- BUKTI TRANSAKSI RESMI (Receipt Card) -->
    <div class="bg-[#F8FAFC] rounded-3xl border border-slate-200 p-6 text-left space-y-3.5 shadow-2xs">
        
        <div class="flex items-center justify-between border-b border-slate-200 pb-3">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status Pembayaran</span>
            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-emerald-100 text-[#16A34A] text-xs font-black">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                <span>LUNAS (Terverifikasi)</span>
            </span>
        </div>

        <div class="space-y-2 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-500">Nomor Transaksi</span>
                <span class="font-mono font-bold text-slate-800">{{ $invoiceNo ?? 'TRX-XND-982104' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Periode Rekening</span>
                <span class="font-semibold text-slate-800">{{ $period ?? 'April 2025' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">ID Pelanggan</span>
                <span class="font-mono font-semibold text-slate-800">1234567890 (Budi Santoso)</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kanal Pembayaran</span>
                <span class="font-semibold text-slate-800" id="successPaymentMethod">{{ request('method') ?? ($paymentMethod ?? 'Saldo Dompet Smart Water') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Waktu Pelunasan</span>
                <span class="text-slate-800 font-mono text-[11px]">{{ now()->format('d M Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Pembatas Garis Putus-Putus -->
        <div class="border-t border-dashed border-slate-300 pt-3 flex justify-between items-baseline">
            <strong class="text-xs uppercase font-bold text-slate-700">Total Dibayar</strong>
            <span class="font-mono text-xl font-black text-[#0284C7]">
                Rp {{ number_format($totalAmount ?? 150800, 0, ',', '.') }}
            </span>
        </div>
    </div>

    <!-- TOMBOL AKSI -->
    <div class="space-y-3 pt-2">
        <a
            href="{{ url('/bill') }}"
            class="w-full py-3.5 px-5 rounded-2xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-extrabold text-xs flex items-center justify-center gap-2 shadow-md shadow-sky-600/20 transition-all cursor-pointer"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
            <span>Lihat Status Tagihan Air & Riwayat Lunas &rarr;</span>
        </a>

        <div class="grid grid-cols-2 gap-3">
            <a
                href="{{ url('/beranda') }}"
                class="w-full py-3 px-4 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                </svg>
                <span>Beranda</span>
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="w-full py-3 px-4 rounded-2xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/>
                </svg>
                <span>Cetak Bukti</span>
            </button>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Audit Payment Safety: Halaman kuitansi hanya menampilkan data read-only.
    // Tidak ada mutasi status tagihan di sisi klien.
});
</script>
@endsection
