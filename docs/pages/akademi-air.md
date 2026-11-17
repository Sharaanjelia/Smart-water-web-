# Smart Water - Kota Payakumbuh (Laravel Blade - Halaman Akademi Air)

Halaman Akademi Air (`AcademyView`, `LessonReaderModal`, `QuizPageView`, dan `WaterCalculator`) berada di `resources/views/pages/akademi-air.blade.php`, dilengkapi header, footer, penyimpanan progres di PHP Session, serta widget TirtaBot AI.

## Struktur Folder

Partial Akademi Air berada di `resources/views/pages/akademi-air/`. API-nya menggunakan sumber di `app/LegacyApi/academy/` dan `app/LegacyApi/chat/`, dengan wrapper publik di `public/api/`.

Konfigurasi dan data Akademi Air berada di `app/LegacyConfig.php`. CSS umum berada di `public/assets/css/shared.css`, JavaScript halaman di `public/assets/js/pages/akademi-air.js`, dan navbar/footer bersama di `resources/views/shared/includes/`.

## Cara Menjalankan

Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/akademi-air`.
