# Smart Water - Kota Payakumbuh (Laravel Blade - Halaman Beranda)

Halaman Beranda berada di `resources/views/pages/beranda.blade.php` beserta komponennya (Header, BrandLogo, Hero Dinamis, Program Unggulan Misi 01–04, Falsafah Air Minangkabau, Manifesto Komunitas, Target Capaian 2026, Footer, dan Widget Chat TirtaBot AI).

## Struktur Folder

Partial halaman berada di `resources/views/pages/beranda/`. Konfigurasi berada di `app/LegacyConfig.php`; API chat menggunakan `app/LegacyApi/beranda/chat.php` dan wrapper publik di `public/api/`.

CSS umum berada di `public/assets/css/shared.css`, CSS khusus Beranda di `public/assets/css/pages/beranda.css`, serta JavaScript dan gambar di `public/assets/`. Komponen navbar/footer bersama ada di `resources/views/shared/includes/`.

## Cara Menjalankan

1. **Menggunakan PHP Built-in Server:**
   Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/beranda`.

2. **Menggunakan XAMPP / Laragon:**
   Buka `http://localhost/smart-water/public/beranda` setelah proyek berada di `htdocs/smart-water/`.

3. **(Opsional) Mengaktifkan Gemini AI:** Set environment variable `GEMINI_API_KEY` pada server PHP Anda. Jika tidak diset, chatbot menggunakan respons fallback seputar Smart Water Kota Payakumbuh.
