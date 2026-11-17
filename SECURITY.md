# PANDUAN KEAMANAN SISTEM — SMART WATER
## Perumda Air Minum Tirta Sago Kota Payakumbuh

---

## 1. Proteksi Autentikasi & Sandi
- **Hashing**: Menggunakan standar industri **Bcrypt** dengan cost factor 12 (`BCRYPT_ROUNDS=12`) via `Illuminate\Support\Facades\Hash`.
- **Eliminasi Backdoor**: Endpoint pengalihan peran bebas tanpa autentikasi (`/switch-role`) telah dibatasi secara ketat dengan pengecekan `Auth::user()->isStaff()`. Pengguna biasa tidak dapat menaikkan hak akses ke Administrator.
- **Session Management**: Session disimpan di basis data MySQL (`SESSION_DRIVER=database`) dengan waktu kedaluwarsa 120 menit dan regenerasi ID berkala saat login untuk mencegah session fixation.

---

## 2. Proteksi Cross-Site Request Forgery (CSRF)
- Middleware `ValidateCsrfTokens` aktif pada seluruh formulir antarmuka web.
- Pengecualian token CSRF dibatasi secara ketat hanya untuk rute stateless API (`api/*`), sementara rute form web (`/login`, `/register`, `/admin-login`, `/meter-reading`) mewajibkan directive `@csrf`.

---

## 3. Pencegahan SQL Injection & XSS
- **SQL Injection**: Seluruh interaksi database dilakukan melalui **Laravel Eloquent ORM** dan **Query Builder** dengan prepared statements ber-parameter bindings. Tidak terdapat query SQL mentah yang merangkai input pengguna secara langsung.
- **Cross-Site Scripting (XSS)**: Seluruh output Blade menggunakan sintaks escaping otomatis `{{ $variabel }}`. Variabel HTML manual disaring sebelum disajikan ke DOM.

---

## 4. Keamanan Unggahan Berkas (Secure File Storage)
- **Validasi Berkas**: Unggahan foto meteran dan bukti pengaduan diverifikasi tipe MIME (`image/jpeg`, `image/png`, `image/webp`) dan dibatasi maksimal 5MB.
- **Nama Berkas Acak**: Berkas disimpan menggunakan penamaan acak terenkripsi (`Str::random(8)`) dan disimpan di luar document root (`storage/app/public/`), lalu disajikan melalui symlink terkontrol.
- **Pembersihan Base64**: Data URI base64 yang dikirimkan oleh peramban didekode menjadi berkas biner nyata sebelum disimpan ke disk untuk mencegah kehabisan memori server PHP (RAM bloat).

---

## 5. Konfigurasi Lingkungan Produksi (Production Hardening)
Saat melakukan deployment ke server produksi:
1. Pastikan `APP_DEBUG=false` pada file `.env`.
2. Pastikan `APP_ENV=production`.
3. Jalankan `php artisan config:cache` dan `php artisan route:cache`.
4. Berkas `.env`, `.git`, dan cadangan database `.sqlite` telah terdaftar di `.gitignore` dan tidak boleh diunggah ke repositori publik.
