@extends('layouts.app')

@section('title', 'Lapor Meter Mandiri AI-OCR')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Judul & Deskripsi Halaman -->
    <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 tracking-tight">
            Lapor Meter Mandiri
        </h1>
        <p class="text-xs sm:text-sm text-slate-500 max-w-2xl">
            Pindai odometer fisik meteran air rumah Anda menggunakan kecerdasan buatan (AI-OCR). Pembacaan otomatis, praktis, dan langsung terhitung ke estimasi tagihan bulan berjalan.
        </p>
    </div>

    <!-- Grid 2 Kolom: Bacaan Terakhir & Upload Mandiri -->
    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 items-start">
        
        <!-- Kolom Kiri: Kartu Odometer Bacaan Terakhir & Panduan (7 cols) -->
        <div class="md:col-span-7 space-y-6">
            
            <!-- Kartu Bacaan Meter Sebelumnya -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2 text-xs font-bold text-slate-700">
                        <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/>
                        </svg>
                        <span>Bacaan Meter Sebelumnya</span>
                    </div>
                </div>

                <!-- Visualizer Odometer Meteran Air Mekanis -->
                <div class="bg-slate-900 rounded-2xl p-5 text-center space-y-3 shadow-inner">
                    <span class="text-[10px] text-slate-400 font-bold uppercase tracking-widest block">
                        Stand Odometer Terakhir (Bulan Lalu)
                    </span>

                    <!-- Kotak Odometer 5 Digit Mekanik -->
                    <div class="inline-flex items-center justify-center gap-1.5 bg-black p-2.5 rounded-xl border-2 border-slate-700 shadow-lg">
                        @php $digits = str_split($latestMeterValue ?? '01000'); @endphp
                        @foreach($digits as $idx => $d)
                            <div class="w-10 h-14 bg-slate-950 rounded-lg border border-slate-800 flex items-center justify-center relative overflow-hidden shadow-sm">
                                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 h-[1px] bg-slate-800/80 pointer-events-none"></div>
                                <span class="font-mono font-black text-2xl text-white tracking-wider {{ $idx >= 3 ? 'text-amber-400' : 'text-white' }}">
                                    {{ $d }}
                                </span>
                            </div>
                        @endforeach
                        <div class="pl-2 font-mono font-bold text-sm text-cyan-400">
                            m³
                        </div>
                    </div>

                    <p class="text-[11px] text-slate-400 font-medium">
                        ⚠️ Pastikan angka yang Anda foto hari ini <strong class="text-white">lebih besar atau sama</strong> dengan angka di atas.
                    </p>
                </div>
            </div>

            <!-- Panduan Pengambilan Foto (3 Baris Instruksi) -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                    </svg>
                    <span>Panduan Pemotretan Meteran yang Akurat</span>
                </h3>

                <div class="space-y-3.5 text-xs text-slate-600">
                    <!-- Baris 1: Pencahayaan Cukup -->
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <strong class="font-bold text-slate-900 block text-xs">Pencahayaan Cukup</strong>
                            <p class="text-slate-500 leading-relaxed">Hindari bayangan pekat pada kaca meteran. Nyalakan senter kamera jika kondisi meteran di tempat yang gelap.</p>
                        </div>
                    </div>

                    <!-- Baris 2: Fokus pada 5 Digit Angka Hitam -->
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-sky-100 text-[#0284C7] flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 7V5a2 2 0 0 1 2-2h2"/><path d="M17 3h2a2 2 0 0 1 2 2v2"/><path d="M21 17v2a2 2 0 0 1-2 2h-2"/><path d="M7 21H5a2 2 0 0 1-2-2v-2"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <strong class="font-bold text-slate-900 block text-xs">Fokus pada Baris Digit Odometer</strong>
                            <p class="text-slate-500 leading-relaxed">Arahkan kamera tegak lurus pada deretan angka odometer agar seluruh angka terbaca jelas tanpa distorsi.</p>
                        </div>
                    </div>

                    <!-- Baris 3: Kaca Meteran Bersih -->
                    <div class="flex items-start gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v4"/><path d="m4.93 4.93 2.83 2.83"/><path d="M2 12h4"/><path d="m4.93 19.07 2.83-2.83"/><path d="M12 22v-4"/><path d="m19.07 19.07-2.83-2.83"/><path d="M22 12h-4"/><path d="m19.07 4.93-2.83 2.83"/>
                            </svg>
                        </div>
                        <div class="space-y-0.5">
                            <strong class="font-bold text-slate-900 block text-xs">Kaca Meteran Bersih</strong>
                            <p class="text-slate-500 leading-relaxed">Lap kaca meteran air dari debu, embun air hujan, atau lumpur sebelum mengambil foto.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan: Aksi Mulai Pindai & Drag and Drop Upload (5 cols) -->
        <div class="md:col-span-5 space-y-6">
            
            <!-- Tombol Utama Buka Kamera AI-OCR -->
            <div class="bg-gradient-to-br from-[#0284C7] to-[#0369A1] rounded-3xl p-6 sm:p-7 text-white shadow-lg shadow-sky-600/20 space-y-4">
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur-xs flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                </div>
                <div class="space-y-1">
                    <h3 class="text-lg font-bold text-white">Mulai Kamera AI-OCR</h3>
                    <p class="text-xs text-sky-100 leading-relaxed">
                        Aktifkan viewfinder kamera web/HP untuk memindai angka meter secara otomatis dan real-time.
                    </p>
                </div>

                <a
                    href="{{ url('/scanning-meter') }}"
                    class="w-full py-3.5 px-4 rounded-xl bg-white hover:bg-slate-50 text-[#0284C7] font-extrabold text-xs flex items-center justify-center gap-2 shadow-sm transition-all"
                >
                    <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                        <circle cx="12" cy="13" r="4"/>
                    </svg>
                    <span>Mulai Pindai Meter Air (AI OCR)</span>
                </a>
            </div>

            <!-- Panel Khusus Web Desktop: Drag and Drop Upload Foto -->
            <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-4">
                <div class="border-b border-slate-100 pb-3">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide">
                        Atau Unggah Foto dari Komputer
                    </h4>
                </div>

                <form action="{{ url('/meter-result') }}" method="GET" enctype="multipart/form-data" class="space-y-3">
                    <input type="hidden" name="scanned" value="01019">
                    <input type="hidden" name="previous" value="{{ $latestMeterValue ?? '01000' }}">

                    <div class="border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-2xl p-6 text-center cursor-pointer transition-colors bg-slate-50/50 hover:bg-sky-50/30">
                        <svg class="w-8 h-8 text-slate-400 mx-auto mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" x2="12" y1="3" y2="15"/>
                        </svg>
                        <p class="text-xs font-semibold text-slate-700">Tarik file foto ke sini atau klik untuk memilih</p>
                        <p class="text-[11px] text-slate-400 mt-1">Maksimal 5 MB</p>
                        <input type="file" id="dropFile" accept="image/*" class="hidden" onchange="simulateFileUploaded()" />
                    </div>

                    <button
                        type="submit"
                        class="w-full py-2.5 px-4 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-700 font-bold text-xs flex items-center justify-center gap-2 transition-colors cursor-pointer"
                    >
                        <span>Gunakan Foto Sampel Meteran</span>
                        <svg class="w-3.5 h-3.5 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m9 18 6-6-6-6"/>
                        </svg>
                    </button>
                </form>
            </div>

        </div>

    </div>

</div>

<script>
function simulateFileUploaded() {
    window.location.href = "{{ url('/meter-result?scanned=01019&previous=01000') }}";
}
</script>
@endsection
