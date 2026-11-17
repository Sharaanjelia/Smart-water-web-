<?php
$footerHandler = e($SHARED_FOOTER_HANDLER ?? '');
$footerVariant = $SHARED_FOOTER_VARIANT ?? 'default';
?>
<footer class="bg-slate-900 text-slate-300 border-t border-slate-800">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
      <div class="md:col-span-1 space-y-4">
        <div class="flex items-center gap-2.5">
          <div class="w-7 h-7 rounded-lg bg-gradient-to-tr from-cyan-500 to-sky-400 flex items-center justify-center text-white shadow-sm">
            <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22a7 7 0 0 0 7-7c0-2-1-3.9-3-5.5s-3.5-4-4-6.5c-.5 2.5-2 4.9-4 6.5C6 11.1 5 13 5 15a7 7 0 0 0 7 7z"/>
            </svg>
          </div>
          <span class="text-base font-bold text-white tracking-tight">Smart Water</span>
        </div>
        <p class="text-xs text-slate-400 leading-relaxed">
          Portal Layanan, Edukasi, dan Aksi Hemat Air bagi seluruh masyarakat dan pelanggan air minum di Kota Payakumbuh.
        </p>
        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 pt-1">
          <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/>
            <circle cx="12" cy="10" r="3"/>
          </svg>
          <span><?= e($APP_META['city']) ?></span>
        </div>
      </div>

      <div class="space-y-3">
        <h4 class="text-xs font-semibold text-white uppercase tracking-wider">Navigasi Komunitas</h4>
        <ul class="space-y-2 text-xs">
          <li><a href="<?= url('/beranda') ?>" class="hover:text-cyan-300 transition-colors">Beranda Utama</a></li>
          <li><a href="<?= url('/meter-reading') ?>" class="hover:text-cyan-300 transition-colors">Lapor Stand Meter Mandiri</a></li>
          <li><a href="<?= url('/pusat-layanan') ?>" class="hover:text-cyan-300 transition-colors">Pusat Layanan &amp; Pengaduan</a></li>
          <li><a href="<?= url('/akademi-air') ?>" class="hover:text-cyan-300 transition-colors">Smart Water Academy &amp; Kuis</a></li>
          <li><a href="<?= url('/tantangan') ?>" class="hover:text-cyan-300 transition-colors">Tantangan Hemat Air 7 Hari</a></li>
          <li><a href="<?= url('/tentang') ?>" class="hover:text-cyan-300 transition-colors">Tentang Ekosistem Smart Water</a></li>
        </ul>
      </div>

      <div class="space-y-3">
        <h4 class="text-xs font-semibold text-white uppercase tracking-wider">Ekosistem Terintegrasi</h4>
        <div class="space-y-2 text-xs text-slate-400 leading-relaxed">
          <p><strong class="text-slate-200">Aplikasi Mobile:</strong> Pembacaan angka meteran menggunakan kecerdasan buatan (AI-OCR kamera) &amp; konfirmasi mandiri pelanggan.</p>
          <p><strong class="text-slate-200">Web Portal:</strong> Informasi layanan terbuka, pelacakan tiket, edukasi interaktif, dan laporan pemakaian air.</p>
          <p><strong class="text-slate-200">Web Admin/Operator:</strong> Verifikasi data meteran resmi, pemantauan reservoir, dan eksekusi teknis lapangan.</p>
          <a href="<?= url('/admin') ?>" class="mt-1 text-[11px] font-bold text-sky-400 hover:text-sky-300 hover:underline flex items-center gap-1 cursor-pointer">
            <?php if ($footerVariant !== 'user'): ?>
              <svg class="w-3.5 h-3.5 text-sky-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                <path d="m9 12 2 2 4-4"/>
              </svg>
            <?php endif; ?>
            <span>Masuk Portal Admin / SCADA Operator &rarr;</span>
          </a>
        </div>
      </div>

      <div class="space-y-3">
        <h4 class="text-xs font-semibold text-white uppercase tracking-wider">Kontak &amp; Bantuan Resmi</h4>
        <div class="space-y-2 text-xs text-slate-400">
          <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
            </svg>
            <span>Call Center: <?= e($APP_META['call_center']) ?></span>
          </div>
          <div class="flex items-center gap-2">
            <svg class="w-3.5 h-3.5 text-cyan-400 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="16" x="2" y="4" rx="2"/>
              <path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            <span><?= e($APP_META['email']) ?></span>
          </div>
          <div class="pt-2">
            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-slate-800 text-[11px] text-cyan-300 border border-slate-700">
              <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/>
                <path d="m9 12 2 2 4-4"/>
              </svg>
              <span>Kanal Pengaduan Resmi Terverifikasi</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="mt-10 pt-6 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4 text-[11px] text-slate-500">
      <p>&copy; <?= e($APP_META['year']) ?> Smart Water Kota Payakumbuh. Seluruh hak cipta dilindungi.</p>
      <div class="flex items-center gap-4">
        <span>Standar Layanan Publik Payakumbuh</span>
        <span aria-hidden="true">&middot;</span>
        <span>Privasi &amp; Keamanan Data Pelanggan</span>
      </div>
    </div>
  </div>
</footer>
