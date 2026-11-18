# SMART WATER KOTA PAYAKUMBUH
## AUDIT PERSIAPAN INTEGRASI FRONTEND BILLING & PAYMENT (TASK 9)

**Tanggal:** 18 November 2026  
**Status Audit:** COMPLETED (AUDIT ONLY — TANPA PERUBAHAN KODE)  
**Lingkungan:** Laravel 12, PHP 8.3, MySQL 8.4 (Laragon)  
**Tujuan:** Memetakan seluruh kebutuhan integrasi frontend Blade + JavaScript dengan arsitektur backend Billing & Payment yang telah diamankan pada Task 8.

---

### 1. DAFTAR HALAMAN FRONTEND YANG SUDAH TERSEDIA

Terdapat 6 file view Blade di dalam direktori `resources/views/billing/` yang telah memiliki desain UI/UX lengkap, responsif, dan rapi:

| No | File View Blade | Judul / Tab | URL Route | Fungsi & Elemen UI Utama |
|---|---|---|---|---|
| 1 | `resources/views/billing/index.blade.php` | Tagihan Saya | `GET /bill` | • Segmentasi Tab: Tagihan Aktif vs Riwayat Tagihan.<br>• Kartu Faktur Tagihan Tertunggak (Periode, Nomor Invoice, Total Tagihan, Jatuh Tempo, Rincian Komponen Biaya Ringkas).<br>• Kartu Semua Tagihan Lunas (State Sukses bila tidak ada tunggakan).<br>• Tombol aksi: "Rincian Lengkap" dan "Bayar Sekarang".<br>• Tombol Reset Demo (`window.SWC.resetDemoData()`). |
| 2 | `resources/views/billing/detail.blade.php` | Rincian Lembar Tagihan | `GET /bill-detail` | • Header Gradient Ringkasan Tagihan (Periode, Status Badge, Total Bersih, Jatuh Tempo).<br>• Kartu 1: Data Sambungan Pelanggan (Nama, No. Sambungan, No. Seri Meter, Zona, Golongan Tarif).<br>• Kartu 2: Catatan Dial Meteran Air (Meter Lalu, Meter Kini, Pemakaian Kubikasi).<br>• Kartu 3: Rincian Perhitungan Biaya Rekening (Tarif Pemakaian, Beban Administrasi, Pemeliharaan, Denda).<br>• Tombol CTA "Lanjut ke Pembayaran" / "Lihat Bukti Kuitansi". |
| 3 | `resources/views/billing/history.blade.php` | Riwayat Pembayaran | `GET /bill-history` | • Arsip riwayat pembayaran rekening air masa lalu.<br>• Tombol cetak rekap transaksi (`window.print()`).<br>• Kartu riwayat per periode (Bulan, Nomor Transaksi TRX, Kanal Pembayaran, Pemakaian, Total Biaya, Tombol "Lihat Bukti"). |
| 4 | `resources/views/billing/payment-method.blade.php` | Pilih Metode Pembayaran | `GET /payment-method` | • Header total pembayaran & invoice.<br>• Pilihan metode pembayaran interaktif (Radio Card): Saldo Dompet Smart Water, Xendit Checkout Pop-up, QRIS, BCA Virtual Account, Mandiri Virtual Account, E-Wallet (OVO/DANA/LinkAja).<br>• Informasi enkripsi SSL 256-bit.<br>• Tombol submit "Lanjut Pembayaran". |
| 5 | `resources/views/billing/payment-instruction.blade.php` | Instruksi Pembayaran | `GET /payment-instruction` | • Timer hitung mundur (*countdown timer*) 15 menit.<br>• Box Nomor Rekening / Virtual Account dengan tombol "Salin".<br>• Jumlah transfer tepat hingga digit terakhir dengan tombol "Salin Jumlah".<br>• Accordion panduan transfer: m-Banking, Mesin ATM, Internet Banking.<br>• Tombol "Saya Sudah Membayar (Verifikasi Rekening)". |
| 6 | `resources/views/billing/payment-success.blade.php` | Pembayaran Berhasil | `GET /payment-success` | • Ikon centang hijau besar (90px) & pesan terima kasih.<br>• Kartu Bukti Transaksi Resmi / Kuitansi (Status Pembayaran, No. Transaksi, Periode, ID Pelanggan, Kanal, Waktu, Total Dibayar).<br>• Tombol "Lihat Status Tagihan & Riwayat", "Beranda", dan "Cetak Bukti" (`window.print()`). |

Selain 6 halaman di atas, terdapat tampilan terkait pada dashboard admin:
- `resources/views/admin/dashboard.blade.php`: Tab 2 (`#billsTab`) yang merender daftar tunggakan pelanggan dan status tagihan Budi Santoso.
- `resources/views/pages/admin.blade.php`: Tab 6 (`#tab-pelanggan`) yang menampilkan register master pelanggan & tagihan.

---

### 2. FUNGSI FRONTEND YANG MASIH MENGGUNAKAN LOCALSTORAGE

Frontend saat ini sepenuhnya bergantung pada modul client-side `public/assets/js/swc-store.js` dan penyimpanan browser `localStorage`. Berikut inventarisasi fungsi-fungsi tersebut:

1. **Konfigurasi Kunci & Data Awal (`swc-store.js`):**
   - `STORAGE_KEYS.BILLS = 'swc_bills_v2'`
   - `STORAGE_KEYS.WALLET = 'swc_wallet_v2'`
   - `DEFAULT_BILLS`: Array statis 4 invoice tiruan (`INV-PYK-2025-04-1000` s/d `INV-PYK-2025-01-1000`) dengan nominal statis Rp 150.800 (19.0 m³).
   - `DEFAULT_WALLET`: Saldo dompet awal Rp 2.500.000 atas nama Budi Santoso.

2. **Metode Baca-Tulis Tagihan & Pembayaran:**
   - `SWC.getBills()`: Membaca seluruh tagihan dari `localStorage.getItem('swc_bills_v2')`.
   - `SWC.getBill(inv)`: Mencari objek tagihan berdasarkan string invoice di `localStorage`.
   - `SWC.isBillPaid(inv)`: Mengecek apakah tagihan berstatus `'PAID'` di `localStorage`.
   - `SWC.payBill(inv, method)`: Mengubah properti `target.status = 'PAID'` di `localStorage`, mengisi string tanggal pelunasan lokal, menambahkan notifikasi lokal, dan memicu event `swc:state-change`. **Fungsi ini sama sekali tidak memanggil REST API backend.**
   - `SWC.deductWallet(amount, note)`: Memotong saldo dompet demo di `localStorage`.
   - `SWC.resetDemoData()`: Menghapus data `swc_bills_v2` dan `swc_wallet_v2` dari browser lalu memuat ulang halaman.

3. **Ketergantungan Script pada Masing-Masing Halaman Blade:**
   - **`billing/index.blade.php`:**
     - `renderBillingView()`: Memanggil `window.SWC.getBills()`. Memfilter tagihan aktif (`b.status === 'UNPAID'`) dan riwayat (`b.status === 'PAID'`).
     - Tampilan kartu faktur belum lunas (`#unpaidBillCard`) memiliki teks statis *"April 2025"*, *"INV-PYK-2025-04-1000"*, *"Rp 150.800"*, *"19.0 m³"*.
     - Riwayat tagihan lunas di-render secara dinamis menggunakan `.map()` dari array `localStorage`.
     - Event listener: `window.addEventListener('swc:state-change', ...)` untuk me-render ulang saat state lokal berubah.
   - **`billing/detail.blade.php`:**
     - Skrip `DOMContentLoaded` memanggil `window.SWC.isBillPaid('INV-PYK-2025-04-1000')`. Jika true, teks badge diubah menjadi "LUNAS (TERVERIFIKASI)" dan tombol pembayaran diarahkan ke `/payment-success`.
     - Data rincian biaya, dial meteran, dan golongan tarif bersifat statis (hardcoded).
   - **`billing/history.blade.php`:**
     - Menggunakan array PHP statis di Blade (`@php $bills = [...] @endphp`) dari November 2024 s/d Maret 2025.
     - Skrip `DOMContentLoaded` menyisipkan kartu April 2025 secara dinamis jika `window.SWC.getBill('INV-PYK-2025-04-1000').status === 'PAID'`.
   - **`billing/payment-method.blade.php`:**
     - Membaca saldo dari `window.SWC.getWallet()`.
     - Event handler `handlePaymentSubmit()`: Jika memilih saldo dompet, langsung mengeksekusi `window.SWC.deductWallet()` dan `window.SWC.payBill()`, lalu me-redirect ke `/payment-success` tanpa verifikasi backend.
   - **`billing/payment-instruction.blade.php`:**
     - Menampilkan nomor Virtual Account palsu (`8808 1234567890 01`).
     - Fungsi `confirmPayment()` memanggil `window.SWC.payBill('INV-PYK-2025-04-1000', method)` dan langsung me-redirect ke `/payment-success` dalam 600ms tanpa mengunggah bukti bayar apapun.
   - **`billing/payment-success.blade.php`:**
     - Skrip `DOMContentLoaded` memanggil `window.SWC.payBill('INV-PYK-2025-04-1000', method)`.
   - **`admin/dashboard.blade.php`:**
     - Sel status tagihan Budi Santoso (`#budiBillStatusCell`) diperbarui berdasarkan `window.SWC.isBillPaid('INV-PYK-2025-04-1000')`.

---

### 3. DAFTAR ENDPOINT BACKEND YANG TERSEDIA

Backend Laravel 12 telah menyediakan endpoint lengkap yang siap menerima integrasi frontend:

#### A. Web Routes (`routes/web.php`)
| Metode | Endpoint URL | Controller & Action | Middleware | Fungsi |
|---|---|---|---|---|
| `GET` | `/bill` | `SmartWaterPortalController@bill` | `role:customer` | Menampilkan halaman utama tagihan |
| `GET` | `/bill-detail` | `SmartWaterPortalController@billDetail` | `role:customer` | Menampilkan halaman detail faktur |
| `GET` | `/bill-history` | `SmartWaterPortalController@billHistory` | `role:customer` | Menampilkan riwayat pembayaran |
| `GET` | `/payment-method` | `SmartWaterPortalController@paymentMethod` | `role:customer` | Menampilkan opsi pembayaran |
| `GET` | `/payment-instruction` | `SmartWaterPortalController@paymentInstruction` | `role:customer` | Menampilkan instruksi pembayaran |
| `GET` | `/payment-success` | `SmartWaterPortalController@paymentSuccess` | `role:customer` | Menampilkan kuitansi pembayaran |
| `POST` | `/payment/submit-manual` | `PaymentController@store` | `auth` | Form submit bukti pembayaran manual (multipart) |
| `POST` | `/admin/payments/{payment}/verify` | `PaymentController@verify` | `auth`, `role:admin,staff` | Admin memvalidasi atau menolak pembayaran |

#### B. REST API Routes (`routes/api.php`)
| Metode | Endpoint API | Controller & Action | Middleware | Deskripsi & Response Format |
|---|---|---|---|---|
| `GET` | `/api/v1/bills` | `BillController@index` | `auth` | Mengambil daftar tagihan milik pelanggan (atau semua tagihan untuk admin) dengan paginasi. Mengembalikan `BillResource` JSON. |
| `GET` | `/api/v1/bills/active` | `BillController@active` | `auth` | Mengambil tagihan aktif/tertunggak (`UNPAID` atau `PENDING_VERIFICATION`) milik pengguna yang sedang login. |
| `GET` | `/api/v1/bills/{bill}` | `BillController@show` | `auth` | Mengambil detail 1 tagihan beserta relasi rincian blok konsumsi (`details`), riwayat pembayaran (`payments`), dan snapshot tarif. Dijaga oleh `BillPolicy@view`. |
| `GET` | `/api/v1/payments` | `PaymentController@index` | `auth` | Mengambil daftar pembayaran transaksi milik pelanggan (atau seluruh transaksi untuk admin). |
| `POST` | `/api/v1/payments` | `PaymentController@store` | `auth` | Mengirim data pengajuan pembayaran manual dengan upload berkas `proof_photo`. Divalidasi oleh `StoreManualPaymentRequest`. Menghasilkan status `PENDING`. |
| `GET` | `/api/v1/payments/{payment}` | `PaymentController@show` | `auth` | Mengambil detail pembayaran, status verifikasi, dan audit log (`histories`). Dijaga oleh `PaymentPolicy@view`. |
| `POST` | `/api/v1/payments/{payment}/verify` | `PaymentController@verify` | `auth` | Petugas/Admin memverifikasi (`VERIFIED`) atau menolak (`REJECTED`) pembayaran manual. Dijaga oleh `PaymentPolicy@verify`. |

---

### 4. PEMETAAN HALAMAN KE ENDPOINT BACKEND

| Halaman Frontend | Endpoint Backend Utama | Data yang Dibutuhkan oleh Frontend | Aksi Pengguna & Payload |
|---|---|---|---|
| **`billing/index.blade.php`** | `GET /api/v1/bills/active`<br>`GET /api/v1/bills` | • Objek tagihan aktif (`bill_number`, `period_label`, `total_amount`, `due_date`, `consumption`, `bill_type`, `is_simulation`, `status`).<br>• Array riwayat tagihan yang sudah lunas (`PAID`). | Klik "Bayar Sekarang" -> Navigasi ke `/payment-method`.<br>Klik "Rincian Lengkap" -> Navigasi ke `/bill-detail?id={id}`. |
| **`billing/detail.blade.php`** | `GET /api/v1/bills/{id}` | • Snapshot tarif & golongan pelanggan.<br>• Angka meter lalu (`previous_reading`), meter kini (`current_reading`), pemakaian (`consumption`).<br>• Rincian subtotal per baris (`details`: blok konsumsi, biaya admin, denda).<br>• Status keabsahan (`bill_type`: `SIMULATION` vs `OFFICIAL`). | Klik "Lanjut ke Pembayaran" -> Navigasi ke `/payment-method?bill_id={id}`.<br>Klik "Lihat Bukti" (jika status PAID) -> Navigasi ke `/payment-success?bill_id={id}`. |
| **`billing/history.blade.php`** | `GET /api/v1/bills` (filter status: PAID) | • Daftar tagihan yang statusnya `PAID` dari tabel MySQL `bills`.<br>• Kanal pembayaran riil dari relasi `latestPayment.payment_method`.<br>• Nomor referensi pembayaran / `payment_number`. | Klik "Lihat Bukti" -> Navigasi ke `/payment-success?bill_id={id}`.<br>Klik "Cetak Rekap" -> `window.print()`. |
| **`billing/payment-method.blade.php`** | `GET /api/v1/bills/{id}` atau session bill data | • Total tagihan riil yang harus dibayar.<br>• Nomor invoice & periode.<br>• Status tagihan: apakah simulasi atau resmi. | Pengguna memilih metode pembayaran: Radio button.<br>Klik "Lanjut Pembayaran" -> Form submit / redirect ke `/payment-instruction?bill_id={id}&method={method}`. |
| **`billing/payment-instruction.blade.php`** | `POST /api/v1/payments`<br>atau `POST /payment/submit-manual` | • Nomor rekening tujuan transfer resmi PDAM.<br>• Nama penerima rekening PDAM.<br>• Nominal transfer tepat.<br>• Batas waktu transfer. | **Aksi Utama Baru:**<br>Pengguna mengisi formulir transfer manual:<br>- File bukti bayar (`proof_photo`)<br>- Nama pengirim (`sender_name`)<br>- Bank pengirim (`bank_name`)<br>- No. rekening pengirim (`sender_account_number`)<br>- No. referensi transfer (`reference_number`)<br>Klik "Saya Sudah Membayar" -> Kirim `multipart/form-data`. |
| **`billing/payment-success.blade.php`** | `GET /api/v1/payments/{id}`<br>atau data tagihan terkait | • Nomor pembayaran (`PAY-PYK-...`).<br>• Status pembayaran riil: `PENDING` (Menunggu Verifikasi) atau `PAID`/`VERIFIED` (Lunas).<br>• Waktu transaksi dan kanal transfer. | Tampilan menyesuaikan state: jika `PENDING`, menampilkan instruksi bahwa bukti sedang diverifikasi admin. Jika `PAID`, menampilkan kuitansi lunas resmi. |

---

### 5. KETIDAKSESUAIAN JSON API DENGAN KEBUTUHAN FRONTEND

Berdasarkan perbandingan detail antara kontrak JSON backend dan kebutuhan skrip frontend, ditemukan beberapa perbedaan struktural:

1. **Format Penamaan Atribut (*Property Naming Discrepancy*):**
   - Di `swc-store.js`, properti tagihan dinamai secara singkat: `inv`, `period`, `usage`, `amount`, `dueDate`, `paidAt`, `method`.
   - Di `BillResource` backend, penamaan mengikuti standar RESTful Laravel: `bill_number`, `billing_period`, `period_label`, `consumption`, `total_amount`, `due_date`, `paid_at`, `status`.
   - *Solusi:* Buat fungsi mapping/adapter pada `swc-store.js` yang secara otomatis mentransformasi objek `BillResource` dari backend menjadi format yang dikenali oleh komponen UI frontend, atau perbarui renderer frontend untuk membaca properti standar tanpa mengubah class CSS/HTML.

2. **Perbedaan Alur Verifikasi Pembayaran (*Asynchronous Verification vs Instant Simulation*):**
   - Frontend saat ini mengasumsikan pembayaran langsung lunas seketika (*instant auto-settlement*), lalu langsung menampilkan halaman *"Pembayaran Berhasil / Kuitansi Lunas"*.
   - Backend menerapkan aturan bisnis pembayaran manual (Task 6 & Task 8): pembayaran manual yang disubmit pelanggan berstatus `PENDING`, dan tagihan berstatus `PENDING_VERIFICATION`. Tagihan **TIDAK BOLEH** langsung berubah menjadi `PAID` sebelum diverifikasi oleh admin PDAM.
   - *Solusi:* Halaman `payment-instruction.blade.php` dan `payment-success.blade.php` harus mengakomodasi status `PENDING` (misal: "Bukti Pembayaran Diterima — Menunggu Verifikasi Petugas"). Tagihan pada `billing/index.blade.php` harus memiliki badge status *"Menunggu Verifikasi"* selain *"Belum Bayar"* dan *"Lunas"*.

3. **Ketiadaan Input Berkas Bukti Transfer pada Frontend:**
   - Backend `StoreManualPaymentRequest` mewajibkan upload file bukti struk transfer (`proof_photo` max 5 MB).
   - Frontend di `payment-instruction.blade.php` saat ini hanya memiliki tombol teks *"Saya Sudah Membayar (Verifikasi Rekening)"* tanpa formulir `<input type="file">`.
   - *Solusi:* Diperlukan penambahan komponen form upload struk dan input identitas pengirim pada `payment-instruction.blade.php` dengan gaya desain visual yang tetap selaras dengan layout yang ada.

4. **Penanganan Tagihan SIMULATION vs OFFICIAL:**
   - Backend Task 8 menghasilkan dua tipe tagihan: `SIMULATION` (dengan nomor `SIM-PYK-...`) jika menggunakan tarif provisional, dan `OFFICIAL` (dengan nomor `INV-PYK-...`) jika menggunakan tarif resmi disetujui PDAM.
   - Frontend saat ini belum memiliki pembeda visual (badge atau penanda) untuk tagihan simulasi.
   - Tagihan berstatus `SIMULATION` tidak boleh mengecoh pelanggan untuk melakukan transfer uang sungguhan ke rekening resmi PDAM.
   - *Solusi:* Tambahkan badge `[SIMULASI / DEMO]` pada kartu tagihan bila `bill.bill_type === 'SIMULATION'`, serta catatan edukasi bahwa ini merupakan simulasi perhitungan sistem.

---

### 6. KEBUTUHAN AUTENTIKASI DAN OTORISASI

1. **Sesi Web vs REST API Token:**
   - Aplikasi saat ini menggunakan autentikasi sesi bawaan Laravel (`web` guard).
   - Seluruh rute API di `routes/api.php` telah dilindungi middleware `auth` (berbagi sesi cookie session web).
   - Permintaan `fetch()` dari JavaScript frontend ke endpoint API `/api/v1/bills/*` dan `/api/v1/payments/*` wajib menyertakan:
     - Header `X-CSRF-TOKEN`: diambil dari `<meta name="csrf-token" content="{{ csrf_token() }}">` pada layout `app.blade.php`.
     - Header `Accept: application/json`.
     - Kredensial cookie sesi (`credentials: 'same-origin'`).

2. **Otorisasi Kepemilikan Data (Pencegahan IDOR):**
   - Pelanggan hanya diizinkan melihat dan membayar tagihan miliknya sendiri (`BillPolicy@view` dan `BillPolicy@pay`).
   - Pelanggan dilarang keras memverifikasi pembayarannya sendiri (`PaymentPolicy@verify` mengembalikan `403 Forbidden` untuk role `customer`).
   - Skenario pengujian otorisasi ini sudah dibuktikan lolos 100% pada PHPUnit test suite.

---

### 7. KEBUTUHAN KONFIGURASI REKENING PEMBAYARAN YANG BELUM TERSEDIA

Sesuai instruksi mutlak: **Jangan mengarang nomor rekening, nama pemilik rekening, atau informasi bank PDAM**.

1. **Kondisi Data Saat Ini pada Frontend:**
   - Pada `payment-instruction.blade.php`, masih tercantum data placeholder simulasi:
     - Nomor Virtual Account: `8808 1234567890 01` (statis).
     - Nama Penerima: `PDAM Smart Water - Budi Santoso` (statis).
     - Bank BCA, Mandiri, dan QRIS fiktif.

2. **Kebutuhan Teknis:**
   - Pembayaran gateway (Midtrans / Xendit) **belum diintegrasikan** sesuai arahan Task 6 (menunggu persetujuan resmi mitra).
   - Metode pembayaran utama yang akan diintegrasikan adalah **Transfer Bank Manual ke Rekening Kas PDAM Kota Payakumbuh**.
   - Untuk mencegah data fiktif beredar di produksi, perlu dibuat file konfigurasi terpusat: `config/billing.php`.
   - File konfigurasi ini akan membaca variabel *environment* `.env`:
     ```env
     PDAM_BANK_NAME="Bank Nagari (Menunggu Konfirmasi)"
     PDAM_ACCOUNT_NUMBER="[MENUNGGU KONFIRMASI MITRA PDAM]"
     PDAM_ACCOUNT_NAME="Perumda Tirta Sago Kota Payakumbuh"
     ```
   - Pada halaman `payment-instruction.blade.php`, data rekening akan diambil dari konfigurasi ini. Jika nomor rekening belum dikonfirmasi, sistem menampilkan label peringatan informatif *"Rekening Pembayaran Resmi Menunggu Konfirmasi Mitra PDAM"*.

---

### 8. DAFTAR FILE YANG PERLU DIUBAH PADA IMPLEMENTASI TASK 10

Berikut daftar berkas yang dipersiapkan untuk integrasi pada Task 10 tanpa merusak UI/UX yang ada:

| No | File yang Akan Dimodifikasi | Peran & Rencana Perubahan pada Task 10 |
|---|---|---|
| 1 | `config/billing.php` *(File Baru)* | Menyediakan konfigurasi resmi rekening tujuan pembayaran PDAM, batas jatuh tempo default, dan status mode simulasi tanpa hardcode. |
| 2 | `app/Http/Controllers/SmartWaterPortalController.php` | Menginjeksi data tagihan riil dari `BillingService` ke view `billing.index`, `billing.detail`, `billing.history`, `billing.payment-method`, `billing.payment-instruction`, dan `billing.payment-success`. |
| 3 | `public/assets/js/swc-store.js` | Menambahkan method sinkronisasi REST API untuk tagihan: `fetchBills()`, `getActiveBill()`, dan `submitManualPaymentApi()`. Mempertahankan fallback `localStorage` bila offline/demo. |
| 4 | `resources/views/billing/index.blade.php` | Mengganti teks statis April 2025 dengan data dinamis Blade/JS. Menambahkan badge `SIMULATION` vs `OFFICIAL`. Menambahkan status `PENDING_VERIFICATION` ("Menunggu Verifikasi Admin"). |
| 5 | `resources/views/billing/detail.blade.php` | Menampilkan rincian blok pemakaian air dinamis dari tabel `bill_details`, dial meteran riil, dan penanda tagihan simulasi vs resmi. |
| 6 | `resources/views/billing/history.blade.php` | Mengganti array statis Blade dengan riwayat pembayaran riil yang ditarik dari database MySQL (`Bill::where('status', 'PAID')`). |
| 7 | `resources/views/billing/payment-method.blade.php` | Menyesuaikan pilihan metode pembayaran dengan ketentuan pembayaran manual transfer bank PDAM dan dompet smart water. Memberikan penanda jika tagihan adalah simulasi. |
| 8 | `resources/views/billing/payment-instruction.blade.php` | Menambahkan form unggah berkas struk transfer (`proof_photo`, nama pengirim, nomor rekening pengirim) secara visual harmonis, membaca rekening tujuan dari konfigurasi, dan mengirim data via form POST ke backend. |
| 9 | `resources/views/billing/payment-success.blade.php` | Mengakomodasi 2 kondisi tampilan: (1) Konfirmasi pengajuan transfer terkirim (Status: PENDING), dan (2) Kuitansi lunas resmi (Status: PAID). |
| 10 | `resources/views/admin/dashboard.blade.php` | Mengintegrasikan tombol verifikasi pembayaran bagi Admin/Staff di tab manajemen tagihan agar admin dapat menyetujui (`VERIFIED`) atau menolak (`REJECTED`) pembayaran manual secara langsung dari UI. |

---

### 9. RISIKO REGRESI DAN STRATEGI PENGUJIAN

#### A. Identifikasi Risiko Regresi
1. **Risiko Kerusakan Tampilan (UI/Layout Shift):**
   Penambahan input form upload atau badge dinamis berpotensi menggeser layout jika class Tailwind tidak diselaraskan dengan seksama.
2. **Risiko Pemutusan Fungsionalitas Demo/Offline:**
   Jika `localStorage` langsung dihapus total, pengguna yang membuka halaman tanpa login atau dalam mode presentasi demo lokal dapat mengalami layar kosong (*blank screen*).
3. **Risiko Penolakan Validasi Form (*Validation Mismatch*):**
   Form upload bukti pembayaran harus mengirimkan *header* `Content-Type: multipart/form-data` dengan token CSRF. Jika dikirim sebagai JSON biasa, upload file akan gagal.
4. **Risiko Ambiguitas Tagihan Resmi vs Simulasi:**
   Pengguna dapat mengira tagihan provisional sebagai tagihan riil jika penanda simulasi tidak ditampilkan secara transparan di antarmuka.

#### B. Strategi Pengujian Task 10
1. **Penerapan *Graceful Fallback*:**
   Kode JavaScript akan memeriksa ketersediaan data dari backend. Jika endpoint mengembalikan data kosong atau gagal diakses, sistem secara elegan menggunakan data cadangan agar UI tidak pernah rusak (*blank*).
2. **Preservasi Elemen Styling & ID HTML:**
   Tidak ada class Tailwind, ID, layout grid, animasi, maupun komponen visual yang diubah secara drastis. Seluruh perubahan hanya berupa injeksi konten teks dinamis dan elemen input pendukung.
3. **Automated End-to-End Feature Testing:**
   Membuat feature test suite baru (`BillingFrontendIntegrationTest.php`) yang memverifikasi:
   - Akses halaman `/bill` merender data tagihan dari database MySQL.
   - Akses `/bill-detail` merender rincian konsumsi kubikasi riil.
   - Form submit pembayaran manual dari `/payment-instruction` berhasil menyimpan berkas ke `storage/app/public/payment_proofs` dan membuat record `payments` berstatus `PENDING`.
   - Admin memverifikasi pembayaran dari dashboard, dan status tagihan berubah menjadi `PAID`.
   - Halaman `/payment-success` dan `/bill-history` menampilkan riwayat lunas secara konsisten.
4. **Verifikasi Live MySQL & Browser Testing:**
   Menjalankan script validasi MySQL dan verifikasi langsung pada browser untuk memastikan transisi state berjalan mulus tanpa error konsol JavaScript.

---
**Kesimpulan:**  
Frontend Blade dan backend Laravel 12 Smart Water Kota Payakumbuh berada dalam kondisi siap untuk diintegrasikan secara penuh pada Task 10. Audit ini memberikan peta jalan integrasi yang aman, terisolasi, dan mempertahankan integritas estetika UI 100%.
