# Portal Admin SCADA PDAM Tirta Sago (Laravel Blade)

View Laravel ini berisi **Halaman Admin / Command Center SCADA** dan mempertahankan tata letak, warna (`#0C1E3C`, `#0284C7`, `#F4F7FB`), serta fitur operasional dari `AdminDashboardView.tsx`.
View halaman ini berada di `resources/views/pages/admin.blade.php`.

## Struktur Folder
- `resources/views/pages/admin.blade.php` — Tampilan Command Center Admin dengan 7 tab operasional:
  1. **Ikhtisar & KPI**: Banner status operasional, 6 KPI real-time, antrean tiket masuk terkini, dan ringkasan 4 instalasi reservoir.
  2. **Manajemen Tiket Aduan**: Filter berdasarkan status/kecamatan/pencarian, panel inspeksi detail, riwayat log penanganan, formulir disposisi status + petugas pelaksana + catatan resmi, dan hapus tiket.
  3. **Validasi Stand AI-OCR**: Tabel pembacaan meteran dari Mobile AI-OCR / Web Mandiri dengan tombol **Setujui** 1-klik dan modal **Koreksi** angka dial fisik.
  4. **Pengumuman Layanan**: Publikasi pengumuman resmi baru (pilih kecamatan terdampak), ubah status siklus (`aktif` -> `dalam_penanganan` -> `selesai`), dan hapus pengumuman.
  5. **SCADA Reservoir & Kualitas**: Monitoring ketinggian tangki, debit inflow/outflow, tekanan (Bar), kekeruhan (NTU), pH, sisa klorin, serta kontrol slider bukaan katup motorik (0–100%).
  6. **Master Pelanggan & Tagihan**: Register sambungan rumah tangga dengan pencarian, filter kecamatan, dan unduh slip rekening.
  7. **Audit Trail**: Catatan kronologis seluruh tindakan operator yang otomatis tercatat setiap ada perubahan.
- `app/LegacyConfig.php` — Konfigurasi dan data seed semua modul, termasuk admin, berbasis PHP Session.
- `public/api/admin/admin.php` — Wrapper endpoint JSON `POST`; implementasi berada di `app/LegacyApi/admin/admin.php`.
- `public/assets/js/pages/admin.js` & `public/assets/css/shared.css` — Logika interaktif, filter real-time, modal, toast, dan styling umum.

## Cara Menjalankan
Jalankan dari root proyek:
```bash
php artisan serve
```
Lalu buka `http://localhost:8000/admin`.
