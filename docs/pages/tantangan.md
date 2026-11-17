# Halaman Tantangan Hemat Air

View Laravel untuk halaman **Tantangan** Smart Water Kota Payakumbuh (`ChallengeView.tsx`), berisi elemen desain dan interaksi:
View halaman ini berada di `resources/views/pages/tantangan.blade.php`.

- **Top Header Navigation & Footer** lengkap dengan indikator Total Poin XP.
- **Daftar Program Tantangan (3 Program, 10 Misi Nyata)**:
  1. *Tantangan 7 Hari Detoks Air Payakumbuh* (4 Misi, +250 XP)
  2. *Operasi Zero-Leak Payakumbuh* (3 Misi, +300 XP)
  3. *Master Literasi Kubikasi Air* (3 Misi, +280 XP)
- **Aksi Interaktif (PHP Session API)**:
  - Tombol *Gabung Tantangan Ini* dan status *Sedang Berjalan*.
  - Checklist *Tandai Selesai* per misi dengan pembaruan XP real-time, progress bar otomatis, serta pembukaan **Hadiah Badge** (`badge-water-hero`, `badge-leak-detective`, `badge-sago-guardian`, dan otomatis `badge-eco-champion` saat mencapai 500+ XP).
  - Toast notification & animasi konfeti.
- **Asisten AI TirtaBot** terhubung dengan endpoint PHP `api/chat.php`.

Konfigurasi halaman berada di `app/LegacyConfig.php`. Navbar/footer bersama berada di `resources/views/shared/includes/`, aset di `public/assets/`, dan sumber endpoint ada di `app/LegacyApi/challenges/` serta `app/LegacyApi/chat/`, dengan wrapper di `public/api/`.

## Cara Menjalankan
Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/tantangan`.
