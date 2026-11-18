# DOKUMEN PERSIAPAN & ARSITEKTUR IMPLEMENTASI BILLING & PAYMENT
## SMART WATER KOTA PAYAKUMBUH — LARAVEL 12 & MYSQL 8.4 (LARAGON ENVIRONMENT)

---

## 1. PENDAHULUAN & KETENTUAN UTAMA

Dokumen ini merupakan panduan arsitektur dan spesifikasi teknis untuk mentransformasikan modul **Billing & Payment** Smart Water Kota Payakumbuh dari simulasi sisi klien (*client-side localStorage* via `swc-store.js`) menjadi subsistem backend transaksional penuh berbasis **Laravel 12**, **MySQL 8.4**, dan **Eloquent ORM**.

### Ketentuan Mutlak Implementasi:
1. **Tanpa Payment Gateway / Midtrans**: Integrasi payment gateway pihak ketiga (seperti Midtrans) **TIDAK diimplementasikan terlebih dahulu**. Integrasi payment gateway baru akan dilakukan setelah ada nota kesepahaman dan persetujuan resmi dari pihak PDAM.
2. **Fokus Pembayaran Manual & Verifikasi Admin**: Untuk tahap awal, seluruh transaksi pembayaran menggunakan saluran manual (Transfer Bank / Bank Nagari / Loket) dengan mekanisme unggah bukti transfer dan **wajib diverifikasi secara manual oleh Admin/Petugas PDAM**.
3. **Nominal & Tarif Bersifat Dinamis (Bukan Ketentuan Resmi)**: Seluruh angka seperti tarif progresif Rp2.500–Rp6.000, estimasi flat Rp7.800, beban administrasi Rp2.600, jatuh tempo tanggal 20, maupun denda Rp10.000 **ditegaskan sebagai data sampel/simulasi**, bukan ketentuan resmi PDAM. Aturan resmi masih menunggu konfirmasi tertulis dari mitra PDAM Tirta Sago.
4. **Struktur Database Tarif Dinamis**: Struktur tabel master tarif dirancang dinamis (multi-tier fleksibel) sehingga parameter blok konsumsi, tarif per $m^3$, beban administrasi, pemeliharaan, denda, dan jatuh tempo dapat disesuaikan sewaktu-waktu tanpa mengubah struktur kode atau database.
5. **Kondisi Perubahan Status Tagihan**: Status tagihan (`bills.status`) **HANYA BOLEH BERUBAH MENJADI `PAID`** setelah pembayaran terkait benar-benar diverifikasi (`VERIFIED`) oleh Admin PDAM. Pembayaran berstatus `PENDING` tidak boleh mengubah tagihan menjadi `PAID`. Jika pembayaran ditolak (`REJECTED`), tagihan kembali berstatus `UNPAID`.
6. **Arsitektur 6 (Enam) Tabel**: Subsistem penagihan dan pembayaran dirancang secara komprehensif menggunakan **6 (enam) tabel** relasional di database MySQL.
7. **Lingkungan Server Aktif**: Project dijalankan pada lingkungan **Laragon aktif** (`C:\laragon\www\smart-water` yang terhubung via Windows Directory Junction ke codebase), menggunakan **PHP 8.3** (`C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe`), **MySQL 8.4** (`C:\laragon\bin\mysql\mysql-8.4.3-winx64`), dan **Apache 2.4** pada port 80.
8. **Batasan UI & Frontend**: Tidak mengubah antarmuka pengguna (UI), Blade views, CSS, Tailwind, JavaScript, navbar, footer, layout, maupun fitur AI Chatbot. Tidak menjalankan perintah destruktif (`migrate:fresh`, `db:wipe`, reset database).

---

## 2. TAHAP 1: AUDIT KHUSUS BILLING & STATUS SISTEM SAAT INI

### 2.1. Audit `MeterReadingService` & Validasi Meter
- Validasi laporan meter air dilakukan melalui method `validateReading()` di `app/Services/MeterReadingService.php`.
- Ketika status laporan meter disetujui (berubah menjadi `'Terverifikasi AI'` atau `'Terverifikasi'`), nilai meter pelanggan di `customer_profiles.current_meter_value` diperbarui.
- Namun, **belum ada record tagihan (`bills`) yang dibuat ke database**, karena tabel penagihan belum diimplementasikan.
- Estimasi biaya meter air pada `estimateCost()` saat ini menggunakan formula flat simulasi:
  $$\text{Estimasi Biaya} = (\text{Pemakaian } m^3 \times 7.800) + 2.600$$

### 2.2. Struktur Tabel `meter_readings`
- Kolom yang tersedia: `id`, `report_number`, `user_id`, `meter_number`, `reading`, `previous_reading`, `consumption`, `photo_path`, `reading_date`, `status`, `notes`, `estimated_cost`, `validated_by`, `validated_at`.
- *Analisis*: Belum memiliki relasi langsung (foreign key atau penanda relasi) ke tagihan yang diterbitkan, serta belum ada kolom periode penagihan eksplisit (`billing_period`).

### 2.3. Struktur `customer_profiles`
- Kolom: `user_id`, `customer_id` (nomor sambungan unik), `meter_number`, `tariff_category` (default `'2A - Rumah Tangga Mandiri'`), `wallet_balance`, `points`, `address`.
- Nilai `tariff_category` saat ini berupa string statis dan belum terhubung ke master tabel tarif dinamis.

### 2.4. Simulasi Frontend `swc-store.js`
- Di frontend (`public/js/swc-store.js`), tagihan disimulasikan melalui `STORAGE_KEYS.BILLS = 'swc_bills_v2'`:
  - Nomor invoice contoh: `INV-PYK-2025-04-1000`.
  - Periode: `April 2025`.
  - Pemakaian: $19.0\text{ m}^3$.
  - Rincian biaya: Pemakaian Air (Rp 148.200), Beban Tetap (Rp 2.600), Pemeliharaan (Rp 0), Denda (Rp 0), Total Tagihan (Rp 150.800).
  - Pilihan metode: Transfer Bank Nagari, BCA, Mandiri.
  - Status tagihan: `UNPAID`, `PENDING_VERIFICATION`, `PAID`, `CANCELLED`.

### 2.5. Audit Halaman Blade
- Halaman yang sudah siap menerima integrasi backend:
  - `resources/views/billing/index.blade.php`: Daftar tagihan aktif dan riwayat.
  - `resources/views/billing/detail.blade.php`: Rincian konsumsi $m^3$, batas blok tarif, dan rincian biaya.
  - `resources/views/billing/history.blade.php`: Riwayat tagihan masa lalu.
  - `resources/views/billing/payment-method.blade.php`: Pemilihan metode transfer.
  - `resources/views/billing/payment-instruction.blade.php`: Panduan transfer dan unggah struk bukti bayar.
  - `resources/views/billing/payment-success.blade.php`: Resi / bukti lunas digital.

---

## 3. TAHAP 2: RANCANGAN DATABASE LENGKAP (6 TABEL RELASIONAL)

Untuk menjamin fleksibilitas tarif dinamis, kepatuhan audit keuangan, dan keandalan data transaksi, dirancang **6 (enam) tabel relasional**:

```
+-------------------------------------------------------------------------------+
|                               SKEMA 6 TABEL                                   |
+-------------------------------------------------------------------------------+
|                                                                               |
|  [ 1. tariffs ] <----+                                                        |
|         |            |                                                        |
|         v            | (tariff_category / snapshot)                           |
|  [ 2. tariff_tiers ] |                                                        |
|                      |                                                        |
|  [ meter_readings ]  |                                                        |
|         |            |                                                        |
|         v            |                                                        |
|  [ 3. bills ] -------+                                                        |
|         |                                                                     |
|         +-----------------------+                                             |
|         | (1:N)                 | (1:N)                                       |
|         v                       v                                             |
|  [ 4. bill_details ]     [ 5. payments ]                                      |
|                                 |                                             |
|                                 v (1:N)                                       |
|                          [ 6. payment_histories ]                             |
|                                                                               |
+-------------------------------------------------------------------------------+
```

### Tabel 1: `tariffs` (Master Golongan Tarif Dinamis)
Menyimpan konfigurasi umum golongan pelanggan. Kolom dirancang dinamis agar angka tarif dan beban dapat disesuaikan tanpa migration baru:
```sql
CREATE TABLE tariffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(30) UNIQUE NOT NULL,    -- Contoh: '2A', '1A', '3B'
    category_name VARCHAR(100) NOT NULL,          -- Contoh: 'Rumah Tangga Mandiri'
    description TEXT NULL,
    admin_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,       -- Beban tetap / administrasi bulanan (dinamis)
    maintenance_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00, -- Biaya pemeliharaan meter (dinamis)
    penalty_flat DECIMAL(12, 2) NOT NULL DEFAULT 0.00,    -- Denda keterlambatan nominal flat (dinamis)
    penalty_percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00, -- Denda persentase (opsional)
    due_day_of_month INT UNSIGNED NOT NULL DEFAULT 20,    -- Tanggal jatuh tempo default setiap bulan (dinamis)
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### Tabel 2: `tariff_tiers` (Blok Pemakaian Progresif Dinamis)
Mendukung skema blok pemakaian berjenjang (multi-tier) tanpa batas jumlah blok:
```sql
CREATE TABLE tariff_tiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tariff_id BIGINT UNSIGNED NOT NULL,
    tier_number INT UNSIGNED NOT NULL,          -- Nomor urut blok: 1, 2, 3, 4, dst.
    tier_name VARCHAR(100) NOT NULL,            -- e.g. 'Blok I (Kebutuhan Pokok)', 'Blok II', dst.
    min_consumption INT UNSIGNED NOT NULL,      -- Batas bawah m3 (e.g. 0, 11, 21, 31)
    max_consumption INT UNSIGNED NULL,          -- Batas atas m3 (NULL jika blok tertinggi tanpa batas)
    rate_per_m3 DECIMAL(12, 2) NOT NULL,        -- Tarif per m3 pada blok ini (dinamis)
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id) ON DELETE CASCADE,
    INDEX idx_tier_lookup (tariff_id, min_consumption)
);
```

### Tabel 3: `bills` (Tagihan Pelanggan)
Mencatat tagihan resmi yang diterbitkan untuk setiap laporan meter yang telah disetujui:
```sql
CREATE TABLE bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(50) UNIQUE NOT NULL,      -- Format: 'INV-PYK-YYYYMM-XXXX'
    meter_reading_id BIGINT UNSIGNED UNIQUE NOT NULL, -- Relasi 1-to-1 ketat ke meter_readings
    user_id BIGINT UNSIGNED NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    meter_number VARCHAR(50) NOT NULL,
    billing_period VARCHAR(7) NOT NULL,           -- Format: 'YYYY-MM' (e.g. '2026-11')
    period_label VARCHAR(50) NOT NULL,            -- e.g. 'November 2026'
    tariff_category VARCHAR(100) NOT NULL,
    previous_reading DECIMAL(10, 2) NOT NULL,
    current_reading DECIMAL(10, 2) NOT NULL,
    consumption DECIMAL(10, 2) NOT NULL,
    water_charge DECIMAL(12, 2) NOT NULL,         -- Total biaya air m3 murni
    admin_fee DECIMAL(12, 2) NOT NULL,            -- Biaya administrasi & beban tetap
    maintenance_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    penalty_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12, 2) NOT NULL,         -- Total tagihan akhir
    due_date DATE NOT NULL,
    status ENUM('UNPAID', 'PENDING_VERIFICATION', 'PAID', 'CANCELLED') NOT NULL DEFAULT 'UNPAID',
    tariff_snapshot JSON NULL,                    -- Snapshot aturan tarif saat tagihan diterbitkan (Audit Trail)
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (meter_reading_id) REFERENCES meter_readings(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_bill_user_status (user_id, status),
    INDEX idx_bill_period (billing_period),
    INDEX idx_bill_due_date (due_date)
);
```

### Tabel 4: `bill_details` (Breakdown Baris Tagihan)
Menyimpan rincian per baris tagihan (rincian blok konsumsi, biaya administrasi, biaya pemeliharaan, denda):
```sql
CREATE TABLE bill_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('WATER_USAGE', 'ADMIN_FEE', 'MAINTENANCE_FEE', 'PENALTY', 'DISCOUNT') NOT NULL,
    description VARCHAR(255) NOT NULL,           -- e.g. 'Pemakaian Air Blok I (0-10 m3)'
    volume DECIMAL(10, 2) NOT NULL DEFAULT 0.00, -- e.g. 10.00
    rate DECIMAL(12, 2) NOT NULL DEFAULT 0.00,   -- e.g. 2500.00
    subtotal DECIMAL(12, 2) NOT NULL,            -- e.g. 25000.00
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);
```

### Tabel 5: `payments` (Transaksi Pembayaran Manual)
Mencatat konfirmasi pembayaran pelanggan melalui transfer manual:
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(50) UNIQUE NOT NULL,  -- Format: 'PAY-PYK-YYYYMM-XXXX'
    bill_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,         -- 'manual_transfer', 'bank_nagari', 'loket_pdam'
    bank_name VARCHAR(100) NULL,                 -- e.g. 'Bank Nagari', 'Bank Mandiri', 'BCA'
    sender_name VARCHAR(150) NULL,               -- Nama pengirim rekening
    sender_account_number VARCHAR(50) NULL,      -- Nomor rekening pengirim
    proof_photo_path VARCHAR(255) NOT NULL,      -- File bukti transfer/resi pembayaran (wajib)
    reference_number VARCHAR(100) NULL,          -- Nomor referensi struk bank
    status ENUM('PENDING', 'VERIFIED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    verified_by BIGINT UNSIGNED NULL,            -- ID Admin/Petugas PDAM yang memverifikasi
    verified_at TIMESTAMP NULL,
    rejection_reason VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE RESTRICT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_payment_bill (bill_id),
    INDEX idx_payment_status (status)
);
```

### Tabel 6: `payment_histories` (Log Audit Status Pembayaran)
Mencatat jejak riwayat verifikasi dari status awal hingga verifikasi atau penolakan oleh admin:
```sql
CREATE TABLE payment_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    actor_role VARCHAR(50) NOT NULL,             -- 'customer', 'staff', 'admin', 'system'
    action_note TEXT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 4. TAHAP 3: ATURAN BISNIS & ALUR PEMBAYARAN MANUAL

### 4.1. Alur Penerbitan Tagihan (Bill Generation)
1. Pelanggan melaporkan meter air melalui menu Lapor Meter Mandiri.
2. Petugas/Admin memverifikasi laporan di dashboard atau melalui validasi AI (`status = 'Terverifikasi'`).
3. Begitu status laporan menjadi valid, sistem memanggil `BillingService::generateBillFromReading($reading)`:
   - Mengambil profil tarif pelanggan (`tariff_category`) dari tabel `tariffs` dan relasi `tariff_tiers`.
   - Menghitung rincian pemakaian air berdasarkan blok konsumsi dinamis.
   - Menyimpan seluruh rincian pemakaian ke `bill_details`.
   - Mengambil snapshot konfigurasi tarif saat itu ke kolom `tariff_snapshot` (format JSON).
   - Menghitung tanggal jatuh tempo dinamis (`due_date` = `due_day_of_month` pada bulan berikutnya).
   - Menyimpan record `bills` dengan status awal `'UNPAID'`.
   - Mengirim notifikasi ke pelanggan melalui tabel `notifications`.

### 4.2. Alur Pembayaran Manual & Syarat Mutlak Status `PAID`
1. **Pengajuan Pembayaran oleh Pelanggan**:
   - Pelanggan melihat tagihan berstatus `'UNPAID'` pada menu Tagihan Air.
   - Pelanggan memilih metode Transfer Manual (misal Bank Nagari / Transfer Bank).
   - Pelanggan mentransfer dana sesuai nominal tagihan, lalu mengunggah foto bukti transfer melalui form pembayaran (`StorePaymentRequest`).
   - Sistem membuat record `payments` baru dengan status **`'PENDING'`**.
   - Status tagihan pada `bills` diperbarui menjadi **`'PENDING_VERIFICATION'`** (bukan `PAID`).
   - Catatan riwayat ditambahkan ke `payment_histories` (`new_status = 'PENDING'`).

2. **Verifikasi oleh Admin PDAM**:
   - Admin membuka menu Administrasi Pembayaran pada dashboard internal.
   - Admin memeriksa kecocokan bukti transfer dengan mutasi bank/laporan teller.
   - **Jika Pembayaran Valid (Disetujui)**:
     - Admin mengeksekusi aksi verifikasi melalui `PaymentService::verifyPayment($payment, $adminId)`.
     - Status pembayaran pada `payments` berubah menjadi **`'VERIFIED'`** dengan `verified_by = $adminId` dan `verified_at = now()`.
     - **HANYA PADA TITIK INI**, status tagihan pada `bills` diubah menjadi **`'PAID'`** dengan `paid_at = now()`.
     - Log ditambahkan ke `payment_histories` (`new_status = 'VERIFIED'`).
     - Notifikasi konfirmasi dikirimkan ke pelanggan ("Pembayaran Tagihan Air Anda Telah Dikonfirmasi Lunas").
   - **Jika Pembayaran Tidak Valid (Ditolak)**:
     - Admin mengeksekusi aksi penolakan melalui `PaymentService::rejectPayment($payment, $adminId, $reason)`.
     - Status pembayaran pada `payments` berubah menjadi **`'REJECTED'`**.
     - Status tagihan pada `bills` dikembalikan menjadi **`'UNPAID'`** agar pelanggan dapat mengajukan pembayaran ulang dengan bukti yang sah.
     - Notifikasi penolakan beserta alasannya dikirim ke pelanggan.

3. **Integritas Relasi & Anti-Duplikasi (Idempotensi)**:
   - Kolom `bills.meter_reading_id` diproteksi oleh constraint `UNIQUE`.
   - Satu laporan meter yang disetujui hanya boleh men-generate tepat 1 tagihan.
   - Tagihan yang sudah memiliki status `PAID` tidak dapat diajukan pembayaran ulang.

4. **Keamanan Otorisasi (Anti-IDOR)**:
   - Pelanggan hanya dapat melihat, mengakses detail, dan mengajukan pembayaran untuk tagihan miliknya sendiri (`user_id === auth()->id()`).
   - Penegakan otorisasi menggunakan `BillPolicy` dan `PaymentPolicy`.

---

## 5. TAHAP 4: KOMPONEN BACKEND YANG DISIAPKAN

### 5.1. File Database Migrations
1. `database/migrations/2026_11_18_000001_create_tariffs_and_tiers_tables.php` (tabel `tariffs` dan `tariff_tiers`)
2. `database/migrations/2026_11_18_000002_create_bills_and_bill_details_tables.php` (tabel `bills` dan `bill_details`)
3. `database/migrations/2026_11_18_000003_create_payments_and_histories_tables.php` (tabel `payments` dan `payment_histories`)
4. `database/seeders/DynamicTariffSeeder.php` (Inisialisasi sampel golongan tarif dinamis yang siap disesuaikan)

### 5.2. Eloquent Models
1. `app/Models/Tariff.php` (`hasMany` `TariffTier`)
2. `app/Models/TariffTier.php` (`belongsTo` `Tariff`)
3. `app/Models/Bill.php` (`belongsTo` `User`, `MeterReading`; `hasMany` `BillDetail`, `Payment`)
4. `app/Models/BillDetail.php` (`belongsTo` `Bill`)
5. `app/Models/Payment.php` (`belongsTo` `Bill`, `User`, `verifiedBy`; `hasMany` `PaymentHistory`)
6. `app/Models/PaymentHistory.php` (`belongsTo` `Payment`, `actor`)

### 5.3. Services Layer
1. `app/Services/BillingService.php`:
   - `generateBillFromReading(MeterReading $reading): Bill`
   - `calculateConsumptionCharges(float $consumption, Tariff $tariff): array`
   - `getUnpaidBillForUser(int $userId): ?Bill`
   - `getBillHistoryForUser(int $userId, int $perPage = 10)`
2. `app/Services/PaymentService.php`:
   - `submitManualPayment(Bill $bill, int $userId, array $data, $proofFile): Payment`
   - `verifyPayment(Payment $payment, int $verifierId, ?string $notes = null): bool`
   - `rejectPayment(Payment $payment, int $verifierId, string $reason): bool`

### 5.4. Form Requests & API Resources
- Form Requests:
  - `app/Http/Requests/StoreManualPaymentRequest.php` (Validasi input bukti bayar foto/PDF, bank tujuan, nama pengirim)
  - `app/Http/Requests/VerifyPaymentRequest.php` (Validasi verifikasi/penolakan oleh Admin)
- API Resources:
  - `app/Http/Resources/BillResource.php`
  - `app/Http/Resources/BillDetailResource.php`
  - `app/Http/Resources/PaymentResource.php`
  - `app/Http/Resources/TariffResource.php`

### 5.5. Policies
- `app/Policies/BillPolicy.php` (`viewAny`, `view`, `pay`)
- `app/Policies/PaymentPolicy.php` (`view`, `create`, `verify`)

### 5.6. Feature Tests
- `tests/Feature/BillingPaymentTest.php` (Menguji alur end-to-end: approval meter $\to$ pembuatan tagihan $\to$ pembayaran manual $\to$ verifikasi admin $\to$ status `PAID`).
