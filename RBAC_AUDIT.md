# AUDIT MIDDLEWARE & ROLE-BASED ACCESS CONTROL (RBAC)
## SISTEM SMART WATER KOTA PAYAKUMBUH (LARAVEL 12)

**Tanggal Audit**: 17 November 2026  
**Fokus**: Keamanan Akses, Kepemilikan Data (*Data Ownership*), Pencegahan IDOR, dan Kontrol Privilege Escalation.  
**Lingkungan**: Lokal (MySQL 8.4 Laragon, Laravel 12)

---

## 1. INVENTARISASI MIDDLEWARE SAAT INI

Berdasarkan pemeriksaan pada `app/Http/Middleware/` dan `bootstrap/app.php`:

| Middleware | Lokasi File | Alias | Tanggung Jawab & Implementasi Saat Ini |
|---|---|---|---|
| `RoleMiddleware` | `app/Http/Middleware/RoleMiddleware.php` | `'role'` | Memeriksa apakah pengguna telah terautentikasi dan memiliki salah satu role yang diminta (`hasRole($roles)`). Jika belum login, dialihkan ke `/admin-login` atau `/login`. Jika role tidak cocok, melempar JSON 403 (bila `expectsJson`) atau `abort(403)`. |
| `EncryptCookies` | Laravel Core | API group | Enkripsi cookie sesi. |
| `AddQueuedCookiesToResponse` | Laravel Core | API group | Menambahkan antrean cookie ke HTTP response. |
| `StartSession` | Laravel Core | API group | Memulai session Laravel pada rute API v1 (`bootstrap/app.php:22`). |
| `ValidateCsrfTokens` | Laravel Core | Global | Proteksi CSRF, dikecualikan untuk `api/*` dan `*/api/*`. |

### Temuan pada `RoleMiddleware`:
1. **Penanganan Unauthenticated pada API**: Jika request API (`/api/v1/...`) tidak mengirimkan sesi login, `RoleMiddleware` saat ini melakukan `redirect(url('/login'))` bukannya mengembalikan JSON response dengan status `401 Unauthorized`.
2. **Fallback Sesi Legacy**: Jika sesi pengguna tersimpan di `$_SESSION['swc_user']` atau `session('swc_user')`, namun `Auth::user()` belum diisi pada request tersebut, `RoleMiddleware` menganggap user adalah guest. Perlu sinkronisasi resolver pengguna.
3. **Penyelarasan Pengalihan Role**: Jika seorang `customer` mencoba membuka area operasional web admin (`/admin`), sistem sebaiknya mengarahkannya kembali ke portal warga `/user` dengan flash message peringatan (sesuai perilaku `ensureAdminRole`).

---

## 2. STRUKTUR ROLE & PERMISSION DALAM BASIS DATA

Sistem Smart Water menggunakan tabel basis data MySQL 8.4:
- Tabel `roles`: `id`, `name`, `display_name`, `description`.
- Tabel `role_user`: `id`, `user_id`, `role_id` (Many-to-Many Pivot).
- Tabel `customer_profiles`: Profil sambungan pelanggan PDAM (`meter_number`, `customer_id`, `tariff_category`, `zone`, saldo & poin).
- Tabel `staff_profiles`: Profil staf kedinasan (`nip`, `department`, `position`, `phone_dinas`).

### Daftar Role Aktif:

| Nama Role | Display Name | Ruang Lingkup Hak Akses (*Authority*) | Batasan Ketat (*Restrictions*) |
|---|---|---|---|
| **`admin`** | Super Administrator | Akses penuh ke Command Center SCADA, memantau seluruh pengaduan kota, menugaskan teknisi lapangan, memvalidasi laporan meter, mengelola kurikulum akademi dan tantangan, serta melihat analitik menyeluruh. | Tetap tunduk pada audit logging sistem. |
| **`staff`** | Petugas & Operator SCADA | Operator teknis TRC, memproses pengaduan lapangan (update status, tanggapan teknis, penyelesaian tiket), memvalidasi laporan stand meter AI-OCR warga, dan memantau status telemetri reservoir/distribusi. | **DILARANG** mengakses fungsi khusus Admin (konfigurasi sistem, eskalasi hak akses pengguna, penghapusan data master). |
| **`customer`** | Pelanggan Warga | Mengakses portal warga (`/user`), melihat & memperbarui profil mandiri, membuat tiket pengaduan, melihat riwayat tiket miliknya, mengirim laporan stand meter AI-OCR miliknya, mengikuti Akademi Air dan Tantangan Hemat Air. | **DILARANG** mengakses data pelanggan lain (IDOR Protection), dilarang membuka portal admin, dan dilarang memvalidasi laporan meter atau mengubah status tiket orang lain. |

*Catatan Arsitektur*: Sistem saat ini belum membutuhkan tabel `permissions` terpisah karena kewenangan role terdefinisi secara hierarkis dan jelas (`admin` $\supset$ `staff` $\supset$ `customer`).

---

## 3. MATRIKS HAK AKSES ENDPOINT (ENDPOINT PERMISSION MATRIX)

### A. Rute Web Portal (`routes/web.php`)

| Endpoint Path | HTTP Method | Target Controller / Action | Hak Akses yang Diizinkan | Status Proteksi Saat Ini | Rekomendasi Perbaikan |
|---|---|---|---|---|---|
| `/` & `/beranda` | GET | `LegacyPageController@show('beranda')` | Public (Semua) | Terbuka (Aman) | Pertahankan |
| `/pusat-layanan` | GET | `LegacyPageController@show('pusat-layanan')` | Public / Customer | Terbuka (Aman) | Pertahankan |
| `/akademi-air` | GET | `LegacyPageController@show('akademi-air')` | Public / Customer | Terbuka (Aman) | Pertahankan |
| `/tantangan` | GET | `LegacyPageController@show('tantangan')` | Public / Customer | Terbuka (Aman) | Pertahankan |
| `/tentang` | GET | `LegacyPageController@show('tentang')` | Public (Semua) | Terbuka (Aman) | Pertahankan |
| `/login` & `/register` | GET/POST | `SmartWaterPortalController` | Guest Only | Terbuka / Redirect jika login | Pertahankan |
| `/admin-login` | GET/POST | `SmartWaterPortalController` | Guest / Petugas | Terbuka untuk login dinas | Pertahankan |
| `/user` | GET | `LegacyPageController@show('user')` | Public / Customer (Ada Guest View) | Terbuka dengan fallback aman | Pertahankan |
| `/admin` | GET | `LegacyPageController@show('admin')` | **Admin, Staff** | **BELUM DIPROTEKSI ROUTE MIDDLEWARE** | **Tambahkan `middleware('role:admin,staff')`** |
| `/admin-dashboard` | GET | `SmartWaterPortalController@adminDashboard` | **Admin, Staff** | Diproteksi controller method | Tambahkan route middleware deklaratif |
| `/water-monitoring` | GET | `SmartWaterPortalController@waterMonitoring` | **Admin, Staff** | Diproteksi controller method | Tambahkan route middleware deklaratif |
| `/reservoir-detail` | GET | `SmartWaterPortalController@reservoirDetail` | **Admin, Staff** | Diproteksi controller method | Tambahkan route middleware deklaratif |
| `/distribution-network`| GET | `SmartWaterPortalController@distributionNetwork` | **Admin, Staff** | Diproteksi controller method | Tambahkan route middleware deklaratif |
| `/distribution-detail` | GET | `SmartWaterPortalController@distributionDetail` | **Admin, Staff** | Diproteksi controller method | Tambahkan route middleware deklaratif |
| `/switch-role/{role}` | GET | `SmartWaterPortalController@switchRole` | **Staff, Admin** | Terproteksi: Customer dilarang eskalasi | Pertahankan |
| `/portal-petugas` | GET | `SmartWaterPortalController@enterAdminDirect` | Demo Akses Kedinasan | Terbuka (Mode Demo Lokal) | Batasi & dokumentasikan |
| `/report-meter` dkk. | GET | `SmartWaterPortalController` (11 rute pelanggan) | **Customer** | Diproteksi controller (`ensureCustomerRole`) | Pertahankan |

### B. Rute REST API v1 (`routes/api.php`)

| Endpoint Path | Method | Controller / Action | Hak Akses Diizinkan | Status Proteksi Saat Ini | Rekomendasi Tindakan |
|---|---|---|---|---|---|
| `/api/v1/auth/login` | POST | Inline / `AuthService` | Public | Terbuka | Pertahankan |
| `/api/v1/auth/register` | POST | Inline / `AuthService` | Public | Terbuka (Otomatis role customer) | Pertahankan |
| `/api/v1/auth/logout` | POST | Inline / `AuthService` | Authenticated | Terbuka | Pasang auth middleware |
| `/api/v1/auth/me` | GET | Inline / `UserProfileResource` | Authenticated | Terproteksi manual (401 jika null) | Pasang middleware auth |
| `/api/v1/profile` | GET | Inline / `UserProfileResource` | Authenticated | Terproteksi manual (401 jika null) | Pasang middleware auth |
| `/api/v1/profile/update` | POST | Inline / `UpdateProfileRequest` | Authenticated | Terproteksi manual (401 jika null) | Perketat parameter tampering |
| `/api/v1/complaints` | GET | `ComplaintController@index` | Public (Koleksi Kota / `mine`) | Terbuka | Pertahankan untuk peta publik |
| `/api/v1/complaints` | POST | `ComplaintController@store` | Public / Customer | Terbuka (Bisa guest / warga) | Pertahankan |
| `/api/v1/complaints/{id}` | GET | `ComplaintController@show` | **Owner, Staff, Admin** | **CEK KEPEMILIKAN KURANG KETAT JIKA GUEST** | **Perbaiki: Jika ada `user_id`, wajib login (401) & pemilik (403)** |
| `/api/v1/complaints/{id}/status` | POST | `ComplaintController@updateStatus` | **Staff, Admin** | Terproteksi Form Request (403) | Tambahkan route middleware |
| `/api/v1/complaints/{id}/assign` | POST | `ComplaintController@assign` | **Staff, Admin** | Terproteksi Form Request (403) | Tambahkan route middleware |
| `/api/v1/complaints/{id}/response` | POST | `ComplaintController@respond` | **Staff, Admin** | Terproteksi Form Request (403) | Tambahkan route middleware |
| `/api/v1/complaints/{id}/resolve` | POST | `ComplaintController@resolve` | **Staff, Admin** | Terproteksi Form Request (403) | Tambahkan route middleware |
| `/api/v1/meter-reports` | GET | `MeterReadingController@apiList` | **Staff, Admin, Owner** | **KEBOCORAN DATA: GUEST MENERIMA SEMUA DATA** | **Perbaiki: Guest menerima array kosong/401; Customer hanya miliknya** |
| `/api/v1/meter-reports` | POST | `MeterReadingController@apiStore` | Customer / Warga | Terbuka | Kunci nomor meter ke profil login |
| `/api/v1/meter-reports/validate` | POST | `MeterReadingController@apiValidate` | **Staff, Admin** | Terproteksi Form Request (403) | Tambahkan route middleware |
| `/api/v1/academy` | GET | `AcademyController@index` | Public | Terbuka | Pertahankan |
| `/api/v1/academy/complete-lesson`| POST | `AcademyController@completeLesson` | Authenticated Customer | Terbuka (fallback session) | Pertahankan |
| `/api/v1/academy/complete-quiz` | POST | `AcademyController@completeQuiz` | Authenticated Customer | Terbuka (fallback session) | Pertahankan |
| `/api/v1/challenges` | GET | `ChallengeController@index` | Public | Terbuka | Pertahankan |
| `/api/v1/challenges/enroll` | POST | `ChallengeController@enroll` | Authenticated Customer | Terbuka (fallback session) | Pertahankan |
| `/api/v1/challenges/complete-mission`| POST | `ChallengeController@completeMission` | Authenticated Customer | Terbuka (fallback session) | Pertahankan |
| `/api/v1/notifications` | GET | Inline / `NotificationResource` | Authenticated Customer | Terproteksi manual | Pertahankan |
| `/api/v1/notifications/mark-all-read`| POST | Inline | Authenticated Customer | Terproteksi manual | Pertahankan |

---

## 4. TEMUAN KERENTANAN & ANALISIS RISIKO (VULNERABILITY ANALYSIS)

### A. Potensi IDOR (Insecure Direct Object Reference)
1. **Tiket Pengaduan Warga (`/api/v1/complaints/{id}`)**:
   - *Kondisi Lama*: Pengecekan saat ini:
     ```php
     if ($complaint->user_id && $user && !$user->isStaff() && $complaint->user_id !== $user->id) {
         return response()->json(['ok' => false, 'error' => '...'], 403);
     }
     ```
   - *Kelemahan*: Jika pemohon adalah Guest (`$user == null`), blok `if` tidak terpenuhi! Akibatnya, siapapun yang mengetahui atau menebak nomor tiket orang lain dapat membacanya tanpa login.
   - *Solusi*: Jika `$complaint->user_id` tidak null dan pemohon belum login, kembalikan `401 Unauthorized`. Jika pemohon bukan staf dan bukan pemilik tiket, kembalikan `403 Forbidden`.

2. **Daftar Laporan Meter Mandiri (`/api/v1/meter-reports`)**:
   - *Kondisi Lama*:
     ```php
     if ($user && !$user->isStaff()) {
         $userId = $user->id;
     }
     $reports = $this->meterReadingService->getReadings($userId);
     ```
   - *Kelemahan*: Jika `$user` adalah null (guest), `$userId` tetap null. Service `getReadings(null)` memuat seluruh laporan meteran seluruh warga kota. Script frontend `swc-store.js` yang memanggil endpoint ini saat startup dapat mengunduh seluruh data meter warga lain ke client local storage.
   - *Solusi*: Jika `$user` adalah null, kembalikan array laporan kosong (`['ok' => true, 'reports' => []]`).

3. **Injeksi Nomor Meter Orang Lain pada Lapor Meter Mandiri**:
   - *Kondisi*: Seorang pelanggan yang login bisa saja mengirimkan request POST dengan payload `meter => '02000'` (nomor meter tetangga).
   - *Solusi*: Pada `MeterReadingService` / `MeterReadingController`, jika pengguna terotentikasi sebagai customer, otomatis kunci `user_id` dan prioritaskan nomor meter dari `customerProfile->meter_number`.

### B. Potensi Privilege Escalation & Parameter Tampering
1. **Manipulasi Role pada Registrasi Akun**:
   - Telah diaudit pada `RegisterCustomerRequest`: rules hanya memvalidasi `name`, `email`, `phone`, `password`. Method `AuthService::registerCustomer()` secara *hardcoded* menetapkan role `customer`. Tidak ada celah penetapan role admin via registrasi.
2. **Manipulasi Role pada Update Profil**:
   - Telah diaudit pada `UpdateProfileRequest`: rules hanya menerima `phone`, `address`, `name`. Endpoint `/profile/update` hanya memutasi kolom `phone` dan `address` pada tabel `customer_profiles`. Parameter `role`, `is_admin`, `points`, dan `wallet_balance` tidak dapat dimutasi.
3. **Eskalasi Role via `/switch-role/admin`**:
   - Telah diverifikasi di `SmartWaterPortalController@switchRole`: customer yang mencoba memanggil endpoint ini langsung ditolak dan dialihkan kembali ke `/user` (didukung oleh uji otomatis MySQL Section 2.11).

### C. Akses Tanpa Autentikasi ke Halaman Web Admin Legacy (`/admin`)
- Rute `/admin` pada `routes/web.php` merender view `pages.admin` tanpa deklarasi middleware `role:admin,staff`. Pengguna publik atau customer yang langsung mengetik URL `/admin` di browser dapat membuka Command Center SCADA.
- *Solusi*: Berikan proteksi middleware `role:admin,staff` pada rute `/admin` dan `/pages/php-admin`.

---

## 5. RENCANA PERBAIKAN & PENYEMPURNAAN

1. **Sempurnakan `RoleMiddleware`**:
   - Dukung pendeteksian otomatis request API/JSON untuk mengembalikan HTTP 401 (Unauthenticated) dan HTTP 403 (Forbidden).
   - Sinkronisasi resolver `$user` dengan sesi aktif `session('swc_user')` jika sesi Laravel Auth belum terikat.
   - Alihkan pelanggan (`customer`) yang membuka URL web admin ke portal `/user` dengan pesan flash yang ramah.
2. **Pasang Route Middleware di `routes/web.php`**:
   - Lindungi `/admin` dengan middleware `role:admin,staff`.
   - Pastikan rute portal operasional SCADA (`/admin-dashboard`, `/water-monitoring`, dll.) memiliki perlindungan ganda (route middleware + controller guard).
3. **Pasang Route Middleware di `routes/api.php`**:
   - Grupkan endpoint operasional pengaduan dan verifikasi meter di bawah middleware `role:admin,staff`.
   - Pastikan endpoint yang membutuhkan login terlindungi secara tepat.
4. **Implementasikan Data Ownership Guard pada Controller**:
   - Perbaiki `ComplaintController@show` untuk memblokir guest dari tiket pengguna lain.
   - Perbaiki `MeterReadingController@apiList` untuk mengisolasi data laporan meter dari pengunjung tanpa login.
   - Kunci kepemilikan meteran pada `MeterReadingService::createReading`.
5. **Buat Automated Security & RBAC Test Suite**:
   - Buat `tests/Feature/RbacSecurityTest.php` untuk menguji:
     - Akses admin ke endpoint petugas/admin (HTTP 200).
     - Larangan customer mengakses endpoint status/assign/resolve/validate (HTTP 403).
     - Larangan customer mengakses web `/admin` (Redirect ke `/user`).
     - Perlindungan IDOR pengaduan (Customer A dilarang mengakses tiket Customer B).
     - Perlindungan IDOR laporan meter (Laporan meter Customer A tidak bocor ke Customer B atau Guest).
     - Penolakan akses tanpa autentikasi (HTTP 401).
     - Penolakan manipulasi parameter role pada update profil.
     - Penolakan manipulasi parameter role pada pendaftaran akun.
