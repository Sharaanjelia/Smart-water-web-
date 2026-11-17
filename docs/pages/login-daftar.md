# Halaman Login & Daftar

View Laravel untuk halaman **Login & Daftar** Smart Water Kota Payakumbuh (`AuthPage.tsx` + `PayakumbuhIllustration.tsx`), lengkap dengan seluruh efek visual dan alur autentikasi:
View halaman ini berada di `resources/views/pages/login-daftar.blade.php`.

- **Full-Page Frosted Scenic Background**: Memakai foto `../../assets/images/hero-lake-bright.jpg` dengan overlay kaca buram (`backdrop-blur-[3px]`) dan ambient water glow.
- **Translucent Top Bar & Bottom Bar** (`Beranda`, `Pusat Bantuan`, serta `Kebijakan Privasi | Syarat & Ketentuan | Kontak`).
- **Kartu Fitur Frosted Glass (`bg-white/80 backdrop-blur-lg`)**:
  - *Akses Layanan*, *Belajar Bersama*, *Tantangan Hemat Air*
- **Ilustrasi Vektor Payakumbuh Lengkap**: Gunung Sago, Rumah Gadang Gonjong, Bendungan Sikabu, Kaligrafi *"Air Bersih untuk Payakumbuh yang Lebih Baik"*, 4 karakter warga, dan dedaunan tropis.
- **Kartu Form Frosted Glass (`bg-white/85 backdrop-blur-xl rounded-[32px]`) dengan 4 Mode Lengkap**:
  1. **Masuk ke Akun Anda** (`?mode=login`) + toggle password & tombol Google
  2. **Bergabung dengan Smart Water** (`?mode=register`)
  3. **Lupa password?** (`?mode=forgot-password`)
  4. **Masuk Portal Admin** (`?mode=admin-login`) + tombol *Masuk Instan sebagai Admin*
- **PHP Session Authentication (`api/auth.php`)** untuk menyimpan status user di `$_SESSION['swc_user']`.

Konfigurasi halaman berada di `app/LegacyConfig.php`. CSS umum berada di `public/assets/css/shared.css`, JavaScript halaman di `public/assets/js/pages/login-daftar.js`, dan sumber endpoint autentikasi serta chatbot berada di `app/LegacyApi/auth/` dan `app/LegacyApi/chat/`, dengan wrapper di `public/api/`.

## Cara Menjalankan
Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/login` (tambahkan `?mode=register`, `?mode=admin-login`, atau `?mode=forgot-password`).
