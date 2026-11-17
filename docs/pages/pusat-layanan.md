# Smart Water - Kota Payakumbuh (Laravel Blade - Halaman Pusat Layanan)

Halaman Pusat Layanan (`ServiceCenterView`) berada di `resources/views/pages/pusat-layanan.blade.php`, beserta seluruh komponen interaktifnya: Header, BrandLogo, Hero Service Center + ilustrasi menara Tirta Sago, Pelacakan Cepat Tiket Pengaduan + Modal Timeline & Simulasi Update Operator, Kartu Pengaduan Saya, Formulir Pengaduan Resmi Lengkap + Layar Sukses, Pengumuman Gangguan & Pemeliharaan Terkonfirmasi dengan filter 5 kecamatan Payakumbuh, Accordion 6 FAQ + ilustrasi layanan pelanggan, Footer 4 kolom, serta Widget Chat TirtaBot AI.

## Struktur Folder

Partial khusus halaman berada di `resources/views/pages/pusat-layanan/includes/` dan `sections/`. Navbar/footer bersama ada di `resources/views/shared/includes/`.

Konfigurasi halaman berada di `app/LegacyConfig.php`. CSS umum dan JavaScript halaman berada di `public/assets/`. Sumber endpoint berada di `app/LegacyApi/complaints/` dan `app/LegacyApi/chat/`, dengan wrapper di `public/api/`.

## Cara Menjalankan

1. **Menggunakan PHP Built-in Server:**
   Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/pusat-layanan`.

2. **Menggunakan XAMPP / Laragon:**
   Buka `http://localhost/smart-water/public/pusat-layanan` setelah proyek berada di `htdocs/smart-water/`.

3. **(Opsional) Mengaktifkan Gemini AI:** Set environment variable `GEMINI_API_KEY` pada server PHP Anda. Jika tidak diset, chatbot menggunakan respons fallback seputar Smart Water Kota Payakumbuh.
