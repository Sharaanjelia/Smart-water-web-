# AUDIT KESIAPAN FINAL IMPLEMENTASI PEMISAHAN ROLE RBAC (TASK 10A.3.2)
## SISTEM SMART WATER KOTA PAYAKUMBUH (PDAM TIRTA SAGO)

**Mode Eksekusi**: **STRICT AUDIT & DESIGN ONLY**  
**Prinsip Batasan**: Dilarang mengubah kode sumber, skema basis data, migration, seeder, maupun data pengguna riil.  
**Tanggal**: 19 November 2026  
**Status Audit**: SELESAI & TERVERIFIKASI — SIAP UNTUK REVIEW DIREKSI & IMPLEMENTASI  

---

## 1. TEMUAN FINAL AUDIT

Berdasarkan investigasi statis dan dinamis terhadap basis data aktif MySQL 8.4 (`smart_water_payakumbuh`) serta seluruh layer kode sumber Laravel 12:

### 1.1 Kondisi Aktual Basis Data MySQL (Terverifikasi Langsung via Kueri)
- **Koneksi Aktif**: Database `smart_water_payakumbuh` pada host `127.0.0.1:3306`, user `root`.
- **Daftar Role di Database**: Tepat **3 role**:
  1. `id = 1` | `name = 'admin'` | `display_name = 'Super Administrator'` (1 pengguna)
  2. `id = 2` | `name = 'staff'` | `display_name = 'Petugas & Operator SCADA'` (2 pengguna)
  3. `id = 3` | `name = 'customer'` | `display_name = 'Pelanggan Warga'` (2 pengguna)
- **Total Pengguna Terdaftar**: Tepat **5 pengguna**:
  - User #1 (`admin@tirtasago.id`): Role `admin`, StaffProfile #1 (NIP: `NIP-TS-198402-089`, Super Admin & Dispatcher Teknis).
  - User #2 (`ahmad.pratama@tirtasago.id`): Role `staff`, StaffProfile #2 (NIP: `NIP-198804-004`, Operator SCADA & Verifikator AI-OCR).
  - User #3 (`budi.santoso@warga.payakumbuh.go.id`): Role `customer`, CustomerProfile #1 (CustID: `1234567890`, Meter: `01000`).
  - User #4 (`siti.nurbaya@payakumbuh.community`): Role `customer`, CustomerProfile #2 (CustID: `1234567891`, Meter: `01842`).
  - User #5 (`dedi.teknisi@tirtasago.id`): Role `staff`, **StaffProfile: TIDAK ADA (NULL)**.
- **Pemeriksaan Multi-Role**: `COUNT = 0`. Tidak ada satupun pengguna di database yang memiliki role ganda.

### 1.2 Temuan Kebocoran Data Pribadi Pelanggan (PII Data Leak)
Pada endpoint publik `GET /api/v1/complaints` (dan variasi response pada `store` dan `simulateProgress`):
- Endpoint ini terbuka untuk publik tanpa autentikasi, dan memanggil `ComplaintResource::collection()`.
- [ComplaintResource.php (Baris 65–72)](file:///c:/xampp/htdocs/smart-water/app/Http/Resources/ComplaintResource.php#L65-L72) saat ini secara terbuka mengembalikan:
  * `'userName'  => $this->reporter_name` (Nama Lengkap Pelapor)
  * `'userPhone' => $this->contact_phone` (Nomor Handphone / WhatsApp Pribadi)
  * `'address'   => $this->address` (Alamat Lengkap Rumah / Titik Koordinat Presisi)
- **Dampak Kritis**: Siapapun di internet dapat melihat nomor telepon pribadi dan alamat rumah pelanggan yang mengajukan komplain tanpa login. Pengamanan wajib diterapkan pada layer Resource backend, bukan sekadar menyembunyikan elemen di Blade frontend.

### 1.3 Temuan Kebocoran Data Daftar / Pagination (Data Ownership Leak)
Pada `BillController::index()`, `PaymentController::index()`, dan `MeterReadingController::apiList()`:
- `if ($user->isAdmin() || $user->isStaff())`: Jika staf lapangan memanggil API tersebut, controller akan mengabaikan filter ID pelanggan dan mengembalikan **seluruh tagihan, pembayaran transfer bank, dan foto meteran se-Kota Payakumbuh**.
- Staf lapangan teknis tidak boleh memiliki akses terhadap data keuangan atau invoice warga kota.

---

## 2. DAFTAR RISIKO YANG SUDAH DISELESAIKAN DALAM DESAIN

| No | Risiko Keamanan & Fungsional | Penyebab Awal | Solusi Arsitektur yang Diselesaikan dalam Desain |
| :-: | :--- | :--- | :--- |
| **1** | **Kebocoran Verifikasi Pembayaran** | `PaymentPolicy::verify` mengizinkan `isStaff()`. | Dikunci **eksklusif `$user->isAdmin()`**. Form Request `VerifyPaymentRequest` juga dikunci hanya Admin. Field Officer & SCADA Operator otomatis ditolak HTTP 403. |
| **2** | **Kebocoran Hak Ganda (Dual-Entitlement Leak)** | Pemakaian `syncWithoutDetaching()` membuat user memegang role `staff` sekaligus role baru. | Menggunakan **Atomic Swap** di pivot `role_user`: role `staff` dilepas dan role baru disematkan dalam 1 transaksi DB. User hanya memegang 1 role aktif. |
| **3** | **Kebocoran Informasi Pribadi (PII Leak)** | `ComplaintResource` mengekspos `userName`, `userPhone`, dan `address` ke publik. | Sanitasi kontekstual backend: jika pemohon bukan pemilik tiket, Admin, atau Petugas Lapangan penugasan, `userPhone` disensor (`0812-****-1111`) dan alamat disederhanakan hanya kecamatan (`$district`). |
| **4** | **Penyusupan Akses SCADA Reservoir** | Rute `/water-monitoring`, dll. diproteksi `role:admin,staff`. | Grup rute SCADA diubah menjadi `role:admin,scada_operator`. Petugas lapangan otomatis ditolak (302/403). |
| **5** | **Penyalahgunaan Validasi AI-OCR** | `MeterReadingPolicy::validate` mengizinkan seluruh `isStaff()`. | Dibatasi hanya untuk **`$user->isAdmin() || $user->isScadaOperator()`**. Petugas lapangan tidak diizinkan memvalidasi stand meter mandiri. |
| **6** | **Pengambilalihan Tiket Lapangan oleh Operator** | `ComplaintPolicy` mengizinkan staf manapun mengklaim tiket yang belum ditugaskan. | Penugasan (`assign`) dikunci **Hanya Admin** (Dispatcher). Penanganan dan penyelesaian (`respond`/`resolve`) dikunci **Hanya Field Officer yang ditugaskan resmi**. |
| **7** | **IDOR pada Daftar Tagihan & Pembayaran** | Controller mengecek `$user->isStaff()` untuk menampilkan semua data. | Diubah menjadi `if ($user->isAdmin())`. Petugas non-admin diperlakukan seperti pengguna biasa yang hanya melihat data miliknya sendiri. |
| **8** | **Kerapuhan Rollback & Foreign Key Break** | Penghapusan role legacy `staff` dapat memicu cascade delete. | Role `staff` dipertahankan di tabel `roles` sebagai *dormant legacy role*. Rollback dirancang deterministik hanya mengembalikan relasi akun yang diubah. |

---

## 3. RISIKO YANG MEMBUTUHKAN KEPUTUSAN / KONFIRMASI PIHAK PDAM

Berikut adalah poin-poin yang memerlukan konfirmasi resmi manajemen PDAM Tirta Sago sebelum eksekusi final:

1. **Konfirmasi SK & Profil Kedinasan Dedi Kurniawan (User ID: 5)**:
   - Di database MySQL saat ini, User #5 **belum memiliki record di tabel `staff_profiles`**.
   - Di file test, ia diberi NIP sementara `NIP-TS-199205-012` dan unit `Tim Reaksi Cepat (TRC) Lapangan`.
   - **Pertanyaan Konfirmasi**: Apakah NIP dan posisi struktural definitif untuk Dedi Kurniawan telah disahkan oleh Kepegawaian PDAM Tirta Sago, atau migration harus menggunakan nomor NIP kedinasan riil yang lain?
2. **Kebijakan Tampilan Antarmuka Petugas Lapangan**:
   - Apakah Petugas Lapangan tetap diarahkan ke `/admin-dashboard` dengan widget khusus "Tugas Lapangan TRC", atau dibuatkan rute portal khusus terpisah (misal `/field-dashboard`) agar antarmuka lapangan lebih ringkas dan mobile-friendly?
3. **Kewenangan Penanganan Tiket Darurat Mandiri (Self-Claim Emergency)**:
   - Apakah dalam situasi darurat (misal pipa transmisi utama pecah tengah malam), Petugas Lapangan diizinkan langsung mengklaim (*self-claim*) tiket tanpa menunggu disposisi Admin Dispatcher, atau seluruh penugasan **mutlak** harus melalui Admin?
4. **Alur Pemasangan Meter Baru vs Modul Pengaduan**:
   - Permohonan sambungan baru saat ini masih tertampung dalam kategori keluhan (`ComplaintCategory: pasang_baru`).
   - Apakah pelaporan fisik stand meter sambungan baru oleh Petugas Lapangan akan tetap masuk lewat penanganan tiket aduan ini, atau menunggu modul New Connection terpisah di Task mendatang?

---

## 4. STRATEGI MIGRATION DAN ROLLBACK DETERMINISTIK

### 4.1 Prinsip Desain Migrasi:
1. **Pencarian Berbasis Unique Email (Bukan Hardcoded User ID)**:
   Migrasi mencari user berdasarkan `email` unik terverifikasi (`ahmad.pratama@tirtasago.id` dan `dedi.teknisi@tirtasago.id`), bukan mengasumsikan angka numerik ID 2 atau ID 5 yang bisa berbeda antarlingskungan database.
2. **Snapshot Tabel Transisi**:
   Membuat tabel log sementara `rbac_transition_logs` dalam transaksi migrasi untuk mencatat `user_id`, `old_role_id`, dan `new_role_id`. Saat rollback dijalankan, sistem membaca snapshot ini secara deterministik tanpa menebak-nebak kondisi awal.
3. **Preservasi Profil Kedinasan Tanpa Menimpa Data**:
   Menggunakan `firstOrCreate` untuk profil kedinasan, sehingga jika profil sudah ada di masa mendatang, nilainya **tidak akan pernah ditimpa**. Pada saat rollback, profil kedinasan **tidak dihapus** untuk menjaga integritas data riwayat.

### 4.2 Rancangan Skrip Migrasi Definitif

```php
<?php

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Buat Tabel Snapshot Audit Relasi Role (Khusus Pelacakan Transisi)
            if (!Schema::hasTable('rbac_transition_logs')) {
                Schema::create('rbac_transition_logs', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                    $table->string('previous_role_name');
                    $table->string('new_role_name');
                    $table->timestamps();
                });
            }

            // 2. Daftarkan Role Baru ke Tabel roles
            $scadaRole = Role::firstOrCreate(
                ['name' => 'scada_operator'],
                [
                    'display_name' => 'Operator PDAM & SCADA',
                    'description'  => 'Operator teknis pengolahan air, telemetri reservoir, dan verifikator baca meter AI-OCR.',
                ]
            );

            $fieldRole = Role::firstOrCreate(
                ['name' => 'field_officer'],
                [
                    'display_name' => 'Petugas Lapangan (TRC)',
                    'description'  => 'Teknisi lapangan, survei sambungan baru, pencatatan fisik meteran, dan tindak lanjut aduan warga.',
                ]
            );

            $staffRole = Role::where('name', 'staff')->first();

            // 3. Transisi Ahmad Pratama -> scada_operator
            $ahmad = User::where('email', 'ahmad.pratama@tirtasago.id')->first();
            if ($ahmad && $staffRole && $ahmad->hasRole('staff')) {
                $ahmad->roles()->detach($staffRole->id);
                $ahmad->roles()->attach($scadaRole->id);
                DB::table('rbac_transition_logs')->insert([
                    'user_id'            => $ahmad->id,
                    'previous_role_name' => 'staff',
                    'new_role_name'      => 'scada_operator',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);
            }

            // 4. Transisi Dedi Kurniawan -> field_officer & Backfill StaffProfile
            $dedi = User::where('email', 'dedi.teknisi@tirtasago.id')->first();
            if ($dedi && $staffRole && $dedi->hasRole('staff')) {
                $dedi->roles()->detach($staffRole->id);
                $dedi->roles()->attach($fieldRole->id);
                DB::table('rbac_transition_logs')->insert([
                    'user_id'            => $dedi->id,
                    'previous_role_name' => 'staff',
                    'new_role_name'      => 'field_officer',
                    'created_at'         => now(),
                    'updated_at'         => now(),
                ]);

                // Backfill profil jika belum ada (tidak menimpa data yang sudah ada)
                StaffProfile::firstOrCreate(
                    ['user_id' => $dedi->id],
                    [
                        'nip'         => 'NIP-TS-199205-012',
                        'department'  => 'Tim Reaksi Cepat (TRC) Lapangan',
                        'position'    => 'Teknisi Lapangan Wilayah Barat',
                        'phone_dinas' => '0811-7788-9900',
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $staffRole = Role::where('name', 'staff')->first();
            $scadaRole = Role::where('name', 'scada_operator')->first();
            $fieldRole = Role::where('name', 'field_officer')->first();

            // Kembalikan akun yang tercatat di rbac_transition_logs ke role aslinya
            if (Schema::hasTable('rbac_transition_logs') && $staffRole) {
                $logs = DB::table('rbac_transition_logs')->get();
                foreach ($logs as $log) {
                    $user = User::find($log->user_id);
                    if ($user) {
                        if ($log->new_role_name === 'scada_operator' && $scadaRole) {
                            $user->roles()->detach($scadaRole->id);
                        }
                        if ($log->new_role_name === 'field_officer' && $fieldRole) {
                            $user->roles()->detach($fieldRole->id);
                        }
                        $user->roles()->syncWithoutDetaching([$staffRole->id]);
                    }
                }
                Schema::dropIfExists('rbac_transition_logs');
            }

            // Hapus role baru HANYA jika sudah tidak ada user yang terikat
            if ($scadaRole && $scadaRole->users()->count() === 0) {
                $scadaRole->delete();
            }
            if ($fieldRole && $fieldRole->users()->count() === 0) {
                $fieldRole->delete();
            }
        });
    }
};
```

---

## 5. MATRIKS HAK AKSES FINAL (COMPREHENSIVE RBAC MATRIX)

| Kapabilitas / Modul Sistem | Admin PDAM (`admin`) | Operator SCADA (`scada_operator`) | Petugas Lapangan (`field_officer`) | Pelanggan Warga (`customer`) | Tamu Publik (`guest`) |
| :--- | :---: | :---: | :---: | :---: | :---: |
| **Manajemen Akun & Role Pegawai** | ✅ CRUD Penuh | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Kelola Konfigurasi Tarif Air** | ✅ CRUD Penuh | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Penerbitan Tagihan Air (Bill Engine)** | ✅ Eksekusi Penuh | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Verifikasi Bukti Pembayaran Manual** | ✅ **Wewenang Tunggal** | ❌ **Dilarang (403)** | ❌ **Dilarang (403)** | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Lihat Daftar Semua Pembayaran Kota** | ✅ Seluruh Data | ❌ Ditolak (Scope) | ❌ Ditolak (Scope) | ❌ Hanya Sendiri | ❌ Wajib Login |
| **Lihat Daftar Semua Tagihan Kota** | ✅ Seluruh Data | ❌ Ditolak (Scope) | ❌ Ditolak (Scope) | ❌ Hanya Sendiri | ❌ Wajib Login |
| **Monitoring Telemetri Sensor SCADA** | ✅ Supervisi | ✅ **Akses Kontrol** | ❌ Ditolak (302/403) | ❌ Ditolak (302) | ❌ Ditolak (302) |
| **Inspeksi Reservoir & Pompa Transmisi** | ✅ Supervisi | ✅ **Monitoring 24/7** | ❌ Ditolak (302/403) | ❌ Ditolak (302) | ❌ Ditolak (302) |
| **Validasi Laporan Meter AI-OCR Warga** | ✅ Supervisi | ✅ **Verifikator Utama** | ❌ Dilarang (403) | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Lihat Antrean Seluruh Stand Meter** | ✅ Seluruh Data | ✅ Seluruh Antrean | ❌ Hanya Sendiri | ❌ Hanya Sendiri | ❌ Dikosongkan |
| **Survei Lokasi Sambungan Baru** | ✅ Disposisi Tugas | ❌ Bukan Tupoksi | ✅ **Input Laporan Survei** | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Catat Fisik Nomor Seri & Stand Meter**| ✅ Monitoring Data | ❌ Bukan Tupoksi | ✅ **Input Pemasangan Pipa**| ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Disposisi / Reassign Tiket Aduan** | ✅ **Dispatcher Utama**| ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Tindak Lanjut & Selesai Aduan Lapangan** | ✅ Wewenang Penuh| ❌ Ditolak (403) | ✅ **Hanya Ditugaskan** | ❌ Ditolak (403) | ❌ Ditolak (302) |
| **Lihat PII Lengkap Pengaduan (Nama/HP/Alamat)** | ✅ Lengkap | ❌ Terproteksi | ✅ **Tiket Ditugaskan** | ✅ **Tiket Sendiri** | ❌ **Disensor Ketat** |
| **Lapor Stand Meter Mandiri (Kamera OCR)** | ❌ (Fitur Dinas) | ❌ (Fitur Dinas) | ❌ (Fitur Dinas) | ✅ **Akses Penuh** | ❌ Wajib Login |
| **Upload Bukti Bayar Tagihan Air** | ❌ (Bukan Konsumen)| ❌ (Bukan Konsumen)| ❌ (Bukan Konsumen)| ✅ **Akses Penuh** | ❌ Wajib Login |

---

## 6. DAFTAR FILE YANG AKAN TERDAMPAK SAAT IMPLEMENTASI

Implementasi pemisahan role RBAC dan proteksi PII akan memengaruhi **15 file utama**:

### 6.1 Database & Migrasi
1. `database/migrations/2026_11_20_000001_add_granular_rbac_roles.php` *(File Baru)*
2. `database/seeders/RoleSeeder.php` *(Pembaruan entri seeder role baru)*
3. `database/seeders/UserSeeder.php` *(Penyelarasan mapping role default)*

### 6.2 Model & Core System
4. [app/Models/User.php](file:///c:/xampp/htdocs/smart-water/app/Models/User.php):
   - Tambah method `isAdmin()`, `isScadaOperator()`, `isFieldOfficer()`, `isInternalStaff()`.
   - Update `primaryRoleName()` untuk mengenali `scada_operator` dan `field_officer`.
   - Deprecate method `isStaff()` secara aman.
5. [app/Http/Middleware/RoleMiddleware.php](file:///c:/xampp/htdocs/smart-water/app/Http/Middleware/RoleMiddleware.php):
   - Mendukung argumen multi-role dinamis (`role:admin,scada_operator`, `role:admin,field_officer`).

### 6.3 Resources (Proteksi Privasi PII)
6. [app/Http/Resources/ComplaintResource.php](file:///c:/xampp/htdocs/smart-water/app/Http/Resources/ComplaintResource.php):
   - Penambahan logika sensor PII: `userPhone` disensor dan `address` diringkas ke `$district` jika pemohon bukan pemilik tiket, admin, atau teknisi penugasan.
7. [app/Http/Resources/UserProfileResource.php](file:///c:/xampp/htdocs/smart-water/app/Http/Resources/UserProfileResource.php):
   - Mendukung serialisasi nama role baru `scada_operator` dan `field_officer`.

### 6.4 Policies (Pengetatan Wewenang Otorisasi)
8. [app/Policies/PaymentPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/PaymentPolicy.php):
   - `verify()`, `viewAny()`, `view()` dikunci eksklusif Admin.
9. [app/Policies/MeterReadingPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/MeterReadingPolicy.php):
   - `validate()` dikunci Admin dan SCADA Operator.
10. [app/Policies/ComplaintPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/ComplaintPolicy.php):
    - `assign()` dikunci Admin. `update()`, `respond()`, `resolve()` dikunci Admin dan Field Officer yang ditugaskan.
11. [app/Policies/BillPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/BillPolicy.php):
    - `view()` dikunci Admin atau pemilik tagihan.

### 6.5 Form Requests & Controllers
12. [app/Http/Requests/VerifyPaymentRequest.php](file:///c:/xampp/htdocs/smart-water/app/Http/Requests/VerifyPaymentRequest.php):
    - `authorize()` dikunci hanya `$this->user()->isAdmin()`.
13. [app/Http/Controllers/BillController.php](file:///c:/xampp/htdocs/smart-water/app/Http/Controllers/BillController.php) & [PaymentController.php](file:///c:/xampp/htdocs/smart-water/app/Http/Controllers/PaymentController.php):
    - Index query diubah dari `isAdmin() || isStaff()` menjadi `isAdmin()`.
14. [app/Http/Controllers/MeterReadingController.php](file:///c:/xampp/htdocs/smart-water/app/Http/Controllers/MeterReadingController.php):
    - Antrean verifikasi kota hanya dibuka untuk Admin dan SCADA Operator.
15. [routes/web.php](file:///c:/xampp/htdocs/smart-water/routes/web.php) & [routes/api.php](file:///c:/xampp/htdocs/smart-water/routes/api.php):
    - Pembaruan proteksi middleware per grup SCADA, Billing, dan Aduan.

---

## 7. RENCANA PENGUJIAN TEST SUITE (25 TEST METHOD BARU)

Untuk membuktikan secara tuntas bahwa pemisahan role dan sensor PII berfungsi 100%, akan dibuat file pengujian terpadu:  
`tests/Feature/GranularRbacAndPiiSecurityTest.php` yang memuat **25 skenario pengujian komprehensif**:

### Kategori A: Perlindungan Privasi PII Pengaduan (7 Tests)
1. `test_public_guest_cannot_see_reporter_phone_on_complaints_index`: Assert `userPhone` tersensor atau null.
2. `test_public_guest_cannot_see_exact_home_address_on_complaints_index`: Assert hanya nama distrik/kelurahan yang tampil.
3. `test_public_guest_cannot_see_full_reporter_name_on_complaints_index`: Assert nama disamarkan (Anonim / Masked).
4. `test_complaint_owner_can_see_full_personal_data_on_own_ticket`: Pemilik tiket melihat data lengkap miliknya.
5. `test_admin_and_assigned_field_officer_can_see_full_reporter_details`: Admin dan teknisi penugasan melihat data lengkap untuk perbaikan di lapangan.
6. `test_complaint_store_response_masks_pii_in_surrounding_complaints_list`: Pembuatan tiket tidak membocorkan PII tiket warga lain di response.
7. `test_simulate_progress_does_not_leak_private_reporter_pii`: Simulasi progres tiket tetap menyensor data privat warga.

### Kategori B: Anti-IDOR & Pembatasan Akses Daftar Data (8 Tests)
8. `test_field_officer_cannot_view_all_city_bills_via_api`: Assert data kosong atau hanya data milik sendiri.
9. `test_scada_operator_cannot_view_all_city_bills_via_api`: Assert data tagihan warga kota tidak bocor ke SCADA.
10. `test_field_officer_cannot_view_all_city_payments_via_api`: Assert daftar pembayaran warga kota tidak bocor ke teknisi lapangan.
11. `test_scada_operator_cannot_view_all_city_payments_via_api`: Assert riwayat pembayaran bank tidak bocor ke operator kontrol.
12. `test_customer_cannot_view_other_customer_payment_via_api`: Assert HTTP 403 Forbidden.
13. `test_customer_cannot_view_other_customer_bill_detail_via_api`: Assert HTTP 403 Forbidden.
14. `test_field_officer_cannot_view_city_wide_meter_reading_queue`: Assert antrean verifikasi OCR kota tidak bocor ke teknisi lapangan.
15. `test_scada_operator_can_view_city_wide_meter_reading_queue`: Assert operator SCADA menerima antrean verifikasi OCR kota.

### Kategori C: Penegakan Batasan Wewenang Antar-Role (8 Tests)
16. `test_field_officer_cannot_verify_payment`: Field officer POST verify -> Assert HTTP 403 Forbidden.
17. `test_scada_operator_cannot_verify_payment`: SCADA operator POST verify -> Assert HTTP 403 Forbidden.
18. `test_only_admin_can_verify_payment`: Admin POST verify -> Assert HTTP 200 OK & Status PAID.
19. `test_field_officer_cannot_access_scada_water_monitoring_page`: Field officer GET `/water-monitoring` -> Assert Redirect / 403.
20. `test_scada_operator_can_access_scada_water_monitoring_page`: SCADA operator GET `/water-monitoring` -> Assert HTTP 200 OK.
21. `test_scada_operator_cannot_assign_complaint_ticket`: SCADA operator POST assign -> Assert HTTP 403 Forbidden.
22. `test_field_officer_cannot_assign_complaint_ticket`: Field officer POST assign -> Assert HTTP 403 Forbidden.
23. `test_scada_operator_cannot_resolve_field_complaint`: SCADA operator POST resolve -> Assert HTTP 403 Forbidden.

### Kategori D: Integritas Migrasi & Rollback (2 Tests)
24. `test_migration_atomic_swap_assigns_single_target_role_without_dual_entitlements`: Verifikasi user hanya memiliki 1 role aktif.
25. `test_migration_down_reverts_roles_deterministically_without_deleting_profiles`: Verifikasi rollback memulihkan kondisi secara presisi.

---

## 8. REKOMENDASI URUTAN IMPLEMENTASI & CHECKLIST KESIAPAN

### 8.1 Urutan Implementasi (6 Tahap)

```
[Tahap 1: Migration Deterministik]
    └── Jalankan migrasi tabel 'rbac_transition_logs', penambahan role, dan atomic swap pivot

[Tahap 2: Model & Resource Hardening]
    ├── Update User.php (isAdmin, isScadaOperator, isFieldOfficer, isInternalStaff)
    └── Update ComplaintResource.php (Masking PII nomor telepon & alamat publik)

[Tahap 3: Policies Hardening]
    ├── PaymentPolicy (verify, viewAny, view -> Admin only)
    ├── MeterReadingPolicy (validate -> Admin & SCADA only)
    └── ComplaintPolicy (assign -> Admin only; update/resolve -> Admin & Assigned Field Officer)

[Tahap 4: Form Request & Controllers Scope]
    ├── Kunci VerifyPaymentRequest
    └── Batasi index query pada BillController, PaymentController, MeterReadingController

[Tahap 5: Routes & Middleware]
    ├── routes/web.php (role:admin,scada_operator untuk SCADA; role:admin untuk verifikasi pembayaran)
    └── routes/api.php (penyesuaian proteksi middleware endpoint)

[Tahap 6: Eksekusi Test Suite & Regresi]
    ├── Jalankan GranularRbacAndPiiSecurityTest (25 tests)
    └── Jalankan seluruh PHPUnit test suite: target 148 tests PASS 100%
```

### 8.2 Checklist Kesiapan Implementasi (Readiness Checklist)

- [x] **Database Immobility**: Basis data MySQL aktif telah diverifikasi utuh tanpa ada record atau tabel yang diubah.
- [x] **Zero Code Changes**: Git status bersih dari modifikasi source code.
- [x] **No Hardcoded User IDs**: Desain migrasi menggunakan email kedinasan unik terverifikasi, bukan angka numerik ID.
- [x] **PII Defense-in-Depth**: Proteksi data pribadi dirancang di level Resource/Controller, bukan hanya frontend.
- [x] **Anti-IDOR & Query Scoping**: Pembatasan data daftar/pagination tagihan dan transaksi telah dirancang aman.
- [x] **Deterministic Rollback**: Mekanisme rollback snapshot disiapkan tanpa risiko kehilangan profil atau akun.
- [x] **Test Suite Plan**: 25 test cases baru telah dipetakan untuk membuktikan seluruh batasan keamanan.
- [ ] **Review & Persetujuan Direksi PDAM**: Menunggu konfirmasi NIP definitif Dedi Kurniawan dan persetujuan implementasi dari pihak PDAM Tirta Sago.

---
*Laporan Kesiapan Final ini disusun secara objektif dan akurat berdasarkan hasil inspeksi langsung terhadap basis data aktif dan seluruh layer kode sumber Smart Water Kota Payakumbuh.*
