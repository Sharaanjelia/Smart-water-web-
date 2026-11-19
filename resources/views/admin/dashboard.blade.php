@extends('layouts.admin')

@section('title', 'Portal Petugas & Admin SCADA')

@section('content')
@php
    $authUser = Auth::user();
    $isAdmin = $isAdmin ?? ($authUser ? $authUser->isAdmin() : false);
    $isScada = $isScada ?? ($authUser ? $authUser->isScadaOperator() : false);
    $isField = $isField ?? ($authUser ? $authUser->isFieldOfficer() : false);
    $isLegacyStaff = $isLegacyStaff ?? ($authUser ? $authUser->hasRole('staff') : false);
@endphp
<div class="max-w-6xl mx-auto space-y-6">

    <!-- HEADER PETUGAS & ROLE OPERATOR -->
    <div class="bg-gradient-to-r from-[#0284C7] via-cyan-600 to-sky-600 rounded-3xl p-6 sm:p-7 text-white shadow-md shadow-sky-600/15 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border border-sky-400/30">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white text-[#0284C7] flex items-center justify-center font-black shadow-md shrink-0">
                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                </svg>
            </div>
            <div class="space-y-0.5">
                <span class="text-[10px] text-cyan-100 font-mono font-bold uppercase tracking-[0.2em] block">
                    {{ $headerRoleBadge ?? 'PORTAL OPERATOR & PETUGAS PDAM' }}
                </span>
                <h1 class="text-xl sm:text-2xl font-black text-white">
                    {{ $officerName ?? 'Petugas PDAM' }}
                </h1>
                <p class="text-xs text-cyan-50 font-mono">
                    {{ $officerSubtitle ?? 'PDAM Tirta Sago Kota Payakumbuh' }}
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                onclick="window.SWC && window.SWC.resetDemoData()"
                class="px-3 py-1.5 rounded-xl bg-white/20 hover:bg-white/30 border border-white/30 text-[11px] font-mono text-white transition-colors cursor-pointer backdrop-blur-xs shadow-xs"
                title="Reset antrean verifikasi & tagihan ke awal"
            >
                ↻ Reset Data Demo
            </button>
            <div class="px-3.5 py-1.5 rounded-xl bg-white/20 border border-white/30 text-[11px] font-mono text-white flex items-center gap-2 shadow-xs backdrop-blur-xs font-semibold">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>SCADA IPA: NORMAL (3.2 BAR)</span>
            </div>
        </div>
    </div>

    @if($isAdmin || $isScada || $isLegacyStaff)
    <!-- ============================================== -->
    <!-- TAB 0: RINGKASAN DASHBOARD (OVERVIEW)          -->
    <!-- ============================================== -->
    <div id="overviewTab" class="{{ ($subTab ?? 'overview') === 'overview' ? '' : 'hidden' }} space-y-6">
        <!-- 1. METRIC STAT CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Card 1: Validasi Meter AI-OCR -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between space-y-3 group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-cyan-800 uppercase tracking-wider bg-cyan-50 border border-cyan-100 px-2.5 py-1 rounded-full">
                        AI-OCR METER
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-cyan-500/10 text-cyan-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                </div>
                <div>
                    <span class="text-3xl font-mono font-black text-slate-900" id="overviewPendingCount">{{ $pendingReportsCount ?? 3 }}</span>
                    <span class="text-xs text-slate-500 block mt-0.5">Laporan perlu validasi</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/admin-dashboard?tab=verify') }}" class="text-xs font-bold text-[#0284C7] hover:text-[#0369A1] inline-flex items-center gap-1.5">
                        <span>Buka Antrean Validasi</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 2: SCADA & Hidrolika -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between space-y-3 group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        SCADA TELEMETRI
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                    </div>
                </div>
                <div>
                    <span class="text-3xl font-mono font-black text-slate-900">3.2 <span class="text-base font-bold text-slate-500">BAR</span></span>
                    <span class="text-xs text-emerald-700 font-semibold block mt-0.5">Reservoir 84% (Stabil Normal)</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/water-monitoring') }}" class="text-xs font-bold text-[#0284C7] hover:text-[#0369A1] inline-flex items-center gap-1.5">
                        <span>Pantau Konsol SCADA</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 3: Kas & Keuangan Tagihan -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between space-y-3 group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-emerald-800 uppercase tracking-wider bg-emerald-50 border border-emerald-100 px-2.5 py-1 rounded-full">
                        KAS BULAN INI
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                </div>
                <div>
                    <span class="text-2xl sm:text-2xl font-mono font-black text-emerald-950">Rp 48.4M</span>
                    <span class="text-xs text-slate-500 block mt-0.5">88.7% tagihan terbayar</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/admin-dashboard?tab=bills') }}" class="text-xs font-bold text-[#0284C7] hover:text-[#0369A1] inline-flex items-center gap-1.5">
                        <span>Kelola Penerimaan Kas</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

            <!-- Card 4: Pelanggan & Pengaduan -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/90 shadow-2xs hover:shadow-md transition-all flex flex-col justify-between space-y-3 group">
                <div class="flex items-center justify-between">
                    <span class="text-[10px] font-bold text-sky-800 uppercase tracking-wider bg-sky-50 border border-sky-100 px-2.5 py-1 rounded-full">
                        SAMBUNGAN &amp; TIKET
                    </span>
                    <div class="w-9 h-9 rounded-xl bg-sky-500/10 text-sky-600 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                    </div>
                </div>
                <div>
                    <span class="text-3xl font-mono font-black text-slate-900">{{ $totalCustomersCount ?? 1248 }}</span>
                    <span class="text-xs text-slate-500 block mt-0.5">{{ $totalComplaintsCount ?? 2 }} tiket gangguan aktif</span>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ url('/admin-dashboard?tab=customers') }}" class="text-xs font-bold text-[#0284C7] hover:text-[#0369A1] inline-flex items-center gap-1.5">
                        <span>Database Sambungan</span>
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                    </a>
                </div>
            </div>

        </div>

        <!-- 2. QUICK ACTIONS & SHORTCUTS -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Pintasan Tindakan Cepat (Quick Actions)</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <a
                    href="{{ url('/admin-dashboard?tab=verify') }}"
                    class="p-4 rounded-2xl bg-cyan-50/70 hover:bg-cyan-100/70 border border-cyan-100 text-cyan-900 transition-all flex flex-col items-center justify-center text-center gap-2 group cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-white shadow-2xs flex items-center justify-center text-cyan-600 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="18" height="18" x="3" y="3" rx="2"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <span class="text-xs font-bold">Validasi Meter</span>
                    <span class="text-[10px] text-cyan-700 font-medium">Periksa foto &amp; OCR</span>
                </a>

                <a
                    href="{{ url('/admin-dashboard?tab=bills') }}"
                    class="p-4 rounded-2xl bg-emerald-50/70 hover:bg-emerald-100/70 border border-emerald-100 text-emerald-900 transition-all flex flex-col items-center justify-center text-center gap-2 group cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-white shadow-2xs flex items-center justify-center text-emerald-600 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                    </div>
                    <span class="text-xs font-bold">Rekonsiliasi Kas</span>
                    <span class="text-[10px] text-emerald-700 font-medium">Verifikasi tagihan</span>
                </a>

                <a
                    href="{{ url('/reservoir-detail') }}"
                    class="p-4 rounded-2xl bg-sky-50/70 hover:bg-sky-100/70 border border-sky-100 text-sky-900 transition-all flex flex-col items-center justify-center text-center gap-2 group cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-white shadow-2xs flex items-center justify-center text-sky-600 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    </div>
                    <span class="text-xs font-bold">Tandon Reservoir</span>
                    <span class="text-[10px] text-sky-700 font-medium">Level Batang Agam</span>
                </a>

                <a
                    href="{{ url('/pusat-layanan') }}"
                    class="p-4 rounded-2xl bg-amber-50/70 hover:bg-amber-100/70 border border-amber-100 text-amber-900 transition-all flex flex-col items-center justify-center text-center gap-2 group cursor-pointer"
                >
                    <div class="w-10 h-10 rounded-xl bg-white shadow-2xs flex items-center justify-center text-amber-600 group-hover:scale-110 transition-transform">
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 9a2 2 0 0 1-2 2H6l-4 4V4c0-1.1.9-2 2-2h8a2 2 0 0 1 2 2v5Z"/></svg>
                    </div>
                    <span class="text-xs font-bold">Tiket Pengaduan</span>
                    <span class="text-[10px] text-amber-700 font-medium">Disposisi &amp; tangani</span>
                </a>
            </div>
        </div>

        <!-- 3. LIVE ACTIVITY FEED -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Aktivitas Operasional Terkini</h3>
                <span class="text-[11px] font-mono text-slate-400">Pembaruan Real-Time</span>
            </div>
            <div class="divide-y divide-slate-100 text-xs">
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                            ✓
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">Pembayaran Tagihan Terverifikasi</p>
                            <p class="text-slate-500 text-[11px]">Budi Santoso (1234567890) melunasi tagihan periode berjalan sebesar Rp 150.800.</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 shrink-0">10 mnt lalu</span>
                </div>
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-cyan-50 text-cyan-600 flex items-center justify-center font-bold">
                            📷
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">Laporan Meter Mandiri AI-OCR Baru</p>
                            <p class="text-slate-500 text-[11px]">Ibu Siti Nurbaya mengunggah foto meteran (15.0 m³). Status: Menunggu Validasi.</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 shrink-0">24 mnt lalu</span>
                </div>
                <div class="py-3 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-sky-50 text-sky-600 flex items-center justify-center font-bold">
                            ⚙
                        </div>
                        <div>
                            <p class="font-bold text-slate-800">Telemetri Sensor Hidrolika Normal</p>
                            <p class="text-slate-500 text-[11px]">Tekanan transmisi pipa distribusi zona DIST-A tercatat stabil di 3.2 BAR.</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-mono text-slate-400 shrink-0">1 jam lalu</span>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($isAdmin || $isScada || $isLegacyStaff)
    <!-- TAB 1: VERIFIKASI LAPOR METER (DINAMIS REAKTIF) -->
    <div id="verifyTab" class="{{ ($subTab ?? 'overview') === 'verify' ? '' : 'hidden' }} space-y-4">
        <div class="flex items-center justify-between">
            <h3 class="text-sm font-extrabold text-slate-800">
                Antrean Verifikasi Foto Meter AI-OCR Pelanggan
            </h3>
            <span class="text-xs font-mono text-slate-400" id="adminPendingSummary">
                {{ $pendingReportsCount ?? 3 }} Laporan Perlu Validasi Petugas
            </span>
        </div>

        <div id="adminMeterReportsContainer" class="space-y-3">
            <!-- Dirender secara dinamis oleh JavaScript -->
        </div>
    </div>
    @endif

    @if($isAdmin)
    <!-- TAB 2: MANAJEMEN TAGIHAN (DINAMIS REAKTIF) -->
    <div id="billsTab" class="{{ ($subTab ?? 'verify') === 'bills' ? '' : 'hidden' }} space-y-4">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="p-5 rounded-3xl bg-emerald-50 border border-emerald-200 space-y-1">
                <span class="text-xs font-bold text-emerald-800 uppercase tracking-wider block">Total Penerimaan Kas Bulan Ini</span>
                <span class="text-2xl sm:text-3xl font-mono font-black text-emerald-950" id="adminRevenueTotal">Rp 48.400.800</span>
                <span class="text-[11px] text-emerald-700 block">Rekening terverifikasi lunas oleh perbankan</span>
            </div>
            <div class="p-5 rounded-3xl bg-rose-50 border border-rose-200 space-y-1">
                <span class="text-xs font-bold text-rose-800 uppercase tracking-wider block">Total Tagihan Tertunggak</span>
                <span class="text-2xl sm:text-3xl font-mono font-black text-rose-950" id="adminUnpaidTotal">Rp 6.169.200</span>
                <span class="text-[11px] text-rose-700 block">Menunggu pelunasan sebelum batas tanggal 20</span>
            </div>
        </div>

        <!-- Tabel Data Tagihan Pelanggan -->
        <div class="bg-white rounded-3xl border border-slate-200/90 overflow-hidden shadow-xs">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800">Daftar Tagihan Pelanggan</h4>
                <button type="button" onclick="window.print()" class="text-xs text-[#0284C7] font-bold hover:underline">
                    Ekspor Laporan PDF
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-500 font-bold uppercase border-b border-slate-200 text-[10px]">
                        <tr>
                            <th class="p-3.5">Pelanggan</th>
                            <th class="p-3.5">ID / No. Seri</th>
                            <th class="p-3.5">Zona</th>
                            <th class="p-3.5">Pemakaian</th>
                            <th class="p-3.5">Nominal</th>
                            <th class="p-3.5">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="adminBillsTableBody">
                        <tr>
                            <td class="p-3.5 font-bold text-slate-900">Budi Santoso</td>
                            <td class="p-3.5 font-mono text-slate-600">1234567890</td>
                            <td class="p-3.5">DIST-A</td>
                            <td class="p-3.5 font-mono">19.0 m³</td>
                            <td class="p-3.5 font-mono font-bold text-slate-900">Rp 150.800</td>
                            <td class="p-3.5" id="budiBillStatusCell">
                                <span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px]">Tertunggak</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-3.5 font-bold text-slate-900">Ibu Siti Nurbaya</td>
                            <td class="p-3.5 font-mono text-slate-600">1234567891</td>
                            <td class="p-3.5">DIST-B</td>
                            <td class="p-3.5 font-mono">15.0 m³</td>
                            <td class="p-3.5 font-mono font-bold text-slate-900">Rp 119.600</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">Lunas</span></td>
                        </tr>
                        <tr>
                            <td class="p-3.5 font-bold text-slate-900">H. Agus Salim</td>
                            <td class="p-3.5 font-mono text-slate-600">1234567892</td>
                            <td class="p-3.5">DIST-A</td>
                            <td class="p-3.5 font-mono">22.0 m³</td>
                            <td class="p-3.5 font-mono font-bold text-slate-900">Rp 174.200</td>
                            <td class="p-3.5"><span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">Lunas</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    @if($isAdmin || $isField || $isLegacyStaff)
    <!-- TAB 3: DATABASE PELANGGAN -->
    <div id="customersTab" class="{{ ($subTab ?? 'verify') === 'customers' ? '' : 'hidden' }} space-y-4">
        <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <input
                    type="text"
                    placeholder="Cari ID Pelanggan, Nama, atau No. Seri Meter..."
                    class="w-full sm:w-80 px-4 py-2.5 rounded-xl border border-slate-200 text-xs font-semibold focus:outline-none focus:border-[#0284C7]"
                />
                <div class="flex items-center gap-2 text-xs">
                    <span class="text-slate-500 font-semibold">Filter Zona:</span>
                    <select class="px-3 py-1.5 rounded-lg border border-slate-200 text-slate-700 font-semibold">
                        <option>Semua Sektor (DIST-A, B, C)</option>
                        <option>DIST-A Sektor Utara</option>
                        <option>DIST-B Sektor Timur</option>
                        <option>DIST-C Sektor Selatan</option>
                    </select>
                </div>
            </div>

            <div class="space-y-3">
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <strong class="text-sm font-bold text-slate-900 block">Budi Santoso (1234567890)</strong>
                        <p class="text-slate-500">Jl. Tan Malaka No. 42, Kel. Balai Nan Duo • Zona DIST-A</p>
                        <span class="font-mono text-slate-400 text-[11px]">Seri Meter: 01000 • Golongan: 2A Rumah Tangga</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs self-start sm:self-auto">
                        Sambungan Aktif
                    </span>
                </div>
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs">
                    <div>
                        <strong class="text-sm font-bold text-slate-900 block">Ibu Siti Nurbaya (1234567891)</strong>
                        <p class="text-slate-500">Jl. Soekarno Hatta No. 18, Kel. Tiakar • Zona DIST-B</p>
                        <span class="font-mono text-slate-400 text-[11px]">Seri Meter: 01842 • Golongan: 2A Rumah Tangga</span>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-xs self-start sm:self-auto">
                        Sambungan Aktif
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- TAB 4: PROFIL PETUGAS / ADMIN                  -->
    <!-- ============================================== -->
    <div id="profileTab" class="{{ ($subTab ?? 'overview') === 'profile' ? '' : 'hidden' }} space-y-6">
        <!-- Hero Profile Card -->
        <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5 text-center sm:text-left">
                <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-[#0284C7] to-cyan-500 text-white flex items-center justify-center font-black text-3xl shadow-lg shadow-sky-600/20 shrink-0">
                    <?= strtoupper(substr(e($officerName ?? 'H'), 0, 1)) ?>
                </div>
                <div class="space-y-1.5 flex-1">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
                        <h2 class="text-xl font-extrabold text-slate-900">{{ $officerName ?? 'Ir. Hendra Pratama, S.T.' }}</h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">
                            {{ $headerRoleBadge ?? 'ADMIN SCADA' }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-mono font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">
                            AKTIF
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">{{ $officerNip ?? 'ADM-04-SCADA' }} &bull; {{ $officerSubtitle ?? 'admin@tirtasago.id' }}</p>
                    <p class="text-xs text-slate-600 leading-relaxed max-w-xl">
                        Koordinator SCADA dan Administrator Sistem Smart Water Perumda Air Minum Tirta Sago Kota Payakumbuh.
                    </p>
                </div>
            </div>
        </div>

        <!-- Detail Informasi Kedinasan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Informasi Kedinasan</h3>
                <div class="space-y-3 text-xs divide-y divide-slate-100">
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Instansi</span>
                        <span class="font-bold text-slate-800">Perumda Air Minum Tirta Sago</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Unit / Instalasi</span>
                        <span class="font-bold text-slate-800">IPA Batang Agam</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Wilayah Kerja</span>
                        <span class="font-bold text-slate-800">Kota Payakumbuh</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Sistem Keamanan</span>
                        <span class="font-bold text-cyan-800 font-mono">RBAC Granular Security</span>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200/90 p-5 shadow-xs space-y-4">
                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-700">Keamanan &amp; Sesi Akses</h3>
                <div class="space-y-3 text-xs divide-y divide-slate-100">
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Status Otentikasi</span>
                        <span class="font-bold text-emerald-700">Sesi Sah Terverifikasi</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Waktu Masuk</span>
                        <span class="font-mono text-slate-800">Hari ini, {{ now()->format('H:i') }} WIB</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Alamat Jaringan</span>
                        <span class="font-mono text-slate-800">127.0.0.1 (Local Server)</span>
                    </div>
                    <div class="pt-2 flex justify-between">
                        <span class="text-slate-500">Perlindungan PII</span>
                        <span class="font-bold text-sky-800">Aktif (Data Masking)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function renderAdminDashboard() {
    if (!window.SWC) return;

    const reports = window.SWC.getMeterReports();
    const pendingReports = reports.filter(r => r.status === 'PENDING');
    const container = document.getElementById('adminMeterReportsContainer');
    const badge = document.getElementById('adminPendingCounter');
    const summary = document.getElementById('adminPendingSummary');

    if (badge) badge.textContent = pendingReports.length;
    if (summary) summary.textContent = `${pendingReports.length} Laporan Perlu Validasi Petugas`;

    if (container) {
        if (reports.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center bg-white rounded-3xl border border-slate-200 text-xs text-slate-400">
                    Tidak ada laporan meter masuk saat ini.
                </div>
            `;
        } else {
            container.innerHTML = reports.map(pr => {
                const isPending = pr.status === 'PENDING';
                const isApproved = pr.status === 'APPROVED';

                return `
                    <div class="bg-white rounded-3xl border border-slate-200/90 p-5 sm:p-6 shadow-xs flex flex-col lg:flex-row lg:items-center justify-between gap-5 transition-all ${isApproved ? 'bg-emerald-50/30 border-emerald-200' : ''}">
                        <div class="flex items-start gap-4">
                            <!-- Thumbnail Foto Meteran -->
                            <div class="w-16 h-16 rounded-2xl ${isApproved ? 'bg-emerald-900 text-emerald-400' : 'bg-slate-900 text-cyan-400'} flex flex-col items-center justify-center shrink-0 border border-slate-800 shadow-inner">
                                <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <rect width="18" height="18" x="3" y="3" rx="2" ry="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21"/>
                                </svg>
                                <span class="text-[9px] font-mono font-bold mt-0.5">FOTO</span>
                            </div>

                            <div class="space-y-1 text-xs">
                                <div class="flex items-center gap-2">
                                    <h4 class="font-extrabold text-slate-900 text-sm sm:text-base">${pr.name}</h4>
                                    <span class="px-2 py-0.5 rounded-full bg-sky-50 text-[#0284C7] font-bold text-[10px] border border-sky-200">
                                        ${pr.zone}
                                    </span>
                                </div>
                                <p class="text-slate-500 font-mono text-[11px]">ID Pelanggan: ${pr.custId} • No. Seri: ${pr.meter} • ${pr.date}</p>
                                <div class="flex flex-wrap items-center gap-3 pt-1">
                                    <span class="text-slate-600">Lalu: <strong class="font-mono text-slate-800">${pr.lastReading} m³</strong></span>
                                    <span class="text-slate-600">Hasil AI: <strong class="font-mono text-[#0284C7] text-sm">${pr.ocrReading} m³</strong></span>
                                    <span class="text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded">Pemakaian: ${pr.usage} m³</span>
                                </div>
                            </div>
                        </div>

                        <!-- Status / Tombol Aksi Petugas -->
                        <div class="flex items-center gap-2 pt-3 lg:pt-0 border-t lg:border-t-0 border-slate-100">
                            ${isPending ? `
                                <button
                                    type="button"
                                    onclick="rejectReport('${pr.id}')"
                                    class="px-4 py-2.5 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors cursor-pointer"
                                >
                                    Tolak / Ulang
                                </button>
                                <button
                                    type="button"
                                    onclick="approveReport('${pr.id}')"
                                    class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"/>
                                    </svg>
                                    <span>Setujui (Approve)</span>
                                </button>
                            ` : isApproved ? `
                                <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-emerald-100 border border-emerald-200 text-emerald-800 font-extrabold text-xs">
                                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                    <span>Telah Disetujui Petugas</span>
                                </span>
                            ` : `
                                <span class="px-3 py-1 rounded-xl bg-rose-100 text-rose-800 font-bold text-xs border border-rose-200">
                                    Permintaan Foto Ulang
                                </span>
                            `}
                        </div>
                    </div>
                `;
            }).join('');
        }
    }

    // Update Status Tagihan Budi Santoso di Tab 2
    const isBudiPaid = window.SWC.isBillPaid('INV-PYK-2025-04-1000');
    const budiCell = document.getElementById('budiBillStatusCell');
    if (budiCell) {
        if (isBudiPaid) {
            budiCell.innerHTML = `<span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold text-[10px]">Lunas</span>`;
        } else {
            budiCell.innerHTML = `<span class="px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold text-[10px]">Tertunggak</span>`;
        }
    }
}

function switchAdminTab(tab, updateUrl = true) {
    const c1 = document.getElementById('verifyTab');
    const c2 = document.getElementById('billsTab');
    const c3 = document.getElementById('customersTab');

    const s1 = document.getElementById('sidebarNavVerify');
    const s2 = document.getElementById('sidebarNavBills');
    const s3 = document.getElementById('sidebarNavCustomers');

    if (c1) c1.classList.toggle('hidden', tab !== 'verify');
    if (c2) c2.classList.toggle('hidden', tab !== 'bills');
    if (c3) c3.classList.toggle('hidden', tab !== 'customers');

    const inactiveClass = 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors duration-150 group text-slate-600 hover:text-slate-900 hover:bg-slate-100/70 border border-transparent font-semibold';
    const activeClass = 'flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition-colors duration-150 group bg-cyan-50 text-cyan-900 border border-cyan-200/90 shadow-2xs font-bold';

    [s1, s2, s3].forEach(s => {
        if (s) {
            s.className = inactiveClass;
            const svg = s.querySelector('svg');
            if (svg) {
                svg.classList.remove('text-cyan-600', 'text-emerald-600', 'text-sky-600');
                svg.classList.add('text-slate-400');
            }
        }
    });

    if (tab === 'bills') {
        if (s2) {
            s2.className = activeClass;
            const svg = s2.querySelector('svg');
            if (svg) {
                svg.classList.remove('text-slate-400');
                svg.classList.add('text-emerald-600');
            }
        }
        if (updateUrl && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '{{ url("/admin-dashboard?tab=bills") }}');
        }
    } else if (tab === 'customers') {
        if (s3) {
            s3.className = activeClass;
            const svg = s3.querySelector('svg');
            if (svg) {
                svg.classList.remove('text-slate-400');
                svg.classList.add('text-sky-600');
            }
        }
        if (updateUrl && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '{{ url("/admin-dashboard?tab=customers") }}');
        }
    } else {
        if (s1) {
            s1.className = activeClass;
            const svg = s1.querySelector('svg');
            if (svg) {
                svg.classList.remove('text-slate-400');
                svg.classList.add('text-cyan-600');
            }
        }
        if (updateUrl && window.history && window.history.replaceState) {
            window.history.replaceState(null, '', '{{ url("/admin-dashboard?tab=verify") }}');
        }
    }
}

function approveReport(id) {
    if (window.SWC) {
        window.SWC.approveMeterReport(id);
    }
    renderAdminDashboard();
    alert(`Laporan meter ${id} berhasil disetujui oleh Petugas Ahmad Pratama, S.T.! Tagihan pelanggan resmi disahkan.`);
}

function rejectReport(id) {
    const reason = prompt('Masukkan alasan penolakan foto meteran:', 'Foto buram / angka meter tidak jelas');
    if (!reason) return;

    if (window.SWC) {
        window.SWC.rejectMeterReport(id, reason);
    }
    renderAdminDashboard();
    alert(`Laporan meter ${id} ditolak. Notifikasi permintaan foto ulang telah dikirimkan ke pelanggan.`);
}

function bindSidebarTab(id, tabName) {
    const el = document.getElementById(id);
    if (!el) return;
    el.addEventListener('click', function(e) {
        e.preventDefault();
        switchAdminTab(tabName, true);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('verifyTab')) {
        renderAdminDashboard();
    }

    bindSidebarTab('sidebarNavVerify', 'verify');
    bindSidebarTab('sidebarNavBills', 'bills');
    bindSidebarTab('sidebarNavCustomers', 'customers');

    // Baca tab aktif dari URL query parameter ?tab=... atau hash #...
    const urlParams = new URLSearchParams(window.location.search);
    const initialTab = urlParams.get('tab') || window.location.hash.replace('#', '') || '{{ $subTab ?? ($isField ? "customers" : "verify") }}';
    switchAdminTab(initialTab, false);
});

window.addEventListener('popstate', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const tab = urlParams.get('tab') || window.location.hash.replace('#', '') || 'verify';
    switchAdminTab(tab, false);
});

window.addEventListener('swc:state-change', () => {
    renderAdminDashboard();
});
</script>
@endsection
