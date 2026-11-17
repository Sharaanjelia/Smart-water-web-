# PANDUAN INSTALASI & MENJALANKAN PROYEK
## SMART WATER KOTA PAYAKUMBUH (LARAGON & XAMPP)

---

## 1. Prasyarat Sistem
- **Sistem Operasi**: Windows 10/11, Linux, atau macOS.
- **PHP**: Versi **8.2 atau lebih tinggi** (dengan ekstensi `pdo_mysql`, `mysqli`, `fileinfo`, `mbstring`, `openssl`).
- **Web Server**: Apache / Nginx (via Laragon atau XAMPP).
- **Basis Data**: MySQL 8.x atau MariaDB 10.4+.
- **Composer**: Versi 2.5+.
- **Node.js**: Versi 18+ (opsional untuk asset build).

---

## 2. Langkah Instalasi Menggunakan Laragon (Sangat Direkomendasikan)

### Langkah 1: Klon / Salin Direktori Proyek
Letakkan direktori proyek pada folder root web Laragon:
```bash
C:\laragon\www\smart-water
# atau jika menggunakan XAMPP:
C:\xampp\htdocs\smart-water
```

### Langkah 2: Buka Terminal dan Install Dependencies
Buka terminal (PowerShell atau Laragon Terminal), lalu masuk ke direktori proyek:
```bash
cd C:\laragon\www\smart-water
composer install
```

### Langkah 3: Konfigurasi File Environment
Salin berkas konfigurasi lingkungan dari template `.env.example`:
```bash
cp .env.example .env
```
Buka berkas `.env`, pastikan konfigurasi basis data sesuai dengan Laragon lokal Anda:
```env
APP_NAME="Smart Water Kota Payakumbuh"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost/smart-water

# Konfigurasi MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smart_water_payakumbuh
DB_USERNAME=root
DB_PASSWORD=
```

### Langkah 4: Generate Application Key
```bash
php artisan key:generate
```

### Langkah 5: Buat Basis Data MySQL di Laragon
1. Buka aplikasi **Laragon**, klik tombol **"Start All"** (memulai Apache & MySQL).
2. Klik tombol **"Database"** (membuka HeidiSQL) atau buka phpMyAdmin di browser: `http://localhost/phpmyadmin`.
3. Buat database baru bernama:
   ```sql
   CREATE DATABASE smart_water_payakumbuh CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

### Langkah 6: Jalankan Database Migrations & Seeders
Jalankan perintah berikut untuk menyusun seluruh struktur tabel dan memasukkan data master awal:
```bash
php artisan migrate --seed
```

### Langkah 7: Buat Symbolic Link File Storage
Hubungkan folder penyimpanan unggahan ke folder publik:
```bash
php artisan storage:link
```

### Langkah 8: Buka Website di Browser
Akses proyek melalui URL berikut:
- **Halaman Beranda Warga**: [http://localhost/smart-water/beranda](http://localhost/smart-water/beranda)  
  *(atau jika menggunakan virtual host Laragon: `http://smart-water.test/beranda`)*
- **Portal Petugas & SCADA**: [http://localhost/smart-water/admin-dashboard](http://localhost/smart-water/admin-dashboard)
- **Halaman Login**: [http://localhost/smart-water/login](http://localhost/smart-water/login)

---

## 3. Akun Default Hasil Seeding (Bcrypt Password)

| Peran | Nama | Email / NIP | Kata Sandi | Hak Akses |
| :--- | :--- | :--- | :--- | :--- |
| **Super Admin** | Ir. Hendra Pratama, S.T. | `admin@tirtasago.id` | `Admin123!` | Penuh ke Command Center & Disposisi |
| **Petugas SCADA**| Ahmad Pratama, S.T. | `ahmad.pratama@tirtasago.id` / `NIP-198804-004` | `Petugas123!` | Verifikasi AI-OCR & SCADA Telemetri |
| **Pelanggan 1** | Budi Santoso | `budi.santoso@warga.payakumbuh.go.id` / `0812-7389-4902` | `Pelanggan123!` | Lapor Meter, Cek Tagihan, Pengaduan |
| **Pelanggan 2** | Ibu Siti Nurbaya | `siti.nurbaya@payakumbuh.community` | `Pelanggan123!` | Akses Warga Payakumbuh Timur |

---

## 4. Perintah Pemeliharaan Harian
```bash
# Membersihkan cache aplikasi
php artisan optimize:clear

# Memeriksa status migrasi tabel
php artisan migrate:status

# Menjalankan server Laravel mandiri (tanpa Laragon / XAMPP)
php artisan serve
```
