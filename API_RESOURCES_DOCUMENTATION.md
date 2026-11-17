# DOKUMENTASI API RESOURCES — SMART WATER KOTA PAYAKUMBUH

Dokumentasi lengkap implementasi layer **Laravel API Resources** pada project Smart Water Kota Payakumbuh (Laravel 12).

---

## 1. PENDAHULUAN & TUJUAN ARSITEKTUR

Implementasi API Resources bertujuan memisahkan tanggung jawab serialisasi dan transformasi data Model Eloquent menjadi respon JSON terstruktur.

### Pemisahan Tanggung Jawab Arsitektur:
- **Form Request**: Memvalidasi input pengguna dan memeriksa hak otorisasi sebelum request masuk ke controller.
- **Controller**: Menerima request HTTP, memanggil Service, dan mengembalikan HTTP Response dengan membungkus Model ke dalam API Resource.
- **Service Layer**: Mengelola business logic murni, transaksi database, komputasi tarif/skor/XP, notifikasi, dan data orchestration.
- **API Resource**: Mengubah Model Eloquent menjadi struktur JSON sesuai kontrak frontend, memformat tanggal, casting angka, dan menyembunyikan data sensitif.
- **Model Eloquent**: Mengelola relasi tabel MySQL, casting tipe data, query builder, dan persistensi data.

---

## 2. DAFTAR API RESOURCES YANG DIIMPLEMENTASIKAN

Lokasi file: `app/Http/Resources/`

| Nama Class Resource | Target Model Eloquent | Tanggung Jawab & Fitur Utama |
|---|---|---|
| `ComplaintResource` | `App\Models\Complaint` | Transformasi tiket pengaduan, formatting alamat & lokasi, penataan histori perkembangan tiket (`updates`), category badge, URL foto bukti lampiran, dan format waktu WIB. Menyediakan alias `id`, `ticketId`, dan `ticket_id`. |
| `ComplaintHistoryResource` | `App\Models\ComplaintHistory` | Transformasi histori tindak lanjut pengaduan (`timestamp`, `status`, `note`, `actor`, `actor_id`). |
| `ComplaintAttachmentResource` | `App\Models\ComplaintAttachment` | Transformasi file lampiran foto bukti tiket pengaduan (`file_name`, `url`, `mime_type`, `file_size`). |
| `MeterReadingResource` | `App\Models\MeterReading` | Transformasi laporan stand meter mandiri AI-OCR, konversi desimal float (`ocrReading`, `previousReading`, `consumption`), integer `estimatedCost`, pemetaan status badge & warna status, dan URL foto meter. |
| `UserProfileResource` | `App\Models\User` | Single source of truth profil pengguna (pelanggan & staf). Menyembunyikan seluruh field sensitif (`password`, `remember_token`). Menyediakan alias `phone` & `phoneNumber`, `name` & `fullName`, `customerId`, `meterNumber`, saldo, dan poin. |
| `UserResource` | `App\Models\User` | Alias inheritance dari `UserProfileResource` untuk standarisasi REST API. |
| `CustomerProfileResource` | `App\Models\CustomerProfile` | Transformasi profil data sambungan pelanggan, nomor meter, tarif, zona distribusi, saldo wallet, dan poin XP. |
| `StaffProfileResource` | `App\Models\StaffProfile` | Transformasi profil kedinasan staf/teknisi PDAM (`nip`, `department`, `position`, `phone_dinas`). |
| `NotificationResource` | `App\Models\Notification` | Transformasi notifikasi pengguna (`isRead` & `is_read`, `time` deskriptif "Hari ini, HH:mm WIB", `link`, tipe). |
| `CourseResource` | `App\Models\Course` | Transformasi modul kurikulum akademi air (`id`, `course_code`, `title`, `difficulty`, `readingTime`, `xpReward`, `badgeReward`, `lessons`). |
| `LessonResource` | `App\Models\Lesson` | Transformasi materi bab pelajaran (`id`, `lesson_code`, `title`, `duration`, `readingTime`, `order`, `content`). |
| `QuizResultResource` | `App\Models\UserQuizResult` | Transformasi histori hasil kuis modul akademi (`score`, `is_passed`, `xp_earned`, `completed_at`). |
| `ChallengeResource` | `App\Models\Challenge` | Transformasi program tantangan hemat air (`id`, `challenge_code`, `title`, `tagline`, `difficulty`, `durationDays`, `totalXp`, `badgeRewardId`, `missions`). |
| `ChallengeMissionResource` | `App\Models\ChallengeMission` | Transformasi misi tantangan aksi harian (`id`, `mission_code`, `title`, `tip`, `xp`, `order`). |
| `ChallengeParticipantResource` | `App\Models\ChallengeParticipant` | Transformasi data kepesertaan tantangan (`status`, `joined_at`, `completed_at`). |
| `ChallengeSubmissionResource` | `App\Models\ChallengeSubmission` | Transformasi bukti penyelesaian misi tantangan (`is_completed`, `proof_notes`, `proof_photo_url`, `completed_at`). |

---

## 3. PERBANDINGAN SEBELUM DAN SESUDAH IMPLEMENTASI

### A. Modul Pengaduan (`ComplaintController`)
*Sebelum:*
```php
// Controller memformat array manual yang panjang secara langsung
$complaints = $this->complaintService->getComplaints()
    ->map(fn($c) => self::formatTicket($c));
return response()->json(['ok' => true, 'complaints' => $complaints]);
```
*Sesudah:*
```php
// Menggunakan ComplaintResource
$complaints = $this->complaintService->getComplaints($status, $district, $userId);
return response()->json([
    'ok'         => true,
    'complaints' => ComplaintResource::collection($complaints),
]);
```

### B. Modul Pembacaan Meter (`MeterReadingController`)
*Sebelum:*
```php
// Manual array mapping di controller formatReport()
return response()->json([
    'ok'      => true,
    'report'  => self::formatReport($reading),
]);
```
*Sesudah:*
```php
// Menggunakan MeterReadingResource
return response()->json([
    'ok'      => true,
    'message' => 'Laporan stand meter berhasil dicatat ke sistem pusat.',
    'report'  => new MeterReadingResource($reading),
]);
```

### C. Modul Akademi Air (`AcademyController`)
*Sebelum:*
```php
return response()->json([
    'ok'       => true,
    'courses'  => $curriculum['courses'],
    'progress' => $curriculum['progress'],
]);
```
*Sesudah:*
```php
return response()->json([
    'ok'       => true,
    'courses'  => CourseResource::collection($curriculum['courses']),
    'progress' => $curriculum['progress'],
]);
```

### D. Modul Tantangan (`ChallengeController`)
*Sebelum:*
```php
return response()->json([
    'ok'         => true,
    'challenges' => $data['challenges'],
    'progress'   => $data['progress'],
]);
```
*Sesudah:*
```php
return response()->json([
    'ok'         => true,
    'challenges' => ChallengeResource::collection($data['challenges']),
    'progress'   => $data['progress'],
]);
```

### E. Modul Profil Pengguna & Notifikasi (`routes/api.php`)
*Sebelum:*
```php
Route::get('/notifications', function () {
    return response()->json([
        'ok'            => true,
        'notifications' => $notifs,
        'unreadCount'   => $notifs->where('is_read', false)->count(),
    ]);
});
```
*Sesudah:*
```php
Route::get('/notifications', function () {
    return response()->json([
        'ok'            => true,
        'notifications' => NotificationResource::collection($notifs),
        'unreadCount'   => $notifs->where('is_read', false)->count(),
    ]);
});
```

---

## 4. KEAMANAN & PENCEGAHAN KEBOCORAN DATA

1. **Atribut Sensitif Terisolasi**:
   `UserProfileResource` dan `UserResource` secara eksplisit mendefinisikan field yang dapat dibaca client. Field `password`, `remember_token`, dan `two_factor_secret` tidak pernah disertakan dalam output JSON.
2. **Pencegahan N+1 Query**:
   Resource memeriksa relasi database menggunakan `relationLoaded()` atau `$this->whenLoaded()` sehingga jika relasi sudah di-eager load oleh Service, tidak ada query tambahan yang dieksekusi.
3. **Casting Numerik Konsisten**:
   Field numerik seperti stand meter (`ocrReading`, `previousReading`), volume konsumsi (`consumption`), tarif kalkulasi (`estimatedCost`), poin XP (`xp`, `points`, `totalXp`), durasi (`durationDays`, `estimatedMinutes`) dipastikan selalu bertipe numerik (float/int), bukan string acak dari driver PDO.

---

## 5. HASIL PENGUJIAN OTOMATIS

### A. Unit Tests Laravel (`php artisan test`)
- Total Tests: **33 tests**
- Total Assertions: **143 assertions**
- Status: **100% PASSED**
- Spesifik Resource Test: `Tests\Unit\Resources\ApiResourceTest` (6 tests, 65 assertions PASSED).

### B. Real Integration Test MySQL 8.4 (`php tests/full_mysql_validation.php`)
- Total Scenarios: **47 skenario**
- Status: **100% ALL TESTS PASSED (0 failures)**
- Menjamin integrasi real-world dengan database MySQL, auth role-based, multi-device sync, isolasi data, dan adapter legacy API.
