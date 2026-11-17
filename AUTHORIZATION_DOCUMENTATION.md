# DOKUMENTASI SISTEM OTORISASI, LARAVEL POLICIES & KEPEMILIKAN DATA (DATA OWNERSHIP)
## Smart Water Kota Payakumbuh — Laravel 12 Backend

---

## 1. PENDAHULUAN & PRINSIP ARSITEKTUR

Dokumentasi ini menjelaskan implementasi otorisasi granular pada level data (**Data-Level Authorization & Policies**) untuk project **Smart Water Kota Payakumbuh** pada Laravel 12.

Berbeda dengan Middleware yang hanya memeriksa apakah pengguna memiliki peran tertentu (Role-Based Access Control / RBAC), **Laravel Policies** memeriksa apakah pengguna terautentikasi berhak melakukan tindakan tertentu pada **instansi data spesifik**.

### Tiga Prinsip Utama yang Ditegakkan:
1. **Pemisahan Wewenang Antar-Petugas (Staff Boundary & Ticket Isolation):**
   Petugas A tidak diperbolehkan memperbarui status, menambahkan tanggapan resmi, menyelesaikan, atau merebut/menugaskan tiket yang telah resmi ditugaskan kepada Petugas B, kecuali tindakan dilakukan oleh Administrator SCADA (Supervisor).
2. **Pencegahan Insecure Direct Object References (Anti-IDOR):**
   Pelanggan tidak dapat mengakses atau memanipulasi data privat milik pelanggan lain dengan mengganti parameter ID numerik pada URL atau Payload permintaan.
3. **Imutabilitas Basis Data pada Operasi Ilegal (Database Immutability):**
   Ketika sebuah aksi ditolak oleh sistem otorisasi (menghasilkan HTTP 403 Forbidden), status data di MySQL dijamin tidak berubah sama sekali (*zero side-effects*).
4. **Pencegahan Eskalasi Hak Akses (Anti-Privilege Escalation):**
   Pelanggan aktif dicegah mengeksploitasi rute demo kedinasan (`/portal-petugas`, `/masuk-petugas`) untuk mengangkat hak akses akunnya menjadi staf secara ilegal.

---

## 2. DAFTAR LARAVEL POLICIES YANG DIIMPLEMENTASIKAN

Seluruh policy berlokasi di direktori `app/Policies/` dan didaftarkan pada `app/Providers/AppServiceProvider.php`:

### A. `ComplaintPolicy` (`app/Policies/ComplaintPolicy.php`)
Mengatur siklus hidup dan wewenang penanganan tiket pengaduan pelanggan:
- `view(?User $user, Complaint $complaint): bool`:
  - Tiket anonim/publik (`user_id === null`): dapat dilihat oleh siapa saja (termasuk tamu tanpa login).
  - Tiket privat (`user_id !== null`): hanya pelapor pemilik tiket atau staf/admin SCADA yang berhak melihat rincian tiket.
- `create(?User $user): bool`: Terbuka untuk seluruh warga (tamu maupun terautentikasi).
- `update(User $user, Complaint $complaint): bool`:
  - Admin: berhak memperbarui seluruh tiket kota.
  - Staf: hanya berhak jika tiket belum ditugaskan (`assigned_to === null`) atau ditugaskan kepada dirinya sendiri (`assigned_to === $user->id`).
- `assign(User $user, Complaint $complaint): bool`:
  - Admin: berhak menugaskan atau mendisposisikan ulang seluruh tiket.
  - Staf: hanya berhak jika tiket belum ditugaskan (klaim mandiri) atau ditugaskan kepada dirinya sendiri. Staf lain ditolak (HTTP 403).
- `respond(User $user, Complaint $complaint): bool`:
  - Hanya admin atau staf yang ditugaskan yang berhak menambahkan tanggapan resmi operasional.
- `resolve(User $user, Complaint $complaint): bool`:
  - Hanya admin atau staf yang ditugaskan resmi yang berhak menyelesaikan atau menolak tiket tuntas.

### B. `MeterReadingPolicy` (`app/Policies/MeterReadingPolicy.php`)
Mengatur pembacaan stand meter AI-OCR dan verifikasinya:
- `viewAny(?User $user): bool`: Diizinkan (filtering dinamis: tamu menerima list kosong demi privasi warga, pelanggan hanya menerima riwayat miliknya, staf menerima seluruh antrean).
- `view(?User $user, MeterReading $reading): bool`: Hanya pemilik laporan atau staf/admin yang berhak melihat rincian stand meter.
- `create(?User $user): bool`: Terbuka untuk pelanggan dan tamu.
- `validate(User $user, MeterReading $reading): bool`: Hanya petugas lapangan atau admin SCADA yang berhak menyetujui atau menolak laporan meter.

### C. `CustomerProfilePolicy` (`app/Policies/CustomerProfilePolicy.php`)
Mengatur isolasi data rekening air, saldo dompet, dan poin reward:
- `view(User $user, CustomerProfile $profile): bool`: Hanya pemilik akun atau admin yang berhak melihat.
- `update(User $user, CustomerProfile $profile): bool`: Hanya pemilik akun atau admin yang berhak memperbarui nomor telepon dan alamat dinisbatkan.

### D. `NotificationPolicy` (`app/Policies/NotificationPolicy.php`)
Mengatur kotak masuk pesan notifikasi privat:
- `view(User $user, Notification $notification): bool`: Hanya penerima notifikasi (`$user->id === $notification->user_id`) atau admin.
- `update(User $user, Notification $notification): bool`: Hanya penerima notifikasi yang berhak menandai pesan sebagai telah dibaca (`is_read = true`).

### E. `AcademyProgressPolicy` (`app/Policies/AcademyProgressPolicy.php`)
Mengatur progres pembelajaran kurikulum air dan evaluasi kuis:
- `view(User $user, UserLessonProgress|UserQuizResult $progress): bool`: Hanya pemilik progres atau admin.
- `completeLesson(User $user): bool`: Mengizinkan pengguna menyelesaikan bab materi untuk akunnya sendiri.
- `completeQuiz(User $user): bool`: Mengizinkan pengguna menyelesaikan kuis untuk akunnya sendiri.

### F. `ChallengeParticipantPolicy` (`app/Policies/ChallengeParticipantPolicy.php`)
Mengatur partisipasi tantangan hemat air:
- `view(?User $user, ChallengeParticipant $participant): bool`: Hanya peserta bersangkutan atau admin.
- `create(User $user): bool`: Mengizinkan pendaftaran tantangan atas nama pengguna aktif.

### G. `ChallengeSubmissionPolicy` (`app/Policies/ChallengeSubmissionPolicy.php`)
Mengatur pengumpulan bukti misi warga:
- `view(?User $user, ChallengeSubmission $submission): bool`: Hanya peserta pemilik bukti atau admin.
- `update(User $user, ChallengeSubmission $submission): bool`: Hanya peserta pemilik misi yang berhak mengumpulkan foto dan catatan refleksi.

---

## 3. PENEGAKAN OTORISASI PADA CONTROLLER & ROUTES

### Penegakan di `ComplaintController`:
```php
// Pemeriksaan wewenang melihat tiket (Anti-IDOR)
if (!Gate::forUser($user)->allows('view', $complaint)) {
    if (!$user) {
        return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
    }
    return response()->json(['ok' => false, 'error' => 'Akses ditolak: Anda tidak memiliki wewenang melihat tiket milik pengguna lain.'], 403);
}

// Pemeriksaan batas kewenangan penugasan staf
if (!Gate::forUser($actor)->allows('assign', $complaint)) {
    return response()->json(['ok' => false, 'error' => 'Akses ditolak: Anda tidak memiliki wewenang menugaskan tiket yang menjadi tanggung jawab petugas lain.'], 403);
}
```

### Penegakan di `MeterReadingController`:
```php
// Pemeriksaan validasi laporan stand meter
if (!Gate::forUser($validator)->allows('validate', $reading)) {
    return response()->json(['ok' => false, 'error' => 'Akses ditolak: Hanya petugas SCADA yang berhak memvalidasi laporan meter.'], 403);
}
```

### Penegakan di `SmartWaterPortalController` (Pencegahan Eskalasi Hak Akses):
```php
public function enterAdminDirect(): RedirectResponse
{
    $sessionData = $this->getSessionData();
    $currentUser = Auth::user() ?? (!empty($sessionData['currentUser']['id']) ? User::find($sessionData['currentUser']['id']) : null);

    // Jika pelanggan aktif mencoba masuk melalui rute demo petugas, tolak dan kembalikan ke portal pelanggan
    if ($currentUser && !$currentUser->isStaff()) {
        return redirect(url('/user'))->with('error', 'Akses ditolak: Akun pelanggan tidak diizinkan masuk ke Portal Petugas SCADA.');
    }

    $staffUser = AuthService::authenticateStaff('', '', true);
    // ...
}
```

---

## 4. MATRIKS HAK AKSES DAN RESPON STATUS HTTP

| Skenario Operasi | Aktor Penguji | Target Data | Ekspektasi Status | Pesan Respon | Status Data di DB |
| :--- | :--- | :--- | :---: | :--- | :---: |
| Ubah Status Tiket | Petugas A | Tiket Petugas B | **403 Forbidden** | Akses ditolak: Petugas lain | **Tidak Berubah** |
| Beri Catatan/Tanggapan | Petugas A | Tiket Petugas B | **403 Forbidden** | Akses ditolak: Petugas lain | **Histori Tidak Bertambah** |
| Selesaikan Tiket | Petugas A | Tiket Petugas B | **403 Forbidden** | Akses ditolak: Petugas lain | **resolved_at Tetap Null** |
| Disposisi/Tugaskan Ulang | Petugas A | Tiket Petugas B | **403 Forbidden** | Akses ditolak: Petugas lain | **assigned_to Tetap Petugas B** |
| Ubah/Tanggapi/Selesai | Petugas B (Assigned) | Tiket Petugas B | **200 OK** | Tiket berhasil diperbarui | **Tersimpan ke MySQL** |
| Klaim Tiket Baru | Petugas A | Tiket Unassigned | **200 OK** | Berhasil ditugaskan ke Petugas A | **assigned_to = Petugas A** |
| Supervisi Tiket Mana Pun | Admin SCADA | Seluruh Tiket | **200 OK** | Berhasil dieksekusi | **Tersimpan ke MySQL** |
| Lihat Tiket Privat | Pelanggan B | Tiket Pelanggan A | **403 Forbidden** | Akses ditolak: Tiket milik warga lain | **Aman Terisolasi** |
| Lihat Tiket Anonim | Tamu / Pelanggan | Tiket Anonim | **200 OK** | Data tiket publik | **Aman Terbaca** |
| Validasi Stand Meter | Pelanggan | Laporan Meter | **403 Forbidden** | Akses ditolak: Khusus petugas | **Status Menunggu Validasi** |
| Buka `/portal-petugas` | Pelanggan Aktif | Portal Kedinasan | **302 Redirect (/user)** | Akses ditolak: Akun pelanggan | **Role Tetap Customer** |
| Buka `/portal-petugas` | Tamu (Demo Mode) | Portal Kedinasan | **302 Redirect (/admin-dashboard)** | Masuk demo petugas | **Sesi Staf Demo** |

---

## 5. HASIL PENGUJIAN OTOMASI (AUTOMATED TESTS)

Penerapan otorisasi Task 5 telah diverifikasi secara menyeluruh menggunakan dua rangkaian pengujian:

1. **Rangkaian Feature & Unit Test Laravel (`php artisan test`)**:
   - Total Test: **62 Tests PASSED (232 Assertions, 100% Lulus)**.
   - Durasi: **3.27 detik**.
   - Berkas pengujian khusus: `tests/Feature/PolicyAuthorizationTest.php` (16 test case mencakup staff boundary, database immutability, anti-IDOR, dan demo privilege escalation rejection).

2. **Rangkaian Integrasi MySQL Nyata (`php tests/full_mysql_validation.php`)**:
   - Total Skenario: **47 Skenario PASSED (100% Lulus)**.
   - Meliputi database MySQL 8.4 riil (28 tabel, 24 foreign keys, multi-device ticket flow, dan validasi keamanan XSS/SQL Injection).
