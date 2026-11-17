@extends('layouts.app')

@section('title', 'Kamera Pindai AI-OCR')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">

    <!-- Top Action Bar -->
    <div class="flex items-center justify-between px-2">
        <a href="{{ url('/report-meter') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Batal & Kembali</span>
        </a>
        <h2 class="text-sm font-extrabold text-slate-800 tracking-tight">
            Pindai Odometer Meter Air
        </h2>
        <button
            type="button"
            id="flashBtn"
            onclick="toggleFlash()"
            class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors"
            title="Nyalakan / Matikan Senter"
        >
            <svg id="flashIcon" class="w-4 h-4 text-slate-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>
            </svg>
        </button>
    </div>

    <!-- Area Kamera / Viewfinder Utama -->
    <div class="relative w-full h-[460px] sm:h-[500px] bg-[#0F172A] rounded-3xl overflow-hidden shadow-2xl flex flex-col items-center justify-center border-4 border-slate-800">
        
        <!-- Live Video Element / Simulasi Feed -->
        <video id="webcam" autoplay playsinline class="absolute inset-0 w-full h-full object-cover"></video>

        <!-- Fallback Poster / Simulasi Background jika webcam belum aktif -->
        <div id="simulatedFeed" class="absolute inset-0 bg-gradient-to-b from-slate-950 via-slate-900 to-slate-950 flex flex-col items-center justify-center p-6 text-center">
            <div class="w-20 h-20 rounded-full bg-cyan-900/40 border border-cyan-500/30 flex items-center justify-center mb-3">
                <svg class="w-10 h-10 text-cyan-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="M12 6v6l4 2"/>
                </svg>
            </div>
            <p class="text-xs text-slate-300 font-semibold max-w-xs">
                Pemindai AI-OCR Siap. Arahkan kotak retikel tepat ke angka odometer 5 digit.
            </p>
        </div>

        <!-- Darkness Vignette Overlay di Luar Retikel -->
        <div class="absolute inset-0 bg-black/40 pointer-events-none"></div>

        <!-- RETIKEL TARGET ODOMETER (280 x 120 px) -->
        <div class="relative z-10 w-[280px] h-[120px] rounded-2xl border-2 border-cyan-400 shadow-[0_0_20px_rgba(6,182,212,0.5)] flex items-center justify-center bg-black/20 backdrop-blur-2xs overflow-hidden">
            
            <!-- Sudut Retikel Aksentuasi -->
            <div class="absolute top-1 left-1 w-3 h-3 border-t-2 border-l-2 border-white"></div>
            <div class="absolute top-1 right-1 w-3 h-3 border-t-2 border-r-2 border-white"></div>
            <div class="absolute bottom-1 left-1 w-3 h-3 border-b-2 border-l-2 border-white"></div>
            <div class="absolute bottom-1 right-1 w-3 h-3 border-b-2 border-r-2 border-white"></div>

            <!-- GARIS LASER ANIMASI (Merah Menyala Bergerak Naik Turun) -->
            <div id="laserLine" class="absolute left-0 right-0 h-0.5 bg-[#EF4444] shadow-[0_0_12px_#EF4444] pointer-events-none transition-all duration-75"></div>

            <!-- Teks Watermark di dalam Retikel -->
            <div class="text-center pointer-events-none select-none">
                <span class="text-cyan-200/50 font-mono text-xl tracking-[0.3em] font-extrabold block">
                    [ 0 1 0 1 9 ]
                </span>
                <span class="text-[9px] text-cyan-300/70 uppercase tracking-widest font-mono">
                    AI ODOMETER DETECTOR
                </span>
            </div>
        </div>

        <!-- FLOATING GUIDE PILL -->
        <div class="relative z-10 mt-6 px-4 py-1.5 rounded-full bg-slate-900/80 backdrop-blur-md border border-cyan-500/40 text-cyan-300 text-xs font-semibold shadow-lg flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-cyan-400 animate-ping"></span>
            <span>Posisikan 5 angka odometer tepat di dalam kotak</span>
        </div>

        <!-- Loading Processing Overlay saat tombol Shutter Ditekan -->
        <div id="processingOverlay" class="hidden absolute inset-0 z-30 bg-slate-950/80 backdrop-blur-sm flex flex-col items-center justify-center text-center p-6 space-y-3">
            <div class="w-12 h-12 rounded-full border-4 border-cyan-500 border-t-transparent animate-spin"></div>
            <strong class="text-white text-sm font-extrabold block">Memproses Deteksi AI-OCR...</strong>
            <p class="text-xs text-slate-300">Mengekstrak karakter digit kubikasi meteran air</p>
        </div>

    </div>

    <!-- KONTROL BAWAH (Shutter, Mode Switch, Upload File) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-4">
        <div class="flex items-center justify-around gap-4">
            
            <!-- Tombol Switch Mode -->
            <button
                type="button"
                onclick="toggleCameraSource()"
                class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                    <path d="M3 3v5h5"/>
                    <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                    <path d="M16 21h5v-5"/>
                </svg>
                <span id="cameraModeText">Beralih ke Kamera Asli</span>
            </button>

            <!-- TOMBOL SHUTTER KAMERA LINGKARAN BESAR (76px) -->
            <button
                type="button"
                id="shutterBtn"
                onclick="triggerCapture()"
                class="w-19 h-19 rounded-full bg-gradient-to-tr from-[#0284C7] to-[#0369A1] hover:scale-105 active:scale-95 border-4 border-white shadow-xl shadow-sky-600/30 flex items-center justify-center transition-all cursor-pointer group"
                title="Tekan untuk Memotret dan Analisis OCR"
            >
                <div class="w-13 h-13 rounded-full bg-white/20 group-hover:bg-white/30 flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19 4h-3.5L14 2H10L8.5 4H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/>
                    </svg>
                </div>
            </button>

            <!-- Tombol Bantuan / Upload File -->
            <label class="px-4 py-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 font-bold text-xs flex items-center gap-2 transition-colors cursor-pointer">
                <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" x2="12" y1="3" y2="15"/>
                </svg>
                <span>Pilih Foto</span>
                <input type="file" accept="image/*" class="hidden" onchange="triggerCapture()" />
            </label>

        </div>
    </div>

</div>

<script>
// Animasi Laser Bergerak Naik-Turun
let laserPos = 0;
let laserDirection = 1;
const laserElem = document.getElementById('laserLine');

setInterval(() => {
    laserPos += laserDirection * 2.5;
    if (laserPos >= 116) {
        laserDirection = -1;
    } else if (laserPos <= 4) {
        laserDirection = 1;
    }
    if (laserElem) {
        laserElem.style.top = laserPos + 'px';
    }
}, 30);

// Webcam Access jika tersedia
let isRealCamera = false;
async function toggleCameraSource() {
    const video = document.getElementById('webcam');
    const simFeed = document.getElementById('simulatedFeed');
    const text = document.getElementById('cameraModeText');

    if (!isRealCamera) {
        try {
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            video.srcObject = stream;
            simFeed.classList.add('hidden');
            text.textContent = 'Mode Sampel Visual';
            isRealCamera = true;
        } catch (e) {
            console.warn('Webcam tidak dapat diakses:', e);
        }
    } else {
        if (video.srcObject) {
            video.srcObject.getTracks().forEach(track => track.stop());
            video.srcObject = null;
        }
        simFeed.classList.remove('hidden');
        text.textContent = 'Gunakan Kamera Langsung';
        isRealCamera = false;
    }
}

function toggleFlash() {
    const flashBtn = document.querySelector('button[onclick="toggleFlash()"]');
    if (flashBtn) {
        flashBtn.classList.toggle('text-amber-400');
        flashBtn.classList.toggle('border-amber-400');
    }
}

function triggerCapture() {
    const overlay = document.getElementById('processingOverlay');
    overlay.classList.remove('hidden');

    setTimeout(() => {
        window.location.href = "{{ url('/meter-result?scanned=01019&previous=01000') }}";
    }, 1200);
}
</script>
@endsection
