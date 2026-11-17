# DOKUMEN PERSIAPAN & ARSITEKTUR IMPLEMENTASI BILLING & PAYMENT
## SMART WATER KOTA PAYAKUMBUH — LARAVEL 12 & MYSQL 8.4

---

## 1. PENDAHULUAN & TUJUAN
Dokumen ini disusun sebagai blueprint teknis untuk mentransformasikan modul **Billing & Payment** Smart Water Kota Payakumbuh dari simulasi sisi klien (*client-side localStorage* via `swc-store.js`) menjadi subsistem backend transaksional penuh berbasis **Laravel 12**, **MySQL 8.4**, dan **Eloquent ORM**.

Alur utama sistem:
$$\text{Approved Meter Reading} \longrightarrow \text{Consumption Calculation} \longrightarrow \text{Bill Generation} \longrightarrow \text{Customer Billing} \longrightarrow \text{Payment Submission} \longrightarrow \text{Payment Verification} \longrightarrow \text{Paid}$$

---

## 2. TAHAP 1: HASIL AUDIT KHUSUS BILLING

### 2.1. Audit `MeterReadingService` & Alur Validasi
- **Kondisi Saat Ini**:
  - Validasi laporan meter air dilakukan melalui method `validateReading()` di `app/Services/MeterReadingService.php`.
  - Ketika laporan meter air diverifikasi (status diubah menjadi `'Terverifikasi AI'` atau `'Terverifikasi'`), nilai meter pelanggan di `customer_profiles.current_meter_value` diperbarui.
  - Namun, **belum ada pembuatan record tagihan (bill)** ke database, karena tabel `bills` belum ada di schema database.
  - Rumus biaya pada laporan meter (`estimateCost()`) saat ini menggunakan formula flat sederhana:
    $$\text{Biaya} = (\text{Pemakaian} \times 7.800) + 2.600$$

### 2.2. Struktur Tabel `meter_readings`
- Tabel `meter_readings` memiliki kolom:
  - `id`, `report_number`, `user_id`, `meter_number`, `reading`, `previous_reading`, `consumption`, `photo_path`, `reading_date`, `status`, `notes`, `estimated_cost`, `validated_by`, `validated_at`.
- *Catatan Kritis*: Belum ada kolom penanda eksplisit `billing_period` (misal format `YYYY-MM`), serta relasi langsung 1-ke-1 ke tabel tagihan `bills`.

### 2.3. Struktur `customer_profiles` & Data Golongan Pelanggan
- Memiliki kolom:
  - `user_id`, `customer_id` (nomor sambungan unik, contoh `CUST-001` / `527101`), `meter_number`, `tariff_category` (default `'2A - Rumah Tangga Mandiri'`), `wallet_balance`, `points`, `address`.
- Belum ada tabel master tarif yang mendefinisikan batas blok, tarif per $m^3$, abonemen/administrasi, dan pemeliharaan untuk setiap golongan pelanggan (`tariff_category`).

### 2.4. Audit Simulasi `swc-store.js`
Di frontend (`public/js/swc-store.js`), tagihan disimulasikan menggunakan `STORAGE_KEYS.BILLS = 'swc_bills_v2'`:
- Format nomor tagihan: `INV-PYK-2025-04-1000`.
- Format rincian tagihan:
  - Pemakaian Air Bersih ($19.0\text{ m}^3$): Rp 148.200 (setara Rp 7.800/$\text{m}^3$).
  - Biaya Administrasi & Beban Tetap: Rp 2.600.
  - Biaya Pemeliharaan Meter: Rp 0.
  - Denda / Keterlambatan: Rp 0.
  - Total Tagihan: Rp 150.800.
- Metode pembayaran yang didukung:
  - `BCA Virtual Account`
  - `Mandiri Virtual Account`
  - `QRIS Bank Nagari`
  - `Transfer Manual / Teller Bank`
- Status tagihan: `UNPAID`, `PENDING_VERIFICATION`, `PAID`, `CANCELLED`.

### 2.5. Audit Halaman Blade & Routing Billing
- View yang telah tersedia:
  - `resources/views/billing/index.blade.php`: Ringkasan tagihan berjalan dan riwayat.
  - `resources/views/billing/detail.blade.php`: Rincian pemakaian, blok tarif, dan breakdown biaya.
  - `resources/views/billing/history.blade.php`: Arsip tagihan bulan-bulan sebelumnya.
  - `resources/views/billing/payment-method.blade.php`: Pilihan saluran pembayaran (VA, QRIS, Transfer).
  - `resources/views/billing/payment-instruction.blade.php`: Instruksi transfer & upload bukti bayar.
  - `resources/views/billing/payment-success.blade.php`: Tanda terima / resi pembayaran digital.
- Seluruh view saat ini dilayani oleh `SmartWaterPortalController` tanpa data Eloquent (hanya merender template statis yang kemudian diisi oleh JavaScript mock).
- **Temuan Database Tersembunyi**: Tidak ditemukan tabel tagihan/pembayaran tersembunyi lain di schema MySQL saat ini.

---

## 3. TAHAP 2: RANCANGAN DATABASE (SCHEMA DESIGN)

Untuk mengakomodasi alur tagihan resmi PDAM, fleksibilitas tarif progresif, dan audit keuangan yang akuntabel, dirancang 5 tabel terstruktur:

### 3.1. Tabel `tariffs` & `tariff_tiers`
Mendukung skema tarif progresif resmi PDAM Tirta Sago Payakumbuh tanpa *hardcoding*.

```sql
-- Tabel Master Golongan Tarif
CREATE TABLE tariffs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(30) UNIQUE NOT NULL, -- Contoh: '2A', '1A', '3B'
    category_name VARCHAR(100) NOT NULL,       -- Contoh: 'Rumah Tangga Mandiri'
    admin_fee DECIMAL(12, 2) NOT NULL DEFAULT 2600.00,
    maintenance_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    penalty_flat DECIMAL(12, 2) NOT NULL DEFAULT 10000.00, -- Denda flat per bulan terlambat
    penalty_percentage DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Tabel Blok Konsumsi Progresif
CREATE TABLE tariff_tiers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tariff_id BIGINT UNSIGNED NOT NULL,
    tier_number INT UNSIGNED NOT NULL, -- 1, 2, 3, 4
    tier_name VARCHAR(50) NOT NULL,   -- 'Blok I (0-10 m3)'
    min_consumption INT UNSIGNED NOT NULL, -- 0
    max_consumption INT UNSIGNED NULL,     -- 10 (NULL untuk tier teratas tak terbatas)
    rate_per_m3 DECIMAL(12, 2) NOT NULL,   -- e.g. 2500.00
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (tariff_id) REFERENCES tariffs(id) ON DELETE CASCADE,
    INDEX idx_tier_lookup (tariff_id, min_consumption)
);
```

### 3.2. Tabel `bills` (Tagihan Pelanggan)
```sql
CREATE TABLE bills (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_number VARCHAR(50) UNIQUE NOT NULL, -- 'INV-PYK-YYYY-MM-XXXX'
    meter_reading_id BIGINT UNSIGNED UNIQUE NOT NULL, -- Hubungan 1-ke-1 ketat
    user_id BIGINT UNSIGNED NOT NULL,
    customer_id VARCHAR(50) NOT NULL,
    meter_number VARCHAR(50) NOT NULL,
    billing_period VARCHAR(7) NOT NULL, -- 'YYYY-MM' (contoh: '2026-11')
    period_label VARCHAR(50) NOT NULL,   -- 'November 2026'
    tariff_category VARCHAR(100) NOT NULL,
    previous_reading DECIMAL(10, 2) NOT NULL,
    current_reading DECIMAL(10, 2) NOT NULL,
    consumption DECIMAL(10, 2) NOT NULL,
    water_charge DECIMAL(12, 2) NOT NULL, -- Total biaya m3 air
    admin_fee DECIMAL(12, 2) NOT NULL,    -- Biaya administrasi & beban tetap
    maintenance_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    penalty_fee DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12, 2) NOT NULL, -- Total tagihan yang harus dibayar
    due_date DATE NOT NULL,
    status ENUM('UNPAID', 'PENDING_VERIFICATION', 'PAID', 'CANCELLED') NOT NULL DEFAULT 'UNPAID',
    tariff_snapshot JSON NULL, -- Snapshot aturan tarif saat tagihan dibuat (audit-trail)
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

### 3.3. Tabel `bill_details` (Rincian Blok Pemakaian)
```sql
CREATE TABLE bill_details (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    bill_id BIGINT UNSIGNED NOT NULL,
    item_type ENUM('WATER_USAGE', 'ADMIN_FEE', 'MAINTENANCE_FEE', 'PENALTY', 'DISCOUNT') NOT NULL,
    description VARCHAR(255) NOT NULL, -- e.g. 'Pemakaian Air Blok I (0-10 m3)'
    volume DECIMAL(10, 2) NOT NULL DEFAULT 0.00, -- e.g. 10.00
    rate DECIMAL(12, 2) NOT NULL DEFAULT 0.00,   -- e.g. 2500.00
    subtotal DECIMAL(12, 2) NOT NULL,            -- e.g. 25000.00
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
);
```

### 3.4. Tabel `payments` & `payment_histories`
```sql
CREATE TABLE payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_number VARCHAR(50) UNIQUE NOT NULL, -- 'PAY-PYK-YYYYMM-XXXX'
    bill_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(12, 2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL, -- 'bca_va', 'mandiri_va', 'qris_nagari', 'manual_transfer'
    payment_channel VARCHAR(50) NOT NULL, -- 'Virtual Account', 'QRIS', 'Bank Transfer'
    va_number VARCHAR(50) NULL,
    qris_payload TEXT NULL,
    proof_photo_path VARCHAR(255) NULL, -- Path upload struk/bukti bayar transfer manual
    reference_number VARCHAR(100) NULL, -- Nomor referensi transaksi dari bank
    status ENUM('PENDING', 'VERIFIED', 'REJECTED') NOT NULL DEFAULT 'PENDING',
    verified_by BIGINT UNSIGNED NULL, -- ID Petugas/Admin yang memverifikasi
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

CREATE TABLE payment_histories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    payment_id BIGINT UNSIGNED NOT NULL,
    previous_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    actor_id BIGINT UNSIGNED NULL,
    actor_role VARCHAR(50) NOT NULL, -- 'customer', 'staff', 'admin', 'system'
    action_note TEXT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (actor_id) REFERENCES users(id) ON DELETE SET NULL
);
```

---

## 4. TAHAP 3: ATURAN BISNIS (BUSINESS RULES & POLICIES)

1. **Prinsip Trigger Tagihan**:
   - Tagihan **hanya boleh dibuat** setelah `meter_readings.status` berubah menjadi `'Terverifikasi AI'` atau `'Terverifikasi'`.
   - Laporan meter berstatus `'Menunggu'` atau `'Ditolak'` tidak boleh men-generate tagihan.
2. **Integritas Relasi & Anti-Duplikasi (Idempotensi)**:
   - Kolom `bills.meter_reading_id` dipasang `UNIQUE KEY`.
   - Satu laporan meter air hanya dapat menghasilkan maksimal 1 (satu) tagihan.
   - Pengecekan idempotensi juga dilakukan pada level database: kombinasi `(customer_id, billing_period)` tidak boleh ganda.
3. **Snapshot Tarif (*Tariff Immutability*)**:
   - Saat tagihan digenerate, rincian aturan tarif yang berlaku dikomputasi dan disimpan ke `tariff_snapshot` (format JSON) di tabel `bills`, serta dijabarkan per baris pada `bill_details`.
   - Jika di kemudian hari PDAM mengubah tarif resmi, nilai tagihan bulan-bulan sebelumnya **tidak akan pernah berubah**.
4. **Alur & Siklus Status Pembayaran**:
   - Jika pembayaran menggunakan metode otomatis (Virtual Account / QRIS simulasi / gateway callback): begitu pembayaran tervalidasi, status payment menjadi `'VERIFIED'`, dan status bill otomatis berubah menjadi `'PAID'`.
   - Jika pembayaran menggunakan transfer manual dengan upload bukti bayar: status payment adalah `'PENDING'`. Admin/Staff PDAM harus memeriksa bukti transfer sebelum menyetujui (`VERIFIED`) atau menolak (`REJECTED`).
   - Tagihan tidak boleh berubah menjadi `'PAID'` sebelum pembayaran terkait berstatus `'VERIFIED'`.
5. **Keamanan & Otorisasi Kepemilikan (Anti-IDOR)**:
   - Pelanggan hanya dapat melihat, mengunduh tagihan, dan melakukan pembayaran untuk tagihan miliknya sendiri (`user_id === auth()->id()`).
   - Petugas/Admin dapat melihat seluruh tagihan dan memverifikasi pembayaran.
   - Validasi kepemilikan ditegakkan melalui `BillPolicy` dan `PaymentPolicy`.
6. **Notifikasi Pelanggan**:
   - Saat tagihan berhasil dibuat: Kirim notifikasi ke pelanggan melalui tabel `notifications` ("Tagihan Air Periode X Sebesar Rp Y Telah Terbit").
   - Saat pembayaran disetujui: Kirim notifikasi konfirmasi ("Pembayaran Tagihan Air Berhasil Disetujui").

---

## 5. TAHAP 4: ARSITEKTUR IMPLEMENTASI BACKEND

### 5.1. Migrations yang Akan Dibuat
1. `2026_11_18_000001_create_tariffs_and_tiers_tables.php`
2. `2026_11_18_000002_create_bills_and_bill_details_tables.php`
3. `2026_11_18_000003_create_payments_and_histories_tables.php`
4. `2026_11_18_000004_seed_default_tariffs.php` (Seeder golongan 2A Rumah Tangga Mandiri, 1A Sosial, 3A Niaga)

### 5.2. Eloquent Models
- `app/Models/Tariff.php` (relasi `hasMany` ke `TariffTier`)
- `app/Models/TariffTier.php` (relasi `belongsTo` ke `Tariff`)
- `app/Models/Bill.php` (relasi `belongsTo` ke `User`, `MeterReading`; `hasMany` ke `BillDetail`, `Payment`)
- `app/Models/BillDetail.php` (relasi `belongsTo` ke `Bill`)
- `app/Models/Payment.php` (relasi `belongsTo` ke `Bill`, `User`, `verifiedBy`; `hasMany` ke `PaymentHistory`)
- `app/Models/PaymentHistory.php` (relasi `belongsTo` ke `Payment`)

### 5.3. Services Layer
- `app/Services/BillingService.php`:
  - `generateBillFromReading(MeterReading $reading): Bill`
  - `calculateWaterCharge(float $consumption, Tariff $tariff): array`
  - `checkOverdueBills(): int` (perhitungan denda jika melewati tanggal jatuh tempo)
- `app/Services/PaymentService.php`:
  - `createPayment(Bill $bill, array $data): Payment`
  - `verifyPayment(Payment $payment, int $verifierId, ?string $notes): bool`
  - `rejectPayment(Payment $payment, int $verifierId, string $reason): bool`

### 5.4. Form Requests & API Resources
- Form Requests:
  - `app/Http/Requests/StorePaymentRequest.php` (validasi metode bayar, bukti bayar jika transfer manual)
  - `app/Http/Requests/VerifyPaymentRequest.php` (validasi status approval/rejection)
- API Resources:
  - `app/Http/Resources/BillResource.php`
  - `app/Http/Resources/BillDetailResource.php`
  - `app/Http/Resources/PaymentResource.php`
  - `app/Http/Resources/TariffResource.php`

### 5.5. Policies & Authorization
- `app/Policies/BillPolicy.php` (`viewAny`, `view`, `pay`)
- `app/Policies/PaymentPolicy.php` (`view`, `create`, `verify`)

### 5.6. Integration Point
- Pada `MeterReadingService::validateReading()`, tambahkan pemanggilan aman:
  ```php
  if ($meterReading->status === 'Terverifikasi AI' || $meterReading->status === 'Terverifikasi') {
      app(BillingService::class)->generateBillFromReading($meterReading);
  }
  ```

---

## 6. DAFTAR PERTANYAAN & HAL YANG MEMERLUKAN KONFIRMASI PDAM

Sebelum aturan tarif dan penagihan dikunci permanen pada tahap coding/migration, berikut parameter spesifik yang memerlukan konfirmasi pihak PDAM Tirta Sago Kota Payakumbuh:

1. **Struktur Skema Tarif Progresif**:
   - Apakah sistem menggunakan 4 blok progresif PDAM Tirta Sago:
     - **Blok I (0–10 $m^3$)**: Rp 2.500 / $m^3$
     - **Blok II (11–20 $m^3$)**: Rp 3.200 / $m^3$
     - **Blok III (21–30 $m^3$)**: Rp 4.500 / $m^3$
     - **Blok IV (> 30 $m^3$)**: Rp 6.000 / $m^3$
   - Atau tetap mempertahankan nilai flat simulasi saat ini (Rp 7.800 / $m^3$)?
2. **Biaya Beban Tetap & Administrasi**:
   - Apakah biaya administrasi tetap sebesar Rp 2.600/bulan untuk semua golongan, atau terdapat biaya pemeliharaan meter air tersendiri (misal Rp 2.000 - Rp 5.000 tergantung diameter pipa meter)?
3. **Aturan Tanggal Jatuh Tempo & Denda**:
   - Apakah batas akhir pembayaran (*due date*) selalu jatuh pada tanggal **20 setiap bulan**?
   - Apakah besaran denda keterlambatan bersifat nominal tetap (misal Rp 10.000/bulan terlambat) atau berupa persentase?
4. **Verifikasi Pembayaran**:
   - Untuk metode Virtual Account dan QRIS, apakah disimulasikan auto-approve (*instant paid*) di level backend untuk demo/pengujian, sementara Transfer Manual wajib diverifikasi manual oleh Petugas/Admin?
