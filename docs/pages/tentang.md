# Halaman Tentang Ekosistem

View Laravel untuk halaman **Tentang** Smart Water Kota Payakumbuh (`AboutView.tsx`), mencakup seluruh elemen visual:
View halaman ini berada di `resources/views/pages/tentang.blade.php`.

- **Top Header Navigation & Footer**.
- **Hero Banner**: *Arsitektur Ekosistem Digital — Smart Water Kota Payakumbuh*.
- **Trilogi Ekosistem Smart Water (3 Pilar Komponen)**:
  - *Komponen A*: Aplikasi Mobile Customer (AI-OCR & geotagging)
  - *Komponen B (Portal Ini)*: Smart Water (`border-2 border-cyan-600`)
  - *Komponen C*: Web Admin & Operator PDAM (SCADA, billing, dispatching)
- **Komitmen Konservasi Batang Agam**: Banner ajakan konservasi dengan tombol CTA menuju *Tantangan Komunitas*.
- **Asisten AI TirtaBot** terhubung dengan endpoint PHP `api/chat.php`.

Konfigurasi halaman berada di `app/LegacyConfig.php`. Navbar/footer bersama berada di `resources/views/shared/includes/`, aset di `public/assets/`, dan sumber endpoint chatbot di `app/LegacyApi/chat/`, dengan wrapper di `public/api/`.

## Cara Menjalankan
Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/tentang`.
