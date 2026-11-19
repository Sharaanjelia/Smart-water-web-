@php
    if (!headers_sent()) {
        header('Location: ' . url('/login'), true, 302);
        exit;
    }
@endphp
<!doctype html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal Masuk Petugas &amp; SCADA - Perumda Air Minum Tirta Sago</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <style>
      body {
        font-family: 'Plus Jakarta Sans', sans-serif;
      }
      .font-mono {
        font-family: 'JetBrains Mono', monospace;
      }
    </style>
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="relative min-h-screen flex flex-col justify-between text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900 overflow-x-hidden">
      
      <!-- Full-Page Scenic Background with Ambient Frosted Gradients (SAMA PERSIS DENGAN USER LOGIN) -->
      <div class="fixed inset-0 z-0 pointer-events-none select-none">
        <img
          src="{{ asset('assets/images/hero-lake-bright.jpg') }}"
          alt="Lanskap Danau dan Sumber Air Alami Payakumbuh"
          class="w-full h-full object-cover object-center transform scale-105"
        />
        <div class="absolute inset-0 bg-gradient-to-tr from-[#EBF5FB]/88 via-white/80 to-[#F0F8FF]/85 backdrop-blur-[3px]"></div>
        <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full bg-cyan-400/20 blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-32 -right-32 w-96 h-96 rounded-full bg-sky-500/15 blur-3xl pointer-events-none"></div>
      </div>

      <!-- Header Atas Minimalis -->
      <header class="relative z-10 w-full px-6 sm:px-10 lg:px-16 py-3.5 flex items-center justify-between border-b border-white/60 bg-white/70 backdrop-blur-md shadow-2xs">
        <div class="flex items-center gap-3">
          <a href="{{ url('/beranda') }}" class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 flex items-center justify-center text-white shadow-xs">
              <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
              </svg>
            </div>
            <div class="flex flex-col">
              <span class="text-xs font-black text-slate-900 tracking-tight leading-none">Smart Water</span>
              <span class="text-[10px] text-cyan-700 font-mono font-bold leading-none mt-0.5">PORTAL PETUGAS &amp; SCADA</span>
            </div>
          </a>
          <div class="hidden sm:block h-6 w-px bg-slate-300 mx-3"></div>
          <span class="hidden sm:inline-block text-xs text-slate-500 font-medium">
            Command Center Perumda Air Minum Tirta Sago
          </span>
        </div>

        <nav class="flex items-center gap-4 text-xs font-semibold">
          <a
            href="{{ url('/beranda') }}"
            class="flex items-center gap-1.5 text-slate-600 hover:text-cyan-700 transition-colors"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <span>Beranda</span>
          </a>
          <a
            href="{{ url('/login') }}"
            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-cyan-800 bg-cyan-50 border border-cyan-200 hover:bg-cyan-100 transition-colors"
          >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <span>Masuk Pelanggan / Warga</span>
          </a>
        </nav>
      </header>

      <!-- Main Layout: Kolom Kiri Hero & Kolom Kanan Kartu Autentikasi -->
      <main class="relative z-10 flex-1 max-w-7xl w-full mx-auto px-5 sm:px-8 lg:px-12 pt-3 sm:pt-4 lg:pt-5 pb-8 lg:pb-10 grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-12 items-start">
        
        <!-- KOLOM KIRI: Informasi & Fitur Petugas -->
        <div class="lg:col-span-7 space-y-6">
          <div class="space-y-3">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 backdrop-blur-md border border-white/80 text-[#0284C7] text-[11px] font-bold shadow-xs">
              <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
              <span>SCADA Node Central: IPA-PYK-01 (Online)</span>
            </div>
            <h1 class="text-3xl sm:text-4xl lg:text-[40px] font-black text-[#0F2C59] tracking-tight leading-[1.15] drop-shadow-xs">
              Portal Operasional &amp; <br class="hidden sm:inline" />
              SCADA Petugas PDAM
            </h1>
            <p class="text-sm sm:text-base text-slate-700 font-medium leading-relaxed max-w-xl">
              Pusat kendali operasional terpadu Perumda Air Minum Tirta Sago Kota Payakumbuh untuk monitoring telemetri pipa, volume reservoir, dan verifikasi lapor meter AI-OCR.
            </p>
          </div>

          <!-- 3 Fitur Petugas -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 pt-1">
            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-[#0284C7] to-sky-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">Verifikasi AI-OCR</h4>
                <p class="text-[11px] text-slate-600 leading-tight">Validasi foto angka stand meter mandiri warga.</p>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-cyan-600 to-teal-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20"/><path d="m17 5-5-3-5 3"/><path d="m17 19-5 3-5-3"/></svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">SCADA Telemetri</h4>
                <p class="text-[11px] text-slate-600 leading-tight">Pantau tekanan (Bar) &amp; laju debit reservoir.</p>
              </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/80 backdrop-blur-lg border border-white/80 shadow-md shadow-sky-950/5 flex items-start gap-3">
              <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-indigo-600 to-sky-400 text-white flex items-center justify-center shrink-0 shadow-xs">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
              </div>
              <div class="space-y-0.5">
                <h4 class="text-xs font-bold text-[#0F2C59]">Manajemen Billing</h4>
                <p class="text-[11px] text-slate-600 leading-tight">Rekap penerimaan kas dan tunggakan rekening.</p>
              </div>
            </div>
          </div>

          <!-- Informasi Akses Kedinasan -->
          <div class="p-4 rounded-2xl bg-sky-50/80 border border-sky-200/80 flex items-center justify-between gap-4">
            <div class="space-y-0.5">
              <span class="text-xs font-bold text-sky-950 block">Keamanan Akses Kedinasan</span>
              <p class="text-[11px] text-sky-800">Setiap akses ke Command Center SCADA diverifikasi melalui sistem database autentikasi terpusat.</p>
            </div>
            <span class="px-3 py-1.5 rounded-xl text-[11px] font-bold bg-white text-sky-800 border border-sky-200 shrink-0">
              RBAC MySQL
            </span>
          </div>
        </div>

        <!-- KOLOM KANAN: Kartu Autentikasi Frosted Glass Kaca Buram -->
        <div class="lg:col-span-5 flex justify-center">
          <div class="w-full max-w-[460px] bg-white/85 backdrop-blur-xl rounded-[32px] p-7 sm:p-9 shadow-2xl shadow-sky-950/15 border border-white/80 relative space-y-5">
            
            <!-- Brand & Badge -->
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-cyan-600 to-sky-400 text-white flex items-center justify-center shadow-xs">
                  <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/></svg>
                </div>
                <span class="text-sm font-black text-slate-900 tracking-tight">Smart Water Petugas</span>
              </div>
              <span class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-full bg-cyan-50 border border-cyan-200 text-cyan-800">
                SCADA CENTRAL
              </span>
            </div>

            <!-- Title & Info -->
            <div class="space-y-1">
              <h2 class="text-2xl font-black text-slate-900 tracking-tight">
                Masuk Portal Petugas
              </h2>
              <p class="text-xs text-slate-500 leading-relaxed">
                Silakan masuk untuk mengelola pengaduan, verifikasi AI-OCR stand meter, dan memantau tandon air.
              </p>
            </div>

            @if (session('error'))
              <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                <span>{{ session('error') }}</span>
              </div>
            @endif
            @if (session('info'))
              <div class="p-3 rounded-xl bg-sky-50 border border-sky-200 text-sky-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="16" y2="12"/><line x1="12" x2="12.01" y1="8" y2="8"/></svg>
                <span>{{ session('info') }}</span>
              </div>
            @endif
            @if (session('success'))
              <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="9 11 12 14 22 4"/></svg>
                <span>{{ session('success') }}</span>
              </div>
            @endif

            <!-- Form Manual Petugas (Verifikasi Database MySQL) -->
            <form action="{{ url('/admin-login') }}" method="POST" class="space-y-3.5">
              @csrf
              <div>
                <label for="admin-nip" class="text-xs font-semibold text-slate-700 block mb-1">
                  NIP Petugas atau Email Dinas
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                  </div>
                  <input
                    id="admin-nip"
                    name="identifier"
                    type="text"
                    required
                    placeholder="NIP atau email dinas"
                    class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                  />
                </div>
              </div>

              <div>
                <label for="admin-pass" class="text-xs font-semibold text-slate-700 block mb-1">
                  Kata Sandi
                </label>
                <div class="relative">
                  <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="11" x="3" y="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                  </div>
                  <input
                    id="admin-pass"
                    name="password"
                    type="password"
                    required
                    placeholder="Masukkan kata sandi dinas"
                    class="w-full text-xs rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-slate-900 placeholder:text-slate-400 focus:outline-hidden focus:border-[#0284C7] focus:ring-2 focus:ring-sky-100 transition-all bg-white"
                  />
                </div>
              </div>

              <button
                type="submit"
                class="w-full py-3 px-4 rounded-xl text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer mt-1"
              >
                <span>Masuk ke Panel Kontrol Admin</span>
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </button>
            </form>

            <div class="pt-2 text-center border-t border-slate-100">
              <a
                href="{{ url('/login') }}"
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-600 hover:text-[#0284C7] transition-colors cursor-pointer"
              >
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                <span>Bukan Petugas? Masuk sebagai Pelanggan / Warga</span>
              </a>
            </div>

          </div>
        </div>

      </main>

      <!-- Footer Minimalis -->
      <footer class="relative z-10 w-full py-4 text-center text-xs text-slate-500 border-t border-white/60 bg-white/50 backdrop-blur-md">
        <span>&copy; {{ date('Y') }} Perumda Air Minum Tirta Sago Kota Payakumbuh &middot; Smart Water SCADA Telemetry</span>
      </footer>
    </div>
  </body>
</html>
