# DOKUMENTASI MIDDLEWARE & ROLE-BASED ACCESS CONTROL (RBAC)
## SISTEM SMART WATER KOTA PAYAKUMBUH (LARAVEL 12)

Dokumentasi resmi implementasi Middleware, Role-Based Access Control (RBAC), pencegahan IDOR (*Insecure Direct Object Reference*), serta perlindungan kepemilikan data (*Data Ownership*) pada aplikasi Smart Water Kota Payakumbuh.

---

## 1. PENDAHULUAN & PRINSIP KEAMANAN

Implementasi RBAC dan Middleware pada Task 4 dirancang dengan berpegang pada prinsip:
1. **Principle of Least Privilege**: Pengguna hanya diberikan hak akses minimum yang diperlukan untuk menyelesaikan tugasnya.
2. **Defense in Depth (Perlindungan Berlapis)**: Otorisasi ditegakkan pada tingkat:
   - **Route Middleware Level** (`RoleMiddleware` pada `routes/web.php` dan `routes/api.php`).
   - **Form Request Authorization Level** (`authorize()` pada kelas Form Request).
   - **Controller Data Ownership Level** (Verifikasi `user_id` pemilik data sebelum membaca atau mengubah entitas).
3. **Data Isolation & Anti-IDOR**: Pelanggan (*Customer*) hanya dapat mengakses, membaca, dan mengubah data tiket dan laporan meter miliknya sendiri.
4. **Zero Frontend Disruption**: Menjaga seluruh Blade view, JavaScript UI, dan kontrak JSON API tetap kompatibel 100%.

---

## 2. ARSITEKTUR ROLE & WEWENANG PENGGUNA

Sistem membedakan 3 peran utama yang tersimpan di tabel basis data `roles` (MySQL 8.4):

```
+--------------------------------------------------------------+
|                        SUPER ADMIN                           |
|  - Hak akses Command Center SCADA & Monitoring Kota          |
|  - Manajemen data pengguna, teknisi, dan pengaturan sistem   |
|  - Disposisi & penugasan tiket pengaduan kepada teknisi      |
|  - Validasi laporan meteran air AI-OCR warga kota            |
+------------------------------+-------------------------------+
                               | mewarisi hak operasional
+------------------------------v-------------------------------+
|                       PETUGAS / STAFF                        |
|  - Operator teknis lapangan & tim TRC Tirta Sago             |
|  - Pemantauan telemetri SCADA reservoir & jaringan pipa      |
|  - Pembaruan status tiket & pemberian tanggapan teknis       |
|  - Verifikasi pembacaan meteran air AI-OCR                   |
|  * DILARANG mengakses fungsi khusus super administrator      |
+--------------------------------------------------------------+

+--------------------------------------------------------------+
|                      PELANGGAN / CUSTOMER                    |
|  - Mengakses portal layanan mandiri warga (/user)            |
|  - Mengirimkan laporan stand meteran air mandiri AI-OCR      |
|  - Membuat tiket pengaduan kendala air dan kebocoran pipa    |
|  - Membaca tiket dan histori tindak lanjut MILIKNYA SENDIRI  |
|  - Belajar di Akademi Air dan mengikuti Tantangan Hemat Air  |
|  * DILARANG mengakses data pelanggan lain (IDOR Guard)       |
|  * DILARANG mengakses halaman admin atau memvalidasi meter   |
+--------------------------------------------------------------+
```

---

## 3. IMPLEMENTASI MIDDLEWARE (`RoleMiddleware`)

File: `app/Http/Middleware/RoleMiddleware.php`  
Alias: `'role'` (Terdaftar di `bootstrap/app.php`)

### Alur Kerja Middleware:
1. **Resolusi Pengguna Multi-Layer**:
   - Memeriksa sesi aktif Laravel Auth (`$request->user()` / `Auth::user()`).
   - Fallback otomatis ke sesi pengguna framework (`session('swc_user.id')`) dan mengikatnya ke autentikasi aktif.
2. **Pemeriksaan Autentikasi (Belum Login)**:
   - Jika request berformat JSON / API (`$request->expectsJson() || $request->is('api/*')`):  
     Mengembalikan HTTP **401 Unauthorized**:
     ```json
     {
       "ok": false,
       "error": "Unauthenticated: Silakan masuk akun terlebih dahulu."
     }
     ```
   - Jika request Web halaman kedinasan (`admin`, `staff`):  
     Mengalihkan pengguna ke `/admin-login` dengan pesan flash kedinasan.
   - Jika request Web umum:  
     Mengalihkan pengguna ke `/login`.
3. **Pemeriksaan Otorisasi Role (RBAC)**:
   - Jika pengguna terautentikasi tetapi tidak memiliki role yang diizinkan:
     - Untuk request API: mengembalikan HTTP **403 Forbidden**:
       ```json
       {
         "ok": false,
         "error": "Akses ditolak: Anda tidak memiliki hak akses yang memadai."
       }
       ```
     - Untuk request Web: Pelanggan yang mencoba membuka area operasional Admin secara otomatis dialihkan kembali ke portal warga `/user` dengan pesan: *"Akses ditolak: Anda tidak memiliki wewenang administrator."*

---

## 4. PENERAPAN PROTEKSI RUTE (ROUTE PROTECTION)

### A. Rute Web (`routes/web.php`)
- `GET /admin` diproteksi dengan `middleware('role:admin,staff')`.
- Grup rute operasional SCADA diproteksi dengan `middleware('role:admin,staff')`:
  - `/admin-dashboard`
  - `/water-monitoring`
  - `/reservoir-detail`
  - `/distribution-network`
  - `/distribution-detail`
- Rute eskalasi role `/switch-role/{role}` memvalidasi apakah akun di database memiliki hak `isStaff()`. Pelanggan biasa ditolak dan diarahkan ke `/user`.

### B. Rute REST API (`routes/api.php`)
- Grup endpoint operasional diproteksi dengan `Route::middleware('role:admin,staff')`:
  - `POST /api/v1/complaints/{id}/status`
  - `POST /api/v1/complaints/{id}/assign`
  - `POST /api/v1/complaints/{id}/response`
  - `POST /api/v1/complaints/{id}/resolve`
  - `POST /api/v1/meter-reports/validate`

---

## 5. PROTEKSI KEPEMILIKAN DATA (DATA OWNERSHIP & ANTI-IDOR)

### A. Tiket Pengaduan Warga (`ComplaintController@show`)
- Tiket pengaduan yang dibuat oleh pelanggan terdaftar terikat pada `complaint.user_id`.
- **Aturan Kepemilikan**:
  1. Jika pengunjung belum login (Guest) mencoba membuka tiket milik warga lain: ditolak dengan HTTP **401 Unauthorized**.
  2. Jika Pelanggan A mencoba membuka tiket milik Pelanggan B: ditolak dengan HTTP **403 Forbidden**.
  3. Pemilik sah tiket (Pelanggan A) dan Petugas/Admin PDAM: diizinkan membaca detail tiket (HTTP **200 OK**).

### B. Laporan Pembacaan Meter AI-OCR (`MeterReadingController@apiList`)
- **Aturan Isolasi Data**:
  1. **Pengunjung Tanpa Login (Guest)**: Endpoint mengembalikan array kosong `[]` (HTTP 200). Privasi seluruh pelanggan terlindungi dan script frontend `swc-store.js` tidak mengalami error.
  2. **Pelanggan (Customer)**: Query database otomatis difilter dengan klausa `WHERE user_id = :customerId`. Pelanggan hanya menerima laporannya sendiri.
  3. **Petugas / Admin**: Menampilkan seluruh antrean verifikasi stand meter kota untuk kebutuhan validasi operasional.

### C. Pencegahan Parameter Tampering & Privilege Escalation
- **Registrasi Akun (`/api/v1/auth/register`)**: Parameter `role` pada request payload diabaikan secara mutlak; akun yang didaftarkan selalu mendapatkan role `customer`.
- **Pembaruan Profil (`/api/v1/profile/update`)**: Request payload hanya memutasi kolom kontak (`phone`) dan alamat (`address`) pada profil pelanggan. Parameter `role`, `is_admin`, saldo (`wallet_balance`), dan poin XP diisolasi dari mutasi massal.

---

## 6. HASIL PENGUJIAN OTOMATIS (TEST SUITE)

### A. Feature Security Test ([tests/Feature/RbacSecurityTest.php](file:///c:/xampp/htdocs/smart-water/tests/Feature/RbacSecurityTest.php))
Dibuat 13 test case komprehensif menguji seluruh vektor akses dan otorisasi:
1. `test_admin_can_access_administrative_endpoints` — **PASS**
2. `test_staff_can_access_operational_endpoints` — **PASS**
3. `test_customer_cannot_access_admin_web_pages` — **PASS**
4. `test_guest_cannot_access_admin_web_pages` — **PASS**
5. `test_customer_cannot_validate_meter_readings` — **PASS**
6. `test_customer_cannot_assign_complaint_ticket` — **PASS**
7. `test_customer_cannot_resolve_complaint_ticket` — **PASS**
8. `test_idor_prevented_customer_cannot_view_other_customer_complaint` — **PASS**
9. `test_idor_prevented_guest_receives_empty_meter_reports` — **PASS**
10. `test_idor_prevented_customer_only_sees_own_meter_reports` — **PASS**
11. `test_privilege_escalation_prevented_on_profile_update` — **PASS**
12. `test_registration_always_assigns_customer_role` — **PASS**
13. `test_customer_cannot_switch_role_to_admin` — **PASS**

### B. Total Test Suite Laravel (`php artisan test`)
- Total Tests: **46 tests** (Unit & Feature)
- Total Assertions: **186 assertions**
- Status: **100% ALL TESTS PASSED**

### C. Validasi Nyata Basis Data MySQL 8.4 (`php tests/full_mysql_validation.php`)
- Total Skenario: **47 skenario**
- Status: **100% ALL TESTS PASSED (0 Failures)**
