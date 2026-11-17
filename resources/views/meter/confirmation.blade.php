@extends('layouts.app')

@section('title', 'Validasi & Konfirmasi Meter Mandiri')

@section('content')
<div class="max-w-xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/meter-result?scanned=' . ($scannedValue ?? '01019')) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Hasil AI</span>
        </a>
        <span class="text-xs font-bold text-cyan-800 bg-cyan-50 px-3 py-1 rounded-full border border-cyan-200">
            Langkah 3 dari 3
        </span>
    </div>

    <!-- Teks Pengantar -->
    <div class="space-y-1">
        <h1 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">
            Periksa & Validasi Angka
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 leading-relaxed">
            Anda dapat mengoreksi angka di bawah ini jika hasil AI berbeda 1 digit dengan fisik meteran air di rumah Anda.
        </p>
    </div>

    <!-- Formulir Validasi & Kolom Koreksi Angka -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        
        <div class="space-y-2">
            <label for="meterInput" class="text-xs font-bold text-slate-700 uppercase tracking-wide block">
                Angka Meter Pelaporan (5 Digit)
            </label>
            <div class="relative">
                <div class="absolute left-4 top-1/2 -translate-y-1/2 text-[#0284C7]">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="M12 22v-4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/>
                    </svg>
                </div>
                <input
                    type="number"
                    id="meterInput"
                    value="{{ (int)($scannedValue ?? 1019) }}"
                    oninput="recalculateUsage()"
                    class="w-full pl-12 pr-14 py-3.5 rounded-2xl border-2 border-slate-200 focus:border-[#0284C7] bg-[#F8FAFC] focus:bg-white text-slate-900 font-mono font-black text-2xl tracking-[0.2em] focus:outline-none focus:ring-4 focus:ring-sky-100 transition-all text-center"
                    min="1000"
                    max="99999"
                    required
                />
                <div class="absolute right-4 top-1/2 -translate-y-1/2 font-mono font-black text-sm text-[#0284C7]">
                    m³
                </div>
            </div>
            <p class="text-[11px] text-slate-400">
                Ketik langsung jika Anda ingin mengubah angka yang terbaca otomatis oleh kamera AI.
            </p>
        </div>

        <!-- KARTU ESTIMASI KALKULASI PEMAKAIAN (Invoice Preview) -->
        <div class="rounded-2xl bg-[#F8FAFC] border border-[#E2E8F0] p-4.5 space-y-3 text-xs">
            <div class="flex items-center justify-between font-bold text-slate-800 border-b border-slate-200 pb-2">
                <span>Rincian Estimasi Tagihan</span>
                <span class="text-[10px] text-slate-400 font-mono">Golongan 2A (Rp 7.800/m³)</span>
            </div>

            <div class="space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Bacaan Sebelumnya</span>
                    <span class="font-mono font-semibold text-slate-800">1000 m³</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Bacaan Bulan Ini</span>
                    <span id="currentReadingDisplay" class="font-mono font-semibold text-slate-800">1019 m³</span>
                </div>
                
                <div class="border-t border-slate-200/80 pt-2 flex justify-between items-center">
                    <span class="font-semibold text-slate-700">Total Pemakaian Air</span>
                    <span id="usageDisplay" class="font-mono font-black text-sm text-[#0284C7]">19.0 m³</span>
                </div>

                <div class="flex justify-between text-slate-600 text-[11px]">
                    <span>Tarif Pemakaian Air (x Rp 7.800)</span>
                    <span id="waterCostDisplay" class="font-mono font-semibold text-slate-800">Rp 148.200</span>
                </div>

                <div class="flex justify-between text-slate-600 text-[11px]">
                    <span>Biaya Administrasi & Beban</span>
                    <span class="font-mono font-semibold text-slate-800">Rp 2.600</span>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-2.5 flex justify-between items-baseline">
                <strong class="text-sm font-black text-slate-900">Estimasi Tagihan Baru</strong>
                <span id="totalBillDisplay" class="font-mono text-base font-black text-[#0284C7]">
                    Rp 150.800
                </span>
            </div>
        </div>

        <!-- Tombol Submit & Aksi Lanjutan -->
        <div class="space-y-3 pt-2">
            <button
                type="button"
                id="submitReportBtn"
                onclick="submitMeterReport()"
                class="w-full py-3.5 px-5 rounded-xl bg-gradient-to-r from-[#0284C7] to-[#0369A1] hover:from-[#0369A1] hover:to-[#075985] text-white font-extrabold text-sm shadow-md shadow-sky-600/20 transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg id="submitIcon" class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                </svg>
                <span id="submitText">Kirim Laporan Meter Mandiri</span>
            </button>

            <a
                href="{{ url('/bill') }}"
                class="w-full py-2.5 px-4 text-center text-xs font-bold text-slate-500 hover:text-[#0284C7] block transition-colors"
            >
                Lihat Lembar Tagihan & Bayar Langsung →
            </a>
        </div>

    </div>

</div>

<script>
const previousReading = 1000;
const tariffPerM3 = 7800;
const adminFee = 2600;

function recalculateUsage() {
    const inputVal = parseFloat(document.getElementById('meterInput').value) || previousReading;
    const usage = Math.max(0, inputVal - previousReading);
    const waterCost = usage * tariffPerM3;
    const totalBill = waterCost + adminFee;

    document.getElementById('currentReadingDisplay').textContent = inputVal + ' m³';
    document.getElementById('usageDisplay').textContent = usage.toFixed(1) + ' m³';
    document.getElementById('waterCostDisplay').textContent = 'Rp ' + waterCost.toLocaleString('id-ID');
    document.getElementById('totalBillDisplay').textContent = 'Rp ' + totalBill.toLocaleString('id-ID');
}

function submitMeterReport() {
    const btn = document.getElementById('submitReportBtn');
    const text = document.getElementById('submitText');
    const icon = document.getElementById('submitIcon');
    const inputVal = parseFloat(document.getElementById('meterInput').value) || 1019;
    const usage = Math.max(0, inputVal - previousReading);

    btn.disabled = true;
    text.textContent = 'Menyimpan & Menghitung Tagihan...';
    btn.classList.add('opacity-80');

    if (window.SWC) {
        window.SWC.addMeterReport({
            name: 'Budi Santoso',
            custId: '1234567890',
            meter: '01000',
            zone: 'DIST-A Sektor Utara',
            lastReading: 1000,
            ocrReading: inputVal,
            usage: usage
        });
    }

    setTimeout(() => {
        alert('Laporan stand meter (' + inputVal + ' m³) berhasil dikirim ke Command Center PDAM! Masuk dalam antrean verifikasi petugas lapangan.');
        window.location.href = "{{ url('/bill') }}";
    }, 800);
}
</script>
@endsection
