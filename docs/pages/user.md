# Halaman User / Profil Pelanggan Smart Water (Laravel Blade)

View Laravel ini berisi **Halaman User / Dashboard Mandiri Pelanggan** yang mempertahankan tata letak, warna (`#F0F6FB`, `#0284C7`, `#0F2C59`), dan seluruh elemen dari `ProfileView.tsx` + `App.tsx` (mode tamu).
View halaman ini berada di `resources/views/pages/user.blade.php`.

## Struktur Folder
- `resources/views/pages/user.blade.php` — Tampilan Dashboard Pelanggan dengan 7 tab lengkap:
  1. **Beranda**: Banner panorama Jam Gadang & Gunung Sago, 4 kartu statistik (Pemakaian Air, Tagihan Terakhir, Pengaduan, Poin Edukasi), Kartu Digital Pelanggan Tirta Sago, Akses Cepat 2x2, dan banner pintasan Beranda Utama.
  2. **Informasi Akun & Data Pelanggan**: Preview Kartu Digital, tabel rincian 7 field identitas & sambungan, serta modal **Ubah Data Kontak**.
  3. **Laporan Pemakaian Air Pribadi**: Tombol Cetak Rekap / Unduh PDF, 4 kartu metrik, grafik batang kubikasi 4 bulan terakhir (dengan garis batas subsidi 10 m³), perhitungan tarif berjenjang Golongan 2A (Blok 1/2/3), tabel riwayat tagihan + modal **Lihat E-Kuitansi**, **Kalkulator & Simulator Tagihan** (1–40 m³), dan formulir **Lapor Stand Meter Mandiri**.
  4. **Riwayat Pengaduan**: Filter `Semua / Dalam Penanganan / Selesai`, daftar tiket pelanggan, dan modal **Buat Pengaduan Baru**.
  5. **Progres Belajar & Kuis**: Daftar kursus Smart Water Academy, skor nilai kuis, dan modal **Sertifikat Digital**.
  6. **Pengaturan Privasi & Keamanan**: Kontrol leaderboard komunitas, notifikasi WhatsApp, dan verifikasi 2 langkah (2FA).
  7. **Lencana Komunitas**: Koleksi 5 badge Smart Water beserta status Terbuka / Terkunci.
  - Mendukung juga pergantian akun demo (`Warga Google` vs `Ibu Siti N.`) serta tampilan **Mode Tamu** saat keluar akun.
- `app/LegacyConfig.php` — Konfigurasi dan data seed semua modul, termasuk profil, berbasis PHP Session.
- `resources/views/pages/user/includes/` — Partial halaman, termasuk modal dan chatbot.
- `resources/views/shared/includes/` — Navbar/footer bersama.
- `public/assets/js/pages/user.js` & `public/assets/css/shared.css` — Logika 7 tab, simulator real-time, filter, modal, dan styling umum.
- `app/LegacyApi/profile/profile.php` — Sumber endpoint profil; wrapper tersedia di `public/api/profile/`.

## Cara Menjalankan
Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/user`.
