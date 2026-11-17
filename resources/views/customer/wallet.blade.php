@extends('layouts.app')

@section('title', 'Dompet & Saldo Operasional')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Header & Navigasi -->
    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
        <a href="{{ url('/profile') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-500 hover:text-slate-800 transition-colors">
            <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6"/>
            </svg>
            <span>Kembali ke Profil</span>
        </a>
        <h2 class="text-sm font-extrabold text-slate-800">Dompet Pelanggan</h2>
        <button
            type="button"
            onclick="window.SWC && window.SWC.resetDemoData()"
            class="text-[11px] font-mono text-slate-400 hover:text-rose-500 transition-colors cursor-pointer"
        >
            ↻ Reset Saldo
        </button>
    </div>

    <!-- KARTU SALDO (Dark Gradient Card: #0F172A ke #1E293B) -->
    <div class="bg-gradient-to-br from-[#0F172A] to-[#1E293B] rounded-3xl p-6 sm:p-8 text-white shadow-xl shadow-slate-900/10 space-y-4 relative overflow-hidden">
        <div class="flex items-center justify-between">
            <span class="text-xs font-bold uppercase tracking-wider text-slate-400">
                Saldo Tersedia
            </span>
            <span class="px-3 py-1 rounded-full bg-white/10 text-cyan-300 text-[11px] font-bold border border-white/10">
                Mitra / Pelanggan
            </span>
        </div>

        <div>
            <span id="walletPageBalance" class="text-3xl sm:text-4xl font-mono font-black text-white tracking-tight">
                Rp 2.500.000
            </span>
            <p class="text-xs text-slate-400 mt-1">
                Saldo dapat digunakan langsung untuk membayar tagihan air bulanan tanpa biaya admin.
            </p>
        </div>

        <div class="pt-2 flex items-center gap-3">
            <a
                href="{{ url('/bill') }}"
                class="py-2.5 px-4 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold text-xs flex items-center gap-1.5 transition-colors cursor-pointer"
            >
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                <span>Gunakan untuk Bayar Tagihan Air</span>
            </a>
        </div>
    </div>

    <!-- FORMULIR PENARIKAN DANA (Withdrawal Form) -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 sm:p-7 shadow-xs space-y-5">
        <div class="border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284C7]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" x2="12" y1="2" y2="22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
                <span>Ajukan Penarikan Dana ke Rekening Bank</span>
            </h3>
            <p class="text-xs text-slate-500 mt-0.5">
                Dana ditransfer ke rekening bank pribadi Anda dalam 1x24 jam kerja.
            </p>
        </div>

        <form onsubmit="handleWithdraw(event)" class="space-y-4 text-xs">
            
            <div class="space-y-1">
                <label for="amount" class="font-bold text-slate-700 block">Nominal Penarikan (Rp)</label>
                <input
                    type="number"
                    id="amount"
                    placeholder="Minimal Rp 50.000"
                    min="50000"
                    max="2500000"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-[#F8FAFC] font-mono text-sm font-bold text-slate-900 focus:outline-none focus:border-[#0284C7] focus:bg-white"
                    required
                />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-1">
                    <label for="bank" class="font-bold text-slate-700 block">Bank Tujuan</label>
                    <select id="bank" class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-[#F8FAFC] text-slate-900 font-semibold focus:outline-none focus:border-[#0284C7]">
                        <option value="BCA">Bank BCA</option>
                        <option value="MANDIRI">Bank Mandiri</option>
                        <option value="BRI">Bank BRI</option>
                        <option value="BNI">Bank BNI</option>
                        <option value="NAGARI">Bank Nagari (Sumbar)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label for="accountNumber" class="font-bold text-slate-700 block">Nomor Rekening Bank</label>
                    <input
                        type="text"
                        id="accountNumber"
                        placeholder="Contoh: 0123456789"
                        class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-[#F8FAFC] font-mono text-slate-900 focus:outline-none focus:border-[#0284C7] focus:bg-white"
                        required
                    />
                </div>
            </div>

            <div class="space-y-1">
                <label for="accountName" class="font-bold text-slate-700 block">Nama Pemilik Rekening Sesuai Buku Tabungan</label>
                <input
                    type="text"
                    id="accountName"
                    value="Budi Santoso"
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-[#F8FAFC] text-slate-900 font-semibold focus:outline-none focus:border-[#0284C7] focus:bg-white"
                    required
                />
            </div>

            <div class="pt-2">
                <button
                    type="submit"
                    class="w-full py-3.5 px-5 rounded-xl bg-[#0284C7] hover:bg-[#0369A1] text-white font-extrabold text-xs shadow-md shadow-sky-600/20 transition-all cursor-pointer"
                >
                    Ajukan Penarikan Saldo
                </button>
            </div>

        </form>
    </div>

    <!-- RIWAYAT MUTASI SALDO DINAMIS -->
    <div class="bg-white rounded-3xl border border-slate-200/90 p-6 shadow-xs space-y-3">
        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wide border-b border-slate-100 pb-2">
            Riwayat Mutasi Saldo
        </h4>

        <div class="space-y-2.5 text-xs" id="mutationsListContainer">
            <!-- Dirender secara dinamis -->
        </div>
    </div>

</div>

<script>
function renderWalletPage() {
    if (!window.SWC) return;
    const wallet = window.SWC.getWallet();

    const balanceEl = document.getElementById('walletPageBalance');
    if (balanceEl) {
        balanceEl.textContent = 'Rp ' + Number(wallet.balance).toLocaleString('id-ID');
    }

    const mutationsEl = document.getElementById('mutationsListContainer');
    if (mutationsEl) {
        if (!wallet.mutations || wallet.mutations.length === 0) {
            mutationsEl.innerHTML = '<div class="text-slate-400 text-center py-4">Belum ada mutasi saldo.</div>';
            return;
        }

        mutationsEl.innerHTML = wallet.mutations.map(m => `
            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                <div>
                    <strong class="text-slate-900 block font-bold">${m.title}</strong>
                    <span class="text-slate-400 text-[11px]">${m.desc || m.date}</span>
                </div>
                <span class="font-mono font-bold ${m.type === 'DB' ? 'text-rose-600' : 'text-emerald-600'} text-sm">
                    ${m.type === 'DB' ? '-' : '+'}Rp ${Number(m.amount).toLocaleString('id-ID')}
                </span>
            </div>
        `).join('');
    }
}

function handleWithdraw(e) {
    e.preventDefault();
    const amount = parseInt(document.getElementById('amount').value);
    const bank = document.getElementById('bank').value;
    const acc = document.getElementById('accountNumber').value;

    if (!amount || amount < 50000) {
        alert('Minimal penarikan adalah Rp 50.000');
        return;
    }

    if (window.SWC) {
        const res = window.SWC.deductWallet(amount, `Penarikan Dana ke Rekening ${bank} (${acc})`);
        if (!res.success) {
            alert(res.message);
            return;
        }
    }

    renderWalletPage();
    alert(`Pengajuan penarikan dana sebesar Rp ${amount.toLocaleString('id-ID')} ke rekening ${bank} ${acc} berhasil dikirim! Menunggu transfer bagian keuangan.`);
    e.target.reset();
}

document.addEventListener('DOMContentLoaded', () => {
    renderWalletPage();
});

window.addEventListener('swc:state-change', () => {
    renderWalletPage();
});
</script>
@endsection
