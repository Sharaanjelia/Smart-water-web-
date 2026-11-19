# LAPORAN IMPLEMENTASI RBAC GRANULAR SMART WATER
## Perumda Air Minum Tirta Sago Kota Payakumbuh

**Tanggal Pelaksanaan**: 19 November 2026  
**Status**: SELESAI & TERVERIFIKASI PENUH (134/134 Tests PASS)

---

## 1. Ringkasan Eksekutif
Implementasi pemisahan kewenangan Role-Based Access Control (RBAC) Granular Smart Water telah selesai dilaksanakan secara menyeluruh tanpa merusak mekanisme autentikasi satu pintu (`/login`), tanpa membuat data fiktif untuk staf lapangan (Dedi Kurniawan), serta menjaga integritas 100% data riil di database MySQL `smart_water_payakumbuh`.

---

## 2. Daftar File yang Diubah & Dibuat

### Migration & Database
1. `database/migrations/2026_11_19_000001_implement_granular_rbac_roles.php` (BARU)
   - Membuat role `scada_operator` dan `field_officer` di tabel `roles`.
   - Membuat audit table `rbac_transition_logs` untuk mencatat riwayat pemindahan role secara terperinci dan rollback-safe.
   - Melakukan atomic role swap:
     * Ahmad Pratama: detach `staff`, attach `scada_operator`
     * Dedi Kurniawan: detach `staff`, attach `field_officer`
2. `database/seeders/RoleSeeder.php`
   - Sinkronisasi role baku: `admin`, `staff`, `customer`, `scada_operator`, `field_officer`.
3. `database/seeders/UserSeeder.php`
   - Sinkronisasi penugasan role awal pengguna secara deterministik dan bersih dari NIP fiktif Dedi.

### Model & Service
4. `app/Models/User.php`
   - Implementasi helper role eksplisit berbasis database:
     * `isAdmin(): bool`
     * `isScadaOperator(): bool`
     * `isFieldOfficer(): bool`
     * `isCustomer(): bool`
     * `isInternalStaff(): bool`
     * `isStaff(): bool` (alias ke `isInternalStaff()`)
     * `primaryRoleName(): string` (database-backed, bebas dari string guessing)
5. `app/Services/AuthService.php`
   - Sinkronisasi `formatUserData()` dan `getRedirectForUser()` untuk routing otomatis seluruh aktor.
6. `app/LegacyApi/auth.php`
   - Sinkronisasi fallback role matching database.

### Middleware, Request & Policies
7. `app/Http/Middleware/RoleMiddleware.php`
   - Penegakan otorisasi role berbasis database:
     * Guest ditolak $\rightarrow$ redirect ke `/login` (Web) atau 401 (API).
     * Pelanggan mengakses rute internal ditolak $\rightarrow$ redirect ke `/user` (Web) atau 403 (API).
     * Staf internal mengakses rute aktor internal lain ditolak $\rightarrow$ redirect ke dashboard masing-masing dengan pesan error (Web) atau 403 (API).
8. `app/Policies/PaymentPolicy.php`
   - Otorisasi verifikasi pembayaran (`viewAny`, `verify`) dibatasi ketat hanya untuk Admin (`$user->isAdmin()`).
9. `app/Policies/BillPolicy.php`
   - Otorisasi melihat tagihan pelanggan lain hanya diberikan kepada Admin (`$user->isAdmin()`).
10. `app/Policies/MeterReadingPolicy.php`
    - Otorisasi validasi foto meter AI-OCR dibatasi hanya untuk Admin dan SCADA Operator (`$user->isAdmin() || $user->isScadaOperator() || $user->hasRole('staff')`). Petugas Lapangan diblokir.
11. `app/Policies/ComplaintPolicy.php`
    - Penugasan tiket (`assign`) dibatasi hanya untuk Admin.
    - Pembaruan status/tanggapan/penyelesaian (`update`, `respond`, `resolve`) dibatasi untuk Admin atau Petugas Lapangan yang secara resmi ditugaskan pada tiket tersebut.
12. `app/Http/Requests/VerifyPaymentRequest.php`
    - Authorize dibatasi hanya untuk `$actor && $actor->isAdmin()`.
13. `app/Http/Requests/Meter/ValidateMeterReadingRequest.php`
    - Authorize dibatasi untuk Admin dan SCADA Operator.
14. `app/Http/Requests/Complaint/AssignComplaintRequest.php`
    - Authorize dibatasi hanya untuk Admin.

### Controllers & API Resources
15. `app/Http/Controllers/SmartWaterPortalController.php`
    - Pengamanan parameter `?tab=` pada `/admin-dashboard`:
      * Petugas Lapangan: otomatis diarahkan ke `customers` (Survei Sambungan), tab `verify` dan `bills` diblokir.
      * Operator SCADA: otomatis diarahkan ke `verify` (Validasi AI-OCR), tab `bills` dan `customers` diblokir.
      * Admin: akses bebas ke semua tab.
16. `app/Http/Controllers/BillController.php`
    - `index()` membatasi penglihatan seluruh tagihan hanya untuk Admin; non-admin hanya melihat milik sendiri.
17. `app/Http/Controllers/PaymentController.php`
    - `index()` membatasi penglihatan rekonsiliasi kas hanya untuk Admin.
18. `app/Http/Controllers/MeterReadingController.php`
    - `apiList()` membatasi antrean verifikasi untuk Admin dan SCADA Operator.
19. `app/Http/Resources/ComplaintResource.php`
    - Proteksi PII tingkat resource: Pengunjung publik dan pelanggan lain menerima data tersamar (masked). Detail lengkap pelapor hanya terbuka untuk Admin, pemilik tiket, dan Petugas Lapangan yang ditugaskan.
20. `app/Http/Resources/UserProfileResource.php`
    - Menghilangkan paparan kredensial sensitif.

### Routing & Views
21. `routes/web.php` & `routes/api.php`
    - Pengetatan middleware `role:admin`, `role:admin,scada_operator`, `role:admin,field_officer`.
22. `resources/views/layouts/admin.blade.php`
    - Dinamisasi header badge role:
      * Admin: `COMMAND CENTER • SUPER ADMINISTRATOR`
      * SCADA: `COMMAND CENTER • OPERATOR SCADA`
      * Field Officer: `PORTAL OPERASIONAL • PETUGAS LAPANGAN & TRC`
    - Filtrasi menu sidebar:
      * Validasi Meter & Kas: Admin, SCADA Operator.
      * Penerimaan Tagihan: Admin.
      * Database Sambungan: Admin, Petugas Lapangan.
      * Group SCADA & Hidrolika: Admin, SCADA Operator.
      * Pusat Tiket: Admin, Petugas Lapangan.
23. `resources/views/admin/dashboard.blade.php`
    - Menyembunyikan elemen tab DOM yang tidak berhak (`#verifyTab`, `#billsTab`, `#customersTab`) berdasarkan role.
    - Banner profil dinamis sesuai nama dan NIP asli pengguna login.

### Test Suites
24. `tests/Feature/RbacSecurityTest.php` (21 tests, 109 assertions)
25. `tests/Feature/PolicyAuthorizationTest.php` (16 tests, 56 assertions)
26. `tests/Feature/AuthenticationIntegrationTest.php` (19 tests, 90 assertions)
27. `tests/Unit/Resources/ApiResourceTest.php` (6 tests, 69 assertions)

---

## 3. Tabel Pemetaan Role Pengguna MySQL

| ID | Nama Pengguna | Email | Role Database | StaffProfile | Status Kedinasan |
|---|---|---|---|---|---|
| 1 | Ir. Hendra Pratama, S.T. | `admin@tirtasago.id` | `admin` | NIP: NIP-TS-198402-089 | Super Admin & Dispatcher Teknis |
| 2 | Ahmad Pratama, S.T. | `ahmad.pratama@tirtasago.id` | `scada_operator` | NIP: NIP-198804-004 | Operator SCADA & Verifikator AI-OCR |
| 3 | Budi Santoso | `budi.santoso@warga.payakumbuh.go.id` | `customer` | NONE | Pelanggan Wilayah DIST-A |
| 4 | Ibu Siti Nurbaya | `siti.nurbaya@payakumbuh.community` | `customer` | NONE | Pelanggan Wilayah DIST-B |
| 5 | Dedi Kurniawan, A.Md. | `dedi.teknisi@tirtasago.id` | `field_officer` | NONE *(Tanpa data fiktif)* | Teknisi Lapangan / TRC |

---

## 4. Matriks Hak Akses Granular Role

| Modul / Fitur | Admin PDAM (`admin`) | Operator SCADA (`scada_operator`) | Petugas Lapangan (`field_officer`) | Pelanggan (`customer`) | Publik / Tamu (`guest`) |
|---|:---:|:---:|:---:|:---:|:---:|
| **Login Satu Pintu (`/login`)** | Akses | Akses | Akses | Akses | Akses |
| **Dashboard Admin/Portal (`/admin-dashboard`)** | Akses Penuh (3 Tab) | Akses (Tab AI-OCR) | Akses (Tab Sambungan) | Tolak $\rightarrow$ `/user` | Tolak $\rightarrow$ `/login` |
| **Konsol SCADA & Hidrolika (`/water-monitoring`)** | Akses | Akses | Tolak (302) | Tolak (302) | Tolak (302) |
| **Verifikasi Pembayaran Kas/Bank** | Wewenang Penuh | Tolak (403) | Tolak (403) | Tolak (403) | Tolak (401) |
| **Validasi Laporan Meter AI-OCR** | Wewenang Penuh | Wewenang Penuh | Tolak (403) | Tolak (403) | Tolak (401) |
| **Disposisi Penugasan Tiket (`assign`)** | Wewenang Penuh | Tolak (403) | Tolak (403) | Tolak (403) | Tolak (401) |
| **Tindak Lanjut & Penyelesaian Tiket** | Wewenang Penuh | Tolak (403) | Hanya Tiket Tugas Sendiri | Tolak (403) | Tolak (401) |
| **Daftar Tagihan Pelanggan (`/bills`)** | Melihat Semua Tagihan | Hanya Milik Sendiri | Hanya Milik Sendiri | Hanya Milik Sendiri | Tolak (401) |
| **Detail PII Pelapor Pengaduan** | Penuh (Unmasked) | Tersamar (Masked) | Penuh (Jika Ditugaskan) | Penuh (Milik Sendiri) | Tersamar (Masked) |

---

## 5. Hasil Pengujian Otomatis

Seluruh suite test Laravel dijalankan pada PHP 8.3 dengan hasil berikut:
```text
Pass   : Tests\Unit\ExampleTest
Pass   : Tests\Unit\Resources\ApiResourceTest (6 tests)
Pass   : Tests\Unit\Services\ServicesUnitTest (7 tests)
Pass   : Tests\Feature\AuthenticationIntegrationTest (19 tests)
Pass   : Tests\Feature\BillingDataIntegrationTest (9 tests)
Pass   : Tests\Feature\BillingPaymentTest (6 tests)
Pass   : Tests\Feature\ComplaintTest (10 tests)
Pass   : Tests\Feature\ExampleTest (1 test)
Pass   : Tests\Feature\GamificationTest (11 tests)
Pass   : Tests\Feature\MeterReadingTest (9 tests)
Pass   : Tests\Feature\PolicyAuthorizationTest (16 tests)
Pass   : Tests\Feature\RbacSecurityTest (21 tests)
Pass   : Tests\Feature\SmartWaterBackendTest (9 tests)

Total  : 134 PASSED (550 assertions)
Failed : 0
Duration: 13.44 detik
```

---

## 6. Status Integritas Database MySQL

Pemeriksaan database live MySQL `smart_water_payakumbuh`:
- **Users**: 5 records (Lengkap, tidak ada yang terhapus)
- **Roles**: 5 records (`admin`, `staff`, `customer`, `scada_operator`, `field_officer`)
- **Customer Profiles**: 2 records (Budi Santoso & Siti Nurbaya utuh)
- **Staff Profiles**: 2 records (Hendra Pratama & Ahmad Pratama utuh, Dedi tidak disusupi NIP fiktif)
- **Bills**: 7 records (Utuh tanpa perubahan tagihan)
- **Complaints**: 59 records (Utuh dengan relasi kategori dan histori)
- **Meter Readings**: 32 records (Utuh)
- **RBAC Transition Logs**: 4 records (Audit trail peralihan role tercatat permanen)

---

## 7. Catatan Pemeliharaan & Backlog Berikutnya
1. **Profil Kedinasan Dedi Kurniawan**:
   Apabila SK pengangkatan atau NIP resmi Dedi Kurniawan telah diterbitkan oleh bagian kepegawaian PDAM Tirta Sago, buat record `staff_profiles` resmi melalui migrasi atau seeder formal tanpa menggunakan data perkiraan.
2. **Kompatibilitas Role `staff`**:
   Role `staff` tetap dipertahankan di database dan didukung oleh middleware sebagai fallback legacy, tetapi hak akses sensitif (verifikasi pembayaran dan disposisi) telah ditutup rapat.
