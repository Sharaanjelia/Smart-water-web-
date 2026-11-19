# RANCANGAN ARSITEKTUR PEMISAHAN ROLE RBAC SMART WATER (TASK 10A.3)
## PERUMDA AIR MINUM TIRTA SAGO KOTA PAYAKUMBUH

**Dokumen**: Desain & Audit Pemisahan Hak Akses Internal  
**Status**: DRAFT RANCANGAN (AUDIT & DESAIN SAJA — TANPA PERUBAHAN KODE / DATABASE)  
**Tanggal**: 19 November 2026  
**Aktor Target**:
1. **Admin PDAM** (`admin`)
2. **Petugas Lapangan** (`field_officer`)
3. **Operator PDAM / SCADA** (`scada_operator`)

---

## 1. KONDISI RBAC SAAT INI (CURRENT STATE AUDIT)

### 1.1 Struktur Tabel Basis Data Saat Ini
Pemeriksaan skema basis data aktif MySQL 8.4 (`smart_water_payakumbuh`) menunjukkan:

- **Tabel `roles`**:
  Hanya terdapat 3 entri role:
  1. `id = 1`, `name = 'admin'`, `display_name = 'Super Administrator'`
  2. `id = 2`, `name = 'staff'`, `display_name = 'Petugas & Operator SCADA'`
  3. `id = 3`, `name = 'customer'`, `display_name = 'Pelanggan Warga'`
- **Tabel `role_user`**:
  Tabel pivot penghubung `users.id` ke `roles.id`.
- **Tabel `staff_profiles`**:
  Menyimpan metadata kedinasan pegawai: `user_id`, `nip`, `department`, `position`, `phone_dinas`.

### 1.2 Temuan Celah Keamanan & Overprivileged Permissions (Kondisi Eksisting)
Berdasarkan hasil audit mendalam terhadap kode sumber:

1. **Role Ganda / Monolitik (`staff`)**:
   - Seluruh pegawai internal non-admin saat ini dimasukkan ke dalam satu role generik: `staff`.
   - Pegawai teknis instalasi/lapangan (TRC) seperti **Dedi Kurniawan** dan operator monitoring pengolahan air (SCADA) seperti **Ahmad Pratama** memiliki peran teknis yang **sama persis** di basis data (`roles.name = 'staff'`).
   - Pembedaan saat ini hanya bersifat tekstual pada kolom `staff_profiles.position` ("Teknisi TRC" vs "Operator SCADA"), yang tidak diproses oleh layer otorisasi Laravel.
2. **Kebocoran Hak Verifikasi Pembayaran (Payment Verification Leak)**:
   - Pada `app/Policies/PaymentPolicy.php` (baris 41–44):
     ```php
     public function verify(User $user, Payment $payment): bool
     {
         return $user->isAdmin() || $user->isStaff();
     }
     ```
   - Rute web pada `routes/web.php` (baris 68):
     ```php
     Route::post('/admin/payments/{payment}/verify', [PaymentController::class, 'verify'])
         ->name('admin.payments.verify')
         ->middleware('role:admin,staff');
     ```
   - **Dampak Kritis**: Petugas lapangan teknis secara teknis memiliki wewenang untuk menyetujui (`VERIFIED`) atau menolak (`REJECTED`) bukti transfer pembayaran air pelanggan, yang seharusnya menjadi kewenangan eksklusif Bagian Keuangan / Administrator PDAM.
3. **Penyatuan Akses SCADA Reservoir & Jaringan Transmisi**:
   - Rute telemetri IoT dan SCADA di `routes/web.php` (baris 78–84) dikelompokkan dalam satu grup:
     ```php
     Route::middleware('role:admin,staff')->group(function () {
         Route::get('/admin-dashboard', ...);
         Route::get('/water-monitoring', ...);
         Route::get('/reservoir-detail', ...);
         Route::get('/distribution-network', ...);
         Route::get('/distribution-detail', ...);
     });
     ```
   - Petugas lapangan yang bertugas di jalan raya dapat membuka dan menginspeksi kontrol telemetri sensor debit/tekanan reservoir instalasi pengolahan air tanpa batas pemisah.
4. **Validasi Stand Meter Mandiri**:
   - Pada `app/Policies/MeterReadingPolicy.php` (baris 50–53):
     ```php
     public function validate(User $user, MeterReading $meterReading): bool
     {
         return $user->isStaff();
     }
     ```
   - Validasi AI-OCR pembacaan meter warga dapat dilakukan oleh siapapun yang berstatus `staff`, tanpa membedakan apakah petugas tersebut memiliki surat tugas verifikasi pembacaan meter atau hanya staf operasional umum.

---

## 2. DAFTAR ROUTE DAN ENDPOINT BERDASARKAN ROLE

Berikut adalah pemetaan rinci seluruh endpoint Smart Water ke dalam 3 role internal target beserta role pelanggan dan publik:

### 2.1 Route Antarmuka Web (`routes/web.php`)

| URI / Nama Route | HTTP Method | Middleware Saat Ini | Middleware Target Usulan | Aktor yang Berhak Mengakses |
| :--- | :---: | :--- | :--- | :--- |
| `/login` (`login`) | GET, POST | Tamu / Publik | Tamu / Publik | Publik, Pelanggan, Calon Pelanggan |
| `/admin-login` (`admin-login`) | GET, POST | Tamu / Dinas | Tamu / Dinas | `admin`, `scada_operator`, `field_officer` |
| `/logout` (`logout`) | ANY | Publik / Auth | `auth` | Seluruh Pengguna Login |
| `/portal-petugas` | GET | Direct Resolver | Direct Resolver | `admin`, `scada_operator`, `field_officer` |
| `/report-meter` (`report-meter`) | GET | `ensureCustomerRole` | `role:customer` | `customer` (Pelanggan Mandiri) |
| `/billing` (`billing`) | GET | `ensureCustomerRole` | `role:customer` | `customer` |
| `/billing/{bill}` (`bill.detail`) | GET | `ensureCustomerRole` | `role:customer` | `customer` (Milik Sendiri) |
| `/admin-dashboard` | GET | `role:admin,staff` | `role:admin,scada_operator,field_officer` | **Semua Internal** (Tampilan adaptif per role) |
| `/water-monitoring` | GET | `role:admin,staff` | `role:admin,scada_operator` | `admin`, `scada_operator` *(Field Officer Ditolak)* |
| `/reservoir-detail` | GET | `role:admin,staff` | `role:admin,scada_operator` | `admin`, `scada_operator` *(Field Officer Ditolak)* |
| `/distribution-network` | GET | `role:admin,staff` | `role:admin,scada_operator` | `admin`, `scada_operator` *(Field Officer Ditolak)* |
| `/distribution-detail` | GET | `role:admin,staff` | `role:admin,scada_operator` | `admin`, `scada_operator` *(Field Officer Ditolak)* |
| `/admin/payments/{payment}/verify` | POST | `role:admin,staff` | `role:admin` | **HANYA `admin`** *(Field & SCADA Ditolak)* |
| `/field/tasks` *(Rencana Rute Baru)* | GET | *(Belum ada)* | `role:admin,field_officer` | `admin`, `field_officer` (Daftar Penugasan TRC) |
| `/field/surveys` *(Rencana Rute Baru)* | GET, POST | *(Belum ada)* | `role:admin,field_officer` | `admin`, `field_officer` (Survei Sambungan Baru) |

### 2.2 Endpoint REST API (`routes/api.php`)

| Endpoint API URI | HTTP Method | Middleware Saat Ini | Middleware Target Usulan | Aktor yang Berhak |
| :--- | :---: | :--- | :--- | :--- |
| `/api/v1/auth/login` | POST | Publik | Publik | Semua |
| `/api/v1/auth/me` | GET | `auth` | `auth` | Semua Pengguna Login |
| `/api/v1/complaints` | GET | Publik / Auth | Publik / Auth | Semua (Pelanggan filter milik sendiri) |
| `/api/v1/complaints/{id}` | GET | Policy View | Policy View | Pemilik tiket, `admin`, `field_officer` |
| `/api/v1/complaints/{id}/assign` | POST | `role:admin,staff` | `role:admin` | **HANYA `admin`** (Disposisi penugasan teknisi) |
| `/api/v1/complaints/{id}/status` | POST | `role:admin,staff` | `role:admin,field_officer` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/complaints/{id}/response` | POST | `role:admin,staff` | `role:admin,field_officer` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/complaints/{id}/resolve` | POST | `role:admin,staff` | `role:admin,field_officer` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/meter-reports` | GET | Policy ViewAny | Policy ViewAny | Semua (Pelanggan filter milik sendiri) |
| `/api/v1/meter-reports/validate` | POST | `role:admin,staff` | `role:admin,scada_operator` | `admin`, `scada_operator` *(Field Officer Ditolak)* |
| `/api/v1/bills` | GET | `auth` | `auth` | Pelanggan (milik sendiri), `admin` |
| `/api/v1/payments/{payment}/verify`| POST | `auth` + Policy | `role:admin` + Policy | **HANYA `admin`** *(SCADA & Field Ditolak)* |
| `/api/v1/field/installation` *(Usulan)* | POST | *(Belum ada)* | `role:admin,field_officer` | `admin`, `field_officer` (Input Stand Pasang) |

---

## 3. MATRIKS HAK AKSES FINAL (FINAL RBAC MATRIX)

Berikut adalah matriks komparatif final yang menjadi dasar implementasi sistem otorisasi Smart Water Kota Payakumbuh:

| Modul & Aksi Fungsional | Admin PDAM (`admin`) | Operator SCADA (`scada_operator`) | Petugas Lapangan (`field_officer`) | Pelanggan Warga (`customer`) | Tamu / Publik (`guest`) |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Manajemen Pengguna & Role** | ✅ Penuh (CRUD) | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak |
| **Konfigurasi Tarif & Aturan Billing** | ✅ Penuh (CRUD) | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak |
| **Penerbitan Tagihan Air (Bill Generation)** | ✅ Penuh (Manual/Sistem) | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak |
| **Verifikasi Bukti Pembayaran Tagihan** | ✅ **Wewenang Eksklusif** | ❌ **Dilarang Keras** | ❌ **Dilarang Keras** | ❌ Ditolak | ❌ Ditolak |
| **Monitoring SCADA (Tekanan, Debit, TDS)** | ✅ Supervisi Penuh | ✅ **Kontrol & Input Data** | ❌ Ditolak (403) | ❌ Ditolak | ❌ Ditolak |
| **Pengawasan Reservoir & Pompa Distribusi** | ✅ Supervisi Penuh | ✅ **Monitoring 24/7** | ❌ Ditolak (403) | ❌ Ditolak | ❌ Ditolak |
| **Validasi Baca Meter AI-OCR Pelanggan** | ✅ Supervisi Penuh | ✅ **Verifikator Utama** | ❌ Dilarang (Kecuali darurat) | ❌ Ditolak | ❌ Ditolak |
| **Pencatatan Pemasangan Fisik Meter Baru** | ✅ Monitoring Data | ❌ Bukan Ranah SCADA | ✅ **Input Nomor Seri & Stand** | ❌ Ditolak | ❌ Ditolak |
| **Survei Lokasi Sambungan Baru** | ✅ Disposisi Penugasan | ❌ Bukan Ranah SCADA | ✅ **Lapor Kelayakan Pipa** | ❌ Ditolak | ❌ Ditolak |
| **Disposisi & Reassign Tiket Pengaduan** | ✅ **Dispatcher Utama** | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak | ❌ Ditolak |
| **Tindak Lanjut & Selesai Aduan Lapangan** | ✅ Wewenang Penuh | ❌ Ditolak | ✅ **Hanya Tiket Ditugaskan** | ❌ Ditolak | ❌ Ditolak |
| **Input Laporan Pekerjaan Fisik Lapangan** | ✅ Monitoring | ❌ Ditolak | ✅ **Kirim Foto & Catatan** | ❌ Ditolak | ❌ Ditolak |
| **Lapor Stand Meter Mandiri (OCR Warga)** | ❌ (Gunakan fitur dinas) | ❌ (Gunakan fitur dinas) | ❌ (Gunakan fitur dinas) | ✅ **Kamera AI-OCR Mandiri** | ❌ Wajib Login |
| **Pembayaran Tagihan Air Mandiri** | ❌ Bukan Konsumen | ❌ Bukan Konsumen | ❌ Bukan Konsumen | ✅ **Upload Bukti Transfer** | ❌ Wajib Login |

---

## 4. MAPPING AKUN STAF LAMA KE ROLE BARU

Data pegawai kedinasan yang ada saat ini di MySQL akan dipetakan ke role baru tanpa menghapus riwayat aktivitas historis akun:

### 4.1 Tabel Pemetaan Akun Pegawai

| ID | Nama Pegawai | Email Kedinasan | NIP Kedinasan | Jabatan & Divisi Saat Ini | Role Lama | Role Baru Target |
| :-: | :--- | :--- | :--- | :--- | :---: | :---: |
| **1** | **Ir. Hendra Pratama, S.T.** | `admin@tirtasago.id` | `NIP-TS-198402-089` | Super Admin & Dispatcher Teknis (Divisi Transmisi, Distribusi & SCADA) | `admin` | `admin` *(Tetap Administrator)* |
| **2** | **Ahmad Pratama, S.T.** | `ahmad.pratama@tirtasago.id` | `NIP-198804-004` | Operator SCADA & Verifikator AI-OCR (Seksi Pengolahan Air & SCADA) | `staff` | `scada_operator` |
| **5** | **Dedi Kurniawan, A.Md.** | `dedi.teknisi@tirtasago.id` | `NIP-TS-199205-012` *(Ditambahkan)* | Teknisi TRC Lapangan (Tim Reaksi Cepat Lapangan Wilayah Barat) | `staff` | `field_officer` |
| **3** | **Budi Santoso** | `budi.santoso@warga.payakumbuh.go.id` | — | Pelanggan Standar Mandiri | `customer` | `customer` *(Tidak Berubah)* |
| **4** | **Ibu Siti Nurbaya** | `siti.nurbaya@payakumbuh.community` | — | Pelanggan Demo Komunitas | `customer` | `customer` *(Tidak Berubah)* |

### 4.2 Pembaruan Metadata `staff_profiles`
Akun User ID #5 (Dedi Kurniawan) yang saat ini belum memiliki record resmi di `staff_profiles` akan dilengkapi record profil kedinasan:
- `user_id`: 5
- `nip`: `'NIP-TS-199205-012'`
- `department`: `'Tim Reaksi Cepat (TRC) Lapangan'`
- `position`: `'Petugas Reaksi Cepat & Teknisi Lapangan'`
- `phone_dinas`: `'0811-7788-9900'`

---

## 5. ANALISIS RISIKO TERHADAP DATA DAN FITUR (RISK & IMPACT ANALYSIS)

Pemisahan role internal membawa beberapa konsekuensi teknis yang harus dimitigasi secara hati-hati:

### 5.1 Risiko 1: Regresi pada Rangkaian Pengujian (123 Test Suite)
- **Tingkat Risiko**: TINGGI
- **Penyebab**:
  * Terdapat 14 file pengujian aktif (123 tests) di mana beberapa file seperti `PolicyAuthorizationTest.php`, `RbacSecurityTest.php`, `AuthenticationIntegrationTest.php`, dan `SmartWaterBackendTest.php` mengasumsikan keberadaan role bernama `'staff'` dan memanggil helper `$user->isStaff()`.
  * Jika role `'staff'` langsung dihapus seketika dari tabel `roles`, sejumlah besar assertion otorisasi akan menghasilkan `FAIL (HTTP 403 / RoleNotFound)`.
- **Mitigasi Arsitektur (Graceful Compatibility)**:
  1. Pada Model `User.php`, method `$user->isStaff()` dipertahankan dan diperluas maknanya sebagai penanda pegawai internal:
     ```php
     public function isStaff(): bool
     {
         return $this->hasRole(['admin', 'staff', 'scada_operator', 'field_officer']);
     }
     ```
  2. Tambahkan helper spesifik baru pada `User.php`:
     ```php
     public function isScadaOperator(): bool
     {
         return $this->hasRole('scada_operator');
     }

     public function isFieldOfficer(): bool
     {
         return $this->hasRole('field_officer');
     }
     ```
  3. Role legacy `'staff'` tetap dibiarkan berada di tabel `roles` selama masa transisi agar seeder dan test lama tidak mengalami *foreign key break*.

### 5.2 Risiko 2: Keterputusan Sesi Pengguna yang Sedang Login
- **Tingkat Risiko**: SEDANG
- **Penyebab**:
  * Pengguna yang sedang memiliki sesi aktif menyimpan payload `swc_role = 'staff'` di tabel `sessions`.
  * Saat middleware diubah menjadi `role:admin,scada_operator`, token sesi lama yang masih membawa `swc_role = 'staff'` akan tertolak.
- **Mitigasi Arsitektur**:
  * Middleware `RoleMiddleware` harus menyinkronkan data peran langsung dari relasi Eloquent `$user->roles` di database MySQL, bukan hanya mempercayai string statis di cookie sesi.

### 5.3 Risiko 3: Isolasi Tiket Pengaduan (Complaint Ticket Reassignment)
- **Tingkat Risiko**: RENDAH KE SEDANG
- **Penyebab**:
  * Tiket yang saat ini ter-assign ke staf (contoh `assigned_to = 2` Ahmad Pratama) adalah ranah perbaikan lapangan.
  * Jika Ahmad Pratama menjadi `scada_operator`, ia seharusnya tidak lagi menangani perbaikan fisik pipa bocor di lapangan.
- **Mitigasi Arsitektur**:
  * Berikan fasilitas bagi Admin untuk me-reassign tiket aduan lapangan yang masih open dari Operator SCADA ke Petugas Lapangan (`field_officer`).

---

## 6. RENCANA MIGRATION DAN ROLLBACK YANG AMAN

Seluruh proses pemisahan role dirancang menggunakan prinsip **Zero-Downtime Safe Migration** tanpa perintah destruktif seperti `migrate:fresh` atau `db:wipe`.

### 6.1 Skrip Migrasi Penambahan Role (Forward Migration)
Migration baru yang akan dibuat bernama:  
`2026_11_20_000001_add_field_officer_and_scada_operator_roles.php`

```php
<?php

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tambahkan Role Baru ke Tabel roles
        $scadaRole = Role::firstOrCreate(
            ['name' => 'scada_operator'],
            [
                'display_name' => 'Operator PDAM & SCADA',
                'description'  => 'Operator teknis pengolahan air, monitoring telemetri reservoir, dan verifikator AI-OCR.',
            ]
        );

        $fieldRole = Role::firstOrCreate(
            ['name' => 'field_officer'],
            [
                'display_name' => 'Petugas Lapangan (TRC)',
                'description'  => 'Teknisi lapangan, survei permohonan pasang baru, pencatatan pemasangan meteran, dan penanganan fisik keluhan.',
            ]
        );

        // 2. Mapping Akun Ahmad Pratama ke scada_operator
        $ahmad = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
        if ($ahmad) {
            $ahmad->roles()->syncWithoutDetaching([$scadaRole->id]);
        }

        // 3. Mapping Akun Dedi Kurniawan ke field_officer & lengkapi StaffProfile
        $dedi = User::where('email', 'dedi.teknisi@tirtasago.id')->first();
        if ($dedi) {
            $dedi->roles()->syncWithoutDetaching([$fieldRole->id]);
            StaffProfile::firstOrCreate(
                ['user_id' => $dedi->id],
                [
                    'nip'         => 'NIP-TS-199205-012',
                    'department'  => 'Tim Reaksi Cepat (TRC) Lapangan',
                    'position'    => 'Petugas Reaksi Cepat & Teknisi Lapangan',
                    'phone_dinas' => '0811-7788-9900',
                ]
            );
        }
    }

    public function down(): void
    {
        // Rollback yang aman: Lepaskan role baru dari user tanpa menghapus user
        $scadaRole = Role::where('name', 'scada_operator')->first();
        $fieldRole = Role::where('name', 'field_officer')->first();

        if ($scadaRole) {
            $scadaRole->users()->detach();
            $scadaRole->delete();
        }

        if ($fieldRole) {
            $fieldRole->users()->detach();
            $fieldRole->delete();
        }
    }
};
```

### 6.2 Prosedur Rollback Darurat (Emergency Rollback)
Jika terjadi kendala saat implementasi:
1. Jalankan `php artisan migrate:rollback --step=1`.
2. Akun Ahmad Pratama dan Dedi Kurniawan akan otomatis tetap memegang role `staff` aslinya (karena `syncWithoutDetaching` tidak menghapus role `staff`).
3. Sistem akan kembali ke kondisi stabil Task 10A.2 tanpa kehilangan data tagihan, pembayaran, maupun aduan warga.

---

## 7. DAFTAR TEST SUITE YANG HARUS DITAMBAHKAN

Untuk membuktikan integritas pemisahan hak akses baru, file test baru `tests/Feature/GranularRbacRoleSeparationTest.php` wajib dibuat dengan cakupan pengujian:

1. `test_field_officer_cannot_verify_or_reject_billing_payment`:
   - Login sebagai Petugas Lapangan (`dedi.teknisi@tirtasago.id`).
   - Kirim `POST /admin/payments/{payment}/verify` dan `POST /api/v1/payments/{payment}/verify`.
   - **Ekspektasi**: Status response **HTTP 403 Forbidden**; status pembayaran tidak berubah.
2. `test_scada_operator_cannot_verify_or_reject_billing_payment`:
   - Login sebagai Operator SCADA (`ahmad.pratama@tirtasago.id`).
   - Kirim `POST /admin/payments/{payment}/verify`.
   - **Ekspektasi**: Status response **HTTP 403 Forbidden**.
3. `test_only_admin_can_verify_or_reject_billing_payment`:
   - Login sebagai Admin (`admin@tirtasago.id`).
   - Kirim `POST /admin/payments/{payment}/verify`.
   - **Ekspektasi**: Status response **HTTP 200 OK**; pembayaran diverifikasi.
4. `test_field_officer_cannot_access_scada_reservoir_monitoring`:
   - Login sebagai Petugas Lapangan.
   - Akses `GET /water-monitoring` dan `GET /reservoir-detail`.
   - **Ekspektasi**: Status response **HTTP 302** (redirect ke `/user` atau `/admin-dashboard`) atau **HTTP 403**.
5. `test_scada_operator_can_access_water_monitoring_and_reservoirs`:
   - Login sebagai Operator SCADA.
   - Akses `GET /water-monitoring`.
   - **Ekspektasi**: Status response **HTTP 200 OK**.
6. `test_scada_operator_cannot_resolve_field_complaint_assigned_to_field_officer`:
   - Buat tiket aduan yang ditugaskan ke Petugas Lapangan.
   - Operator SCADA mencoba memanggil `POST /api/v1/complaints/{id}/resolve`.
   - **Ekspektasi**: Status response **HTTP 403 Forbidden**.
7. `test_field_officer_can_respond_and_resolve_assigned_complaint`:
   - Petugas Lapangan mengirimkan tanggapan dan menyelesaikan tiket yang ditugaskan kepada dirinya.
   - **Ekspektasi**: Status response **HTTP 200 OK**.
8. `test_admin_retains_universal_supervision_over_all_endpoints`:
   - Admin dapat mengakses seluruh rute SCADA, pembayaran, aduan, dan dashboard.
   - **Ekspektasi**: Status response **HTTP 200 OK**.

---

## 8. REKOMENDASI URUTAN IMPLEMENTASI (PHASED ROADMAP)

Implementasi disarankan dilakukan dalam 5 tahap bertingkat:

```
[Tahap 1: Database] -> [Tahap 2: Models & Core] -> [Tahap 3: Policies] -> [Tahap 4: Routes & Controllers] -> [Tahap 5: Verifikasi]
```

### Tahap 1: Persiapan Database (Migration & Seeding Tanpa Downtime)
- Buat file migration penambahan role `field_officer` dan `scada_operator`.
- Eksekusi migrasi untuk memasukkan 2 role baru ke tabel `roles`.
- Hubungkan role baru ke User ID #2 dan User ID #5 via `role_user`.
- Lengkapi record `staff_profiles` untuk User ID #5.

### Tahap 2: Pembaruan Core Model & Middleware
- Perbarui `app/Models/User.php`:
  * Tambahkan helper `isScadaOperator()`, `isFieldOfficer()`.
  * Sesuaikan `isStaff()` agar mencakup `['admin', 'staff', 'scada_operator', 'field_officer']` untuk menjaga *backward compatibility*.
- Perbarui `app/Http/Middleware/RoleMiddleware.php`:
  * Pastikan middleware mampu menangani parameter `role:admin,scada_operator` atau `role:admin,field_officer`.

### Tahap 3: Pengetatan Kebijakan Otorisasi (Laravel Policies)
- Perbarui `app/Policies/PaymentPolicy.php`:
  * Ubah wewenang `verify()` dari `isAdmin() || isStaff()` menjadi **hanya `isAdmin()`**.
- Perbarui `app/Policies/MeterReadingPolicy.php`:
  * Batasi `validate()` untuk `isAdmin() || hasRole('scada_operator')`.
- Perbarui `app/Policies/ComplaintPolicy.php`:
  * Batasi penanganan lapangan teknis untuk `isAdmin() || hasRole('field_officer')`.

### Tahap 4: Penyesuaian Routes & Controllers
- Di `routes/web.php`:
  * Ubah grup SCADA menjadi `middleware('role:admin,scada_operator')`.
  * Kunci rute verifikasi pembayaran `/admin/payments/{payment}/verify` menjadi `middleware('role:admin')`.
- Di `routes/api.php`:
  * Terapkan proteksi `role:admin` pada endpoint verifikasi pembayaran API.

### Tahap 5: Eksekusi Test Suite & Verifikasi Regresi
- Tambahkan 8 test cases pada `tests/Feature/GranularRbacRoleSeparationTest.php`.
- Jalankan seluruh test suite PHPUnit untuk memastikan:
  * 123 tests lama tetap **100% PASS**.
  * 8 tests baru **100% PASS** (total menjadi 131 tests).
- Uji simulasi login langsung via browser/HTTP Kernel untuk membuktikan tidak ada pemutusan sesi dinas.

---

> [!IMPORTANT]
> **Instruksi Kepatuhan Task 10A.3**:
> Rancangan arsitektur ini disusun secara ketat tanpa mengubah satu baris pun kode implementasi, skema database, maupun data pengguna. Tahap implementasi baru akan dimulai setelah rancangan ini ditinjau dan disetujui.
