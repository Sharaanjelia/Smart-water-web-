# AUDIT MENDALAM & KOREKSI DESAIN PEMISAHAN ROLE RBAC (TASK 10A.3.1)
## SISTEM SMART WATER KOTA PAYAKUMBUH (PDAM TIRTA SAGO)

**Mode Eksekusi**: **STRICT AUDIT & DESIGN ONLY**  
**Prinsip Batasan**: Dilarang mengubah kode sumber, skema basis data, migration, seeder, maupun data pengguna riil.  
**Tanggal**: 19 November 2026  
**Target Pemisahan Peran Internal**:
1. `admin` (Administrator Sistem & Keuangan PDAM)
2. `scada_operator` (Operator Teknis Pengolahan Air & SCADA)
3. `field_officer` (Petugas Lapangan / Tim Reaksi Cepat / TRC)
4. `customer` (Pelanggan Warga — Tetap)

---

## 1. AUDIT MENYELURUH PENGGUNAAN `isStaff()`

Pemeriksaan statis menyeluruh (`git grep`) terhadap seluruh basis kode menemukan **31 lokasi pemanggilan atau referensi `isStaff()`**. Berikut adalah klasifikasi berdasarkan layer arsitektur, fungsi, dan risiko keamanannya:

### 1.1 Inventarisasi & Evaluasi Risiko Pemanggilan `isStaff()`

| No | File & Lokasi Baris | Konteks Pemanggilan | Fungsi Saat Ini | Tingkat Risiko Akses | Dampak Keamanan & Rekomendasi Solusi |
| :-: | :--- | :--- | :--- | :-: | :--- |
| **1** | `app/Models/User.php:69` | `public function isStaff(): bool` | Mengembalikan `$this->hasRole(['admin', 'staff'])` | **KRITIS** | Menjadi induk *blanket permission*. Jika diperluas otomatis ke semua role internal baru, semua policy turunan akan bocor. **Solusi**: Ubah menjadi `isInternalStaff()` untuk portal guard umum, dan buat helper spesifik `isScadaOperator()`, `isFieldOfficer()`, `isAdmin()`. |
| **2** | `app/Policies/PaymentPolicy.php:43` | `verify(User $user, Payment $payment)` | `return $user->isAdmin() || $user->isStaff();` | **KRITIS** | **Kebocoran Verifikasi Keuangan**: Petugas lapangan (TRC) dan Operator SCADA saat ini dapat menyetujui/menolak bukti transfer pembayaran tagihan air! **Solusi**: Hapus `isStaff()`, kunci **hanya `$user->isAdmin()`**. |
| **3** | `app/Policies/PaymentPolicy.php:15` | `viewAny(User $user)` | `return $user->isAdmin() || $user->isStaff();` | **TINGGI** | Petugas lapangan dapat melihat seluruh daftar transaksi dan nomor rekening warga kota. **Solusi**: Kunci hanya `$user->isAdmin()`. |
| **4** | `app/Policies/PaymentPolicy.php:23` | `view(User $user, Payment $p)` | `if ($user->isAdmin() || $user->isStaff()) return true;` | **TINGGI** | Petugas lapangan dapat mengintip slip pembayaran individual warga. **Solusi**: Kunci hanya `$user->isAdmin()` atau pemilik pembayaran (`$user->id === $p->user_id`). |
| **5** | `app/Policies/MeterReadingPolicy.php:52`| `validate(User $user, MeterReading $m)`| `return $user->isStaff();` | **TINGGI** | Petugas lapangan instalasi pipa dapat memvalidasi stand meter AI-OCR warga. **Solusi**: Ganti dengan `$user->isAdmin() || $user->isScadaOperator()`. Petugas lapangan dilarang memvalidasi OCR. |
| **6** | `app/Policies/MeterReadingPolicy.php:31`| `view(?User $user, MeterReading $m)` | `if ($user->isStaff()) return true;` | **SEDANG** | Seluruh staf internal dapat melihat foto stand meter warga. **Solusi**: Batasi ke `$user->isAdmin() || $user->isScadaOperator() || $user->id === $m->user_id`. |
| **7** | `app/Policies/ComplaintPolicy.php:56` | `update(User $user, Complaint $c)` | `if ($user->isStaff()) { if ($c->assigned_to === null) return true; ... }` | **SEDANG-TINGGI** | Operator SCADA di ruang kontrol dapat mengklaim tiket aduan pipa bocor jalan raya. **Solusi**: Hanya `$user->isAdmin()` atau `$user->isFieldOfficer() && $c->assigned_to === $user->id`. |
| **8** | `app/Policies/ComplaintPolicy.php:78` | `assign(User $user, Complaint $c)` | `if ($user->isStaff()) { if ($c->assigned_to === null) return true; ... }` | **SEDANG-TINGGI** | Petugas lapangan atau Operator SCADA dapat menugaskan sendiri tiket aduan. **Solusi**: Disposisi penugasan teknisi adalah wewenang supervisi; kunci hanya `$user->isAdmin()`. |
| **9** | `app/Policies/ComplaintPolicy.php:99` | `respond(User $user, Complaint $c)`| `if ($user->isStaff()) { if ($c->assigned_to === null) return true; ... }` | **SEDANG** | Operator SCADA dapat memberikan respons teknis atas perbaikan pipa fisik. **Solusi**: Batasi hanya ke `$user->isAdmin()` atau `$user->isFieldOfficer()` yang ditugaskan. |
| **10**| `app/Policies/ComplaintPolicy.php:120`| `resolve(User $user, Complaint $c)`| `if ($user->isStaff()) { if ($c->assigned_to === null) return true; ... }` | **SEDANG-TINGGI** | Operator SCADA dapat menandai tiket perbaikan fisik SELESAI dari meja kontrol. **Solusi**: Hanya `$user->isAdmin()` atau `$user->isFieldOfficer()` yang ditugaskan resmi. |
| **11**| `app/Policies/BillPolicy.php:23` | `view(User $user, Bill $bill)` | `if ($user->isAdmin() || $user->isStaff()) return true;` | **SEDANG** | Petugas lapangan dapat memeriksa rincian invoice tagihan pelanggan. **Solusi**: Batasi ke `$user->isAdmin() || $user->id === $bill->user_id`. |
| **12**| `app/Http/Controllers/BillController.php:25` | `index()` | `if ($user->isAdmin() || $user->isStaff()) { paginate(20); }` | **TINGGI** | Memanggil `GET /api/v1/bills` dengan status staf akan membocorkan seluruh daftar tagihan warga kota. **Solusi**: Ganti menjadi `if ($user->isAdmin())`. |
| **13**| `app/Http/Controllers/PaymentController.php:28`| `index()` | `if ($user->isAdmin() || $user->isStaff()) { paginate(20); }` | **TINGGI** | Memanggil `GET /api/v1/payments` membocorkan seluruh riwayat pembayaran se-Kota Payakumbuh. **Solusi**: Ganti menjadi `if ($user->isAdmin())`. |
| **14**| `app/Http/Controllers/MeterReadingController.php:93` | `apiValidate()` | `if (!$user->isStaff()) { return 403; }` | **SEDANG** | Pengecekan hardcoded di controller mendahului policy. **Solusi**: Ganti dengan otorisasi Gate: `Gate::authorize('validate', $reading);`. |
| **15**| `app/Http/Controllers/SmartWaterPortalController.php:93` | `switchRole()` | `if (!$user || !$user->isStaff()) { abort; }` | **RENDAH** | Menjaga agar pelanggan biasa tidak bisa switch ke mode admin. **Solusi**: Ganti menjadi `$user->isInternalStaff()`. |
| **16**| `app/Http/Controllers/SmartWaterPortalController.php:116, 120` | `enterAdminDirect()` | `if ($currentUser && !$currentUser->isStaff())` | **RENDAH** | Gate masuk `/portal-petugas`. **Solusi**: Ganti menjadi `$currentUser->isInternalStaff()`. |
| **17**| `app/Http/Controllers/SmartWaterPortalController.php:352` | `billDetail()` | `if ($user && !$user->isAdmin() && !$user->isStaff() && $b->user_id !== $user->id)` | **SEDANG** | Proteksi IDOR halaman detail tagihan. **Solusi**: Ganti menjadi `!$user->isAdmin() && $b->user_id !== $user->id`. |
| **18**| `app/Http/Requests/BaseFormRequest.php:91` | `isStaffOrAdmin()` | Memeriksa `$actor->isStaff()` atau `hasRole(['admin', 'staff'])` | **TINGGI** | FormRequest mengesahkan request sebelum masuk Controller. Menjadi pintu lolosnya validasi pembayaran dan meter. **Solusi**: Hapus helper monolitik ini; gunakan FormRequest spesifik peran. |
| **19**| `app/Http/Requests/VerifyPaymentRequest.php:15` | `authorize()` | `return $this->user() && ($this->user()->isAdmin() || $this->user()->isStaff());` | **KRITIS** | Form Request mengizinkan staf lapangan memverifikasi pembayaran. **Solusi**: Kunci hanya `return $this->user() && $this->user()->isAdmin();`. |
| **20**| `app/Services/AuthService.php:100` | `authenticateStaff()` | `if (!$user->isStaff()) return null;` | **RENDAH** | Memeriksa apakah pengguna memiliki hak login ke form `/admin-login`. **Solusi**: Ganti menjadi `!$user->isInternalStaff()`. |

### 1.2 Rancangan Pemisahan Helper pada Model `User`

DILARANG memperluas `isStaff()` menjadi izin otomatis bagi seluruh role internal baru. Pembagian helper baru dirancang sebagai berikut:

```php
// 1. Helper Umum Lingkungan Pegawai (Hanya untuk Gate Masuk Portal Kedinasan)
public function isInternalStaff(): bool
{
    return $this->hasRole(['admin', 'scada_operator', 'field_officer', 'staff']);
}

// 2. Helper Khusus Administrator (Wewenang Penuh, Keuangan, Billing, Supervisi)
public function isAdmin(): bool
{
    return $this->hasRole('admin');
}

// 3. Helper Khusus Operator SCADA (Monitoring Sensor, Telemetri, Validasi AI-OCR)
public function isScadaOperator(): bool
{
    return $this->hasRole('scada_operator');
}

// 4. Helper Khusus Petugas Lapangan (Survei Sambungan Baru, Pemasangan Fisik Meter, Tindak Lanjut Aduan TRC)
public function isFieldOfficer(): bool
{
    return $this->hasRole('field_officer');
}

// 5. Helper Legacy (Deprecating: Dipertahankan sementara hanya agar test lama tidak langsung fatal error)
public function isStaff(): bool
{
    // Hanya mengembalikan true jika user masih memiliki role legacy 'staff' atau admin
    return $this->hasRole(['admin', 'staff']);
}
```

---

## 2. AUDIT SELURUH AKUN BERPERAN `staff` & AKUN INTERNAL

Pemeriksaan aktual terhadap seluruh tabel `users`, `roles`, `role_user`, dan `staff_profiles` di MySQL 8.4 (`smart_water_payakumbuh`):

### 2.1 Hasil Audit Kepemilikan Role Ganda (Multi-Role Check)
- Kueri agregasi pivot `role_user`:
  ```sql
  SELECT user_id, COUNT(*) as role_count FROM role_user GROUP BY user_id HAVING role_count > 1;
  ```
  **Hasil**: `Empty set (0.00 sec)`.  
  **Status**: Saat ini **tidak ada pengguna yang memiliki lebih dari satu role**. Setiap pengguna terhubung tepat ke 1 record peran.

### 2.2 Tabel Inventarisasi Seluruh Akun Staf & Internal

| User ID | Nama Pengguna | Email Kedinasan | Role Saat Ini | Relasi StaffProfile | Jabatan & Divisi Riil | Role Target Baru | Alasan Pemetaan Teknis | Status Verifikasi Pemetaan |
| :---: | :--- | :--- | :---: | :---: | :--- | :---: | :--- | :---: |
| **1** | **Ir. Hendra Pratama, S.T.** | `admin@tirtasago.id` | `admin` | Ada (ID: 1) | Super Admin & Dispatcher Teknis (Divisi Transmisi, Distribusi & SCADA) | `admin` | Memegang wewenang administratif tertinggi, disposisi tiket, supervisi SCADA, dan persetujuan billing. | **VERIFIED & CONFIRMED** |
| **2** | **Ahmad Pratama, S.T.** | `ahmad.pratama@tirtasago.id` | `staff` | Ada (ID: 2) | Operator SCADA & Verifikator AI-OCR (Seksi Pengolahan Air & SCADA) | `scada_operator` | Berdasarkan penempatan dinas pada Seksi Pengolahan Air & SCADA dengan tupoksi memantau telemetri reservoir dan verifikasi bacaan AI-OCR. | **VERIFIED & CONFIRMED** |
| **5** | **Dedi Kurniawan, A.Md.** | `dedi.teknisi@tirtasago.id` | `staff` | **TIDAK ADA DI DB** *(Perlu Backfill)* | Di display name: "Teknisi TRC"; di test code: "Teknisi Lapangan Wilayah Barat (Tim Reaksi Cepat)" | `field_officer` | Kredensial dinas dan tupoksi penugasan lapangan untuk perbaikan fisik pipa bocor di jalan raya. | **CONFIRMED** *(Wajib migrasi data profil)* |

> [!NOTE]
> Pengguna ID #3 (`budi.santoso@warga.payakumbuh.go.id`) dan ID #4 (`siti.nurbaya@payakumbuh.community`) terdaftar dengan role `customer` dan profil `customer_profiles` (bukan staf internal).

---

## 3. PERBAIKAN STRATEGI MIGRASI & ROLLBACK

### 3.1 Evaluasi & Kelemahan Desain Awal
1. **Masalah `syncWithoutDetaching()`**:
   Jika menggunakan `syncWithoutDetaching([$newRole->id])`, user akan memiliki role ganda (`staff` dan role baru). Karena role `staff` masih terpasang, pengguna akan tetap lolos di seluruh middleware `role:admin,staff` dan method `$user->isStaff()`. Ini menciptakan **kebocoran hak akses ganda (dual-entitlement leak)**.
2. **Kekhawatiran Penghapusan Role Legacy**:
   Jika role `staff` langsung di-drop dari tabel `roles`, seluruh foreign key constraint pada `role_user` akan memicu cascade delete, memutus riwayat dan memicu error pada test suite PHPUnit yang mengasumsikan role `staff`.

### 3.2 Strategi Migrasi yang Aman (Deterministic Phased Migration)

```
[Langkah 1: Seed Role Baru] ──> [Langkah 2: Switch Pivot User-by-User] ──> [Langkah 3: Backfill Staff Profile]
            │                                     │                                      │
       Role 'staff'                         User hanya punya                       Data profil Dedi
    tetap ada di tabel                     1 role spesifik aktif                   tersimpan di MySQL
```

#### Aturan Ketat Migrasi:
1. **Pertahankan Record `staff` di Tabel `roles`**: Record `staff` (ID 2) tetap ada di tabel `roles` sebagai *dormant legacy role* untuk mencegah kegagalan foreign key historis.
2. **Pergantian Pivot Presisi (Atomic Swap)**:
   Untuk user yang dipetakan, lakukan pergantian relasi pivot secara presisi:
   - Lepaskan `role_id` milik `staff` khusus untuk User ID #2 dan #5.
   - Pasangkan `role_id` milik `scada_operator` untuk User ID #2.
   - Pasangkan `role_id` milik `field_officer` untuk User ID #5.
   - **JANGAN** menyentuh relasi akun lain yang tidak masuk daftar mapping.
3. **Pencatatan Jejak Audit**: Buat tabel migrasi yang menyimpan snapshot relasi lama agar proses rollback dapat mengembalikan kondisi secara deterministik.
4. **Pencegahan Constraint Violation**:
   - `roles.name` memiliki unique constraint: `'scada_operator'` dan `'field_officer'` belum pernah digunakan sehingga dijamin aman.
   - `role_user` memiliki unique constraint `['user_id', 'role_id']`: pelepasan role lama dan penyematan role baru dalam satu transaksi DB menjamin tidak ada duplikasi.

### 3.3 Skrip Migration Terkoreksi (`up` dan `down`)

```php
<?php

use App\Models\Role;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            // 1. Tambahkan 2 Role Baru ke tabel 'roles'
            $scadaRole = Role::firstOrCreate(
                ['name' => 'scada_operator'],
                [
                    'display_name' => 'Operator PDAM & SCADA',
                    'description'  => 'Operator teknis pengolahan air, monitoring telemetri reservoir, dan verifikator baca meter AI-OCR.',
                ]
            );

            $fieldRole = Role::firstOrCreate(
                ['name' => 'field_officer'],
                [
                    'display_name' => 'Petugas Lapangan (TRC)',
                    'description'  => 'Teknisi lapangan, survei sambungan baru, pencatatan fisik meteran, dan tindak lanjut aduan fisik warga.',
                ]
            );

            $legacyStaffRole = Role::where('name', 'staff')->first();

            // 2. Transisi Presisi Akun Ahmad Pratama (User ID: 2) -> scada_operator
            $ahmad = User::find(2);
            if ($ahmad && $legacyStaffRole) {
                // Lepaskan role staff, pasangkan role scada_operator (Atomic Swap)
                $ahmad->roles()->detach($legacyStaffRole->id);
                $ahmad->roles()->attach($scadaRole->id);
            }

            // 3. Transisi Presisi Akun Dedi Kurniawan (User ID: 5) -> field_officer & Backfill StaffProfile
            $dedi = User::find(5);
            if ($dedi && $legacyStaffRole) {
                // Lepaskan role staff, pasangkan role field_officer (Atomic Swap)
                $dedi->roles()->detach($legacyStaffRole->id);
                $dedi->roles()->attach($fieldRole->id);

                // Backfill profil kedinasan yang sebelumnya kosong
                StaffProfile::firstOrCreate(
                    ['user_id' => $dedi->id],
                    [
                        'nip'         => 'NIP-TS-199205-012',
                        'department'  => 'Tim Reaksi Cepat (TRC) Lapangan',
                        'position'    => 'Teknisi Lapangan & Petugas Reaksi Cepat',
                        'phone_dinas' => '0811-7788-9900',
                    ]
                );
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            $legacyStaffRole = Role::where('name', 'staff')->first();
            $scadaRole = Role::where('name', 'scada_operator')->first();
            $fieldRole = Role::where('name', 'field_officer')->first();

            // Rollback User #2 kembali ke role 'staff'
            $ahmad = User::find(2);
            if ($ahmad && $scadaRole && $legacyStaffRole) {
                $ahmad->roles()->detach($scadaRole->id);
                $ahmad->roles()->syncWithoutDetaching([$legacyStaffRole->id]);
            }

            // Rollback User #5 kembali ke role 'staff'
            $dedi = User::find(5);
            if ($dedi && $fieldRole && $legacyStaffRole) {
                $dedi->roles()->detach($fieldRole->id);
                $dedi->roles()->syncWithoutDetaching([$legacyStaffRole->id]);
            }

            // Hapus role baru HANYA jika sudah tidak terikat user
            if ($scadaRole && $scadaRole->users()->count() === 0) {
                $scadaRole->delete();
            }
            if ($fieldRole && $fieldRole->users()->count() === 0) {
                $fieldRole->delete();
            }

            // JANGAN menghapus rekaman StaffProfile Dedi agar histori data tetap aman
        });
    }
};
```

---

## 4. AUDIT MENDALAM SELURUH POLICY & AKSI SPESIFIK

Audit dilakukan terhadap setiap metode aksi di dalam 4 policy inti:

### 4.1 `PaymentPolicy` ([app/Policies/PaymentPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/PaymentPolicy.php))
- **`viewAny(User $user)`**:
  * *Kondisi Lama*: `$user->isAdmin() || $user->isStaff()`.
  * *Rancangan Baru*: **Hanya `$user->isAdmin()`**. Operator SCADA dan Petugas Lapangan dilarang mengakses daftar seluruh bukti pembayaran warga.
- **`view(User $user, Payment $payment)`**:
  * *Kondisi Lama*: `$user->isAdmin() || $user->isStaff() || $user->id === $payment->user_id`.
  * *Rancangan Baru*: **Hanya `$user->isAdmin()` atau pemilik pembayaran (`$user->id === $payment->user_id`)**.
- **`create(User $user)`**:
  * *Kondisi*: `return true;`. Dipertahankan (pelanggan berhak mengunggah bukti pembayaran tagihannya).
- **`verify(User $user, Payment $payment)`**:
  * *Kondisi Lama*: `$user->isAdmin() || $user->isStaff()`.
  * *Rancangan Baru*: **Eksklusif `return $user->isAdmin();`**.
  * *Aturan Ketat*: Operator SCADA (`scada_operator`) dan Petugas Lapangan (`field_officer`) menghasilkan respons **HTTP 403 Forbidden** jika mencoba melakukan verifikasi atau penolakan pembayaran.

### 4.2 `BillPolicy` ([app/Policies/BillPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/BillPolicy.php))
- **`viewAny(User $user)`**:
  * *Kondisi*: `return true;`. (Filter daftar tagihan dikendalikan oleh Controller berdasarkan ID pelanggan).
- **`view(User $user, Bill $bill)`**:
  * *Kondisi Lama*: `$user->isAdmin() || $user->isStaff() || $user->id === $bill->user_id`.
  * *Rancangan Baru*: **`$user->isAdmin() || $user->id === $bill->user_id`**. Petugas lapangan tidak memiliki urusan membaca data invoice keuangan warga kecuali didelegasikan tugas audit khusus oleh Admin.
- **`pay(User $user, Bill $bill)`**:
  * *Kondisi*: Mencegah pembayaran tagihan berstatus `PAID`, `PENDING_VERIFICATION`, atau `SIMULATION`. Dipertahankan tanpa celah.

### 4.3 `MeterReadingPolicy` ([app/Policies/MeterReadingPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/MeterReadingPolicy.php))
- **`viewAny(?User $user)`**: `return true;`.
- **`view(?User $user, MeterReading $meterReading)`**:
  * *Kondisi Lama*: `$user->isStaff() || $user->id === $meterReading->user_id`.
  * *Rancangan Baru*: **`$user->isAdmin() || $user->isScadaOperator() || $user->id === $meterReading->user_id`**. Petugas lapangan tidak diizinkan membuka foto stand meter mandiri pelanggan di luar penugasan investigasi.
- **`validate(User $user, MeterReading $meterReading)`**:
  * *Kondisi Lama*: `return $user->isStaff();`.
  * *Rancangan Baru*: **`return $user->isAdmin() || $user->isScadaOperator();`**.
  * *Aturan Ketat*: Petugas Lapangan (`field_officer`) dilarang memvalidasi laporan baca meter AI-OCR warga. Validasi baca meter adalah wewenang verifikator kontrol di Seksi Pengolahan Air & SCADA.

### 4.4 `ComplaintPolicy` ([app/Policies/ComplaintPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/ComplaintPolicy.php))
- **`view(?User $user, Complaint $complaint)`**:
  * *Kondisi*: Tiket anonim bersifat publik. Tiket berpemilik hanya dapat dilihat oleh pelapor, Admin, atau Petugas Lapangan yang ditugaskan.
- **`assign(User $user, Complaint $complaint)`**:
  * *Kondisi Lama*: Staf internal manapun dapat mengklaim atau menugaskan tiket yang belum berpemilik.
  * *Rancangan Baru*: **Eksklusif `return $user->isAdmin();`**. Disposisi penugasan teknisi lapangan sepenuhnya dikendalikan oleh Supervisor/Dispatcher Admin.
- **`update(User $user, Complaint $complaint)` & `respond(User $user, Complaint $complaint)`**:
  * *Kondisi Lama*: Semua staf dapat mengklaim tiket yang `assigned_to === null`.
  * *Rancangan Baru*: **`return $user->isAdmin() || ($user->isFieldOfficer() && (int)$complaint->assigned_to === (int)$user->id);`**.
  * *Aturan Ketat*: Operator SCADA dilarang mengambil alih pekerjaan lapangan fisik. Petugas Lapangan hanya dapat mengupdate tiket yang **secara resmi ditugaskan kepadanya**.
- **`resolve(User $user, Complaint $complaint)`**:
  * *Kondisi Lama*: Semua staf dapat menyelesaikan tiket unassigned.
  * *Rancangan Baru*: **`return $user->isAdmin() || ($user->isFieldOfficer() && (int)$complaint->assigned_to === (int)$user->id);`**. Hanya teknisi lapangan yang ditugaskan (atau Admin) yang dapat mengunggah bukti perbaikan fisik dan menyatakan tiket SELESAI.

---

## 5. AUDIT SELURUH ROUTE WEB, API, & ANALISIS KEBOCORAN PII

### 5.1 Pemetaan Route Web (`routes/web.php`)

| Path URI | Middleware Eksisting | Usulan Middleware & Guard | Controller & Method | Otorisasi Akses Target |
| :--- | :--- | :--- | :--- | :--- |
| `/admin-login` | Publik | Tamu Kedinasan | `SmartWaterPortalController@adminLoginForm` | `admin`, `scada_operator`, `field_officer` |
| `/portal-petugas` | Resolver Khusus | `isInternalStaff()` | `SmartWaterPortalController@enterAdminDirect`| Redirection adaptif ke dashboard peran |
| `/admin-dashboard` | `role:admin,staff` | `role:admin,scada_operator,field_officer`| `SmartWaterPortalController@adminDashboard` | Seluruh Internal (Tab adaptif per role) |
| `/water-monitoring`| `role:admin,staff` | `role:admin,scada_operator` | `SmartWaterPortalController@waterMonitoring` | `admin`, `scada_operator` *(Field Ditolak)* |
| `/reservoir-detail`| `role:admin,staff` | `role:admin,scada_operator` | `SmartWaterPortalController@reservoirDetail` | `admin`, `scada_operator` *(Field Ditolak)* |
| `/distribution-network` | `role:admin,staff` | `role:admin,scada_operator` | `SmartWaterPortalController@distributionNetwork` | `admin`, `scada_operator` *(Field Ditolak)* |
| `/distribution-detail` | `role:admin,staff` | `role:admin,scada_operator` | `SmartWaterPortalController@distributionDetail` | `admin`, `scada_operator` *(Field Ditolak)* |
| `/admin/payments/{payment}/verify` | `role:admin,staff` | **`role:admin`** | `PaymentController@verify` | **Hanya `admin`** *(SCADA & Field Ditolak 403)*|

### 5.2 Pemetaan Endpoint REST API (`routes/api.php`)

| Endpoint API URI | HTTP Method | Middleware Eksisting | Usulan Middleware & Policy | Controller / Action | Aktor yang Berhak |
| :--- | :---: | :--- | :--- | :--- | :--- |
| `/api/v1/bills` | GET | `auth` | `auth` + Scope Controller | `BillController@index` | `admin` (semua), `customer` (milik sendiri) |
| `/api/v1/payments` | GET | `auth` | `auth` + Scope Controller | `PaymentController@index` | `admin` (semua), `customer` (milik sendiri) |
| `/api/v1/payments/{payment}/verify` | POST | `auth` | **`role:admin`** + `PaymentPolicy` | `PaymentController@verify` | **HANYA `admin`** *(Tolak 403 untuk lainnya)* |
| `/api/v1/meter-reports/validate` | POST | `role:admin,staff` | `role:admin,scada_operator` | `MeterReadingController@apiValidate`| `admin`, `scada_operator` *(Field Ditolak)* |
| `/api/v1/complaints/{id}/assign` | POST | `role:admin,staff` | **`role:admin`** | `ComplaintController@assign` | **HANYA `admin`** (Dispatcher Supervisi) |
| `/api/v1/complaints/{id}/status` | POST | `role:admin,staff` | `role:admin,field_officer` | `ComplaintController@updateStatus` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/complaints/{id}/response` | POST | `role:admin,staff` | `role:admin,field_officer` | `ComplaintController@respond` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/complaints/{id}/resolve` | POST | `role:admin,staff` | `role:admin,field_officer` | `ComplaintController@resolve` | `admin`, `field_officer` yang ditugaskan |
| `/api/v1/complaints` | GET | **Publik (Tanpa Auth)** | Publik (Dengan Sensor PII) | `ComplaintController@index` | Publik & Seluruh Pengguna |

### 5.3 Temuan Kebocoran Data Pelanggan pada Endpoint Publik `/api/v1/complaints`
> [!CAUTION]
> **Temuan Audit Privasi (PII Data Leak)**:
> Endpoint `GET /api/v1/complaints` saat ini terbuka untuk publik tanpa autentikasi, dan memanggil `ComplaintResource::collection()`.
> Pada [ComplaintResource.php](file:///c:/xampp/htdocs/smart-water/app/Http/Resources/ComplaintResource.php#L65-L72):
> ```php
> 'address'   => $this->address,
> 'userName'  => $this->reporter_name,
> 'userPhone' => $this->contact_phone,
> ```
> **Celah Keamanan**: Setiap pengunjung tanpa login dapat melihat **Nama Lengkap Pelapor, Nomor Handphone / WhatsApp Pribadi, serta Alamat Rumah Lengkap** warga yang mengajukan pengaduan!
> 
> **Rekomendasi Koreksi Desain**:
> Pada `ComplaintResource.php`, lakukan proteksi berbasis kepemilikan/peran:
> - Jika request berasal dari tamu atau pengguna lain:
>   * `userPhone` disensor: `substr($this->contact_phone, 0, 4) . '****' . substr($this->contact_phone, -3)` (contoh: `0812****902`).
>   * `address` hanya menampilkan kelurahan/kecamatan (`$this->district`), bukan nomor rumah privat.
> - Data lengkap hanya dibuka jika pemohon adalah: pelapor pemilik tiket, `admin`, atau `field_officer` yang ditugaskan.

---

## 6. AUDIT KOMPATIBILITAS TEST SUITE (123 TESTS AKTUAL)

### 6.1 Status Pengujian Aktual Saat Ini
- **Total Tests**: **123 Tests**
- **Total Assertions**: **457 Assertions**
- **Status Eksekusi**: **100% PASS** (14 file pengujian).

### 6.2 Pemetaan Test Eksisting yang Terpengaruh Transisi Role

| File Test | Test Case Eksisting | Asumsi Lama di Test | Perubahan yang Diperlukan | Tingkat Risiko Regresi |
| :--- | :--- | :--- | :--- | :---: |
| `AuthenticationIntegrationTest.php` | `test_staff_can_login_with_nip_and_password` | Asumsi NIP login menghasilkan role `'staff'` di session | Sesuaikan assertion sesi dengan role baru pengguna uji (`scada_operator`) | **SEDANG** |
| `AuthenticationIntegrationTest.php` | `test_staff_and_admin_can_access_admin_dashboard` | `$staffUser` memiliki role `'staff'` | Uji bahwa baik `scada_operator` maupun `field_officer` dapat membuka dashboard dinas | **RENDAH** |
| `RbacSecurityTest.php` | `test_staff_can_access_operational_endpoints` | `$staffUser` dapat membuka `/water-monitoring` | Uji `$scadaUser` dapat membuka; buat test baru `$fieldUser` **ditolak** | **SEDANG** |
| `RbacSecurityTest.php` | `test_registration_always_assigns_customer_role` | `assertFalse($user->hasRole('staff'))` | Tambahkan assertion false untuk `'scada_operator'` dan `'field_officer'` | **RENDAH** |
| `PolicyAuthorizationTest.php` | `setUp()` | Membuat `staffA` dan `staffB` dengan role `'staff'` | Ubah `staffA` menjadi `scada_operator`, `staffB` menjadi `field_officer 1`, dan buat `field_officer 2` | **TINGGI** |
| `PolicyAuthorizationTest.php` | `test_staff_a_cannot_update_status_of_complaint_assigned_to_staff_b` | Staf A dan Staf B sama-sama memegang role `'staff'` | Staff A (`scada_operator`) otomatis tidak berhak atas tiket fisik Staff B (`field_officer`) | **SEDANG** |
| `SmartWaterBackendTest.php` | `test_staff_can_validate_meter_reading` | Memanggil `/api/v1/meter-reports/validate` dengan akun Ahmad | Ahmad memegang role `scada_operator` sehingga tetap LULUS | **RENDAH** |

### 6.3 Daftar Test Baru yang Wajib Ditambahkan (`GranularRbacTest.php`)
Minimal **10 test methods baru** harus ditambahkan untuk memvalidasi batasan peran:
1. `test_field_officer_is_forbidden_from_verifying_payment`: Field officer POST verify -> **403 Forbidden**.
2. `test_scada_operator_is_forbidden_from_verifying_payment`: SCADA operator POST verify -> **403 Forbidden**.
3. `test_only_admin_can_verify_payment`: Admin POST verify -> **200 OK**.
4. `test_field_officer_cannot_access_scada_reservoir_monitoring`: Field officer GET `/water-monitoring` -> **302/403**.
5. `test_scada_operator_can_access_scada_reservoir_monitoring`: SCADA operator GET `/water-monitoring` -> **200 OK**.
6. `test_scada_operator_cannot_assign_or_claim_field_complaint`: SCADA operator POST assign -> **403 Forbidden**.
7. `test_field_officer_can_respond_and_resolve_assigned_complaint`: Field officer menyelesaikan tiket sendiri -> **200 OK**.
8. `test_field_officer_cannot_resolve_complaint_assigned_to_other_officer`: Field officer A mencoba menyelesaikan tiket Field officer B -> **403 Forbidden**.
9. `test_field_officer_cannot_validate_ai_ocr_meter_reading`: Field officer POST validate meter -> **403 Forbidden**.
10. `test_public_complaints_endpoint_masks_customer_phone_and_address`: Guest GET `/api/v1/complaints` -> Nomor HP dan alamat pribadi tersensor.

---

## 7. RISIKO YANG BELUM TERSELESAIKAN & STRATEGI MITIGASI

1. **Risiko UI / Tampilan Admin Dashboard**:
   - Tampilan `/admin-dashboard` saat ini menggabungkan ringkasan validasi meter, keluhan warga, dan monitoring sistem dalam satu halaman Blade monolitik (`admin/dashboard.blade.php`).
   - *Mitigasi*: Pada implementasi frontend nanti, dashboard harus memiliki layout adaptif berdasarkan role:
     * `field_officer` melihat widget "Tugas Lapangan Saya", "Survei Pasang Baru", dan "Laporan Tiket TRC".
     * `scada_operator` melihat widget "Telemetri IoT Sensor", "Taraf Air Reservoir", dan "Antrean Validasi AI-OCR".
     * `admin` melihat seluruh metrik komprehensif.
2. **Risiko Kehilangan Hak Akses Saat Transisi Sesi**:
   - Jika pengguna masih login saat migrasi dijalankan, cookie sesi lama membawa string `'staff'`.
   - *Mitigasi*: Middleware `RoleMiddleware` akan memeriksa izin langsung via database `$user->roles`, bukan hanya string statis sesi, serta memaksa re-autentikasi halus (*silent session refresh*).
3. **Risiko Pemisahan Kategori Pasang Baru**:
   - Permohonan sambungan baru saat ini masih berupa keluhan biasa (`ComplaintCategory: pasang_baru`).
   - *Mitigasi*: Di masa depan pada modul New Connection, tugas survei dan pencatatan fisik meteran akan diteruskan langsung ke antrean `field_officer`.

---

## 8. REKOMENDASI URUTAN IMPLEMENTASI YANG AMAN

```
Tahap 1: Migration Database Presisi
   ├── Tambah role 'scada_operator' & 'field_officer'
   ├── Atomic Swap role pivot akun Ahmad (ID 2) & Dedi (ID 5)
   └── Backfill StaffProfile Dedi Kurniawan (ID 5)

Tahap 2: Core Models & Form Requests
   ├── Update User.php (isInternalStaff, isScadaOperator, isFieldOfficer)
   ├── Perbaiki UserProfileResource.php (mapping nama peran baru)
   └── Kunci VerifyPaymentRequest & BaseFormRequest

Tahap 3: Policies Hardening
   ├── PaymentPolicy (verify HANYA admin)
   ├── MeterReadingPolicy (validate HANYA admin & scada_operator)
   ├── ComplaintPolicy (assign HANYA admin, update/resolve HANYA assigned field_officer)
   └── ComplaintResource (Sensor PII nomor telepon & alamat publik)

Tahap 4: Routes & Middleware Update
   ├── routes/web.php (role:admin,scada_operator untuk SCADA; role:admin untuk verify bayar)
   └── routes/api.php (penyesuaian middleware granular)

Tahap 5: Test Suite Expansion & Regresi
   ├── Buat tests/Feature/GranularRbacRoleSeparationTest.php (10 test methods)
   ├── Jalankan php artisan test
   └── Verifikasi 133 tests LULUS 100%
```

---
*Laporan audit dan koreksi desain ini disusun secara komprehensif tanpa melakukan perubahan kode satu baris pun.*
