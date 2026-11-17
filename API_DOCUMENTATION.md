# DOKUMENTASI REST API (V1) — SMART WATER
## Perumda Air Minum Tirta Sago Kota Payakumbuh

---

## 1. Ikhtisar & Standar Respons
API Smart Water dirancang untuk melayani integrasi web dan aplikasi mobile.
- **Base URL**: `http://localhost/smart-water/api/v1`
- **Header Standar**:
  ```http
  Accept: application/json
  Content-Type: application/json
  ```
- **Format Respons Standar**:
  ```json
  {
    "ok": true,
    "message": "Deskripsi hasil operasi",
    "data": { ... }
  }
  ```

---

## 2. Modul Autentikasi (`/auth`)

### 1. Login Pengguna
- **Method**: `POST`
- **URL**: `/api/v1/auth/login`
- **Request Body**:
  ```json
  {
    "identifier": "budi.santoso@warga.payakumbuh.go.id",
    "password": "Pelanggan123!"
  }
  ```
- **Response 200 (OK)**:
  ```json
  {
    "ok": true,
    "message": "Login berhasil.",
    "role": "customer",
    "user": {
      "id": "1",
      "name": "Budi Santoso",
      "email": "budi.santoso@warga.payakumbuh.go.id",
      "phone": "0812-7389-4902",
      "customerId": "1234567890",
      "meterNumber": "01000",
      "zone": "DIST-A Sektor Utara",
      "points": 380,
      "walletBalance": 2500000
    }
  }
  ```

### 2. Registrasi Pelanggan Baru
- **Method**: `POST`
- **URL**: `/api/v1/auth/register`
- **Request Body**:
  ```json
  {
    "name": "Rahmat Hidayat",
    "email": "rahmat@warga.payakumbuh.go.id",
    "phone": "0813-9988-7766",
    "password": "Rahasia123!"
  }
  ```
- **Response 201 (Created)**:
  ```json
  {
    "ok": true,
    "message": "Pendaftaran akun berhasil.",
    "user": { ... }
  }
  ```

### 3. Profil Pengguna Sedang Login
- **Method**: `GET`
- **URL**: `/api/v1/auth/me`

### 4. Logout
- **Method**: `POST`
- **URL**: `/api/v1/auth/logout`

---

## 3. Modul Pengaduan (`/complaints`)

### 1. Ambil Seluruh Daftar Pengaduan
- **Method**: `GET`
- **URL**: `/api/v1/complaints`
- **Query Parameter (Opsional)**:
  - `status`: Filter status (`BARU`, `DIPROSES`, `SELESAI`)
  - `district`: Filter kecamatan (`Payakumbuh Barat`, dll.)
  - `mine=1`: Hanya tampilkan laporan pengguna saat ini

### 2. Buat Pengaduan Baru
- **Method**: `POST`
- **URL**: `/api/v1/complaints`
- **Request Body**:
  ```json
  {
    "category": "pipa_bocor",
    "title": "Kebocoran Pipa Utama Sektor Utara",
    "description": "Air menggenang di badan jalan depan SPBU Ngalau.",
    "district": "Payakumbuh Barat",
    "address": "Jl. Soekarno Hatta No. 12",
    "userPhone": "0812-7389-4902",
    "imageUrl": "data:image/jpeg;base64,..."
  }
  ```
- **Response 200 (OK)**:
  ```json
  {
    "ok": true,
    "ticket": {
      "id": "TK-PYK-2026-8812",
      "status": "BARU",
      "updates": [
        {
          "timestamp": "17 Nov 2026 10:20 WIB",
          "status": "BARU",
          "note": "Laporan pengaduan berhasil tercatat dalam sistem pusat layanan pelanggan.",
          "actor": "Sistem Otomatis"
        }
      ]
    }
  }
  ```

### 3. Perbarui Status Pengaduan (Khusus Petugas / Admin)
- **Method**: `POST`
- **URL**: `/api/v1/complaints/{id}/status`
- **Request Body**:
  ```json
  {
    "status": "DIPROSES",
    "note": "Tim Reaksi Cepat (TRC) meluncur ke lokasi dengan peralatan klem pipa.",
    "actor": "Ahmad Pratama, S.T."
  }
  ```

### 4. Detail Pengaduan Spesifik
- **Method**: `GET`
- **URL**: `/api/v1/complaints/{id}`
- **Otorisasi**: Pemilik tiket (Pelanggan) atau Petugas / Admin. Pelanggan lain akan ditolak dengan HTTP 403 Forbidden.

### 5. Penugasan Teknisi Lapangan (Khusus Petugas / Admin)
- **Method**: `POST`
- **URL**: `/api/v1/complaints/{id}/assign`
- **Request Body**:
  ```json
  {
    "staff_id": 2,
    "note": "Ditugaskan ke Teknisi Ahmad Pratama untuk perbaikan pipa."
  }
  ```

### 6. Beri Tanggapan Resmi Petugas (Khusus Petugas / Admin)
- **Method**: `POST`
- **URL**: `/api/v1/complaints/{id}/response`
- **Request Body**:
  ```json
  {
    "note": "Petugas telah sampai di lokasi perbaikan dan mulai mematikan valve utama.",
    "actor": "Ahmad Pratama"
  }
  ```

### 7. Selesaikan atau Tolak Pengaduan (Khusus Petugas / Admin)
- **Method**: `POST`
- **URL**: `/api/v1/complaints/{id}/resolve`
- **Request Body**:
  ```json
  {
    "status": "SELESAI",
    "resolution_notes": "Pipa utama 3 inch berhasil diganti baru, uji tekanan 3.2 bar normal.",
    "actor": "Ahmad Pratama"
  }
  ```
*(Atau jika ditolak, kirim `"status": "DITOLAK"` dan `"rejection_reason": "Alasan penolakan..."`)*

---

## 4. Modul Lapor Meter AI-OCR (`/meter-reports`)

### 1. Ambil Antrean Lapor Meter
- **Method**: `GET`
- **URL**: `/api/v1/meter-reports`

### 2. Kirim Hasil Pembacaan Stand Meter Mandiri
- **Method**: `POST`
- **URL**: `/api/v1/meter-reports`
- **Request Body**:
  ```json
  {
    "meter": "01000",
    "ocrReading": 1019.0,
    "lastReading": 1000.0,
    "name": "Budi Santoso",
    "zone": "DIST-A Sektor Utara"
  }
  ```
- **Response 200 (OK)**:
  ```json
  {
    "ok": true,
    "message": "Laporan stand meter berhasil dicatat ke sistem pusat.",
    "report": {
      "id": "REP-04-1004",
      "usage": 19.0,
      "cost": 150800,
      "status": "PENDING"
    }
  }
  ```

### 3. Validasi Laporan Meter oleh Petugas Lapangan
- **Method**: `POST`
- **URL**: `/api/v1/meter-reports/validate`
- **Request Body**:
  ```json
  {
    "id": "REP-04-1004",
    "action": "APPROVE"
  }
  ```

---

## 5. Modul Akademi Air (`/academy`)
- `GET /api/v1/academy`: Ambil katalog kursus & progres pengguna.
- `POST /api/v1/academy/complete-lesson`: Tandai bab materi selesai dibaca (`{ "lessonId": "lesson-1-1" }`).
- `POST /api/v1/academy/complete-quiz`: Simpan perolehan nilai kuis (`{ "courseId": "course-1", "score": 100, "xpReward": 150 }`).

---

## 6. Modul Tantangan Konservasi Air (`/challenges`)
- `GET /api/v1/challenges`: Daftar program tantangan hemat air aktif.
- `POST /api/v1/challenges/enroll`: Mendaftar ke tantangan (`{ "challengeId": "challenge-1" }`).
- `POST /api/v1/challenges/complete-mission`: Menyelesaikan misi harian (`{ "challengeId": "challenge-1", "missionId": "m-1-1" }`).
