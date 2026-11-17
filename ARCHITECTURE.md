# ARSITEKTUR SISTEM — SMART WATER KOTA PAYAKUMBUH
## Perumda Air Minum Tirta Sago Kota Payakumbuh

---

## 1. Ikhtisar Arsitektur
Smart Water Kota Payakumbuh dikembangkan menggunakan pola arsitektur **Model-View-Controller (MVC)** berbasis **Laravel Framework 12 (PHP 8.2+)** dengan mesin basis data relasional **MySQL**.

Sistem ini mengadopsi prinsip **Zero Frontend Disruption**, di mana seluruh antarmuka Blade, tata letak, skema warna, animasi Tailwind CSS v4, dan interaksi JavaScript yang telah dibangun tetap dipertahankan 100%, sementara lapisan data dialihkan dari sesi memori dan LocalStorage ke lapisan basis data persisten.

```
+-----------------------------------------------------------------------+
|                           LAPISAN KLIEN                               |
|   Blade Views (Tailwind CSS v4)  |  JavaScript State (swc-store.js)   |
|   Admin SCADA Command Center     |  Portal Layanan & Pelanggan Warga  |
+-----------------------------------------------------------------------+
                                   |
                  HTTP Requests / REST JSON Payloads
                                   v
+-----------------------------------------------------------------------+
|                           LAPISAN ROUTING                             |
|   routes/web.php (Web Controllers & Legacy API Adapters)              |
|   routes/api.php (RESTful API v1 untuk Mobile App & Integrasi)        |
|   Middleware: RoleMiddleware, CSRF Protection, Auth Guards            |
+-----------------------------------------------------------------------+
                                   |
                                   v
+-----------------------------------------------------------------------+
|                          LAPISAN KONTROLER                            |
|   SmartWaterPortalController   |   ComplaintController                |
|   MeterReadingController       |   AcademyController                  |
|   ChallengeController          |   AuthService                        |
+-----------------------------------------------------------------------+
                                   |
                        Eloquent ORM / Services
                                   v
+-----------------------------------------------------------------------+
|                       LAPISAN DATA & STORAGE                          |
|   MySQL Database: Users, Profiles, Complaints, Academy, Challenges    |
|   Laravel Storage: storage/app/public (Bukti Lapor & Foto Pengaduan)  |
+-----------------------------------------------------------------------+
```

---

## 2. Struktur Direktori Proyek
```text
smart-water/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AcademyController.php        # Modul kursus & kuis akademi air
│   │   │   ├── ChallengeController.php      # Modul misi hemat air & partisipasi
│   │   │   ├── ComplaintController.php      # Modul tiket pengaduan & histori
│   │   │   ├── MeterReadingController.php   # Modul lapor meter mandiri & approval
│   │   │   └── SmartWaterPortalController.php# Portal pelanggan & SCADA admin
│   │   └── Middleware/
│   │       └── RoleMiddleware.php           # Validasi otorisasi role
│   ├── LegacyApi/                           # Adapter API lama (terhubung ke Controller)
│   │   ├── academy.php
│   │   ├── admin.php
│   │   ├── auth.php
│   │   ├── challenges.php
│   │   ├── chat.php
│   │   ├── complaints.php
│   │   └── profile.php
│   ├── Models/                              # Eloquent Models & Relasi
│   │   ├── AcademyCategory.php
│   │   ├── Challenge.php
│   │   ├── ChallengeMission.php
│   │   ├── ChallengeParticipant.php
│   │   ├── ChallengeSubmission.php
│   │   ├── Complaint.php
│   │   ├── ComplaintAttachment.php
│   │   ├── ComplaintCategory.php
│   │   ├── ComplaintHistory.php
│   │   ├── Course.php
│   │   ├── CustomerProfile.php
│   │   ├── Lesson.php
│   │   ├── MeterReading.php
│   │   ├── Notification.php
│   │   ├── Role.php
│   │   ├── StaffProfile.php
│   │   ├── User.php
│   │   ├── UserLessonProgress.php
│   │   └── UserQuizResult.php
│   └── Services/
│       └── AuthService.php                  # Logika autentikasi & format data sesi
├── database/
│   ├── migrations/                          # Skema tabel MySQL ber-foreign key
│   └── seeders/                             # Master data & akun demo awal
├── public/
│   └── assets/
│       ├── css/                             # Gaya visual Tailwind v4 & halaman
│       └── js/
│           ├── swc-store.js                 # Reactive store adapter bersinkronisasi ke API
│           └── pages/                       # Logika interaktif per halaman
├── resources/
│   └── views/                               # Template Blade (Warga & Admin Portal)
├── routes/
│   ├── api.php                              # Endpoint REST API v1
│   └── web.php                              # Rute antarmuka web
└── storage/
    └── app/public/                          # Penyimpanan foto lapor meter & pengaduan
```

---

## 3. Alur Komunikasi Data

### A. Autentikasi Pengguna & Petugas
1. Klien mengirim identitas dan kata sandi ke `/api/auth.php` atau `/api/v1/auth/login`.
2. `AuthService` memeriksa kecocokan akun di tabel `users` menggunakan `Hash::check()` terhadap Bcrypt hash.
3. Jika akun terdaftar sebagai admin/staf, sistem memuat relasi `staffProfile`. Jika pelanggan, sistem memuat `customerProfile`.
4. Sesi pengguna disinkronkan ke Laravel Session dan format kontrak legacy array, lalu mengarahkan ke dashboard yang sesuai.

### B. Pengaduan Gangguan (Service Center)
1. Pelanggan mengisi formulir di `/pusat-layanan`. Foto bukti diunggah sebagai file multipart atau data URL.
2. `ComplaintController` memvalidasi input, menyimpan berkas foto ke `storage/app/public/complaints/` menggunakan nama acak ber-UUID.
3. Tiket pengaduan (`TK-PYK-YYYY-XXXX`) dicatat pada tabel `complaints`, dan catatan pertama dibuat di `complaint_histories`.
4. Petugas di portal admin dapat melihat antrean laporan dan memperbarui status secara permanen ke MySQL.

### C. Pembacaan Meter AI-OCR & Validasi Petugas
1. Pelanggan mengambil foto meteran dan mengonfirmasi angka kubikasi di `/meter-confirmation`.
2. Skrip `swc-store.js` menyimpan data di antrean lokal sekaligus mengirim payload ke `/api/v1/meter-reports`.
3. `MeterReadingController` menyimpan ke tabel `meter_readings` dengan status `Menunggu Validasi` dan membuat notifikasi di tabel `notifications`.
4. Petugas lapangan di portal admin memvalidasi laporan (`APPROVE` / `REJECT`). Status diperbarui dan pelanggan menerima notifikasi resmi.
