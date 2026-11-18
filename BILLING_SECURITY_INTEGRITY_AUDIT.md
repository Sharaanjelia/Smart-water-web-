# DOKUMEN AUDIT KEAMANAN & INTEGRITAS BILLING PAYMENT
## SMART WATER KOTA PAYAKUMBUH — LARAVEL 12 & MYSQL 8.4 (LARAGON ENVIRONMENT)

---

## 1. PENDAHULUAN & TUJUAN AUDIT

Dokumen ini disusun sebagai laporan **Audit Mendalam Keamanan & Integritas** terhadap implementasi subsistem **Billing & Payment** pada commit `00e3668`.

Audit ini bertujuan untuk:
1. Memastikan bahwa sistem penagihan dan pembayaran tidak memperlakukan tarif simulasi/provisional sebagai tagihan resmi yang mengikat secara hukum.
2. Memastikan tidak ada celah keamanan otorisasi (IDOR), manipulasi nominal (*amount tampering*), pengajuan pembayaran ganda (*double submission*), maupun kondisi balapan (*race conditions*) antar petugas/admin.
3. Memastikan integritas alur verifikasi meter air hingga status lunas (*PAID*).
4. Menganalisis seluruh perubahan kode di luar modul billing (`LegacyApi` dan runner pengujian) guna memastikan tidak ada risiko regresi.
5. Memverifikasi kepatuhan penuh terhadap larangan modifikasi UI, Blade, JavaScript, AI Chatbot, dan larangan integrasi payment gateway (Midtrans).

> [!NOTE]
> **Metode & Batasan**: Audit ini berstatus **AUDIT ONLY**. Tidak ada perubahan kode aplikasi atau manipulasi skema database yang dilakukan pada tahapan ini. Seluruh pengujian audit dijalankan pada database in-memory isolated testing tanpa menyentuh data operasional MySQL 8.4 Laragon.

---

## 2. MATRIKS RINGKASAN TEMUAN AUDIT

| No | Kode Temuan | Deskripsi Temuan | Tingkat Risiko | Status Penanganan |
| :--- | :--- | :--- | :---: | :---: |
| 1 | **SEC-BILL-01** | Ketiadaan penanda status tarif resmi (`is_approved` / `is_simulation`) pada tabel `tariffs` | **HIGH** | Rekomendasi Solusi |
| 2 | **SEC-BILL-02** | Fallback otomatis ke tarif flat hardcoded (Rp7.800 & Rp2.600) saat blok tarif kosong | **HIGH** | Rekomendasi Solusi |
| 3 | **SEC-BILL-03** | Status `'Terverifikasi AI'` dapat memicu penagihan tanpa verifikator manusia eksplisit | **MEDIUM** | Rekomendasi Solusi |
| 4 | **SEC-BILL-04** | Form Request pembayaran belum memvalidasi kesamaan nominal input dengan total tagihan | **MEDIUM** | Rekomendasi Solusi |
| 5 | **SEC-BILL-05** | Pelanggan dapat mengajukan bukti bayar baru saat pembayaran sebelumnya masih `PENDING` | **MEDIUM** | Rekomendasi Solusi |
| 6 | **SEC-BILL-06** | Potensi *race condition* verifikasi bersamaan oleh 2 Admin tanpa *Pessimistic Locking* | **MEDIUM** | Rekomendasi Solusi |
| 7 | **SEC-BILL-07** | Nilai meter menurun (`reading < lastReading`) diserap menjadi 0 tanpa penanda anomali | **LOW** | Rekomendasi Solusi |
| 8 | **SEC-BILL-08** | Fallback dummy `-19.0 m³` pada meter reading jika data stand lampau belum ada | **LOW** | Rekomendasi Solusi |

---

## 3. AUDIT MENDALAM PER BIDANG

### 3.1. Audit Bidang 1: Tarif dan Tagihan (Tariffs & Bills)

#### Temuan 1.1: Ketiadaan Penanda Status Persetujuan Resmi Tarif (`is_simulation` / `is_approved`)
* **Tingkat Risiko**: **HIGH**
* **File & Baris Kode**:
  - [database/migrations/2026_11_18_000001_create_tariffs_and_tiers_tables.php](file:///c:/xampp/htdocs/smart-water/database/migrations/2026_11_18_000001_create_tariffs_and_tiers_tables.php#L16-L30)
  - [app/Models/Tariff.php](file:///c:/xampp/htdocs/smart-water/app/Models/Tariff.php#L15-L25)
  - [app/Services/BillingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/BillingService.php#L47-L57)
* **Kondisi Saat Ini**:
  Tabel `tariffs` saat ini hanya memiliki kolom `is_active BOOLEAN`. Setiap record yang berstatus `is_active = true` (termasuk data seeder sampel provisional pada `DynamicTariffSeeder`) langsung dianggap sah oleh `BillingService` untuk menerbitkan tagihan dengan nomor invoice resmi `INV-PYK-...`.
* **Analisis Dampak**:
  Jika sistem digunakan sebelum ada konfirmasi resmi dari mitra PDAM Tirta Sago, tagihan yang terbit berisiko dianggap sebagai tagihan resmi yang sah secara hukum oleh pelanggan, padahal nominal tarif per $m^3$ masih berstatus sampel/simulasi.
* **Rekomendasi Perbaikan**:
  Tambahkan kolom `is_simulation BOOLEAN DEFAULT TRUE` dan `status ENUM('PROVISIONAL', 'SUBMITTED', 'APPROVED_BY_PDAM') DEFAULT 'PROVISIONAL'` pada tabel `tariffs`. Pada tabel `bills`, tambahkan kolom `bill_type ENUM('SIMULATION', 'OFFICIAL') DEFAULT 'SIMULATION'`. Sistem harus mencegah penerbitan tagihan resmi jika tarif yang aktif masih berstatus simulasi/belum disetujui mitra.

#### Temuan 1.2: Fallback Diam-diam ke Nilai Flat Hardcoded (Rp7.800 & Rp2.600)
* **Tingkat Risiko**: **HIGH**
* **File & Baris Kode**:
  - [app/Services/BillingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/BillingService.php#L180-L195)
* **Kondisi Saat Ini**:
  Pada method `calculateConsumptionCharges()`, jika objek `$tariff` bernilai `null` atau tidak memiliki `tiers`, terdapat kode fallback:
  ```php
  // Default flat fallback if no tiers configured
  $flatRate = 7800.00;
  $subtotal = $consumption * $flatRate;
  ...
  $adminFee = (float) ($tariff?->admin_fee ?? 2600.00);
  ```
* **Analisis Dampak**:
  Jika konfigurasi tarif pelanggan belum diisi atau data tier terhapus, sistem secara diam-diam (*silent fallback*) menggunakan angka Rp7.800/m³ dan Rp2.600. Hal ini bertentangan dengan prinsip bahwa sistem tidak boleh menggunakan tarif simulasi lama secara sembunyi-sembunyi.
* **Rekomendasi Perbaikan**:
  Hapus fallback statis tersebut. Jika tarif atau blok tier tidak ditemukan/tidak valid, `BillingService` harus melemparkan exception eksplisit (`ValidationException` / `InvalidTariffException`) dan menolak penerbitan tagihan sampai admin mengonfigurasi tarif resmi.

#### Verifikasi Integritas Snapshot & Anti-Duplikasi (Lulus / Aman)
* **Bukti Kode**:
  - [app/Services/BillingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/BillingService.php#L97-L109) (`tariff_snapshot` tersimpan dalam format JSON di tabel `bills`).
  - [app/Services/BillingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/BillingService.php#L23-L31) (`meter_reading_id` diproteksi secara idempotent).
  - [database/migrations/2026_11_18_000002_create_bills_and_bill_details_tables.php](file:///c:/xampp/htdocs/smart-water/database/migrations/2026_11_18_000002_create_bills_and_bill_details_tables.php#L17) (`unique()` constraint pada `meter_reading_id`).
* **Hasil Uji**:
  - Tagihan lama terbukti **tidak terpengaruh** ketika master tarif dinaikkan (Lulus pada `test_existing_bill_preserves_snapshot_when_tariff_is_modified`).
  - Satu laporan meter air terbukti **hanya menghasilkan satu tagihan** (Lulus pada `test_bill_generation_is_idempotent`).

---

### 3.2. Audit Bidang 2: Validasi Meter Air (Meter Reading Validation)

#### Temuan 2.1: Status `'Terverifikasi AI'` Menjadi Pemicu Tagihan Tanpa Verifikator Petugas
* **Tingkat Risiko**: **MEDIUM**
* **File & Baris Kode**:
  - [app/Services/MeterReadingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/MeterReadingService.php#L117-L125)
  - [app/Services/BillingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/BillingService.php#L34-L39)
* **Kondisi Saat Ini**:
  Pada `MeterReadingService::validateReading()`, ketika tindakan approval dijalankan, status diubah menjadi `'Terverifikasi AI'` (bukan pembedaan antara verifikasi otomatis OCR vs persetujuan petugas lapangan). Sementara itu pada `BillingService`:
  ```php
  $allowedStatuses = ['Terverifikasi AI', 'Terverifikasi'];
  ```
* **Analisis Dampak**:
  Hasil scan AI-OCR di frontend atau proses simulasi berpotensi men-generate tagihan tanpa tanda tangan digital/verifikasi manual seorang petugas (`validated_by IS NULL`).
* **Rekomendasi Perbaikan**:
  Pisahkan status laporan meter air secara tegas:
  - `'Menunggu Validasi'`: Baru diunggah oleh pelanggan melalui AI-OCR.
  - `'Terverifikasi Petugas'`: Telah diperiksa dan disetujui petugas PDAM (`validated_by` terisi).
  Hanya laporan dengan status `'Terverifikasi Petugas'` dan `validated_by IS NOT NULL` yang diizinkan menerbitkan tagihan.

#### Temuan 2.2: Nilai Meter Menurun Diserap Menjadi 0 Tanpa Penanda Anomali
* **Tingkat Risiko**: **LOW**
* **File & Baris Kode**:
  - [app/Services/MeterReadingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/MeterReadingService.php#L73)
* **Kondisi Saat Ini**:
  `$consumption = max(0, round($ocrReading - $lastReading, 2));`
* **Analisis Dampak**:
  Jika terjadi salah baca OCR atau input pelanggan di mana stand baru lebih rendah dari stand lama (`ocrReading < lastReading`), kubikasi pemakaian otomatis dijadikan `0`. Dalam standar operasional PDAM, stand meter yang menurun merupakan anomali serius (kemungkinan meter rusak, penggantian meteran baru, atau manipulasi) yang mewajibkan status `'Anomali / Perlu Pemeriksaan Fisik'`, bukan disahkan pemakaian 0 m³.
* **Rekomendasi Perbaikan**:
  Tambahkan validasi: jika `$ocrReading < $lastReading`, tandai laporan dengan status `'Perlu Penyesuaian Meter'` dan cegah approval otomatis sebelum petugas mencatat berita acara pergantian/koreksi meter air.

#### Temuan 2.3: Nilai Fallback Dummy `-19.0 m³`
* **Tingkat Risiko**: **LOW**
* **File & Baris Kode**:
  - [app/Services/MeterReadingService.php](file:///c:/xampp/htdocs/smart-water/app/Services/MeterReadingService.php#L69)
* **Kondisi Saat Ini**:
  `$lastReading = $previousRecord ? (float)$previousRecord->reading : max(0, round($ocrReading - 19.0, 2));`
* **Analisis Dampak**:
  Angka `19.0` adalah sisa kode simulasi dari frontend lama. Jika seorang pelanggan baru pertama kali lapor meter tanpa riwayat, stand lalunya dipaksa berkurang 19 $m^3$.
* **Rekomendasi Perbaikan**:
  Gunakan nilai stand awal sambungan baru dari `customer_profiles.last_meter_value` atau `0.00`, bukan konstanta `19.0`.

---

### 3.3. Audit Bidang 3: Pembayaran Manual (Manual Payment Verification)

#### Temuan 3.1: Validasi Nominal Pembayaran Belum Memeriksa Kesesuaian Total Tagihan (*Amount Tampering*)
* **Tingkat Risiko**: **MEDIUM**
* **File & Baris Kode**:
  - [app/Http/Requests/StoreManualPaymentRequest.php](file:///c:/xampp/htdocs/smart-water/app/Http/Requests/StoreManualPaymentRequest.php#L40)
  - [app/Services/PaymentService.php](file:///c:/xampp/htdocs/smart-water/app/Services/PaymentService.php#L34)
* **Kondisi Saat Ini**:
  Pada `StoreManualPaymentRequest.php`:
  `'amount' => ['required', 'numeric', 'min:0']`
  Tidak ada pengecekan bahwa nominal yang dikirimkan harus sama dengan `$bill->total_amount`.
* **Analisis Dampak**:
  Pengguna dapat memodifikasi payload HTTP request dan mengirimkan nominal Rp 500 untuk tagihan sebesar Rp 150.000 (Terbukti pada uji `test_amount_tampering_behavior_audit`, request berhasil diterima dengan HTTP 201). Walaupun admin dapat melihat nominal pada bukti transfer, catatan tabel `payments.amount` menjadi tidak akurat dan dapat merusak pencatatan akuntansi.
* **Rekomendasi Perbaikan**:
  Pada Form Request atau `PaymentService`, kunci nominal pembayaran agar selalu sama persis dengan `total_amount` tagihan yang bersangkutan (`'amount' => ['required', 'numeric', function(...) { ... }]`).

#### Temuan 3.2: Pengajuan Bukti Bayar Ganda Saat Masih `PENDING`
* **Tingkat Risiko**: **MEDIUM**
* **File & Baris Kode**:
  - [app/Services/PaymentService.php](file:///c:/xampp/htdocs/smart-water/app/Services/PaymentService.php#L20-L73)
* **Kondisi Saat Ini**:
  `PaymentService::submitManualPayment` memeriksa `if ($bill->status === 'PAID')`, namun tidak memeriksa apakah sudah ada record `Payment` berstatus `'PENDING'` pada tagihan tersebut.
* **Analisis Dampak**:
  Jika pelanggan menekan tombol kirim beberapa kali atau mengirim ulang bukti bayar saat pembayaran sebelumnya belum diverifikasi oleh admin, akan terbentuk beberapa record `Payment` `PENDING` untuk 1 tagihan yang sama (Terbukti pada uji `test_multiple_pending_payments_behavior_audit`). Hal ini membingungkan petugas verifikator.
* **Rekomendasi Perbaikan**:
  Tolak pengajuan pembayaran baru jika tagihan tersebut memiliki pembayaran berstatus `'PENDING'`:
  ```php
  if ($bill->payments()->where('status', 'PENDING')->exists()) {
      throw ValidationException::withMessages(['payment' => 'Pembayaran sebelumnya sedang menunggu verifikasi Admin PDAM.']);
  }
  ```

#### Temuan 3.3: Potensi *Race Condition* Verifikasi Bersamaan (Ketiadaan *Locking*)
* **Tingkat Risiko**: **MEDIUM**
* **File & Baris Kode**:
  - [app/Services/PaymentService.php](file:///c:/xampp/htdocs/smart-water/app/Services/PaymentService.php#L80-L125)
* **Kondisi Saat Ini**:
  `verifyPayment()` dijalankan dalam `DB::transaction()`, namun model `Payment` dan `Bill` di-update tanpa memanggil `lockForUpdate()`.
* **Analisis Dampak**:
  Jika dua Admin PDAM membuka antrean pembayaran yang sama dan menekan tombol "Verifikasi" di waktu bersamaan (konkurensi tinggi), keduanya dapat membaca status `'PENDING'` secara simultan, menyebabkan dua record histori `PaymentHistory` berstatus `VERIFIED` ganda dan duplikasi notifikasi ke pelanggan.
* **Rekomendasi Perbaikan**:
  Gunakan *Pessimistic Locking* di dalam transaksi:
  ```php
  $payment = Payment::where('id', $payment->id)->lockForUpdate()->firstOrFail();
  if ($payment->status !== 'PENDING') {
      return false; // Sudah diproses oleh admin lain
  }
  ```

#### Verifikasi Otorisasi IDOR & Keamanan Berkas Bukti (Lulus / Aman)
* **Bukti Kode**:
  - [app/Http/Requests/StoreManualPaymentRequest.php](file:///c:/xampp/htdocs/smart-water/app/Http/Requests/StoreManualPaymentRequest.php#L14-L31) (`authorize()` membatasi pemilik sah tagihan).
  - [app/Policies/BillPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/BillPolicy.php#L21-L44) & [app/Policies/PaymentPolicy.php](file:///c:/xampp/htdocs/smart-water/app/Policies/PaymentPolicy.php#L21-L44).
  - [app/Services/PaymentService.php](file:///c:/xampp/htdocs/smart-water/app/Services/PaymentService.php#L29) (`$proofFile->store('payment_proofs', 'public')` menggunakan hashing SHA aman).
  - [app/Http/Requests/StoreManualPaymentRequest.php](file:///c:/xampp/htdocs/smart-water/app/Http/Requests/StoreManualPaymentRequest.php#L45) (`mimes:jpg,jpeg,png,pdf`, `max:5120`).
* **Hasil Uji**:
  - Pelanggan lain terbukti **ditolak dengan HTTP 403 Forbidden** saat mencoba membayar tagihan warga lain (Lulus pada `test_other_customer_cannot_pay_someone_else_bill`).
  - Tagihan berstatus `PAID` terbukti **ditolak dan tidak dapat dibayar ulang** (Lulus pada `test_paid_bill_cannot_be_paid_again`).
  - Berkas bukti pembayaran tersimpan di direktori privat/aman dengan nama hash acak, mencegah *directory traversal* dan eksekusi skrip berbahaya.

---

### 3.4. Audit Bidang 4: Analisis Perubahan di Luar Modul Billing

Pada commit `00e3668`, terdapat perubahan minor pada 4 file di luar penambahan modul billing:
1. `app/LegacyApi/complaints.php`
2. `app/LegacyApi/academy.php`
3. `app/LegacyApi/challenges.php`
4. `tests/full_mysql_validation.php`

Berikut evaluasi mendalam terhadap perubahan tersebut:

| File | Alasan Perubahan | Perubahan Perilaku | Diperlukan Billing atau Test? | Risiko Regresi |
| :--- | :--- | :--- | :--- | :---: |
| `app/LegacyApi/complaints.php` | Menghindari PHP fatal error saat dipanggil di runner CLI di mana output teks terminal sudah berjalan | Membungkus `http_response_code($code)` dengan pengecekan `if (!headers_sent())` | Perbaikan kompatibilitas runner PHP 8.3 | **0% (Aman)** |
| `app/LegacyApi/academy.php` | Menghindari error `Cannot set response code - headers already sent` | Membungkus `http_response_code($code)` dengan pengecekan `if (!headers_sent())` | Perbaikan kompatibilitas runner PHP 8.3 | **0% (Aman)** |
| `app/LegacyApi/challenges.php` | Menghindari error `Cannot set response code - headers already sent` | Membungkus `http_response_code($code)` dengan pengecekan `if (!headers_sent())` | Perbaikan kompatibilitas runner PHP 8.3 | **0% (Aman)** |
| `tests/full_mysql_validation.php` | Menyertakan sesi pengguna pelanggan (`$customer`) pada pengujian adapter legacy Section 11 | Endpoint sekarang menerima konteks user login saat menguji perolehan data pengaduan, akademi, dan tantangan | Penyesuaian keabsahan pengujian regresi | **0% (Aman)** |

* **Kesimpulan Analisis**:
  Perubahan pada adapter `LegacyApi` **tidak mengubah respons JSON, kontrak data, maupun fungsionalitas di web browser**. Di browser, `headers_sent()` selalu bernilai `false` pada tahap awal sehingga kode status HTTP tetap dikirim sebagaimana mestinya. Perubahan ini murni *hardening* kompatibilitas PHP 8.3 untuk mencegah *crash* saat skrip dijalankan via terminal atau unit test runner. Perubahan ini **aman dan dipertahankan**.

---

### 3.5. Audit Bidang 5: Audit UI & Integrasi Antarmuka

Berdasarkan audit git diff pada commit `00e3668`:
1. **File Blade & View**: `git diff 00e3668^ 00e3668 -- resources/views/` $\to$ **Nol Perubahan (0 files modified)**.
2. **File CSS & Tailwind**: `git diff 00e3668^ 00e3668 -- public/css/` $\to$ **Nol Perubahan (0 files modified)**.
3. **File JavaScript Frontend**: `git diff 00e3668^ 00e3668 -- public/js/` $\to$ **Nol Perubahan (0 files modified)**.
4. **AI Chatbot**: File `public/php-chat-ai/` dan `app/LegacyApi/chat.php` $\to$ **Nol Perubahan / Tidak Disentuh**.
5. **Payment Gateway / Midtrans**: Tidak ada *dependency*, package, skrip Snap.js, maupun webhook Midtrans yang diinstal atau dipanggil.
6. **Kompatibilitas Tampilan Billing**: Halaman `resources/views/billing/*` (`index`, `detail`, `history`, `payment-method`, `payment-instruction`, `payment-success`) tetap utuh dan siap menerima *binding* data backend resmi ketika dihubungkan.

---

## 4. STATUS HASIL PENGUJIAN OTOMASI

Pengujian dilakukan menggunakan PHPUnit pada lingkungan database pengujian terisolasi (`sqlite:memory`) serta live audit pada MySQL 8.4 Laragon:

| No | Skenario Pengujian | Target Uji | Hasil Uji | Keterangan |
| :---: | :--- | :--- | :---: | :--- |
| 1 | Meter reading belum divalidasi petugas | `BillingService::generateBillFromReading` | **PASS** | Exception terlempar, tagihan ditolak |
| 2 | Pembayaran tagihan milik pelanggan lain | `StoreManualPaymentRequest` | **PASS** | Ditolak dengan HTTP 403 Forbidden |
| 3 | Pembayaran ulang pada tagihan PAID | `PaymentService::submitManualPayment` | **PASS** | Ditolak dengan ValidationException |
| 4 | Immutabilitas snapshot tarif lampau | `Bill::tariff_snapshot` | **PASS** | Nilai tagihan lama tidak berubah |
| 5 | Siklus penolakan & pengajuan ulang bukti bayar | `PaymentService::rejectPayment` | **PASS** | Riwayat tersimpan, tagihan kembali UNPAID |
| 6 | Uji manipulasi nominal pembayaran | `StoreManualPaymentRequest` | **TEMUAN** | Teridentifikasi risiko nominal tidak dicocokkan |
| 7 | Uji pengajuan bukti bayar ganda bersamaan | `PaymentService::submitManualPayment` | **TEMUAN** | Teridentifikasi risiko 2 status PENDING bersamaan |
| 8 | Uji ketiadaan status persetujuan tarif | `Tariff` Model Schema | **TEMUAN** | Teridentifikasi kolom `is_approved` belum ada |
| 9 | Uji regresi modul Complaint, Academy, Challenge | `tests/full_mysql_validation.php` | **PASS** | 47 dari 47 uji lulus (100% PASS) |
| 10 | Uji seluruh suite PHPUnit | `vendor/phpunit/phpunit` | **PASS** | 76 dari 76 uji lulus (291 assertions OK) |

---

## 5. REKOMENDASI & RENCANA PERBAIKAN (ACTION PLAN)

Sebelum melangkah ke tahap implementasi perbaikan pada Task berikutnya, berikut daftar perbaikan yang direkomendasikan berdasarkan temuan audit di atas:

### Rencana 1: Penguatan Skema & Status Tarif (Menjawab SEC-BILL-01 & SEC-BILL-02)
1. **Migration Baru**: Tambahkan kolom pada tabel `tariffs`:
   - `is_simulation`: `BOOLEAN DEFAULT TRUE`
   - `approval_status`: `ENUM('DRAFT', 'PROVISIONAL', 'APPROVED_PDAM') DEFAULT 'PROVISIONAL'`
2. Tambahkan kolom pada tabel `bills`:
   - `bill_type`: `ENUM('SIMULATION', 'OFFICIAL') DEFAULT 'SIMULATION'`
3. Pada `BillingService`:
   - Jika tarif yang digunakan berstatus `is_simulation = true`, terbitkan tagihan dengan `bill_type = 'SIMULATION'`.
   - Hapus fallback flat Rp7.800 & Rp2.600; lemparkan exception jika tarif tidak valid.

### Rencana 2: Penguatan Validasi & Pemicu Meter (Menjawab SEC-BILL-03, SEC-BILL-07 & SEC-BILL-08)
1. Pertegas status pada `MeterReadingService::validateReading()`: Tagihan hanya boleh diterbitkan jika tindakan validasi adalah persetujuan resmi petugas (`'Terverifikasi Petugas'`), dan `validated_by` tidak boleh kosong.
2. Tambahkan pemeriksaan anomali stand meter: Jika `$ocrReading < $lastReading`, tandai status sebagai `'Perlu Koreksi Petugas'` dan tolak pembentukan tagihan sebelum dilakukan audit fisik.
3. Hapus nilai statis pengurangan `-19.0 m³` pada stand awal pelanggan baru.

### Rencana 3: Penguatan Transaksi Pembayaran Manual (Menjawab SEC-BILL-04, SEC-BILL-05 & SEC-BILL-06)
1. Pada `StoreManualPaymentRequest.php`, tambahkan aturan validasi pencocokan nominal:
   ```php
   $bill = Bill::find($this->input('bill_id'));
   if ($bill && abs((float)$this->input('amount') - (float)$bill->total_amount) > 0.01) {
       $validator->errors()->add('amount', 'Nominal pembayaran harus sama persis dengan total tagihan (Rp ' . number_format($bill->total_amount, 0, ',', '.') . ').');
   }
   ```
2. Cegah pembayaran ganda: Jika tagihan sudah memiliki pembayaran berstatus `PENDING`, tolak pengajuan baru sampai admin menyelesaikan verifikasi pembayaran sebelumnya.
3. Tambahkan *Pessimistic Lock* (`lockForUpdate()`) pada method `verifyPayment()` dan `rejectPayment()` di `PaymentService` untuk mencegah eksekusi ganda jika dua admin mengklik tombol verifikasi pada detik yang sama.

---

## 6. KESIMPULAN

Implementasi Billing & Payment pada commit `00e3668` telah berhasil membangun fondasi arsitektur 6 tabel relasional, perhitungan tarif dinamis bertingkat (*multi-tier*), snapshot tarif permanen, perlindungan IDOR, serta siklus status tagihan yang aman (status `PAID` hanya muncul setelah verifikasi admin).

Namun, untuk memastikan kesiapan produksi dan kepatuhan penuh terhadap tata kelola PDAM, terdapat **2 temuan berisiko High** (penandaan status simulasi tarif dan penghapusan fallback flat diam-diam) serta **4 temuan berisiko Medium** (validasi kesesuaian nominal, proteksi pengajuan ganda, pemisahan verifikasi petugas vs AI, dan *concurrency lock*) yang direkomendasikan untuk disempurnakan pada tahap implementasi selanjutnya.
