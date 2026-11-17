# AUDIT OTORISASI MENDALAM, LARAVEL POLICIES & KEPEMILIKAN DATA (DATA OWNERSHIP)
## Smart Water Kota Payakumbuh — Laravel 12 Backend Architecture

**Tanggal Audit:** 17 November 2026  
**Status:** COMPLETE AUDIT (Menuju Implementasi Task 5)  
**Lingkup:** Models, Controllers, Services, Form Requests, Middleware, Routes, Data Ownership, Anti-IDOR, dan Pencegahan Eskalasi Hak Akses.

---

## 1. RINGKASAN EKSEKUTIF

Pada tahap sebelumnya (Task 4), sistem keamanan Role-Based Access Control (RBAC) pada level perutean (Route Middleware) telah berhasil ditegakkan (`RoleMiddleware`, proteksi `/admin-dashboard`, `/admin`, endpoint operasional, dan pencegahan role-switching ilegal).

Namun, pemeriksaan keamanan berbasis role pada middleware saja **belum mencukupi** untuk menjamin keamanan level data (Data-Level Security). Diperlukan lapisan otorisasi granular melalui **Laravel Policies** dan **Data Ownership Checks** untuk menjawab skenario-skenario kritis:
1. **Pemisahan Kewenangan Antar-Petugas (Staff Isolation Boundary):** Petugas A tidak boleh mengubah, menanggapi, menugaskan ulang, atau menyelesaikan tiket pengaduan yang sudah ditugaskan secara resmi kepada Petugas B, kecuali dilakukan oleh Administrator SCADA (Supervisor).
2. **Pencegahan Insecure Direct Object Reference (IDOR):** Pelanggan tidak boleh mengakses atau memanipulasi entitas privat milik pelanggan lain hanya dengan mengubah parameter ID pada URL atau body permintaan.
3. **Pencegahan Manipulasi Parameter (Payload Tampering / Anti-Mass Assignment):** Aplikasi tidak boleh mempercayai nilai sensitif seperti `user_id`, `points`, atau `wallet_balance` yang dikirim dari klien. Identitas harus selalu diambil dari sesi terautentikasi (`auth()->id()`).
4. **Hardening Rute Demo terhadap Eskalasi Hak Akses:** Rute kemudahan demo kedinasan (`/portal-petugas`, `/masuk-petugas`) tidak boleh dimanfaatkan oleh pelanggan aktif untuk menaikkan privilese akunnya menjadi petugas secara diam-diam.

---

## 2. AUDIT MODEL & RELASI KEPEMILIKAN DATA (DATA OWNERSHIP)

| Model Eloquent | Kolom Pemilik / Penugasan | Relasi User | Aturan Kepemilikan & Integritas | Status Risiko Awal |
|---|---|---|---|---|
| **Complaint** | `user_id` (pelapor), `assigned_to` (petugas TRC) | `belongsTo(User)` | - Jika `user_id == null`: Tiket publik/anonim warga.<br>- Jika `user_id != null`: Hanya pelapor & staf/admin yang boleh melihat rincian tiket.<br>- Jika `assigned_to != null`: Hanya staf bersangkutan & admin yang berhak melakukan update, respond, resolve, dan reassign. | MEDIUM (Staf A dapat memodifikasi tiket Staf B jika hanya memakai middleware `role:admin,staff`). |
| **MeterReading** | `user_id` (pelanggan), `validated_by` (petugas SCADA) | `belongsTo(User)` | - Tamu (Guest) hanya melihat daftar kosong.<br>- Pelanggan hanya melihat laporan meter miliknya sendiri.<br>- Validasi hanya boleh dieksekusi oleh Petugas/Admin. | LOW-MEDIUM (Perlu Policy `validate` eksplisit). |
| **CustomerProfile** | `user_id` | `belongsTo(User)` | - Profil sensitif mencakup saldo dompet air, poin konservasi, alamat, dan nomor meter.<br>- Pembaruan profil hanya boleh dilakukan oleh pemilik akun itu sendiri (`auth()->id() === $profile->user_id`). | SECURE (Sudah terikat ke `auth()->user()`, perlu Policy formal). |
| **Notification** | `user_id` | `belongsTo(User)` | - Pesan notifikasi bersifat privat per pengguna.<br>- Pembacaan dan status `is_read` hanya boleh diubah oleh pemilik notifikasi. | SECURE (Terkunci via Service dengan parameter `auth()->id()`). |
| **UserLessonProgress** | `user_id`, `lesson_id` | `belongsTo(User)` | - Progres materi dan modul belajar warga.<br>- Penyelesaian materi hanya boleh dicatat untuk user yang sedang aktif login. | SECURE (Terkunci via Service). |
| **UserQuizResult** | `user_id`, `course_id` | `belongsTo(User)` | - Hasil evaluasi kuis dan poin XP edukasi.<br>- Skor dan XP tidak boleh dimanipulasi untuk user lain. | SECURE (Terkunci via Service). |
| **ChallengeParticipant** | `user_id`, `challenge_id` | `belongsTo(User)` | - Partisipasi tantangan hemat air.<br>- Pendaftaran hanya boleh dilakukan oleh akun pemilik. | SECURE (Terkunci via Service). |
| **ChallengeSubmission** | `participant_id` (terikat ke `ChallengeParticipant.user_id`) | Via `participant->user` | - Bukti foto dan catatan misi tantangan warga.<br>- Penyerahan misi hanya boleh dilakukan oleh peserta pemilik tantangan. | SECURE (Terkunci via Service). |

---

## 3. IDENTIFIKASI KERENTANAN & CELAH OTORISASI AKTUAL

### Celah 1: Batasan Kewenangan Antar-Petugas TRC (Staff Authority Boundary)
- **Lokasi:** `ComplaintController.php` (`updateStatus`, `assign`, `respond`, `resolve`), `AssignComplaintRequest.php`, `ResolveComplaintRequest.php`.
- **Kondisi:** Saat ini endpoint tersebut hanya diproteksi dengan Middleware `role:admin,staff` dan Form Request `isStaffOrAdmin()`.
- **Dampak Potensial:** Petugas TRC "A" dapat mengubah status atau menyelesaikan tiket milik Petugas "B" yang sedang bertugas di lapangan.
- **Mitigasi Task 5:** Terapkan pemeriksaan ketat pada `ComplaintPolicy`:
  - `admin`: memiliki kewenangan supervisi menyeluruh atas seluruh tiket.
  - `staff`: hanya berhak memperbarui status, menanggapi, menyelesaikan, atau menugaskan tiket yang **belum ditugaskan** (`assigned_to === null`) ATAU yang **ditugaskan kepada dirinya sendiri** (`assigned_to === auth()->id()`).
  - Jika tiket sudah menjadi tanggung jawab Petugas B, Petugas A akan ditolak dengan `403 Forbidden` dan kondisi database tetap tidak berubah (immutable).

### Celah 2: Potensi Eskalasi Hak Akses pada Rute Demo Kedinasan
- **Lokasi:** `SmartWaterPortalController::enterAdminDirect()` pada `/portal-petugas` dan `/masuk-petugas`.
- **Kondisi:** Rute ini otomatis memanggil `AuthService::authenticateStaff('', '', true)` untuk kenyamanan simulasi demo pameran/pengujian lokal.
- **Dampak Potensial:** Jika seorang pelanggan aktif (`role: customer`) sedang login dan membuka tautan `/portal-petugas`, sesi pelanggan tersebut langsung di-overwrite menjadi sesi staf, menghasilkan eskalasi hak akses (privilege escalation) yang tidak sah.
- **Mitigasi Task 5:** Tambahkan proteksi: Jika ada pengguna terautentikasi dan pengguna tersebut BUKAN staf/admin (yaitu pelanggan biasa), tolak akses eskalasi dengan pengalihan aman atau abort `403 Forbidden`, menginstruksikan bahwa pelanggan tidak berhak mengakses portal petugas.

### Celah 3: Pengecekan Eksplisit Laravel Policy pada Operasi Lapor Meter
- **Lokasi:** `MeterReadingController::apiValidate()`.
- **Kondisi:** Validasi meter saat ini menggunakan `RoleMiddleware`.
- **Mitigasi Task 5:** Integrasikan pemanggilan `MeterReadingPolicy::validate()` langsung menggunakan `Gate::authorize('validate', $reading)` atau `$this->authorize('validate', $reading)` untuk memenuhi standar baku otorisasi Laravel 12.

---

## 4. MATRIKS KEBIJAKAN OTORISASI (AUTHORIZATION MATRIX)

| Modul & Aksi | Guest (Tamu) | Pelanggan Pemilik | Pelanggan Lain | Petugas Lapangan (Assigned) | Petugas Lapangan (Unassigned / Other) | Admin SCADA |
|---|---|---|---|---|---|---|
| **Complaint: View Anonymous** | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) |
| **Complaint: View Authenticated** | Deny (401) | Allow (200) | **Deny (403)** | Allow (200) | Allow (200) | Allow (200) |
| **Complaint: Create** | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) |
| **Complaint: Claim Unassigned** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | Allow (200) | Allow (200) |
| **Complaint: Update Status Assigned** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | **Deny (403)** | Allow (200) |
| **Complaint: Add Response Assigned** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | **Deny (403)** | Allow (200) |
| **Complaint: Resolve Ticket Assigned** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | **Deny (403)** | Allow (200) |
| **Complaint: Reassign Assigned** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | **Deny (403)** | Allow (200) |
| **MeterReading: View List** | Filter Kosong | Filter Sendiri | Filter Sendiri | Filter Lengkap | Filter Lengkap | Filter Lengkap |
| **MeterReading: Store Report** | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) | Allow (200) |
| **MeterReading: Validate** | Deny (401) | Deny (403) | Deny (403) | Allow (200) | Allow (200) | Allow (200) |
| **Profile: View & Update** | Deny (401) | Allow (200) | **Deny (403)** | Allow (Sendiri) | Allow (Sendiri) | Allow (Supervisi) |
| **Notification: View & Mark Read** | Deny (401) | Allow (200) | **Deny (403)** | Allow (Sendiri) | Allow (Sendiri) | Allow (Supervisi) |
| **Academy: Complete Lesson/Quiz** | Deny (401) | Allow (200) | **Deny (403)** | Allow (Sendiri) | Allow (Sendiri) | Allow (200) |
| **Challenge: Enroll & Complete Mission**| Deny (401) | Allow (200) | **Deny (403)** | Allow (Sendiri) | Allow (Sendiri) | Allow (200) |
| **Portal Petugas (`/portal-petugas`)** | Allow (Demo) | **Deny (403)** | **Deny (403)** | Allow (200) | Allow (200) | Allow (200) |

---

## 5. RANCANGAN LARAVEL POLICIES (TASK 5)

Berikut adalah 7 Policy yang akan dibuat di bawah direktori `app/Policies/`:

1. **`app/Policies/ComplaintPolicy.php`**
   - `view(?User $user, Complaint $complaint): bool`
   - `create(?User $user): bool`
   - `update(User $user, Complaint $complaint): bool`
   - `assign(User $user, Complaint $complaint): bool`
   - `respond(User $user, Complaint $complaint): bool`
   - `resolve(User $user, Complaint $complaint): bool`

2. **`app/Policies/MeterReadingPolicy.php`**
   - `viewAny(?User $user): bool`
   - `view(?User $user, MeterReading $meterReading): bool`
   - `create(?User $user): bool`
   - `validate(User $user, MeterReading $meterReading): bool`

3. **`app/Policies/CustomerProfilePolicy.php`**
   - `view(User $user, CustomerProfile $profile): bool`
   - `update(User $user, CustomerProfile $profile): bool`

4. **`app/Policies/NotificationPolicy.php`**
   - `view(User $user, Notification $notification): bool`
   - `update(User $user, Notification $notification): bool`

5. **`app/Policies/AcademyProgressPolicy.php`**
   - `view(User $user, UserLessonProgress|UserQuizResult $progress): bool`
   - `completeLesson(User $user): bool`
   - `completeQuiz(User $user): bool`

6. **`app/Policies/ChallengeParticipantPolicy.php`**
   - `view(?User $user, ChallengeParticipant $participant): bool`
   - `create(User $user): bool`

7. **`app/Policies/ChallengeSubmissionPolicy.php`**
   - `view(?User $user, ChallengeSubmission $submission): bool`
   - `update(User $user, ChallengeSubmission $submission): bool`

---

## 6. RENCANA PENGUJIAN OTOMASI (AUTOMATED TESTS)

Akan dibuat pengujian fitur komprehensif pada `tests/Feature/PolicyAuthorizationTest.php` yang mencakup:
1. Pembuktian Staff A ditolak (403) saat mencoba mengubah status, menanggapi, menugaskan ulang, atau menyelesaikan tiket milik Staff B.
2. Pembuktian integritas data (Database Immutability): status tiket tidak berubah di MySQL saat operasi ditolak.
3. Pembuktian Admin dapat memperbarui dan menyelesaikan tiket apa pun.
4. Pembuktian Pelanggan A ditolak saat mencoba mengakses profil atau tiket privat Pelanggan B.
5. Pembuktian Pelanggan aktif ditolak melakukan eskalasi via `/portal-petugas`.
6. Pembuktian seluruh 46 unit test dan 47 skrip MySQL validasi tetap lulus 100%.
