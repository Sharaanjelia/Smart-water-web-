# Halaman Smart Water

Semua halaman utama adalah Blade views di `resources/views/pages/` dan dirender melalui rute Laravel. Entry point Laravel tetap `public/index.php`.

| Halaman | View | URL |
| --- | --- | --- |
| Beranda | `beranda.blade.php` | `/beranda` |
| Pusat Layanan | `pusat-layanan.blade.php` | `/pusat-layanan` |
| Akademi Air | `akademi-air.blade.php` | `/akademi-air` |
| Tantangan | `tantangan.blade.php` | `/tantangan` |
| Tentang | `tentang.blade.php` | `/tentang` |
| Chat AI | `chat-ai.blade.php` | `/chat-ai` |
| Login & Daftar | `login-daftar.blade.php` | `/login` |
| Profil Pelanggan | `user.blade.php` | `/user` |
| Admin | `admin.blade.php` | `/admin` |

Setiap halaman utama kini berupa satu file Blade utuh dan mandiri di `resources/views/pages/` (misalnya `beranda.blade.php`). Komponen global yang dipakai bersama, seperti logo brand (`brand_logo.php`), chatbot AI (`chatbot.php`), navbar (`header.php`), dan footer (`footer.php`), berada langsung di `resources/views/shared/` tanpa subfolder. Seluruh subfolder duplikasi `includes` dan `sections` yang berulang di setiap halaman telah disatukan dan dibersihkan. Aset CSS, JavaScript, dan gambar berada di `public/assets`.

Konfigurasi dan data sesi halaman terpusat di `app/LegacyConfig.php`. Sumber API berada langsung di `app/LegacyApi/` (tanpa subfolder), dengan wrapper URL publik langsung di `public/api/`. Jalankan aplikasi dari root proyek menggunakan `php artisan serve`.
