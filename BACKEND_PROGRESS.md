# LAPORAN KEMAJUAN PENYEMPURNAAN BACKEND (BACKEND PROGRESS)
## Sistem Cerdas Smart Water Kota Payakumbuh
### Perumda Air Minum Tirta Sago Kota Payakumbuh

---

## 1. Ringkasan Eksekutif (Executive Summary)

Penyempurnaan backend untuk project **Smart Water Kota Payakumbuh** telah berhasil diselesaikan secara menyeluruh tanpa merusak atau mengubah frontend yang sudah ada (*Zero Frontend Disruption*). Seluruh modul operasional kini terhubung secara penuh ke database **MySQL 8.4 (Laragon)**, menggunakan **Eloquent ORM**, autentikasi berbasis role (*admin*, *petugas/staff*, dan *pelanggan/customer*), serta kontrol keamanan yang ketat.

### Metrik Kunci Hasil Implementasi:
- **Database Engine**: MySQL 8.4 Community Edition (Port `3306`, DB: `smart_water_payakumbuh`).
- **Skema Basis Data**: **28 Tabel**, **24 Foreign Key Constraints**, **11 Migrations Batch**.
- **Hasil Pengujian Komprehensif (MySQL Validation Suite)**: **47 dari 47 Pengujian LULUS (100% PASS)**.
- **Hasil Pengujian Fitur (Laravel PHPUnit)**: **9 dari 9 Feature Test LULUS (24 Assertions)**.
- **Integritas Frontend**: **100% Utuh** (Tidak ada perubahan pada Blade template, styling Tailwind/CSS, ID elemen JavaScript, navbar, maupun footer).
- **Status Scope Kerja**: Fokus penuh pada penyempurnaan backend lokal. Pengujian sinkronisasi multi-device, deployment hosting, dan pengujian antar-laptop ditunda sesuai arahan.

---

## 2. Status Detail Penyempurnaan Per Modul

### Modul 1: Audit & Skema Database MySQL
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Migration baru ditambahkan: `2026_11_17_000007_refine_complaints_assignment_and_resolution.php`.
  - Penambahan kolom pada tabel `complaints`: `assigned_to` (FK ke `users.id`), `assigned_at`, `resolved_at`, `resolution_notes`, dan `rejection_reason`.
  - Composite indexes dipasang untuk performa query pencarian dan pelaporan: `['status', 'district']`, `['assigned_to', 'status']`.
  - Relasi Eloquent disempurnakan di `app/Models/Complaint.php`, `app/Models/User.php`, dan `app/Models/CustomerProfile.php`.
  - Menghindari perintah destruktif (`migrate:fresh` atau `db:wipe`), seluruh data dan tabel yang ada tetap aman.

### Modul 2: Autentikasi, Registrasi & Role-Based Access Control (RBAC)
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Hashing password menggunakan algoritma **Bcrypt** (`$2y$12$`).
  - `app/Services/AuthService.php` disempurnakan sehingga pelanggan dapat login melalui salah satu identifier: **Email**, **Nomor Telepon**, **Nomor Pelanggan**, atau **Nomor Meter Air** (`01000`).
  - Registrasi pelanggan dibungkus dalam `DB::transaction()` dengan pembuatan otomatis profil pelanggan (`CustomerProfile`) dan notifikasi sambutan awal.
  - Otorisasi ketat pada `app/Http/Controllers/SmartWaterPortalController.php`:
    - Pengunjung tanpa sesi (*guest*) yang mencoba mengakses `/admin-dashboard` langsung dialihkan ke `/admin-login`.
    - Pelanggan (*customer*) yang mencoba membuka halaman admin atau mengakses endpoint eskalasi `/switch-role/admin` ditolak dan dialihkan kembali ke portal warga `/user` dengan pesan peringatan.
    - Hanya petugas (*staff*) dan administrator yang diizinkan mengakses area operasional admin.

### Modul 3: Pusat Layanan / Pengaduan Pelanggan (Ticketing Engine)
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Siklus hidup tiket pengaduan lengkap: `BARU` -> `DIPROSES` -> `MENUNGGU TINDAK LANJUT` -> `SELESAI` / `DITOLAK`.
  - Pembuatan nomor tiket otomatis berformat unik `TK-PYK-YYYY-XXXX` dengan pencatatan histori awal.
  - Endpoint baru pada `ComplaintController`:
    - `show($id)`: Menampilkan rincian tiket beserta histori dan lampiran, dilengkapi **proteksi isolasi data** (pelanggan hanya bisa melihat tiket miliknya sendiri; akses tiket milik warga lain diblokir dengan HTTP 403 Forbidden).
    - `assign($id)`: Penugasan tiket ke teknisi/petugas lapangan tertentu (`assigned_to`) disertai pencatatan riwayat penugasan dan notifikasi otomatis.
    - `respond($id)`: Pemberian tanggapan resmi operasional oleh tim TRC / petugas lapangan.
    - `resolve($id)`: Penyelesaian tiket tuntas dengan pencatatan `resolved_at` dan catatan teknis perbaikan, atau penolakan dengan `rejection_reason`.
  - Optimasi query dengan *eager loading* relasi (`category`, `histories`, `attachments`, `assignedStaff`) untuk mencegah masalah *N+1 queries*.

### Modul 4: Lapor Stand Meter Mandiri (AI-OCR & Verifikasi)
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Pelanggan mengirimkan stand meter mandiri yang langsung tersimpan persisten ke tabel `meter_readings` di MySQL.
  - Dekoder gambar base64 (`data:image/...`) menyimpan foto fisik meteran ke disk penyimpanan lokal (`storage/app/public/meter_readings/`).
  - Antrean verifikasi (`MeterReadingController::apiList`) menerapkan pemisahan data: pelanggan hanya melihat riwayat lapor meter miliknya, sedangkan petugas melihat seluruh antrean verifikasi kota.
  - Verifikasi petugas (`apiValidate`):
    - Diberi *role guard* ketat (hanya *staff* atau *admin* yang berhak memvalidasi; pelanggan yang mencoba memanggil endpoint ini diblokir dengan HTTP 403 Forbidden).
    - Saat petugas menyetujui stand meter, data profil pelanggan (`customer_profiles.last_meter_value` dan `current_meter_value`) secara otomatis diperbarui di MySQL.
    - Notifikasi konfirmasi otomatis dikirimkan ke akun pelanggan yang bersangkutan.

### Modul 5: Akademi Air & Edukasi Gamifikasi
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Kurikulum dan modul pelajaran tersimpan secara relasional di `courses` dan `lessons`.
  - Progres belajar warga dicatat di tabel `user_lesson_progress`. Mendukung pencarian materi baik menggunakan string code (`lesson-1-1`) maupun integer ID (`1`).
  - Kuis interaktif dievaluasi dan hasilnya dicatat di tabel `user_quiz_results`.
  - Poin / XP dari hasil kelulusan kuis langsung disinkronkan dan ditambahkan ke saldo reward pelanggan (`customer_profiles.points`).

### Modul 6: Tantangan Hemat Air & Aksi Warga
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Program tantangan aktif dikelola dari database tabel `challenges` dan misi di `challenge_missions`.
  - Pendaftaran warga sebagai peserta tersimpan di `challenge_participants`.
  - Pengumpulan bukti aksi warga (catatan refleksi dan foto) tersimpan di `challenge_submissions`.
  - Penambahan poin reward tantangan secara otomatis ke akun warga yang menyelesaikan misi.

### Modul 7: Notifikasi Terintegrasi (Notification Hub)
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Notifikasi terhubung langsung ke tabel `notifications` berelasi dengan `user_id`.
  - Event pemicu notifikasi otomatis:
    - Sambutan registrasi pelanggan baru.
    - Pembaharuan status pengaduan warga.
    - Penugasan tiket ke teknisi lapangan.
    - Verifikasi dan persetujuan stand meteran air.
    - Keberhasilan menyelesaikan materi/kuis Akademi Air.
  - Fitur penandaan seluruh notifikasi telah dibaca (`is_read = 1`) berjalan cepat dan akurat.

### Modul 8: Dashboard Admin & Agregasi Data Riil
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Seluruh statistik pada portal admin/petugas (`SmartWaterPortalController::adminDashboard`) dihitung langsung dari query agregasi SQL riil:
    - Total pelanggan terdaftar (`users` role customer).
    - Total pengaduan aktif (`status != 'SELESAI'`).
    - Antrean verifikasi stand meter (`status = 'Menunggu Validasi'`).
    - Efisiensi SCADA & distribusi air per zona.
  - Tidak ada lagi angka statistik operasional yang di-hardcode.

### Modul 9: Konsolidasi API REST v1 & Legacy Adapters
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - REST API v1 (`routes/api.php`) dan Legacy API (`app/LegacyApi/*.php` via `/api/...`) disinkronkan agar keduanya mengarah ke data model MySQL yang sama.
  - Masalah pemanggilan `exit;` pada legacy script diperbaiki dengan mekanisme *output buffering* (`ob_start()` / `ob_get_clean()`) di `routes/web.php`, sehingga mengembalikan objek `Response` Laravel standar tanpa memutus siklus aplikasi.
  - Middleware sesi (`StartSession`, `EncryptCookies`) diaktifkan pada grup API untuk mendukung autentikasi sesi yang mulus pada pemanggilan AJAX/Fetch dari frontend yang sudah ada.

### Modul 10: Kebijakan Session vs LocalStorage
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Seluruh data operasional (tiket pengaduan, progres belajar, kuis, stand meter, riwayat tagihan, dan notifikasi) sepenuhnya disimpan di **MySQL**.
  - **LocalStorage** dipertahankan khusus untuk preferensi antarmuka pengguna di sisi klien (seperti *active tab state*, riwayat input form sementara, atau preferensi visual), sesuai aturan bahwa LocalStorage untuk preferensi UI tidak boleh dihapus.
  - `swc-store.js` tersinkronisasi dengan baik tanpa merusak interaktivitas frontend.

### Modul 11: Error Handling, Keamanan & Integritas Transaksi
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - **Prepared Statements** Eloquent mencegah 100% celah SQL Injection (telah diuji dengan payload injection `admin' OR '1'='1`).
  - **XSS Sanitization**: Input teks dan deskripsi disimpan secara aman dan di-escape saat dirender ke Blade view (`{{ $var }}`).
  - **Keamanan Berkas**: Validasi ketat format berkas dan tipe MIME pada unggahan foto pengaduan dan stand meter.
  - **Database Transactions (`DB::transaction`)**: Diterapkan pada registrasi pelanggan, pembaruan status tiket beserta historinya, dan validasi meteran untuk menjamin integritas data (ACID).

### Modul 12: Otorisasi & Perlindungan Akses Antar-Pengguna (IDOR Prevention)
- **Status**: **SELESAI & TERUJI (100%)**
- **Implementasi**:
  - Pengguna tidak dapat melihat atau mengubah pengaduan atau laporan meter milik pengguna lain (`ComplaintController::show` dan `MeterReadingController::apiList` memeriksa kepemilikan tiket).
  - Pelanggan tidak diizinkan memanggil endpoint verifikasi petugas (`apiValidate`), penugasan (`assign`), maupun eskalasi role (`switchRole`), dan akan menerima respons **HTTP 403 Forbidden**.

---

## 3. Rekapitulasi Berkas yang Diubah dan Dibuat

| Berkas / Direktori | Status | Deskripsi Perubahan |
| :--- | :--- | :--- |
| `database/migrations/2026_11_17_000007_refine_complaints_assignment_and_resolution.php` | **Dibuat** | Migration penambahan kolom penugasan, penyelesaian, dan composite index pada tabel complaints. |
| `app/Models/Complaint.php` | **Diperbarui** | Penambahan field fillable, cast timestamp, dan relasi `assignedStaff`. |
| `app/Models/User.php` | **Diperbarui** | Penambahan relasi `assignedComplaints` dan `validatedMeterReadings`. |
| `app/Services/AuthService.php` | **Diperbarui** | Login via nomor meter, database transaction pada registrasi dan pembuatan notifikasi awal. |
| `app/Http/Controllers/ComplaintController.php` | **Diperbarui** | Penambahan method `show`, `assign`, `respond`, `resolve`, proteksi role petugas, dan isolasi akses tiket warga. |
| `app/Http/Controllers/MeterReadingController.php` | **Diperbarui** | Isolasi data pembacaan per pelanggan, decoding base64 gambar, sinkronisasi stand meter ke profil pelanggan, dan role guard verifikasi. |
| `app/Http/Controllers/AcademyController.php` | **Diperbarui** | Fleksibilitas pencarian materi via code/ID dan sinkronisasi reward poin kuis ke profil pelanggan. |
| `app/Http/Controllers/ChallengeController.php` | **Diperbarui** | Fleksibilitas pencarian misi via code/ID, validasi bukti aksi, dan sinkronisasi reward poin misi. |
| `app/Http/Controllers/SmartWaterPortalController.php` | **Diperbarui** | Pencegahan eskalasi role, pengamanan akses dashboard admin untuk pelanggan dan guest. |
| `routes/api.php` | **Diperbarui** | Registrasi rute endpoint pengaduan: detail, assign, response, resolve. |
| `routes/web.php` | **Diperbarui** | Perbaikan runner legacy API menggunakan output buffering agar tidak terminate dengan `exit;`. |
| `app/LegacyApi/complaints.php` | **Diperbarui** | Penanganan aksi assign, response, resolve, dan update_status untuk kompatibilitas frontend legacy. |
| `bootstrap/app.php` | **Diperbarui** | Penambahan middleware sesi pada API group. |
| `tests/full_mysql_validation.php` | **Dibuat** | Script pengujian komprehensif 47 skenario pada database riil MySQL 8.4. |
| `DATABASE.md` | **Diperbarui** | Pembaruan dokumentasi skema tabel complaints dan relasinya. |
| `API_DOCUMENTATION.md` | **Diperbarui** | Pendokumentasian endpoint baru pengaduan dan otorisasi role. |
| `BACKEND_PROGRESS.md` | **Dibuat** | Dokumen rekapitulasi kemajuan backend komprehensif ini. |

---

## 4. Log Hasil Pengujian pada Database MySQL Riil

Pengujian dijalankan langsung terhadap instance database **MySQL 8.4 (Laragon)** pada `127.0.0.1:3306`:

```text
============================================================
 SMART WATER KOTA PAYAKUMBUH - REAL MYSQL VALIDATION
============================================================

SECTION 1: Database & Migration Validation (MySQL 8.4)
  [PASS] Koneksi MySQL Aktif
         Details: Driver: mysql, Database: smart_water_payakumbuh
  [PASS] Seluruh Tabel Terbentuk di MySQL (28 Tabel)
         Details: Total tabel ditemukan: 28
  [PASS] Foreign Key Constraints Terpasang di MySQL
         Details: Total constraint relasi: 24
  [PASS] Seluruh Database Migrations Berhasil Dijalankan
         Details: Total migration tercatat: 11

SECTION 2: Authentication & Role Authorization
  [PASS] Password Menggunakan Bcrypt Hashing (Bukan Plain Text)
         Details: Hash prefix: $2y$12$
  [PASS] Login Admin dengan Kredensial Valid
         Details: Status: 200, Role: admin
  [PASS] Login Petugas Lapangan dengan Kredensial Valid
         Details: Status: 200, Role: staff
  [PASS] Login Pelanggan via Alamat Email
         Details: Status: 200, User: Budi Santoso
  [PASS] Login Pelanggan via Nomor Meter (01000)
         Details: Status: 200, User: Budi Santoso
  [PASS] Login Ditolak Saat Password Salah (HTTP 401)
         Details: Status Code: 401
  [PASS] Guest Dilarang Mengakses /admin-dashboard (Redirect ke Login)
         Details: Redirected to: http://localhost/admin-login
  [PASS] Pelanggan Ditolak Mengakses /admin-dashboard (Redirect ke /user)
         Details: Customer diarahkan ke: http://localhost/user
  [PASS] Petugas Diizinkan Mengakses /admin-dashboard (HTTP 200)
         Details: Status: 200
  [PASS] Eskalasi Role Tanpa Sesi Ditolak (/switch-role/admin)
         Details: Redirected to: http://localhost/admin-login
  [PASS] Pelanggan Dilarang Eskalasi Menjadi Admin (/switch-role/admin)
         Details: Customer diarahkan ke: http://localhost/user

SECTION 3: Modul Pusat Layanan / Pengaduan (CRUD & Multi-Device Sync)
  [PASS] Pelanggan Berhasil Membuat Pengaduan (Tersimpan ke MySQL)
         Details: No Tiket: TK-PYK-2026-2279, DB ID: 14
  [PASS] Histori Status Tiket Awal Tercatat di MySQL
         Details: Histori status: BARU
  [PASS] Petugas Melihat Tiket Pelanggan dari Sesi Petugas Berbeda
         Details: Tiket TK-PYK-2026-2279 ditemukan di inbox petugas
  [PASS] Petugas Memperbarui Status Tiket Menjadi DIPROSES
         Details: Status terkini di DB: DIPROSES
  [PASS] Pelanggan Melihat Perubahan Status dari Perangkat Berbeda
         Details: Pelanggan menerima status: DIPROSES
  [PASS] Penugasan Teknisi Lapangan Berhasil Tercatat di MySQL
         Details: Teknisi ID: 2, Waktu: 2026-11-17 06:22:58
  [PASS] Petugas Memberikan Tanggapan Resmi Penanganan Tiket
         Details: Tanggapan berhasil disimpan di riwayat penanganan
  [PASS] Penyelesaian Tiket Tuntas Tercatat di MySQL (Status SELESAI)
         Details: Waktu selesai: 2026-11-17 06:22:58, Catatan: Pipa utama 3 inch berhasil diganti baru, uji tekanan 3.2 bar normal.
  [PASS] Pelanggan Lain Dilarang Mengakses Tiket Milik Warga Lain (HTTP 403)
         Details: Status Code: 403 (Forbidden)

SECTION 4: Modul Lapor Meter Mandiri & Verifikasi Petugas
  [PASS] Pelanggan Mengirim Laporan Meter AI-OCR ke MySQL
         Details: No Laporan: REP-04-5883, Stand: 1085.0 m3, Status: Menunggu Validasi
  [PASS] Laporan Meter Pelanggan Masuk ke Dashboard Verifikasi Petugas
         Details: Laporan REP-04-5883 muncul di daftar validasi petugas
  [PASS] Petugas Memvalidasi Laporan Meter (Status Terverifikasi AI)
         Details: Status: Terverifikasi AI, Validated by ID: 2
  [PASS] Isolasi Data Meter: Pelanggan Lain Tidak Dapat Mengakses Data Meter Milik Warga Lain
         Details: Laporan meter warga lain disaring dan diisolasi dengan aman

SECTION 5: Modul Akademi Air (Materi & Kuis Interaktif)
  [PASS] Mendapatkan Kurikulum & Modul Kursus dari MySQL
         Details: Total modul: 2
  [PASS] Menyelesaikan Materi Pelajaran (Tersimpan ke user_lesson_progress)
         Details: Lesson ID 1 (lesson-1-1) selesai
  [PASS] Menyelesaikan Kuis Modul dengan Skor 95 (Tersimpan ke user_quiz_results)
         Details: Course ID 1, Skor: 95

SECTION 6: Modul Tantangan Hemat Air (Pendaftaran & Misi)
  [PASS] Daftar Program Tantangan Terbaca dari MySQL
         Details: Total tantangan aktif: 2
  [PASS] Pendaftaran Peserta Tantangan Tersimpan di challenge_participants
         Details: Participant ID: 1
  [PASS] Penyelesaian Misi Tantangan Tersimpan di challenge_submissions
         Details: Misi: Gunakan baskom penampung saat mencuci piring & bahan masakan pagi

SECTION 7: Modul Notifikasi
  [PASS] Daftar Notifikasi Terambil dari MySQL Sesuai User ID
         Details: Total notifikasi user: 20
  [PASS] Tandai Seluruh Notifikasi Sudah Dibaca (is_read = 1)
         Details: Sisa notifikasi belum dibaca: 0

SECTION 8: Modul Profil Pengguna
  [PASS] Mendapatkan Data Profil Pengguna & Sambungan Meter dari MySQL
         Details: Nama: Budi Santoso, No Meter: 01000
  [PASS] Pembaruan Kontak & Alamat Profil Tersimpan ke MySQL
         Details: Nomor HP baru di database: 0812-7766-5544

SECTION 9: Modul Dashboard Admin (Agregasi Data Riil)
  [PASS] Halaman Dashboard Admin Merender Statistik Database Riil
         Details: Total Pengaduan Terdata: 14, Total Pengguna Terdaftar: 4

SECTION 10: Validasi Keamanan (Security Controls)
  [PASS] SQL Injection pada Form Login Berhasil Dicegah (Prepared Statement)
         Details: Status Code: 401
  [PASS] XSS Payload Tertangani dengan Aman (Stored Safely, No Execution)
         Details: Tersimpan dengan aman sebagai entitas teks
  [PASS] Pelanggan Dilarang Mengubah Status Tiket Pengaduan (Role Guard)
         Details: Status Code: 403 (Forbidden)
  [PASS] Pelanggan Dilarang Memvalidasi Laporan Meter (Role Guard)
         Details: Status Code: 403 (Forbidden)

SECTION 11: Validasi Kompatibilitas Legacy API Adapters
  [PASS] Adapter /api/auth/auth.php Menghasilkan Response JSON yang Valid
         Details: ok: false
  [PASS] Adapter /api/complaints/complaints.php Kompatibel dengan Frontend
         Details: Total pengaduan terambil: 15
  [PASS] Adapter /api/academy/academy.php Kompatibel dengan Frontend
         Details: Kurikulum: 2 modul
  [PASS] Adapter /api/challenges/challenges.php Kompatibel dengan Frontend
         Details: Tantangan: 2

============================================================
 VALIDATION SUMMARY
 Total Passed: 47
 Total Failed: 0
 Final Status: 100% ALL TESTS PASSED (PASS)
============================================================
```

Pengujian fitur Laravel PHPUnit:
```text
   PASS  Tests\Feature\SmartWaterBackendTest
  ✓ public pages are accessible                                                   1.74s  
  ✓ customer can login with valid credentials                                     0.20s  
  ✓ login with invalid password is rejected                                       0.12s  
  ✓ new customer can register                                                     0.19s  
  ✓ complaint can be created and stored                                           0.41s  
  ✓ academy returns courses and progress                                          0.12s  
  ✓ challenges returns active list                                                0.09s  
  ✓ meter reading report is stored                                                0.11s  
  ✓ staff can validate meter reading                                              0.09s  

  Tests:    9 passed (24 assertions)
  Duration: 4.05s
```

---

## 5. Catatan Penting & Sisa Pekerjaan (Roadmap Selanjutnya)

Sesuai dengan instruksi pengguna, pekerjaan berikut **DITUNDA** dan menjadi agenda untuk tahap selanjutnya setelah backend lokal benar-benar sempurna dan disetujui:

1. **Pengujian Sinkronisasi Multi-Device / Antar-Laptop Fisik**:
   - Menghubungkan 2 perangkat laptop di satu jaringan WiFi lokal untuk interaksi real-time antara portal warga dan portal admin.
2. **Konfigurasi Produksi & Deployment Online**:
   - Pengaturan *Virtual Host*, SSL/HTTPS, reverse proxy Nginx/Apache untuk hosting publik, serta optimasi caching produksi (`config:cache`, `route:cache`, `view:cache`).
3. **Penyempurnaan Integrasi Perangkat IoT Eksternal**:
   - Apabila nantinya sensor SCADA fisik dihubungkan melalui protokol MQTT / Webhook HTTP langsung ke database MySQL.

---

## 6. Laporan Implementasi Task 1: Layer Form Requests

Pemisahan seluruh validasi input dari Controller ke layer **Laravel Form Requests** telah berhasil diselesaikan secara penuh pada seluruh endpoint operasional Smart Water.

### A. Daftar Form Request yang Dibuat (`app/Http/Requests/`)
1. `BaseFormRequest.php`: Kelas abstrak induk yang menangani penanganan seragam `failedValidation()` (mengembalikan JSON 422 berformat konsisten `{ ok: false, error: ..., message: ..., errors: ... }` untuk API dan flash session untuk web) serta `failedAuthorization()` (mengembalikan JSON 403), pendeteksi actor/role, dan validator aman Base64 gambar.
2. `Auth/LoginRequest.php`: Validasi login pelanggan, petugas dinas, dan admin SCADA.
3. `Auth/RegisterCustomerRequest.php`: Validasi pendaftaran pelanggan baru dengan konfirmasi kata sandi dan keunikan email.
4. `Complaint/StoreComplaintRequest.php`: Validasi pelaporan gangguan/kendala air, normalisasi alias field (`category`, `userPhone`, `userName`, `imageUrl`), serta pengujian format Base64 dan batas ukuran 10MB.
5. `Complaint/UpdateComplaintStatusRequest.php`: Validasi pembaruan status pengaduan dan otorisasi petugas/admin.
6. `Complaint/AssignComplaintRequest.php`: Validasi penugasan teknisi TRC (`staff_id` exists in users) dan otorisasi petugas.
7. `Complaint/RespondComplaintRequest.php`: Validasi catatan tanggapan resmi operasional dan otorisasi petugas.
8. `Complaint/ResolveComplaintRequest.php`: Validasi penuntasan/penolakan tiket pengaduan (`status in:SELESAI,DITOLAK`) dan otorisasi petugas.
9. `Meter/StoreMeterReadingRequest.php`: Validasi lapor stand meter AI-OCR, normalisasi alias data (`meterNumber`, `ocrReading`, `lastReading`), pengecekan numerik non-negatif, dan pengujian foto Base64.
10. `Meter/ValidateMeterReadingRequest.php`: Validasi persetujuan/penolakan stand meter oleh petugas dan otorisasi petugas.
11. `Profile/UpdateProfileRequest.php`: Validasi pembaruan nomor kontak dan alamat tempat tinggal pelanggan.
12. `Academy/CompleteLessonRequest.php`: Validasi penyelesaian bab materi (`lessonId`).
13. `Academy/CompleteQuizRequest.php`: Validasi kelulusan kuis (`courseId`, skor 0-100).
14. `Challenge/EnrollChallengeRequest.php`: Validasi pendaftaran aksi tantangan hemat air (`challengeId`).
15. `Challenge/CompleteMissionRequest.php`: Validasi klaim penyelesaian misi (`challengeId`, `missionId`, catatan refleksi, dan foto bukti Base64).

### B. Endpoint yang Terhubung ke Form Request
| Endpoint | Method | Form Request Terpasang | Otorisasi (`authorize()`) |
| :--- | :---: | :--- | :--- |
| `/api/v1/auth/login` & `/login` & `/admin-login` | POST | `LoginRequest` | Publik |
| `/api/v1/auth/register` & `/register` | POST | `RegisterCustomerRequest` | Publik |
| `/api/v1/complaints` | POST | `StoreComplaintRequest` | Publik / Warga |
| `/api/v1/complaints/{id}/status` | POST | `UpdateComplaintStatusRequest` | Khusus Petugas / Admin (403 jika Pelanggan) |
| `/api/v1/complaints/{id}/assign` | POST | `AssignComplaintRequest` | Khusus Petugas / Admin (403 jika Pelanggan) |
| `/api/v1/complaints/{id}/response` | POST | `RespondComplaintRequest` | Khusus Petugas / Admin (403 jika Pelanggan) |
| `/api/v1/complaints/{id}/resolve` | POST | `ResolveComplaintRequest` | Khusus Petugas / Admin (403 jika Pelanggan) |
| `/api/v1/meter-reports` | POST | `StoreMeterReadingRequest` | Publik / Pelanggan |
| `/api/v1/meter-reports/validate` | POST | `ValidateMeterReadingRequest` | Khusus Petugas / Admin (403 jika Pelanggan) |
| `/api/v1/profile/update` | POST | `UpdateProfileRequest` | Pengguna Terautentikasi |
| `/api/v1/academy/complete-lesson` | POST | `CompleteLessonRequest` | Publik / Warga |
| `/api/v1/academy/complete-quiz` | POST | `CompleteQuizRequest` | Publik / Warga |
| `/api/v1/challenges/enroll` | POST | `EnrollChallengeRequest` | Publik / Warga |
| `/api/v1/challenges/complete-mission` | POST | `CompleteMissionRequest` | Publik / Warga |

### C. Berkas Controller & Rute yang Diperbarui
- `app/Http/Controllers/ComplaintController.php`: Method `store`, `updateStatus`, `assign`, `respond`, dan `resolve` diinjeksikan Form Request terdedikasi; logika validasi dan autorisasi manual dihapus.
- `app/Http/Controllers/MeterReadingController.php`: Method `apiStore` dan `apiValidate` diinjeksikan Form Request; pengecekan manual digantikan `StoreMeterReadingRequest` dan `ValidateMeterReadingRequest`.
- `app/Http/Controllers/AcademyController.php`: Method `completeLesson` dan `completeQuiz` diinjeksikan Form Request.
- `app/Http/Controllers/ChallengeController.php`: Method `enroll` dan `completeMission` diinjeksikan Form Request.
- `app/Http/Controllers/SmartWaterPortalController.php`: Method `handleLogin`, `handleAdminLogin`, dan `handleRegister` diinjeksikan Form Request.
- `routes/api.php`: Injeksi `LoginRequest`, `RegisterCustomerRequest`, dan `UpdateProfileRequest` pada closure rute API.

### D. Hasil Pengujian Menyeluruh
1. **Feature Test Khusus Validasi (`tests/Feature/FormRequestValidationTest.php`)**:
   - `test_login_validation_rejects_empty_credentials`: **PASS** (Menolak login kosong dengan HTTP 422).
   - `test_register_validation_rejects_invalid_email_and_short_password`: **PASS** (Menolak email rusak & password < 6 char dengan HTTP 422).
   - `test_complaint_validation_rejects_empty_required_fields`: **PASS** (Menolak laporan kosong dengan HTTP 422).
   - `test_complaint_validation_rejects_corrupted_base64_image`: **PASS** (Menolak Base64 gambar korup dengan HTTP 422).
   - `test_meter_reading_validation_rejects_negative_or_invalid_reading`: **PASS** (Menolak stand meter negatif & teks dengan HTTP 422).
   - `test_meter_validation_rejects_unauthorized_customer_access`: **PASS** (Memblokir pelanggan memvalidasi meter dengan HTTP 403).
   - `test_academy_validation_rejects_missing_lesson_id`: **PASS** (Menolak request tanpa `lessonId` dengan HTTP 422).
   - `test_quiz_validation_rejects_invalid_score_range`: **PASS** (Menolak nilai kuis > 100 dengan HTTP 422).
   - `test_assign_complaint_rejects_unauthorized_customer`: **PASS** (Memblokir pelanggan menugaskan teknisi dengan HTTP 403).
   - **Hasil: 9 of 9 PASSED (20 assertions)**.

2. **Feature Test Alur Bisnis (`tests/Feature/SmartWaterBackendTest.php`)**:
   - **Hasil: 9 of 9 PASSED (24 assertions)**.

3. **Total Suite PHPUnit (`php artisan test`)**:
   - **Hasil: 20 of 20 PASSED (46 assertions, 100% PASS)**.

4. **Integrasi Nyata MySQL 8.4 (`tests/full_mysql_validation.php`)**:
   - **Hasil: 47 of 47 PASSED (100% ALL TESTS PASSED)**.

### E. Kendala & Pekerjaan Selanjutnya
- **Kendala**: Tidak ada kendala teknis. Seluruh kontrak respons API tetap 100% kompatibel dengan frontend JavaScript dan test runner yang sudah ada.
- **Pekerjaan Selanjutnya**: Melanjutkan implementasi layer Services (Task 2) untuk mewujudkan Thin Controller.

---

## 7. Laporan Implementasi Task 2: Layer Services & Thin Controllers

Pemisahan seluruh *business logic*, transaksi database, manipulasi berkas fisik, dan kalkulasi operasional dari Controller ke dalam **Services Layer** telah berhasil diselesaikan secara menyeluruh.

### A. Daftar Services yang Dibuat & Diperbarui (`app/Services/`)

1. **`NotificationService.php` (Baru)**:
   - **Tanggung Jawab**: Mengelola pembuatan, penyimpanan, query, dan status keterbacaan notifikasi terpusat untuk warga dan petugas.
   - **Method Utama**:
     - `send(int $userId, string $type, string $title, string $desc, ?string $link)`
     - `getForUser(int $userId, int $limit)`
     - `getUnreadCount(int $userId)`
     - `markAllAsRead(int $userId)`
     - `markAsRead(int $notificationId, int $userId)`

2. **`ComplaintService.php` (Baru)**:
   - **Tanggung Jawab**: Menjalankan seluruh proses bisnis siklus pengaduan masyarakat.
   - **Method Utama**:
     - `createComplaint(array $data, ?User $user)`: Transaksional pembuatan tiket, pencocokan kategori, penyimpanan foto Base64 ke disk publik, pembuatan riwayat status awal, dan pemicu notifikasi.
     - `generateTicketNumber()`: Algoritma penomoran tiket `TK-PYK-YYYY-XXXX` dengan loop proteksi keunikan di MySQL.
     - `saveAttachment(Complaint $complaint, string $base64Data)`: Dekoder aman Base64, deteksi MIME type, dan penyimpanan fisik di `storage/app/public/complaints/`.
     - `updateStatus(...)`: Pembaruan status operasional lapangan dan pencatatan history.
     - `assignTechnician(...)`: Penugasan teknisi TRC lapangan, update status ke `DIPROSES`, dan notifikasi otomatis ke pelapor.
     - `addResponse(...)`: Pencatatan tanggapan resmi tim lapangan ke riwayat penanganan.
     - `resolveComplaint(...)`: Penuntasan tiket, pengisian timestamp `resolved_at`, dan catatan perbaikan.
     - `rejectComplaint(...)`: Penolakan pengaduan tidak valid disertai alasan penolakan.
     - `findComplaint(string|int $idOrNumber)`: Query detail pengaduan dengan *eager loading* lengkap.
     - `getComplaints(...)`: Query daftar pengaduan terfilter tanpa masalah N+1.

3. **`MeterReadingService.php` (Baru)**:
   - **Tanggung Jawab**: Menangani proses pelaporan pembacaan stand meter AI-OCR, kalkulasi kubikasi, dan verifikasi dinas.
   - **Method Utama**:
     - `createReading(array $data, ?User $user)`: Menghitung selisih stand meter (`consumption`), estimasi tagihan (`round(($consumption * 7800) + 2600)`), penyimpanan foto dial meteran, dan generate kode unik `REP-04-XXXX`.
     - `validateReading(...)`: Persetujuan petugas dinas (`status = 'Terverifikasi AI'`), sinkronisasi nilai meter ke tabel `customer_profiles`, pencatatan riwayat validator, dan dispatch notifikasi status rekening ke warga. Serta penanganan penolakan stand meter jika dial buram.
     - `getReadings(?int $userId, ?string $status)`: Pengambilan antrean verifikasi terisolasi per user/publik.

4. **`AcademyService.php` (Baru)**:
   - **Tanggung Jawab**: Manajemen kurikulum edukasi air, pencatatan materi selesai, dan evaluasi kelulusan kuis gamifikasi.
   - **Fitur Kritis**: **Pencegahan Poin Ganda**: Mengecek apakah peserta sudah pernah lulus kuis (`is_passed = true`) sebelumnya. Tambahan poin XP ke `customer_profiles` hanya diberikan satu kali pada kelulusan pertama. Request berulang tidak akan menggandakan saldo poin warga.
   - **Method Utama**: `getCurriculum()`, `getUserProgress()`, `completeLesson()`, `completeQuiz()`.

5. **`ChallengeService.php` (Baru)**:
   - **Tanggung Jawab**: Manajemen partisipasi tantangan aksi hemat air dan validasi misi warga.
   - **Fitur Kritis**: **Pencegahan Klaim Poin Ganda**: Memeriksa keberadaan `ChallengeSubmission` yang telah disetujui untuk misi tertentu. Jika misi sudah tuntas, submission susulan tidak akan menambah poin reward lagi.
   - **Bonus Penuntasan**: Mendeteksi otomatis apabila seluruh misi dalam suatu tantangan telah tuntas (`completedMissionsCount >= totalMissions`), mengubah status peserta menjadi `COMPLETED`, memberikan bonus poin tantangan, dan mengirim notifikasi selebrasi.

6. **`AuthService.php` (Diperbarui)**:
   - **Tanggung Jawab**: Sentralisasi autentikasi staf, admin, dan pelanggan; registrasi pelanggan transaksional; sinkronisasi sesi; dan pembersihan logout terpadu.
   - **Penyempurnaan**: Penambahan method `attemptLogin()` dan `performLogout()` untuk mengeliminasi duplikasi logika antara Controller dan route closures.

---

### B. Controller yang Menggunakan Dependency Injection

Seluruh Controller terkait telah direfactor menjadi **Thin Controllers**:

| Controller | Service yang Diinjeksikan | Peran Controller Setelah Refactoring |
| :--- | :--- | :--- |
| `ComplaintController.php` | `ComplaintService` | Hanya menerima `StoreComplaintRequest` / `AssignComplaintRequest` / dll., memanggil method service, dan mengembalikan `formatTicket()` dalam `JsonResponse`. |
| `MeterReadingController.php` | `MeterReadingService` | Menerima `StoreMeterReadingRequest` / `ValidateMeterReadingRequest`, memanggil service `createReading` / `validateReading`, mengembalikan `formatReport()`. |
| `AcademyController.php` | `AcademyService` | Menerima request validasi akademi, memanggil `getCurriculum` / `completeLesson` / `completeQuiz`, mengembalikan JSON kurikulum & progres. |
| `ChallengeController.php` | `ChallengeService` | Menerima request tantangan, memanggil `enrollChallenge` / `completeMission`, mengembalikan JSON respons misi. |
| `SmartWaterPortalController.php` | `AuthService` | Menggunakan `AuthService::attemptLogin` dan `AuthService::performLogout`, merender Blade views portal. |

---

### C. Evaluasi Pemindahan Business Logic

- **Business Logic yang Berhasil Dipindahkan ke Services**:
  1. Penomoran otomatis unik `TK-PYK-YYYY-XXXX` dan `REP-04-XXXX`.
  2. Dekoding Base64 gambar dan penyimpanan file fisik ke direktori storage publik.
  3. Alur transaksional multi-tabel (`DB::transaction`) untuk integritas ACID.
  4. Perhitungan kubikasi pemakaian air dan kalkulasi tarif resmi.
  5. Sinkronisasi stand meter ke profil pelanggan (`CustomerProfile`).
  6. Aturan perolehan XP dan proteksi anti-duplikasi poin pada kuis dan misi tantangan.
  7. Mekanisme pengiriman notifikasi internal ke database.
- **Logika yang Dipertahankan di Controller**:
  - Transformasi serialisasi array HTTP (`formatTicket()`, `formatReport()`), penanganan HTTP Status Codes (200, 201, 403, 404, 422), dan perenderan Blade View web. Logika ini memang merupakan tanggung jawab layer Presentasi/Transport HTTP sebelum nantinya dipindahkan ke API Resources pada Task 3.

---

### D. Hasil Pengujian Menyeluruh (Testing Suite)

1. **Unit Tests Baru untuk Business Logic Services (`tests/Unit/Services/ServicesUnitTest.php`)**:
   - `test_complaint_service_creates_ticket_with_unique_number_and_history`: **PASS**
   - `test_complaint_service_assign_and_resolve_flow`: **PASS**
   - `test_meter_reading_service_calculates_consumption_and_cost`: **PASS**
   - `test_meter_reading_validation_updates_customer_profile`: **PASS**
   - `test_academy_service_prevents_duplicate_xp_points`: **PASS** (Terbukti saldo poin tidak berlipat ganda saat kuis disubmit berulang).
   - `test_challenge_service_prevents_duplicate_mission_points`: **PASS** (Terbukti klaim misi berulang ditolak dan poin tidak bertambah).
   - `test_notification_service_send_and_mark_all_read`: **PASS**
   - **Hasil: 7 of 7 Unit Tests PASSED (32 assertions)**.

2. **Feature Tests Validasi Input (`tests/Feature/FormRequestValidationTest.php`)**:
   - **Hasil: 9 of 9 Feature Tests PASSED (20 assertions)**.

3. **Feature Tests Alur Bisnis (`tests/Feature/SmartWaterBackendTest.php`)**:
   - **Hasil: 9 of 9 Feature Tests PASSED (24 assertions)**.

4. **Total PHPUnit Suite (`php artisan test`)**:
   - **Hasil: 27 of 27 PASSED (78 assertions, 100% PASS)**.

5. **Uji Validasi Database Nyata MySQL 8.4 (`tests/full_mysql_validation.php`)**:
   - **Hasil: 47 of 47 PASSED (100% ALL TESTS PASSED)**.

---

### E. Kepatuhan Batasan & Status Selanjutnya

- **Frontend & Blade**: 100% utuh tanpa perubahan CSS, JavaScript, ID elemen, maupun layout.
- **Database**: Tidak ada migrasi baru, tidak ada skema tabel yang diubah, dan data MySQL tidak dihapus.
- **Batasan Scope**: Sesuai instruksi, layer **API Resources** sengaja belum dibuat pada tahap ini dan siap dilanjutkan pada Task 3 berikutnya.


---

## 5. Pelaksanaan Task 3 — Implementasi Laravel API Resources

Task 3 berfokus pada implementasi penuh **Laravel API Resources Layer** (`app/Http/Resources/`) untuk memisahkan serialisasi Model Eloquent dari Controller tanpa mengubah format kontrak JSON yang dibutuhkan frontend.

### A. Komponen API Resources yang Diimplementasikan (16 Files)

| File Resource | Model Terkait | Tanggung Jawab Utama |
| :--- | :--- | :--- |
| `ComplaintResource.php` | `Complaint` | Transformasi tiket pengaduan lengkap, format waktu WIB, integrasi lampiran foto, histori status (`updates`), category badge, dan backward compatibility aliases (`id`, `ticketId`, `ticket_id`). |
| `ComplaintHistoryResource.php` | `ComplaintHistory` | Transformasi riwayat status dan penanganan tiket. |
| `ComplaintAttachmentResource.php` | `ComplaintAttachment` | Transformasi URL file bukti foto pengaduan. |
| `MeterReadingResource.php` | `MeterReading` | Transformasi laporan stand meter AI-OCR, casting nilai desimal/integer, pemetaan badge status dan warna indikator. |
| `UserProfileResource.php` | `User` | Single source of truth representasi profil pengguna. Mengisolasi field sensitif (`password`, `remember_token`), menggabungkan data profil pelanggan dan staf. |
| `UserResource.php` | `User` | Alias inheritance dari `UserProfileResource`. |
| `CustomerProfileResource.php` | `CustomerProfile` | Transformasi data rekening air, ID pelanggan, nomor meter, tarif, dan saldo/poin. |
| `StaffProfileResource.php` | `StaffProfile` | Transformasi data kedinasan petugas (`nip`, `department`, `position`). |
| `NotificationResource.php` | `Notification` | Transformasi notifikasi, status baca ganda (`isRead` & `is_read`), dan waktu deskriptif. |
| `CourseResource.php` | `Course` | Transformasi kurikulum kursus akademi air beserta bab materi (`lessons`). |
| `LessonResource.php` | `Lesson` | Transformasi bab materi akademi (`id`, `duration`, `content`, `order`). |
| `QuizResultResource.php` | `UserQuizResult` | Transformasi histori dan nilai kuis akademi. |
| `ChallengeResource.php` | `Challenge` | Transformasi program tantangan hemat air beserta daftar misi (`missions`). |
| `ChallengeMissionResource.php` | `ChallengeMission` | Transformasi detail misi aksi harian (`xp`, `tip`, `order`). |
| `ChallengeParticipantResource.php` | `ChallengeParticipant` | Transformasi data peserta tantangan hemat air. |
| `ChallengeSubmissionResource.php` | `ChallengeSubmission` | Transformasi bukti penyelesaian misi warga. |

### B. Controller & Routes Refactored to Thin Controllers
1. `ComplaintController.php`: Method `formatTicket()` didelegasikan langsung ke `ComplaintResource::resolve()`. Seluruh method (`index`, `store`, `show`, `simulateProgress`, `updateStatus`, `assign`, `respond`, `resolve`) mengembalikan JSON Response via `ComplaintResource`.
2. `MeterReadingController.php`: Method `formatReport()` didelegasikan ke `MeterReadingResource::resolve()`. Seluruh endpoint (`apiList`, `apiStore`, `apiValidate`) memanfaatkan `MeterReadingResource`.
3. `AcademyController.php`: Mengembalikan kurikulum via `CourseResource::collection()`.
4. `ChallengeController.php`: Mengembalikan tantangan via `ChallengeResource::collection()`.
5. `routes/api.php`: Endpoint `/notifications`, `/profile`, `/profile/update`, dan `/auth/me` memanfaatkan `NotificationResource` dan `UserProfileResource`.
6. `AuthService.php`: Method `formatUserData()` mendelegasikan serialisasi langsung ke `UserProfileResource`, menjamin satu sumber kebenaran (Single Source of Truth) di seluruh sistem.

### C. Hasil Pengujian Menyeluruh (Testing Suite)
- **Unit Tests Baru (`tests/Unit/Resources/ApiResourceTest.php`)**: **6 of 6 Tests PASSED (65 assertions)**.
- **Total PHPUnit Suite (`php artisan test`)**: **33 of 33 Tests PASSED (143 assertions, 100% PASS)**.
- **Uji Validasi Database Nyata MySQL 8.4 (`tests/full_mysql_validation.php`)**: **47 of 47 Skenario PASSED (100% ALL TESTS PASSED)**.


---

## 6. Pelaksanaan Task 4 — Audit & Penyempurnaan Middleware dan RBAC

Task 4 berfokus pada penyempurnaan menyeluruh sistem **Middleware**, **Role-Based Access Control (RBAC)**, dan **Data Ownership Protection** (Anti-IDOR) pada Laravel 12 Smart Water Kota Payakumbuh.

### A. Tindakan Penyempurnaan yang Dikerjakan
1. **Audit Keamanan Menyeluruh (`RBAC_AUDIT.md`)**:
   - Memetakan matriks wewenang seluruh role (`admin`, `staff`, `customer`).
   - Mengidentifikasi celah unauthenticated access pada tiket pengaduan privat.
   - Mengidentifikasi celah kebocoran data stand meteran warga ke tamu (guest).
2. **Refactoring `RoleMiddleware.php`**:
   - Menambahkan deteksi request API/JSON yang mengembalikan respon JSON HTTP 401 (Unauthenticated) dan HTTP 403 (Forbidden).
   - Menyelaraskan pengalihan web: Pelanggan yang mencoba membuka web admin dialihkan ke `/user`, sedangkan tamu dialihkan ke `/admin-login`.
   - Mengintegrasikan resolver sesi pengguna framework untuk menjaga kompatibilitas.
3. **Penerapan Route Middleware Deklaratif**:
   - `routes/web.php`: Memproteksi rute `/admin` dan seluruh rute operasional SCADA (`/admin-dashboard`, `/water-monitoring`, dll.) dengan `middleware('role:admin,staff')`.
   - `routes/api.php`: Mengelompokkan endpoint status tiket, disposisi/penugasan, tanggapan, penyelesaian tiket, dan validasi lapor meter di bawah grup `Route::middleware('role:admin,staff')`.
4. **Data Ownership & Anti-IDOR Enforcement**:
   - `ComplaintController@show`: Menolak akses tamu tanpa login (HTTP 401) dan pelanggan lain (HTTP 403) pada tiket pengaduan privat.
   - `MeterReadingController@apiList`: Mengembalikan array kosong untuk pengunjung tanpa login guna mencegah kebocoran data meter warga kota.
   - `UpdateProfileRequest` & `RegisterCustomerRequest`: Mencegah manipulasi parameter `role` dan `is_admin`.
5. **Dokumentasi Lengkap (`RBAC_DOCUMENTATION.md`)**:
   - Memuat panduan arsitektur RBAC, diagram hierarki wewenang, dan matriks hak akses endpoint.

### B. Hasil Pengujian Menyeluruh (Testing Suite)
- **Feature Security Test Baru (`tests/Feature/RbacSecurityTest.php`)**: **13 of 13 Tests PASSED (43 assertions)**.
- **Total PHPUnit Suite (`php artisan test`)**: **46 of 46 Tests PASSED (186 assertions, 100% PASS)**.
- **Uji Validasi Database Nyata MySQL 8.4 (`tests/full_mysql_validation.php`)**: **47 of 47 Skenario PASSED (100% ALL TESTS PASSED)**.

---

## 7. Pelaksanaan Task 5 — Deep Authorization Audit, Laravel Policies & Data Ownership

Task 5 berfokus pada penyempurnaan otorisasi pada level data dan tindakan pengguna (**Data-Level Authorization**) melalui implementasi resmi **Laravel Policies**, pemisahan wewenang antar-petugas (Staff Isolation Boundary), pencegahan IDOR (Insecure Direct Object Reference), dan pencegahan eskalasi hak akses (Privilege Escalation Prevention).

### A. Komponen Laravel Policies yang Diimplementasikan (7 Files)

| Berkas Policy | Model Terkait | Tanggung Jawab & Aturan Otorisasi |
| :--- | :--- | :--- |
| `ComplaintPolicy.php` | `Complaint` | Mengatur wewenang view (publik vs privat), update status, assign, respond, dan resolve. Mencegah Petugas A mengubah/menyelesaikan tiket yang ditugaskan kepada Petugas B. Memberi kewenangan supervisi penuh kepada Admin SCADA. |
| `MeterReadingPolicy.php` | `MeterReading` | Mengatur pembacaan stand meter, penyaringan kepemilikan laporan, dan membatasi validasi/persetujuan stand hanya untuk Petugas/Admin. |
| `CustomerProfilePolicy.php` | `CustomerProfile` | Melindungi data rekening air, saldo dompet, dan poin reward agar hanya dapat diakses dan diperbarui oleh pemilik akun bersangkutan atau Admin. |
| `NotificationPolicy.php` | `Notification` | Mengisolasi pesan notifikasi privat pengguna dan penandaan status baca (`is_read`). |
| `AcademyProgressPolicy.php` | `UserLessonProgress`, `UserQuizResult` | Mengatur hak akses progres materi belajar dan evaluasi skor kuis warga. |
| `ChallengeParticipantPolicy.php` | `ChallengeParticipant` | Mengatur pendaftaran dan kepesertaan tantangan hemat air. |
| `ChallengeSubmissionPolicy.php` | `ChallengeSubmission` | Mengatur bukti pengumpulan misi aksi hemat air. |

### B. Registrasi & Penegakan Otorisasi (Enforcement Layer)

1. **Registrasi Global di `app/Providers/AppServiceProvider.php`**:
   - Seluruh 7 kebijakan dipetakan ke model Eloquent masing-masing menggunakan `Gate::policy()`.
2. **Penegakan di `ComplaintController.php`**:
   - `show`: Memeriksa `Gate::forUser($user)->allows('view', $complaint)`. Tiket anonim dapat diakses publik, tiket terikat akun dibatasi untuk pelapor dan petugas.
   - `updateStatus`: Memeriksa `Gate::forUser($actor)->allows('update', $complaint)`. Staf A diblokir (403) jika tiket ditugaskan ke Staf B.
   - `assign`: Memeriksa `Gate::forUser($actor)->allows('assign', $complaint)`. Mencegah perebutan tiket antar-petugas.
   - `respond`: Memeriksa `Gate::forUser($actor)->allows('respond', $complaint)`. Hanya petugas yang ditugaskan atau admin yang berhak merespons.
   - `resolve`: Memeriksa `Gate::forUser($actor)->allows('resolve', $complaint)`. Hanya petugas bersangkutan atau admin yang berhak menyelesaikan atau menolak tiket.
3. **Penegakan di `MeterReadingController.php`**:
   - `apiValidate`: Memeriksa `Gate::forUser($validator)->allows('validate', $reading)`. Pelanggan biasa diblokir dengan 403 Forbidden.
4. **Pencegahan Eskalasi Hak Akses di `SmartWaterPortalController.php`**:
   - `enterAdminDirect`: Jika pengguna aktif yang sedang login adalah pelanggan biasa (`!$currentUser->isStaff()`), akses ke `/portal-petugas` ditolak dan dialihkan kembali ke `/user`, mencegah eskalasi sesi menjadi staf secara diam-diam.
5. **Penegakan di `routes/api.php`**:
   - `/profile/update`: Mengintegrasikan `Gate::allows('update', $profile)`.

### C. Hasil Pengujian Otomasi (Testing Suite)

- **Feature Authorization Test Baru (`tests/Feature/PolicyAuthorizationTest.php`)**: **16 of 16 Tests PASSED (46 assertions)**.
  - Membuktikan Petugas A ditolak (403) saat mencoba mengubah status, merespons, menugaskan ulang, atau menyelesaikan tiket milik Petugas B.
  - Membuktikan integritas basis data (**Database Immutability**): status tiket, riwayat, dan `resolved_at` tidak berubah di MySQL saat operasi ditolak.
  - Membuktikan Admin berhak mengelola tiket mana pun.
  - Membuktikan Pelanggan B ditolak (403) saat mencoba mengakses tiket privat Pelanggan A.
  - Membuktikan tiket anonim dapat diakses siapapun tanpa kebocoran data pribadi.
  - Membuktikan pelanggan yang login ditolak mengeksploitasi rute demo `/portal-petugas`.
- **Total PHPUnit Suite (`php artisan test`)**: **62 of 62 Tests PASSED (232 assertions, 100% PASS)**.
- **Uji Validasi Database Nyata MySQL 8.4 (`tests/full_mysql_validation.php`)**: **47 of 47 Skenario PASSED (100% ALL TESTS PASSED)**.

