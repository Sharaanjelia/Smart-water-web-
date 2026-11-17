# AUDIT ARSITEKTUR API RESOURCES — SMART WATER KOTA PAYAKUMBUH

Dokumen ini memetakan kondisi transformasi data Eloquent Model menjadi JSON Response di seluruh endpoint Laravel 12 Smart Water Kota Payakumbuh, sebelum implementasi Laravel API Resources.

Tanggal Audit: 17 November 2026  
Status Arsitektur: Task 3 — Implementasi API Resources Layer

---

## 1. PEMETAAN ENDPOINT, CONTROLLER, MODEL, DAN RESPONSE KONTRAK

| # | Endpoint & HTTP Method | Controller & Method | Model & Relasi Terkait | Format JSON Saat Ini (Current Contract) | Frontend Consumer & Test Assertion | Kebutuhan API Resource |
|---|---|---|---|---|---|---|
| 1 | `GET /api/v1/complaints`<br>`GET /api/complaints.php` | `ComplaintController@index` | `Complaint`<br>- `category`<br>- `assignedStaff`<br>- `attachments`<br>- `histories` | `{ ok: true, complaints: [ { id, ticketId, ticket_id, title, category, categoryName, categoryBadge, desc, description, district, address, location, status, priority, userName, userPhone, imageUrl, hasPhoto, assignedTo, assignedStaffId, assignedAt, resolvedAt, resolutionNotes, rejectionReason, createdAt, updatedAt, updates: [...] } ] }` | `public/assets/js/pages/pusat-layanan.js`<br>`public/assets/js/pages/admin.js`<br>`tests/full_mysql_validation.php` (Sec 3.3, 3.5) | `ComplaintResource`<br>`ComplaintHistoryResource`<br>`ComplaintAttachmentResource` |
| 2 | `POST /api/v1/complaints`<br>`POST /api/complaints.php` | `ComplaintController@store` | `Complaint`<br>- Relasi terkait | `{ ok: true, ticket: { ...ticketObj }, complaints: [ ...allTickets ] }` | `pusat-layanan.js`<br>`full_mysql_validation.php` (Sec 3.1) | `ComplaintResource` |
| 3 | `GET /api/v1/complaints/{id}` | `ComplaintController@show` | `Complaint`<br>- Relasi terkait | `{ ok: true, complaint: { ...ticketObj } }` | `pusat-layanan.js`<br>`admin.js`<br>`full_mysql_validation.php` (Sec 3.9) | `ComplaintResource` |
| 4 | `POST /api/v1/complaints/{id}/status`<br>`POST /api/v1/complaints/simulate-progress` | `ComplaintController@updateStatus`<br>`@simulateProgress` | `Complaint`, `ComplaintHistory` | `{ ok: true, message: "...", updatedTicket: { ...ticketObj } }` | `pusat-layanan.js`<br>`admin.js`<br>`full_mysql_validation.php` (Sec 3.4) | `ComplaintResource` |
| 5 | `POST /api/v1/complaints/{id}/assign` | `ComplaintController@assign` | `Complaint`, `User` (Staff) | `{ ok: true, message: "...", updatedTicket: { ...ticketObj } }` | `admin.js`<br>`full_mysql_validation.php` (Sec 3.6) | `ComplaintResource` |
| 6 | `POST /api/v1/complaints/{id}/response` | `ComplaintController@respond` | `Complaint`, `ComplaintHistory` | `{ ok: true, message: "...", updatedTicket: { ...ticketObj } }` | `admin.js`<br>`full_mysql_validation.php` (Sec 3.7) | `ComplaintResource` |
| 7 | `POST /api/v1/complaints/{id}/resolve` | `ComplaintController@resolve` | `Complaint`, `ComplaintHistory` | `{ ok: true, message: "...", updatedTicket: { ...ticketObj } }` | `admin.js`<br>`full_mysql_validation.php` (Sec 3.8) | `ComplaintResource` |
| 8 | `GET /api/v1/meter-reports` | `MeterReadingController@apiList` | `MeterReading`<br>- `validator`<br>- `user.customerProfile` | `{ ok: true, reports: [ { id, reportId, report_number, meterNumber, customerName, customerZone, ocrReading, previousReading, consumption, estimatedCost, status, badgeStatus, badgeColor, submittedAt, validatedAt, validatedBy, rejectionReason, photoUrl, notes } ] }` | `public/assets/js/swc-store.js`<br>`admin.js`<br>`full_mysql_validation.php` (Sec 4.2, 4.4) | `MeterReadingResource` |
| 9 | `POST /api/v1/meter-reports` | `MeterReadingController@apiStore` | `MeterReading`<br>- Relasi terkait | `{ ok: true, message: "...", report: { ...reportObj } }` | `swc-store.js`<br>`full_mysql_validation.php` (Sec 4.1) | `MeterReadingResource` |
| 10 | `POST /api/v1/meter-reports/validate` | `MeterReadingController@apiValidate` | `MeterReading`<br>- `validator` | `{ ok: true, message: "...", report: { ...reportObj } }` | `admin.js`<br>`full_mysql_validation.php` (Sec 4.3) | `MeterReadingResource` |
| 11 | `GET /api/v1/academy`<br>`GET /api/academy.php` | `AcademyController@index` | `Course`<br>- `lessons`<br>- `category` | `{ ok: true, courses: [ { id, title, category, level, estimatedMinutes, xpReward, lessons: [ { id, title, duration, content, order } ] } ], progress: { completedLessons: [...], completedQuizzes: {...}, points: ..., badges: [...] } }` | `public/assets/js/pages/akademi-air.js`<br>`full_mysql_validation.php` (Sec 5.1) | `CourseResource`<br>`LessonResource` |
| 12 | `POST /api/v1/academy/complete-lesson`<br>`POST /api/v1/academy/complete-quiz` | `AcademyController@completeLesson`<br>`@completeQuiz` | `UserLessonProgress`<br>`UserQuizResult` | `{ ok: true, progress: { ...progressObj } }` | `akademi-air.js`<br>`full_mysql_validation.php` (Sec 5.2, 5.3) | `QuizResultResource` / Service Response |
| 13 | `GET /api/v1/challenges`<br>`GET /api/challenges.php` | `ChallengeController@index` | `Challenge`<br>- `missions`<br>- `participants` | `{ ok: true, challenges: [ { id, title, tagline, description, difficulty, durationDays, totalXp, badgeRewardId, badgeTitle, missions: [ { id, title, xp, tip, order } ] } ], progress: { activeChallenges: [...], completedMissions: [...], points: ..., badges: [...] } }` | `public/assets/js/pages/tantangan.js`<br>`full_mysql_validation.php` (Sec 6.1) | `ChallengeResource`<br>`ChallengeMissionResource` |
| 14 | `POST /api/v1/challenges/enroll` | `ChallengeController@enroll` | `Challenge`, `ChallengeParticipant` | `{ ok: true, message: "...", progress: { ... } }` | `tantangan.js`<br>`full_mysql_validation.php` (Sec 6.2) | `ChallengeResource`<br>`ChallengeParticipantResource` |
| 15 | `POST /api/v1/challenges/complete-mission` | `ChallengeController@completeMission` | `ChallengeSubmission`<br>- `participant`<br>- `mission` | `{ ok: true, message: "...", progress: { ... }, already?: bool }` | `tantangan.js`<br>`full_mysql_validation.php` (Sec 6.3) | `ChallengeSubmissionResource` |
| 16 | `GET /api/v1/notifications` | `routes/api.php` | `Notification` | `{ ok: true, notifications: [ { id, type, title, desc, link, isRead, is_read, time, created_at } ], unreadCount: int }` | `swc-store.js`<br>`full_mysql_validation.php` (Sec 7.2) | `NotificationResource` |
| 17 | `GET /api/v1/profile`<br>`POST /api/v1/profile/update` | `routes/api.php` | `User`<br>- `customerProfile`<br>- `staffProfile`<br>- `roles` | `{ ok: true, profile: { id, user_id, name, fullName, email, phone, phoneNumber, role, isAdmin, customerId, meterNumber, tariffCategory, address, zone, district, lastMeterValue, currentMeterValue, walletBalance, points, staffId, nip, department, position } }` | `public/assets/js/pages/user.js`<br>`full_mysql_validation.php` (Sec 8.1, 8.2) | `UserProfileResource`<br>`CustomerProfileResource`<br>`StaffProfileResource` |
| 18 | `POST /api/v1/auth/login`<br>`POST /api/v1/auth/register`<br>`GET /api/v1/auth/me` | `routes/api.php` | `User`<br>- `customerProfile`<br>- `staffProfile`<br>- `roles` | `{ ok: true, user: { ...userProfileObj }, role?: string }` | `public/assets/js/pages/login-daftar.js`<br>`full_mysql_validation.php` (Sec 2.2, 2.3, 2.4, 2.5) | `UserProfileResource` |

---

## 2. KEKURANGAN ARSITEKTUR SEBELUM API RESOURCES

1. **Manual Serialization di Controller & Service**:
   - `ComplaintController::formatTicket()` dan `MeterReadingController::formatReport()` mengimplementasikan array parsing manual langsung di dalam class controller.
   - Apabila controller lain atau endpoint baru membutuhkan format tiket/laporan yang sama, terjadi duplikasi logika pemformatan array.
2. **Ketiadaan Abstraksi Transformasi Model**:
   - Model `Course`, `Lesson`, `Challenge`, dan `ChallengeMission` diekspos melalui pemanggilan atribut mentah model atau mapping array ad-hoc.
3. **Resiko Kebocoran Data Sensitif**:
   - Jika model `User` di-serialize langsung tanpa Resource layer, atribut seperti `password`, `remember_token`, atau metadata internal dapat bocor ke client.
4. **Inkonsistensi Format Tanggal & Nilai Numerik**:
   - Tanggal diformat manual di berbagai tempat (`->format('Y-m-d H:i') . ' WIB'` vs ISO string).
   - Nilai angka desimal (seperti `reading`, `consumption`) berpotensi berubah menjadi string jika diekstrak langsung dari PDO tanpa casting eksplisit.

---

## 3. STRATEGI IMPLEMENTASI API RESOURCES

Untuk menjamin 100% kompatibilitas dengan frontend dan test suite:
1. **Wrapper Handling**:
   - Laravel `JsonResource` secara default membungkus response dengan key `data`.
   - Frontend Smart Water dan test suite mengharapkan struktur root spesifik:
     - `{ "ok": true, "complaints": [...] }`
     - `{ "ok": true, "ticket": {...}, "complaints": [...] }`
     - `{ "ok": true, "reports": [...] }`
     - `{ "ok": true, "courses": [...], "progress": {...} }`
     - `{ "ok": true, "challenges": [...], "progress": {...} }`
     - `{ "ok": true, "notifications": [...], "unreadCount": ... }`
     - `{ "ok": true, "profile": {...} }`
     - `{ "ok": true, "user": {...} }`
   - Strategi: Matikan auto-wrapping default (`JsonResource::withoutWrapping()`) atau gunakan `->resolve()` / mapping custom wrapper langsung di controller response.
2. **Backward Compatibility Function Preservation**:
   - Method statis publik `ComplaintController::formatTicket()` dan `MeterReadingController::formatReport()` dipertahankan dan direfaktor agar memanfaatkan `ComplaintResource` dan `MeterReadingResource`.
3. **Eager Loading Optimization (`whenLoaded`)**:
   - Menggunakan `whenLoaded()` atau helper kondisional untuk relasi database guna mencegah N+1 Query.
4. **Dual Property Aliasing untuk Frontend Safety**:
   - Tiket pengaduan: menyediakan alias `id`, `ticketId`, dan `ticket_number`.
   - Laporan meter: menyediakan alias `id`, `reportId`, dan `report_number`.
   - Profil: menyediakan alias `phone` & `phoneNumber`, `name` & `fullName`, `id` & `user_id`.
   - Notifikasi: menyediakan `isRead` & `is_read`.
   - Kursus: menyediakan `id` (e.g. `'course-1'`), `course_code`, `estimatedMinutes` & `estimated_minutes`, `xpReward` & `xp_reward`.
   - Tantangan: menyediakan `id` (e.g. `'challenge-1'`), `challenge_code`, `durationDays` & `duration_days`, `totalXp` & `xp_total`, `badgeRewardId` & `badge_code`.

---

## 4. DAFTAR API RESOURCES YANG DIBUAT

1. `app/Http/Resources/ComplaintResource.php`
2. `app/Http/Resources/ComplaintHistoryResource.php`
3. `app/Http/Resources/ComplaintAttachmentResource.php`
4. `app/Http/Resources/MeterReadingResource.php`
5. `app/Http/Resources/UserProfileResource.php`
6. `app/Http/Resources/CustomerProfileResource.php`
7. `app/Http/Resources/StaffProfileResource.php`
8. `app/Http/Resources/NotificationResource.php`
9. `app/Http/Resources/CourseResource.php`
10. `app/Http/Resources/LessonResource.php`
11. `app/Http/Resources/ChallengeResource.php`
12. `app/Http/Resources/ChallengeMissionResource.php`
13. `app/Http/Resources/ChallengeParticipantResource.php`
14. `app/Http/Resources/ChallengeSubmissionResource.php`

---
Dokumen audit ini menjadi acuan mutlak selama implementasi Task 3.
