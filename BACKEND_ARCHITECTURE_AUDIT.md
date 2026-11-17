# AUDIT KELENGKAPAN ARSITEKTUR BACKEND LARAVEL 12
## Project: Smart Water Kota Payakumbuh
### Perumda Air Minum Tirta Sago Kota Payakumbuh

---

## 1. Tabel Evaluasi Komponen Arsitektur Backend

| Komponen | File/Folder | Kondisi | Kekurangan | Rekomendasi |
|---|---|---|---|---|
| **1. Migrations** | `database/migrations/` (11 file) | **COMPLETE** | Tidak ada kekurangan mendasar; seluruh 28 tabel, 24 foreign key, dan composite index sudah terpetakan rapi di MySQL 8.4. | Pertahankan migrasi bertahap, hindari perintah destruktif (`migrate:fresh`). |
| **2. Eloquent Models** | `app/Models/` (19 file) | **COMPLETE** | Model sudah lengkap dan mendefinisikan relasi (`belongsTo`, `hasMany`, dll.) serta `$fillable`. Namun, beberapa logic query scope (misal: filter status aktif atau filter per kecamatan) belum diekstraksi ke *Query Scopes*. | Tambahkan *Local Scopes* pada Model (seperti `scopeActive()`, `scopePendingValidation()`) untuk memperjelas query di service/controller. |
| **3. Controllers** | `app/Http/Controllers/` (7 file) | **COMPLETE** | Berhasil direfaktor menjadi **Thin Controller**. Validasi input didelegasikan ke Form Requests, alur bisnis didelegasikan ke Services, dan serialisasi JSON didelegasikan ke API Resources. | Pertahankan pola Thin Controller saat menambahkan endpoint baru di masa mendatang. |
| **4. Form Requests** | `app/Http/Requests/` (15 file) | **COMPLETE** | Layer Form Request telah diimplementasikan lengkap (Task 1). Validasi input, tipe data, regex, ukuran gambar, dan otorisasi terisolasi dari Controller. | Pertahankan penggunaan Form Request untuk setiap endpoint HTTP yang menerima payload input. |
| **5. Services** | `app/Services/` (6 file) | **COMPLETE** | Layer Services telah diimplementasikan lengkap (Task 2) mencakup `ComplaintService`, `MeterReadingService`, `AcademyService`, `ChallengeService`, `NotificationService`, dan `AuthService`. | Seluruh alur transaksi database, komputasi tarif/XP, dan logic murni terpusat di Services. |
| **6. API Resources** | `app/Http/Resources/` (16 file) | **COMPLETE** | Layer API Resources telah diimplementasikan lengkap (Task 3) mencakup 16 file Resource. Mengisolasi field sensitif, memformat tanggal WIB, menstandarkan casting numerik, dan menjaga 100% kontrak JSON frontend. | Gunakan API Resources untuk setiap serialisasi Model Eloquent menjadi JSON response. |
| **7. Middleware** | `app/Http/Middleware/` & `bootstrap/app.php` | **COMPLETE** | `RoleMiddleware` telah disempurnakan (Task 4) dengan dukungan penuh untuk respon JSON API (HTTP 401 & 403), pengalihan web berbasis role, dan diterapkan secara deklaratif pada grup rute operasional di `routes/web.php` dan `routes/api.php`. Dilengkapi proteksi anti-IDOR dan data ownership guard. | Pertahankan middleware role untuk setiap penambahan endpoint yang membutuhkan hak akses khusus. |
| **8. API Routes** | `routes/api.php` | **PARTIAL** | Rute auth, notifikasi, dan profil masih ditulis menggunakan *inline closure* di dalam `routes/api.php`, bukan diarahkan ke controller tersendiri. | Pindahkan seluruh rute closure ke Controller khusus (`AuthController`, `NotificationController`, `CustomerProfileController`) agar file rute tetap bersih dan deklaratif. |
| **9. Seeders** | `database/seeders/` (6 file) | **COMPLETE** | Seluruh data awal (Role, Admin, Petugas, Pelanggan, Kategori Tiket, Kurikulum Akademi, dan Tantangan) sudah tersedia dan terangkai di `DatabaseSeeder.php`. | Pertahankan dan pastikan selalu *idempotent* menggunakan `firstOrCreate()` atau `updateOrCreate()`. |
| **10. Automated Tests** | `tests/Feature/` & `tests/Unit/` | **COMPLETE** | Testing suite telah diperluas mencakup 62 automated tests di Laravel PHPUnit (`FormRequestValidationTest`, `ServicesUnitTest`, `ApiResourceTest`, `RbacSecurityTest`, `PolicyAuthorizationTest`, `SmartWaterBackendTest`) dengan 232 assertions, serta 47 skenario validasi database nyata MySQL 8.4 (`tests/full_mysql_validation.php`). Seluruhnya 100% PASS. | Pertahankan pengujian otomatis saat merilis atau mengubah fitur. |
| **11. Policies & Data Ownership** | `app/Policies/` (7 file) | **COMPLETE** | 7 Laravel Policies telah diimplementasikan (Task 5) mencakup `ComplaintPolicy`, `MeterReadingPolicy`, `CustomerProfilePolicy`, `NotificationPolicy`, `AcademyProgressPolicy`, `ChallengeParticipantPolicy`, dan `ChallengeSubmissionPolicy`. Mengatur isolasi wewenang antar-petugas (Staff Boundary), anti-IDOR, dan pencegahan eskalasi hak akses rute demo. | Pertahankan penegakan otorisasi level data via `$this->authorize()` atau `Gate::allows()` untuk entitas privat. |

---

## 2. Pemeriksaan Mendalam Aspek Arsitektur

### 1. Ketersediaan Folder & Berkas
- **Sudah Tersedia**: `database/migrations/`, `database/seeders/`, `app/Models/`, `app/Http/Controllers/`, `app/Services/` (baru 1 file), `app/Http/Middleware/`, `routes/api.php`, `tests/Feature/`.
- **Belum Tersedia (Missing)**:
  - `app/Http/Requests/`: Menyebabkan validasi HTTP tidak memiliki rumah terpisah.
  - `app/Http/Resources/`: Menyebabkan format serialisasi JSON tidak terstandarisasi.
  - Subfolder modular di dalam `app/Services/`: Seluruh logic transaksi belum terdistribusi merata.

### 2. Pemanfaatan Implementasi (Apakah Benar Digunakan?)
- Seluruh 19 Model dan 11 Migrasi benar-benar aktif digunakan oleh Controller dan berinteraksi langsung dengan database **MySQL 8.4**.
- `AuthService` benar-benar dipanggil di autentikasi web dan API.
- Namun, `RoleMiddleware` yang sudah terdaftar di `bootstrap/app.php` masih jarang dipanggil pada route level, melainkan baru diperiksa secara imperatif di dalam controller method via `$user->hasRole()`.

### 3. Pemisahan Tanggung Jawab Antar-Layer (Separation of Concerns)
- **Kondisi Saat Ini**: Terjadi tumpang tindih tanggung jawab. Controller menangani:
  1. *HTTP Request Handling* (ekstraksi payload JSON dan multipart).
  2. *Data Validation* (validasi rule manual & `Validator::make`).
  3. *Business Logic & File Processing* (decode base64 gambar, penyimpanan fisik ke disk, generate ticket number, perhitungan saldo poin).
  4. *Database Transaction Orchestration* (`DB::transaction`).
  5. *Response Formatting* (perakitan manual array associative).
- **Kondisi Ideal**:
  - **FormRequest**: Menangani validasi dan otorisasi masukan.
  - **Controller**: Hanya menerima request yang sudah valid, memanggil Service, dan mengembalikan Resource.
  - **Service**: Menjalankan business logic, manipulasi file, event notifikasi, dan transaksi database.
  - **Eloquent Model**: Menangani skema, relasi data, mutator/casting, dan query scope.
  - **API Resource**: Memformat output JSON yang aman dan konsisten.

### 4. Penumpukan Business Logic di Controller (Fat Controller)
- **Contoh Kasus 1 (`ComplaintController::store`)**:
  Method ini memuat 110+ baris kode yang mencakup decoding data string base64 menjadi binary JPEG, manipulasi path penyimpanan filesystem, penomoran urut tiket tahunan, pembukaan transaksi database, pencatatan attachment fisik, dan seeding histori pertama. Seluruh alur ini seharusnya berada di dalam `ComplaintService::createComplaint()`.
- **Contoh Kasus 2 (`MeterReadingController::apiValidate`)**:
  Validasi stand meter menangani verifikasi role, pembacaan dial meter lama vs baru, kalkulasi konsumsi air, update saldo meter di `customer_profiles`, pencatatan histori validator, dan trigger pembuatan entitas `Notification`. Alur ini idealnya berada di `MeterReadingService::approveReading()`.

### 5. Validasi Input di Controller
- Masih ditemukan pola:
  ```php
  $raw = $request->json()->all();
  $id = trim((string)($raw['id'] ?? $request->input('id', '')));
  if (!$id) {
      return response()->json(['error' => 'ID wajib diisi.'], 422);
  }
  ```
- Pola manual ini rentan terhadap *type casting bug*, mengabaikan validasi format data (misal regex nomor telepon atau validitas integer), dan membuat kode controller menjadi kotor.
- Solusi terbaik adalah memindahkan seluruh aturan ke kelas Form Request terdedikasi (`app/Http/Requests/*`).

### 6. Konsistensi Format Response JSON
- Saat ini format response API memiliki beberapa variasi format:
  - Sukses:
    - `{ "ok": true, "ticket": { ... } }`
    - `{ "ok": true, "message": "...", "data": { ... } }`
    - `{ "ok": true, "notifications": [ ... ] }`
  - Error:
    - `{ "error": "Pesan error" }` dengan status HTTP 422 / 401
    - `{ "ok": false, "message": "Pesan error" }` dengan status HTTP 404
    - `{ "ok": false, "error": "Pesan error" }` dengan status HTTP 403
- Ketidakkonsistenan ini menyulitkan sisi frontend client dalam membuat *interceptor* response error standar.

### 7. Kode Duplikat dan Komponen Legacy
- **Legacy API Scripts (`app/LegacyApi/*.php`)**:
  Tersedia 7 file skrip legacy (`complaints.php`, `auth.php`, `academy.php`, dll.) yang berfungsi sebagai *backward-compatibility shim* untuk JavaScript lama. Kodenya sebagian besar mengulang query database yang mirip dengan apa yang dilakukan Controller.
- **Rute Closure di `routes/api.php`**:
  Logika autentikasi login/register/logout diulang dua kali: satu kali di `SmartWaterPortalController` (untuk web form) dan satu kali sebagai anonymous closure di `routes/api.php`. Keduanya harus disatukan dalam konsumsi `AuthService`.

---

## 3. Rekomendasi Struktur Folder Backend Laravel 12 yang Ideal

Berikut adalah rancangan arsitektur modular (*Layered Architecture*) yang ideal dan bersih untuk Laravel 12 pada project **Smart Water Kota Payakumbuh**:

```text
app/
├── Http/
│   ├── Controllers/
│   │   ├── Api/
│   │   │   ├── AuthController.php
│   │   │   ├── ComplaintController.php
│   │   │   ├── MeterReadingController.php
│   │   │   ├── AcademyController.php
│   │   │   ├── ChallengeController.php
│   │   │   ├── NotificationController.php
│   │   │   └── ProfileController.php
│   │   ├── Web/
│   │   │   ├── SmartWaterPortalController.php
│   │   │   └── AdminDashboardController.php
│   │   └── Controller.php
│   ├── Requests/
│   │   ├── Auth/
│   │   │   ├── LoginRequest.php
│   │   │   └── RegisterCustomerRequest.php
│   │   ├── Complaint/
│   │   │   ├── StoreComplaintRequest.php
│   │   │   ├── AssignComplaintRequest.php
│   │   │   └── ResolveComplaintRequest.php
│   │   ├── Meter/
│   │   │   ├── StoreMeterReadingRequest.php
│   │   │   └── ValidateMeterReadingRequest.php
│   │   └── Profile/
│   │       └── UpdateProfileRequest.php
│   ├── Resources/
│   │   ├── ComplaintResource.php
│   │   ├── MeterReadingResource.php
│   │   ├── CourseResource.php
│   │   ├── ChallengeResource.php
│   │   ├── NotificationResource.php
│   │   └── UserProfileResource.php
│   └── Middleware/
│       └── RoleMiddleware.php
├── Models/
│   ├── User.php
│   ├── Complaint.php
│   ├── MeterReading.php
│   └── ... (seluruh 19 model yang ada)
├── Services/
│   ├── AuthService.php
│   ├── ComplaintService.php
│   ├── MeterReadingService.php
│   ├── AcademyService.php
│   ├── ChallengeService.php
│   └── NotificationService.php
└── LegacyApi/ (dipertahankan sementara sebagai backward compatibility shim)
```

---

## 4. Standarisasi Format Response JSON yang Direkomendasikan

Seluruh endpoint REST API dianjurkan mengikuti struktur seragam:

### Respons Sukses (HTTP 200 / 201):
```json
{
  "success": true,
  "message": "Operasi berhasil dijalankan.",
  "data": {
    "id": "TK-PYK-2026-0001",
    ...
  },
  "meta": null
}
```

### Respons Gagal (HTTP 400 / 401 / 403 / 404 / 422 / 500):
```json
{
  "success": false,
  "message": "Validasi masukan gagal atau akses ditolak.",
  "errors": {
    "meter_number": ["Nomor meteran air wajib diisi."]
  }
}
```

---

## 5. Ringkasan & Langkah Selanjutnya

Audit ini membuktikan bahwa:
1. **Pondasi Data dan Fungsionalitas**: Sangat solid (100% lulus pada 47 skenario MySQL dan 9 feature tests). Seluruh fitur operasional dari login hingga lapor meter AI sudah berjalan di MySQL.
2. **Kerapian Arsitektur**: Berada pada level fungsional-pragmatis namun belum mencapai level *Enterprise Clean Architecture*. Komponen utama yang perlu dilengkapi di masa depan adalah **Form Requests**, **Services pendamping**, **API Resources**, serta refactoring Controller menjadi **Thin Controller**.
3. **Status Kode**: Pada tahap audit ini, **tidak ada kode backend, database, maupun frontend yang diubah**, sesuai instruksi pengguna.
