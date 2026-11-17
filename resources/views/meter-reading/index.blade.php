@extends('layouts.app')

@section('title', 'Laporan Meter')
@section('page_title', 'Laporan Meter')

@section('content')
<div class="space-y-8">

    <!-- Card Form Laporan Meter -->
    <div class="card bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-9 shadow-sm">
        <div>
            <div class="flex items-center gap-2.5 text-xs text-cyan-800 font-bold uppercase tracking-wider mb-1">
                <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
                </svg>
                <span>Pelaporan Mandiri Pelanggan Tirta Sago</span>
            </div>
            <h2 class="card-heading text-2xl font-black text-slate-900 tracking-tight">Form Laporan Meter</h2>
            <p class="section-description text-xs sm:text-sm text-slate-500 mt-1 mb-6 leading-relaxed">
                Masukkan angka terbaru pada meter pelanggan. Data yang Anda kirimkan langsung tersimpan ke sistem database PDAM dan disinkronkan dengan estimasi tagihan rekening air Anda.
            </p>
        </div>

        <form class="form-grid" method="POST" action="{{ route('meter-reading.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-field">
                <label for="meter_number" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Nomor Meter</label>
                <div class="relative">
                    <input
                        class="form-control"
                        id="meter_number"
                        name="meter_number"
                        type="text"
                        value="{{ old('meter_number', $meterNumber ?? '04-1234-5678') }}"
                        placeholder="Contoh: 04-1234-5678"
                        required
                    />
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-[11px] font-bold text-slate-400 bg-slate-200/80 px-2 py-0.5 rounded">
                        ID Pelanggan
                    </div>
                </div>
            </div>

            <div class="form-field">
                <label for="reading" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Angka Meter Terbaru (m³)</label>
                <div class="relative">
                    <input
                        class="form-control"
                        id="reading"
                        name="reading"
                        type="number"
                        step="0.01"
                        min="0"
                        value="{{ old('reading', $reading ?? '128.42') }}"
                        placeholder="Contoh: 128.42"
                        required
                    />
                    <div class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-cyan-800 bg-cyan-100 px-2 py-0.5 rounded">
                        m³
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Catat seluruh angka hitam dan digit merah pada meteran fisik rumah Anda.
                </p>
            </div>

            <div class="form-field">
                <label for="meter_photo" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Foto Fisik Meteran (Opsional untuk Validasi AI-OCR)</label>
                <input
                    class="form-control"
                    id="meter_photo"
                    name="meter_photo"
                    type="file"
                    accept="image/*"
                />
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Format file: JPG, PNG, atau JPEG. Maksimal ukuran 5 MB.
                </p>
            </div>

            <div class="form-field">
                <label for="notes" class="text-xs font-bold text-slate-700 uppercase tracking-wide">Catatan Tambahan (Opsional)</label>
                <input
                    class="form-control"
                    id="notes"
                    name="notes"
                    type="text"
                    value="{{ old('notes') }}"
                    placeholder="Contoh: Meteran berada di dekat pagar depan, kondisi bersih."
                />
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-between gap-4">
                <button class="btn btn-primary w-full sm:w-auto" type="submit">
                    <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>
                    </svg>
                    <span>Kirim Laporan</span>
                </button>
                <span class="text-xs text-slate-400 flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    </svg>
                    Terenkripsi &amp; Terverifikasi Petugas SCADA
                </span>
            </div>
        </form>
    </div>

    <!-- Riwayat Pembacaan Meter Terbaru -->
    @if(isset($recentReadings) && $recentReadings->count() > 0)
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-8 shadow-sm space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Laporan Meteran Pelanggan</h3>
                <p class="text-xs text-slate-500">Daftar laporan stand meteran yang telah tersimpan di database backend.</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-cyan-50 text-cyan-800 border border-cyan-200">
                {{ $recentReadings->count() }} Data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[11px] font-semibold bg-slate-50/70">
                        <th class="py-3 px-4">Waktu Lapor</th>
                        <th class="py-3 px-4">Nomor Meter</th>
                        <th class="py-3 px-4 text-right">Stand Sebelumnya</th>
                        <th class="py-3 px-4 text-right">Stand Terbaru</th>
                        <th class="py-3 px-4 text-right">Konsumsi</th>
                        <th class="py-3 px-4 text-right">Estimasi Biaya</th>
                        <th class="py-3 px-4 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    @foreach($recentReadings as $item)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="py-3 px-4 font-mono text-[11px] text-slate-500">
                            {{ $item->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                            {{ $item->meter_number }}
                        </td>
                        <td class="py-3 px-4 text-right font-mono text-slate-500">
                            {{ number_format($item->previous_reading, 2) }} m³
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-cyan-800">
                            {{ number_format($item->reading, 2) }} m³
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                            +{{ number_format($item->consumption, 2) }} m³
                        </td>
                        <td class="py-3 px-4 text-right font-mono font-semibold text-emerald-700">
                            Rp {{ number_format($item->estimated_cost, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                {{ $item->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
