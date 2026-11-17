<?php

$SWC_LEGACY_PAGE_KEYS = [
    'php-admin' => 'admin',
    'php-akademi-air' => 'akademi-air',
    'php-beranda' => 'beranda',
    'php-chat-ai' => 'chat-ai',
    'php-login-daftar' => 'login-daftar',
    'php-pusat-layanan' => 'pusat-layanan',
    'php-tantangan' => 'tantangan',
    'php-tentang' => 'tentang',
    'php-user' => 'user',
];

if (!isset($SWC_PAGE_MODULE) || !isset($SWC_LEGACY_PAGE_KEYS[$SWC_PAGE_MODULE])) {
    throw new InvalidArgumentException('Modul konfigurasi halaman tidak valid.');
}

if (!isset($SWC_PAGE_URL)) {
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $publicPath = preg_replace('~/(?:pages|api)(?:/.*)?$~', '', $scriptName) ?? '';
    $SWC_PAGE_URL = rtrim($publicPath, '/') . '/' . $SWC_LEGACY_PAGE_KEYS[$SWC_PAGE_MODULE];
}

if (session_status() === PHP_SESSION_NONE) {
    $currentSavePath = session_save_path();
    if (empty($currentSavePath) || !@is_writable($currentSavePath)) {
        $storageSessionDir = function_exists('storage_path')
            ? storage_path('framework/sessions')
            : dirname(__DIR__) . '/storage/framework/sessions';
        if (!is_dir($storageSessionDir)) {
            @mkdir($storageSessionDir, 0777, true);
        }
        @session_save_path($storageSessionDir);
    }
}

switch ($SWC_PAGE_MODULE) {
    case 'php-admin': {
        /**
         * Konfigurasi & Data Command Center Admin SCADA - Smart Water Kota Payakumbuh
         * Sesuai 100% dengan AdminDashboardView.tsx & mockData.ts
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Portal Admin SCADA - PDAM Tirta Sago Payakumbuh',
            'description' => 'Command Center Operasional, Disposisi Tiket Pengaduan, Validasi Stand AI-OCR, dan SCADA Telemetri Reservoir Perumda Air Minum Tirta Sago Kota Payakumbuh.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'scada@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $DISTRICTS_PAYAKUMBUH = [
            'Payakumbuh Barat',
            'Payakumbuh Utara',
            'Payakumbuh Timur',
            'Payakumbuh Selatan',
            'Lampasi Tigo Nagori (Latina)',
        ];
        
        $DEFAULT_ADMIN_USER = [
            'id'             => 'admin-ts-01',
            'name'           => 'Ir. Hendra Pratama, S.T.',
            'email'          => 'admin@tirtasago.id',
            'phone'          => '0811-6677-8899',
            'role'           => 'Kepala Seksi Distribusi & SCADA',
            'district'       => 'Payakumbuh Barat',
            'address'        => 'Kantor Pusat PDAM Tirta Sago, Jl. Imam Bonjol No. 14, Kota Payakumbuh',
            'meterNumber'    => 'ADM-TS-001',
            'tariffCategory' => 'Pengelola Internal PDAM Tirta Sago',
            'staffId'        => 'NIP-TS-198402-089',
            'staffRole'      => 'Super Admin & Dispatcher Teknis',
            'department'     => 'Divisi Transmisi, Distribusi & Pelayanan Pelanggan',
        ];
        
        $ADMIN_DEMO_CUSTOMERS = [
            [
                'id'             => 'user-01',
                'name'           => 'Warga Google Payakumbuh',
                'email'          => 'warga.google@gmail.com',
                'phone'          => '0812-7389-4902',
                'district'       => 'Payakumbuh Barat',
                'address'        => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo, Kec. Payakumbuh Barat, Kota Payakumbuh',
                'meterNumber'    => '04-0821-4902',
                'tariffCategory' => '2A - Rumah Tangga Mandiri (Standar PDAM)',
                'currentMonthM3' => 9.4,
                'currentBillIdr' => 47000,
                'lastBillStatus' => 'Lunas',
            ],
            [
                'id'             => 'user-02',
                'name'           => 'Ibu Siti Nurbaya',
                'email'          => 'siti.nurbaya@payakumbuh.community',
                'phone'          => '0852-6389-1122',
                'district'       => 'Payakumbuh Timur',
                'address'        => 'Kompleks Griya Tiakar Indah No. 12, Kec. Payakumbuh Timur, Kota Payakumbuh',
                'meterNumber'    => '02-0199-3118',
                'tariffCategory' => '2B - Rumah Tangga Menengah (PDAM)',
                'currentMonthM3' => 14.8,
                'currentBillIdr' => 74000,
                'lastBillStatus' => 'Lunas',
            ],
        ];
        
        function getInitialAdminSeedState(): array
        {
            return [
                'complaints' => [
                    [
                        'id'          => 'TK-PYK-2026-9015',
                        'userId'      => 'user-03',
                        'userName'    => 'Ahmad Fauzi',
                        'userPhone'   => '0813-6311-9044',
                        'category'    => 'air_mati',
                        'title'       => 'Tekanan Air Melemah Drastis di Sekitar Jl. Sudirman Labuh Baru',
                        'description' => 'Aliran keran di rumah tangga lantai 1 dan 2 mengecil sejak pukul 06:30 WIB. Beberapa rumah tetangga juga mengalami hal serupa.',
                        'district'    => 'Payakumbuh Utara',
                        'address'     => 'Jl. Sudirman No. 88, Kel. Labuh Baru, Payakumbuh Utara',
                        'status'      => 'BARU',
                        'createdAt'   => '2026-10-17 09:05 WIB',
                        'updatedAt'   => '2026-10-17 09:05 WIB',
                        'updates'     => [
                            [
                                'timestamp' => '2026-10-17 09:05 WIB',
                                'status'    => 'BARU',
                                'note'      => 'Laporan pengaduan darurat masuk melalui portal web dan menunggu verifikasi dispatcher.',
                                'actor'     => 'Sistem Otomatis',
                            ],
                        ],
                    ],
                    [
                        'id'          => 'TK-PYK-2026-8942',
                        'userId'      => 'user-01',
                        'userName'    => 'Rian Pratama',
                        'userPhone'   => '0812-7654-3210',
                        'category'    => 'pipa_bocor',
                        'title'       => 'Rembesan Air Bersih di Bahu Jalan Dekat Pasar Ibuh',
                        'description' => 'Terlihat genangan air jernih menyembur dari sambungan pipa bawah aspal di depan pertokoan blok B. Aliran cukup deras sejak pagi.',
                        'district'    => 'Payakumbuh Barat',
                        'address'     => 'Jl. Ahmad Yani No. 45, Dekat Simpang Pasar Ibuh',
                        'status'      => 'SELESAI',
                        'createdAt'   => '2026-10-17 07:45 WIB',
                        'updatedAt'   => '2026-10-17 09:15 WIB',
                        'updates'     => [
                            [
                                'timestamp' => '2026-10-17 07:45 WIB',
                                'status'    => 'BARU',
                                'note'      => 'Laporan pengaduan berhasil tercatat dalam sistem pusat layanan pelanggan.',
                                'actor'     => 'Sistem Otomatis',
                            ],
                            [
                                'timestamp' => '2026-10-17 08:30 WIB',
                                'status'    => 'DIPROSES',
                                'note'      => 'Laporan diverifikasi oleh operator. Tim Reaksi Cepat (TRC) Zona Barat telah diberangkatkan ke lokasi.',
                                'actor'     => 'Operator Pengaduan Payakumbuh',
                            ],
                            [
                                'timestamp' => '2026-10-17 09:15 WIB',
                                'status'    => 'SELESAI',
                                'note'      => 'Penggantian clamp pipa dan uji tekanan selesai dilakukan. Aliran air kembali normal.',
                                'actor'     => 'Tim Lapangan TRC-02',
                            ],
                        ],
                    ],
                    [
                        'id'          => 'TK-PYK-2026-8109',
                        'userId'      => 'user-02',
                        'userName'    => 'Ibu Siti Nurbaya',
                        'userPhone'   => '0852-6389-1122',
                        'category'    => 'kualitas_keruh',
                        'title'       => 'Air Keran Mengandung Endapan Cokelat Halus',
                        'description' => 'Sejak tadi malam air di bak penampungan agak kekuningan setelah hujan lebat di hulu.',
                        'district'    => 'Payakumbuh Timur',
                        'address'     => 'Kompleks Griya Tiakar Indah No. 12',
                        'status'      => 'SELESAI',
                        'createdAt'   => '2026-10-15 16:20 WIB',
                        'updatedAt'   => '2026-10-16 11:30 WIB',
                        'updates'     => [
                            [
                                'timestamp' => '2026-10-15 16:20 WIB',
                                'status'    => 'BARU',
                                'note'      => 'Tiket pengaduan diterbitkan dan dialokasikan ke Pengawas Kualitas Air.',
                                'actor'     => 'Sistem Otomatis',
                            ],
                            [
                                'timestamp' => '2026-10-16 08:00 WIB',
                                'status'    => 'DIPROSES',
                                'note'      => 'Petugas melakukan flushing pipa hydrant di ujung jalur Tiakar untuk membuang endapan.',
                                'actor'     => 'Petugas Distribusi Zona Timur',
                            ],
                            [
                                'timestamp' => '2026-10-16 11:30 WIB',
                                'status'    => 'SELESAI',
                                'note'      => 'Flushing selesai. Uji sampel kejernihan (NTU) memenuhi standar baku mutu air minum.',
                                'actor'     => 'Supervisor Layanan Pelanggan',
                            ],
                        ],
                    ],
                    [
                        'id'          => 'TK-PYK-2026-7241',
                        'userId'      => 'user-01',
                        'userName'    => 'Warga Google Payakumbuh',
                        'userPhone'   => '0812-7389-4902',
                        'category'    => 'meteran_rusak',
                        'title'       => 'Pemeriksaan Jarum Dial Meteran Air Lambat',
                        'description' => 'Pengecekan fisik penunjuk angka dial jarum meteran air karena terasa agak macet saat kran bak mandi dibuka.',
                        'district'    => 'Payakumbuh Barat',
                        'address'     => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo',
                        'status'      => 'DIPROSES',
                        'createdAt'   => '2026-10-14 10:15 WIB',
                        'updatedAt'   => '2026-10-15 09:30 WIB',
                        'updates'     => [
                            [
                                'timestamp' => '2026-10-14 10:15 WIB',
                                'status'    => 'BARU',
                                'note'      => 'Laporan permohonan kalibrasi meteran masuk sistem.',
                                'actor'     => 'Sistem Otomatis',
                            ],
                            [
                                'timestamp' => '2026-10-15 09:30 WIB',
                                'status'    => 'DIPROSES',
                                'note'      => 'Teknisi meteran PDAM Tirta Sago memeriksa segel dan akurasi putaran dial.',
                                'actor'     => 'Teknisi Meter Wilayah Barat',
                            ],
                        ],
                    ],
                ],
                'announcements' => [
                    [
                        'id'                  => 'ANN-2026-03-01',
                        'title'               => 'Optimalisasi Jaringan Pipa Distribusi Utama Jl. Soekarno-Hatta',
                        'category'            => 'pemeliharaan',
                        'districtsAffected'   => ['Payakumbuh Barat', 'Payakumbuh Utara'],
                        'status'              => 'dalam_penanganan',
                        'publishedAt'         => '2026-10-17 08:30 WIB',
                        'estimatedResolution' => '17 Oktober 2026, 17:00 WIB',
                        'description'         => 'Pekerjaan interkoneksi pipa transmisi 250mm guna memperkuat tekanan aliran air ke area perumahan warga di Koto Nan Ampek dan Labuh Baru.',
                        'actionAdvice'        => 'Masyarakat diimbau menampung air bersih secukupnya. Armada tangki darurat siaga di posko kelurahan jika dibutuhkan.',
                        'source'              => 'Divisi Transmisi & Distribusi PDAM Tirta Sago',
                    ],
                    [
                        'id'                  => 'ANN-2026-03-02',
                        'title'               => 'Pembersihan Sedimen Bak Prasedimentasi Sumber Mata Air Sikabu',
                        'category'            => 'kualitas_air',
                        'districtsAffected'   => ['Payakumbuh Timur', 'Payakumbuh Selatan'],
                        'status'              => 'selesai',
                        'publishedAt'         => '2026-10-16 14:00 WIB',
                        'estimatedResolution' => 'Telah Selesai (Tekanan Normal Kembali)',
                        'description'         => 'Pembersihan rutin material pasir dan lumpur pasca hujan intensitas tinggi di hulu Gunung Sago.',
                        'actionAdvice'        => 'Jika air keran awal terlihat keruh sesaat, buka keran pembuangan selama 1-2 menit hingga jernih.',
                        'source'              => 'Unit Pengawasan Kualitas Air Tirta Sago',
                    ],
                    [
                        'id'                  => 'ANN-2026-03-03',
                        'title'               => 'Imbauan Waspada Pipa Tersumbat & Sosialisasi Pelaporan Cepat',
                        'category'            => 'info_layanan',
                        'districtsAffected'   => ['Lampasi Tigo Nagori (Latina)', 'Payakumbuh Barat'],
                        'status'              => 'aktif',
                        'publishedAt'         => '2026-10-15 09:00 WIB',
                        'estimatedResolution' => 'Berlaku Selama Musim Pancaroba',
                        'description'         => 'Layanan respons cepat tim reaksi cepat (TRC) siaga 24 jam untuk menindaklanjuti laporan kebocoran air di jalan umum atau meteran pelanggan.',
                        'actionAdvice'        => 'Gunakan fitur Buat Pengaduan di Smart Water untuk mendapatkan nomor tiket pelacakan otomatis.',
                        'source'              => 'Humas Perumda Tirta Sago Payakumbuh',
                    ],
                ],
                'reservoirs' => [
                    [
                        'id'              => 'res-sikabu',
                        'name'            => 'Reservoir Mata Air Gravitasi Sikabu',
                        'location'        => 'Kaki Gunung Sago, Kec. Payakumbuh Barat',
                        'waterLevelPct'   => 92,
                        'inflowDebitLps'  => 142.5,
                        'outflowDebitLps' => 138.0,
                        'turbidityNtu'    => 0.8,
                        'phLevel'         => 7.2,
                        'chlorineMgL'     => 0.32,
                        'pressureBar'     => 2.8,
                        'valveOpenPct'    => 95,
                        'status'          => 'optimal',
                        'lastUpdated'     => 'Baru saja',
                    ],
                    [
                        'id'              => 'res-batang-agam',
                        'name'            => 'Reservoir Intake Hulu DAS Batang Agam',
                        'location'        => 'Sungai Batang Agam, Payakumbuh',
                        'waterLevelPct'   => 84,
                        'inflowDebitLps'  => 98.0,
                        'outflowDebitLps' => 92.5,
                        'turbidityNtu'    => 2.1,
                        'phLevel'         => 7.1,
                        'chlorineMgL'     => 0.38,
                        'pressureBar'     => 2.4,
                        'valveOpenPct'    => 88,
                        'status'          => 'optimal',
                        'lastUpdated'     => '1 menit lalu',
                    ],
                    [
                        'id'              => 'res-ipa-payobasung',
                        'name'            => 'Instalasi Pengolahan Air (IPA) Payobasung',
                        'location'        => 'Kec. Payakumbuh Timur',
                        'waterLevelPct'   => 79,
                        'inflowDebitLps'  => 72.0,
                        'outflowDebitLps' => 70.4,
                        'turbidityNtu'    => 1.2,
                        'phLevel'         => 7.3,
                        'chlorineMgL'     => 0.40,
                        'pressureBar'     => 2.1,
                        'valveOpenPct'    => 80,
                        'status'          => 'optimal',
                        'lastUpdated'     => '3 menit lalu',
                    ],
                    [
                        'id'              => 'res-booster-limbukan',
                        'name'            => 'Stasiun Booster & Tangki Tekanan Limbukan',
                        'location'        => 'Kec. Payakumbuh Selatan',
                        'waterLevelPct'   => 68,
                        'inflowDebitLps'  => 45.0,
                        'outflowDebitLps' => 46.2,
                        'turbidityNtu'    => 1.5,
                        'phLevel'         => 7.0,
                        'chlorineMgL'     => 0.35,
                        'pressureBar'     => 1.9,
                        'valveOpenPct'    => 75,
                        'status'          => 'waspada',
                        'lastUpdated'     => '5 menit lalu',
                    ],
                ],
                'meterVerifications' => [
                    [
                        'id'             => 'mv-01',
                        'userId'         => 'user-01',
                        'customerName'   => 'Budi Santoso',
                        'meterNumber'    => '04-2819-8942',
                        'address'        => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo',
                        'district'       => 'Payakumbuh Barat',
                        'tariffCategory' => '2A - Rumah Tangga Mandiri',
                        'date'           => '16 Oktober 2026',
                        'lastMonthStand' => 1832.6,
                        'recordedStand'  => 1842.0,
                        'volumeM3'       => 9.4,
                        'source'         => 'mobile_ai_ocr',
                        'ocrConfidence'  => 97.8,
                        'status'         => 'terverifikasi',
                        'verifiedBy'     => 'Teknisi AI Dispatcher',
                        'verifiedAt'     => '16 Okt 2026 14:20 WIB',
                        'notes'          => 'Angka dial 1842.0 terbaca sangat jelas dan presisi sesuai kurva konsumsi.',
                    ],
                    [
                        'id'             => 'mv-02',
                        'userId'         => 'user-02',
                        'customerName'   => 'Ibu Siti Nurbaya',
                        'meterNumber'    => '02-0199-3118',
                        'address'        => 'Kompleks Griya Tiakar Indah No. 12',
                        'district'       => 'Payakumbuh Timur',
                        'tariffCategory' => '2B - Rumah Tangga Menengah',
                        'date'           => '17 Oktober 2026',
                        'lastMonthStand' => 2395.2,
                        'recordedStand'  => 2410.0,
                        'volumeM3'       => 14.8,
                        'source'         => 'mobile_ai_ocr',
                        'ocrConfidence'  => 94.5,
                        'status'         => 'menunggu_validasi',
                        'verifiedBy'     => null,
                        'verifiedAt'     => null,
                        'notes'          => 'Perlu konfirmasi angka desimal dial merah oleh petugas operator.',
                    ],
                    [
                        'id'             => 'mv-03',
                        'userId'         => 'user-03',
                        'customerName'   => 'Ahmad Fauzi',
                        'meterNumber'    => '01-4412-5509',
                        'address'        => 'Jl. Sudirman No. 88, Labuh Baru',
                        'district'       => 'Payakumbuh Utara',
                        'tariffCategory' => '2A - Rumah Tangga Mandiri',
                        'date'           => '17 Oktober 2026',
                        'lastMonthStand' => 3105.0,
                        'recordedStand'  => 3118.5,
                        'volumeM3'       => 13.5,
                        'source'         => 'web_mandiri',
                        'ocrConfidence'  => 89.2,
                        'status'         => 'menunggu_validasi',
                        'verifiedBy'     => null,
                        'verifiedAt'     => null,
                        'notes'          => 'Foto dikirim via formulir Lapor Stand Mandiri web pelanggan.',
                    ],
                    [
                        'id'             => 'mv-04',
                        'userId'         => 'user-04',
                        'customerName'   => 'Rahmat Hidayat (Usaha Roti Sago)',
                        'meterNumber'    => '05-9921-1204',
                        'address'        => 'Jl. Raya Bukittinggi - Payakumbuh Km. 3',
                        'district'       => 'Payakumbuh Barat',
                        'tariffCategory' => '3A - Niaga Kecil (UMKM)',
                        'date'           => '15 Oktober 2026',
                        'lastMonthStand' => 5410.0,
                        'recordedStand'  => 5448.2,
                        'volumeM3'       => 38.2,
                        'source'         => 'petugas_lapangan',
                        'ocrConfidence'  => 99.1,
                        'status'         => 'terverifikasi',
                        'verifiedBy'     => 'Petugas Lapangan Zona Barat',
                        'verifiedAt'     => '15 Okt 2026 10:15 WIB',
                        'notes'          => 'Pencatatan langsung oleh petugas pembaca meter keliling.',
                    ],
                ],
                'auditLogs' => [
                    [
                        'id'        => 'log-01',
                        'timestamp' => '17 Okt 2026 09:15 WIB',
                        'actor'     => 'Ir. Hendra Pratama, S.T.',
                        'action'    => 'DISPATCH_TIKET',
                        'target'    => 'TK-PYK-2026-8942',
                        'details'   => 'Status diubah ke SELESAI. Penggantian clamp pipa di dekat Pasar Ibuh rampung.',
                    ],
                    [
                        'id'        => 'log-02',
                        'timestamp' => '17 Okt 2026 08:30 WIB',
                        'actor'     => 'Ir. Hendra Pratama, S.T.',
                        'action'    => 'PUBLISH_PENGUMUMAN',
                        'target'    => 'ANN-2026-03-01',
                        'details'   => 'Menerbitkan pengumuman optimalisasi jaringan pipa distribusi Jl. Soekarno-Hatta.',
                    ],
                    [
                        'id'        => 'log-03',
                        'timestamp' => '16 Okt 2026 14:22 WIB',
                        'actor'     => 'Sistem AI-OCR',
                        'action'    => 'VERIFIKASI_METER',
                        'target'    => '04-2819-8942 (Budi Santoso)',
                        'details'   => 'Stand meter 1842.0 m³ divalidasi otomatis dengan tingkat akurasi 97.8%.',
                    ],
                ],
            ];
        }
        
        if (!isset($_SESSION['swc_admin_state']) || !is_array($_SESSION['swc_admin_state'])) {
            $_SESSION['swc_admin_state'] = getInitialAdminSeedState();
        }
        
        $adminUser  = $DEFAULT_ADMIN_USER;
        $adminState = $_SESSION['swc_admin_state'];
        
        if (!function_exists('e')) {
            function e(string $str): string
            {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
        }
        
        function formatIdr(int $val): string
        {
            return 'Rp ' . number_format($val, 0, ',', '.');
        }
        
        break;
    }
    case 'php-akademi-air': {
        /**
         * Konfigurasi & Data Halaman Akademi Air - Smart Water Kota Payakumbuh
         * Berisi 100% data kursus, materi bacaan, studi kasus, dan 40 soal kuis (10 soal x 4 modul)
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Akademi Air - Smart Water Kota Payakumbuh',
            'description' => 'Pusat Edukasi & Literasi Konservasi Air Bersih Kota Payakumbuh lengkap dengan materi interaktif, kuis 10 soal berhadiah poin diskon, dan kalkulator konsumsi air.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'akademi';
        
        if (!isset($_SESSION['swc_academy_progress']) || !is_array($_SESSION['swc_academy_progress'])) {
            $_SESSION['swc_academy_progress'] = [
                'points'           => 380,
                'completedLessons' => ['lesson-1-1', 'lesson-1-2'],
                'completedQuizzes' => ['course-1' => 100, 'course-2' => 100],
                'badges'           => ['badge-sago-guardian'],
            ];
        }
        
        $currentUser = $_SESSION['swc_user'] ?? null;
        $academyProgress = $_SESSION['swc_academy_progress'];
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $ACADEMY_CATEGORIES = [
            ['id' => 'all',             'label' => 'Semua Modul'],
            ['id' => 'dasar_air',       'label' => 'Dasar Air Bersih'],
            ['id' => 'deteksi_bocor',   'label' => 'Deteksi Kebocoran'],
            ['id' => 'konservasi',      'label' => 'Konservasi Hemat Air'],
            ['id' => 'meteran_tagihan', 'label' => 'Literasi Meteran'],
        ];
        
        $ACADEMY_COURSES = [
            [
                'id'          => 'course-1',
                'title'       => 'Siklus Air Payakumbuh & Konservasi Batang Agam',
                'category'    => 'dasar_air',
                'difficulty'  => 'Pemula',
                'readingTime' => '6 Menit',
                'summary'     => 'Pahami dari mana air keran Anda berasal: perpaduan mata air Gunung Sago, sungai Batang Agam, dan tanggung jawab menjaga kelestariannya.',
                'xpReward'    => 120,
                'lessons'     => [
                    [
                        'id'                => 'lesson-1-1',
                        'title'             => 'Hulu ke Hilir: Perjalanan Air di Lembah Nan Elok',
                        'readingTime'       => '3 Menit',
                        'summary'           => 'Mengenal sistem tangkapan air alami Payakumbuh dari lereng Gunung Sago.',
                        'contentParagraphs' => [
                            'Kota Payakumbuh diberkahi letak geografis istimewa di antara Gunung Sago yang menjulang dan aliran Batang Agam yang membelah kota. Sumber air baku utama Perumda Tirta Sago bertumpu pada mata air gravitasi alami seperti Mata Air Sikabu, Sungai Dareh, serta instalasi pengolahan air (IPA) Batang Agam.',
                            'Mata air gravitasi memiliki kualitas kejernihan alami yang tinggi karena tersaring bebatuan vulkanik bertahun-tahun. Namun, perubahan iklim, pembukaan lahan di zona resapan, dan pembuangan sampah ke aliran sungai menjadi ancaman nyata kelangsungan debit air.',
                            'Sebagai warga Kota Payakumbuh, setiap tetes air yang kita gunakan di rumah terhubung langsung dengan kelestarian hutan di lereng gunung kita. Menjaga efisiensi air berarti menjaga cadangan air untuk generasi mendatang.',
                        ],
                        'practicalTips' => [
                            'Dukung ruang terbuka hijau di pekarangan rumah Anda untuk membantu resapan air hujan.',
                            'Jangan pernah membuang minyak jelantah atau limbah kimia ke saluran air/got karena bermuara ke Batang Agam.',
                            'Ajarkan keluarga bahwa air bersih bukanlah sumber daya tak terbatas.',
                        ],
                        'caseStudy' => [
                            'title'         => 'Studi Kasus: Fluktuasi Debit Musim Kemarau di Koto Nan Gadang',
                            'scenario'      => 'Pada puncak kemarau 2024, debit mata air Sikabu sempat menurun 18%. Warga yang memiliki kebiasaan menampung air hujan untuk menyiram tanaman dan menerapkan mandi shower terbukti tidak mengalami kekurangan pasokan dibanding warga yang membiarkan keran bocor.',
                            'lessonLearned' => 'Kesiapsiagaan konservasi komunitas jauh lebih efektif daripada mengandalkan pasokan darurat di saat krisis debit.',
                        ],
                    ],
                    [
                        'id'                => 'lesson-1-2',
                        'title'             => 'Standar Baku Mutu & Mengapa Air Perlu Dikelola Bijak',
                        'readingTime'       => '3 Menit',
                        'summary'           => 'Memahami proses panjang penjernihan, desinfeksi, dan biaya energi distribusi air bersih.',
                        'contentParagraphs' => [
                            'Memproduksi air bersih yang aman hingga mengalir di keran rumah membutuhkan proses bertahap: koagulasi, flokulasi, sedimentasi, filtrasi pasir kuarsa, hingga desinfeksi klorinasi terukur.',
                            'Seluruh proses ini memerlukan energi listrik pompa bertekanan tinggi, bahan penolong ramah lingkungan, dan pemeliharaan pipa transmisi puluhan kilometer di bawah jalan raya kota.',
                            'Ketika kita membiarkan keran terbuka tanpa faedah, kita tidak hanya menyia-nyiakan air, melainkan juga menghamburkan energi listrik dan bahan kimia yang membebani ekosistem.',
                        ],
                        'practicalTips' => [
                            'Tutup keran saat menggosok gigi atau mencuci muka: menghemat hingga 6 liter per menit!',
                            'Gunakan air cucian beras untuk menyiram bunga dan tanaman hias: tanaman lebih subur dan hemat air PDAM.',
                        ],
                    ],
                ],
                'quiz' => [
                    [
                        'id'            => 'q1-1',
                        'question'      => 'Dari manakah salah satu sumber mata air utama alami yang melayani warga Kota Payakumbuh?',
                        'options'       => [
                            'Mata Air Bawah Tanah Pesisir Pantai',
                            'Mata Air Gravitasi Sikabu di Lereng Gunung Sago',
                            'Air Danau Singkarak',
                            'Penyulingan Air Laut',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Benar! Sumber mata air utama Tirta Sago mengandalkan sistem gravitasi alami Sikabu di kawasan kaki Gunung Sago yang menghasilkan air jernih berkualitas tinggi.',
                    ],
                    [
                        'id'            => 'q1-2',
                        'question'      => 'Berapa perkiraan volume air bersih yang terbuang jika keran dibiarkan menyala saat menyikat gigi selama 1 menit?',
                        'options'       => [
                            'Sekitar 0.5 liter',
                            'Sekitar 1-2 liter',
                            'Sekitar 6-9 liter',
                            'Tidak ada air terbuang',
                        ],
                        'correctAnswer' => 2,
                        'explanation'   => 'Keran air bertekanan standar mengalirkan 6 hingga 9 liter per menit. Mematikan keran saat menyikat gigi adalah langkah hemat air instan yang berdampak besar!',
                    ],
                    [
                        'id'            => 'q1-3',
                        'question'      => 'Tindakan rumah tangga manakah yang paling ramah dalam mendukung siklus air Batang Agam?',
                        'options'       => [
                            'Membuang minyak goreng bekas ke saluran selokan',
                            'Menggunakan air bekas cucian beras untuk menyiram tanaman pekarangan',
                            'Membuka keran maksimal setiap mencuci piring',
                            'Membiarkan pelampung toren meluap setiap hari',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Tepat sekali! Memanfaatkan kembali air bilasan beras (greywater ringan organik) tidak mencemari lingkungan dan langsung menghemat air bersih.',
                    ],
                    [
                        'id'            => 'q1-4',
                        'question'      => 'Mengapa sistem transmisi air gravitasi lereng Gunung Sago sangat hemat energi dan ramah lingkungan?',
                        'options'       => [
                            'Memanfaatkan kemiringan lereng bumi secara alami tanpa ketergantungan pompa diesel berkekuatan besar',
                            'Karena airnya mendidih sendiri di dalam tanah',
                            'Karena menggunakan bahan bakar batu bara',
                            'Karena air dialirkan lewat udara terbuka',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Sistem gravitasi memanfaatkan kontur elevasi Gunung Sago, sehingga air mengalir stabil ke kota dengan konsumsi energi karbon yang sangat minim.',
                    ],
                    [
                        'id'            => 'q1-5',
                        'question'      => 'Berapa jumlah kecamatan di Kota Payakumbuh yang tercakup dalam ekosistem layanan Smart Water?',
                        'options'       => [
                            '3 Kecamatan',
                            '5 Kecamatan (Barat, Utara, Timur, Selatan, dan Latina)',
                            '8 Kecamatan',
                            '10 Kecamatan',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Kota Payakumbuh terdiri dari 5 kecamatan: Payakumbuh Barat, Payakumbuh Utara, Payakumbuh Timur, Payakumbuh Selatan, dan Lampasi Tigo Nagori (Latina).',
                    ],
                    [
                        'id'            => 'q1-6',
                        'question'      => 'Apa fungsi utama tahapan sedimentasi dan filtrasi pasir kuarsa pada Instalasi Pengolahan Air (IPA)?',
                        'options'       => [
                            'Menambahkan pewarna agar air tampak biru',
                            'Mengendapkan partikel lumpur dan menyaring partikel halus agar air jernih dan steril',
                            'Memanaskan air hingga suhu 100°C',
                            'Mengubah air tawar menjadi air mineral berkarbonasi',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Sedimentasi mengendapkan gumpalan lumpur hasil koagulasi, dan saringan pasir kuarsa menyaring partikel mikroskopis sebelum disalurkan ke pipa warga.',
                    ],
                    [
                        'id'            => 'q1-7',
                        'question'      => 'Apa yang dimaksud dengan "Greywater" dalam pengelolaan air rumah tangga?',
                        'options'       => [
                            'Air limbah beracun dari pabrik kimia',
                            'Air limbah domestik non-kakus seperti sisa bilasan wastafel, mandi, dan cucian beras yang masih aman dimanfaatkan',
                            'Air hujan yang asam pekat',
                            'Air yang telah dicampur oli bekas',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Greywater adalah air sisa kegiatan domestik non-kakus yang dapat diolah atau digunakan kembali untuk menyiram tanaman dan mengepel teras.',
                    ],
                    [
                        'id'            => 'q1-8',
                        'question'      => 'Mengapa menjaga tutupan vegetasi pohon di kawasan tangkapan air Gunung Sago sangat krusial?',
                        'options'       => [
                            'Akar pohon menahan laju erosi dan menyerap air hujan ke dalam akuifer tanah sehingga mata air tetap mengalir saat kemarau',
                            'Agar daun-daun gugur ke dalam pipa air',
                            'Agar udara menjadi lebih panas',
                            'Agar hewan liar tidak bisa minum air',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Hutan lindung di lereng Gunung Sago berfungsi sebagai spons raksasa yang menjaga ketersediaan debit air bersih sepanjang tahun.',
                    ],
                    [
                        'id'            => 'q1-9',
                        'question'      => 'Kearifan lokal Minangkabau apa yang menjadi landasan filosofis menjaga kedamaian dan ketertiban pemanfaatan air bersih?',
                        'options'       => [
                            'Aia Tagang Manyerak, Aia Janiah Manyanangkan',
                            'Takuruang Nak Kaluar, Tahimpik Nak Ka Ateh',
                            'Alu Tusuak Kadalam Kumbang',
                            'Bajalan Bagageh, Bakato Tagageh',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => '"Aia tagang manyerak, aia janiah manyanangkan" mengajarkan bahwa air yang jernih dan tata kelola yang transparan menenangkan serta mempersatukan masyarakat.',
                    ],
                    [
                        'id'            => 'q1-10',
                        'question'      => 'Bagaimana poin XP yang Anda dapatkan setelah lulus kuis Smart Water menguntungkan Anda saat membayar rekening air?',
                        'options'       => [
                            'Poin hanya berupa angka dekoratif tanpa manfaat',
                            'Poin otomatis tersinkron ke aplikasi mobile dan dapat ditukarkan menjadi potongan diskon tagihan rekening air (1 Poin = Rp 100)',
                            'Poin hanya bisa ditukar dengan botol plastik kosong',
                            'Poin menghapus nama pelanggan dari data PDAM',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Setiap poin yang Anda kumpulkan di portal Smart Water terhubung langsung ke akun aplikasi mobile dan dapat dipakai sebagai potongan harga saat bayar tagihan bulanan!',
                    ],
                ],
            ],
            [
                'id'          => 'course-2',
                'title'       => 'Detektif Kebocoran Rumah Tangga (Zero-Leak)',
                'category'    => 'deteksi_bocor',
                'difficulty'  => 'Menengah',
                'readingTime' => '7 Menit',
                'summary'     => 'Pelajari trik praktis mendeteksi rembesan tersembunyi, toilet bocor halus, dan jarum meteran yang berputar sendiri di malam hari.',
                'xpReward'    => 150,
                'lessons'     => [
                    [
                        'id'                => 'lesson-2-1',
                        'title'             => 'Misteri Jarum Merah Meteran Air yang Terus Berputar',
                        'readingTime'       => '3.5 Menit',
                        'summary'           => 'Metode uji statis malam hari untuk mendeteksi pipa tanam yang retak.',
                        'contentParagraphs' => [
                            'Pernahkah tagihan air Anda melonjak padahal pemakaian anggota keluarga terasa biasa saja? Penyebab nomor satu adalah "silent leak" atau kebocoran tersembunyi pada pipa dinding atau pipa bawah tanah.',
                            'Meteran air memiliki jarum penunjuk kecil (sering disebut flow indicator atau bintang segitiga putar). Jarum ini sangat sensitif terhadap aliran sekecil 0.1 liter per menit.',
                            'Cara mendeteksinya sangat mudah: Tutup seluruh keran air di rumah (termasuk stop-keran toren dan dispenser). Jangan ada yang menggunakan air selama 15 menit. Amati jarum kecil meteran Anda. Jika jarum masih berputar lambat, berarti terdapat kebocoran aktif di antara meteran dan titik kran rumah Anda!',
                        ],
                        'practicalTips' => [
                            'Lakukan uji statis meteran setiap 1 bulan sekali di malam hari sebelum tidur.',
                            'Periksa apakah ada dinding yang lembap, cat mengelupas, atau ubin yang terasa dingin basah.',
                            'Pasang stop-kran (valve) terpisah di dekat toren penampung untuk memudahkan isolasi.',
                        ],
                    ],
                    [
                        'id'                => 'lesson-2-2',
                        'title'             => 'Uji Pewarna Makanan pada Tangki Kloset Duduk',
                        'readingTime'       => '3.5 Menit',
                        'summary'           => 'Kloset duduk yang katup karetnya getas bisa membuang 200 liter air per hari tanpa disadari.',
                        'contentParagraphs' => [
                            'Kebocoran pada flapper valve kloset duduk sering tidak terdengar karena air menetes langsung ke dalam mangkuk kloset.',
                            'Tetesan halus yang konstan ini jika diakumulasi selama 24 jam dapat mencapai 150 hingga 300 liter air bersih terbuang sia-sia—setara dengan 2 bak mandi penuh!',
                            'Uji sederhana: Teteskan 3–4 tetes pewarna makanan (misal warna hijau atau merah) ke dalam tangki penampung kloset. Tunggu 15 menit tanpa menekan tombol flush. Jika air di mangkuk kloset berubah warna, dipastikan katup karet kloset Anda sudah aus dan perlu diganti karet seal-nya.',
                        ],
                        'practicalTips' => [
                            'Biaya karet seal kloset baru hanya sekitar Rp15.000–Rp25.000, namun mampu mencegah lonjakan tagihan hingga ratusan ribu rupiah.',
                            'Jangan gunakan pembersih tangki kloset berbentuk tablet korosif yang merusak seal karet.',
                        ],
                    ],
                ],
                'quiz' => [
                    [
                        'id'            => 'q2-1',
                        'question'      => 'Bagaimana cara memastikan ada kebocoran pipa tersembunyi dengan mengamati meteran air?',
                        'options'       => [
                            'Menyalakan semua keran secara bersamaan',
                            'Menutup semua keran rapat-rapat lalu mengamati apakah jarum flow indikator meteran tetap berputar',
                            'Mengetuk kaca penutup meteran dengan palu',
                            'Melihat warna pipa meteran',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Benar! Uji statis dengan menutup semua kran: jika jarum meteran tetap berputar, dipastikan ada aliran bocor di hilir meteran.',
                    ],
                    [
                        'id'            => 'q2-2',
                        'question'      => 'Apa tujuan meneteskan pewarna makanan ke dalam tangki kloset pada uji kebocoran?',
                        'options'       => [
                            'Membuat wangi ruangan kamar mandi',
                            'Melihat apakah warna merembes ke mangkuk kloset tanpa di-flush, menandakan katup karet aus',
                            'Menguji kejernihan pipa PDAM',
                            'Membersihkan kerak toilet',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Tepat! Jika warna merembes ke mangkuk toilet sebelum ditekan flush, flapper valve bocor dan harus segera diganti.',
                    ],
                    [
                        'id'            => 'q2-3',
                        'question'      => 'Kapan waktu paling ideal dan akurat untuk melakukan uji statis kebocoran pipa meteran air?',
                        'options'       => [
                            'Di siang hari saat mencuci pakaian',
                            'Di malam hari saat seluruh anggota keluarga tidur dan tidak ada aktivitas keran',
                            'Saat hujan lebat mengguyur atap',
                            'Saat aliran air PDAM sedang dimatikan untuk pemeliharaan',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Uji statis malam hari selama 15-30 menit sangat presisi karena tidak terganggu oleh penggunaan air normal di dalam rumah.',
                    ],
                    [
                        'id'            => 'q2-4',
                        'question'      => 'Berapa potensi air bersih yang bisa terbuang sia-sia setiap hari hanya karena kebocoran halus katup kloset duduk?',
                        'options'       => [
                            'Kurang dari 5 liter',
                            'Sekitar 20 liter',
                            'Antara 150 hingga 300 liter per hari (setara 2 bak mandi penuh)',
                            'Tidak ada air terbuang',
                        ],
                        'correctAnswer' => 2,
                        'explanation'   => 'Kebocoran silent leak pada kloset duduk tidak menimbulkan suara gemuruh, namun rembesan terus-menerus bisa membuang ratusan liter air dan melipatgandakan tagihan bulanan.',
                    ],
                    [
                        'id'            => 'q2-5',
                        'question'      => 'Apa komponen toren (tangki atas) yang paling sering menyebabkan air meluap ke genteng atau pekarangan rumah?',
                        'options'       => [
                            'Radar pelampung otomatis atau valve pelampung mekanik yang macet',
                            'Warna cat toren yang pudar',
                            'Ketinggian tiang toren',
                            'Tutup toren yang terlalu kencang',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Pelampung otomatis berfungsi memutus aliran ketika toren penuh. Jika sensor atau mekaniknya aus, air akan terus terisi dan tumpah melalui pipa over-flow.',
                    ],
                    [
                        'id'            => 'q2-6',
                        'question'      => 'Tanda fisik apa di dalam rumah yang mengindikasikan adanya retak pipa tanam (di balik dinding bata)?',
                        'options'       => [
                            'Dinding terasa lembap dingin, cat menggelembung/terkelupas, atau muncul bercak jamur air',
                            'Lampu ruangan berkedip-kedip',
                            'Pintu rumah berderit kencang',
                            'Suhu ruangan menjadi sangat hangat',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Air yang merembes dari pipa tanam akan terserap pori-pori dinding bata, menimbulkan kelembapan permanen dan merusak lapisan semen serta cat dinding.',
                    ],
                    [
                        'id'            => 'q2-7',
                        'question'      => 'Apa fungsi utama stop-kran (ball valve) isolasi yang dipasang setelah meteran atau sebelum tangki toren?',
                        'options'       => [
                            'Hanya sebagai aksesoris pipa tambahan',
                            'Memungkinkan pemutusan aliran air secara cepat saat terjadi kebocoran darurat tanpa perlu menunggu petugas',
                            'Membuat air mengalir dua arah berlawanan',
                            'Menaikkan tekanan air hingga 10 kali lipat',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Stop-kran darurat memungkinkan pemilik rumah mengisolasi pipa bocor secara instan sehingga air bersih tidak terbuang sia-sia selama masa perbaikan.',
                    ],
                    [
                        'id'            => 'q2-8',
                        'question'      => 'Apa yang harus dilakukan warga jika melihat semburan air pipa transmisi pecah di bahu jalan raya Kota Payakumbuh?',
                        'options'       => [
                            'Mengabaikannya karena bukan pipa di halaman rumah sendiri',
                            'Segera memfoto dan melaporkan lewat menu "Pusat Layanan" di portal Smart Water agar Tim Reaksi Cepat terjun ke lokasi',
                            'Menutup pipa dengan kain basah sendiri',
                            'Menyiram jalan dengan air deterjen',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Laporan warga melalui portal Smart Water membantu petugas lapangan mendeteksi dan memperbaiki pipa bocor sebelum cadangan tekanan di zona tersebut drop.',
                    ],
                    [
                        'id'            => 'q2-9',
                        'question'      => 'Mengapa jarum segitiga kecil (bintang putar) pada kaca meteran dirancang sangat responsif?',
                        'options'       => [
                            'Untuk mengukur kecepatan angin di Payakumbuh',
                            'Sebagai detektor sensitif yang berputar bahkan pada kebocoran sangat kecil (0,1 liter/menit) yang tidak menggerakkan register angka kubikasi',
                            'Sebagai jarum jam penunjuk waktu',
                            'Hiasan manufaktur meteran',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Jarum flow indicator dibuat seringan mungkin agar kebocoran mikro (seperti keran wastafel menetes) tetap terdeteksi oleh pelanggan.',
                    ],
                    [
                        'id'            => 'q2-10',
                        'question'      => 'Bagaimana warga yang rajin belajar dan melaporkan kebocoran diuntungkan saat membayar tagihan di mobile app Tirta Sago?',
                        'options'       => [
                            'Mendapatkan Koin Reward yang otomatis menjadi diskon pemotong tagihan bulanan langsung di aplikasi mobile',
                            'Mendapatkan voucher tiket pesawat gratis',
                            'Mendapatkan diskon tagihan listrik PLN',
                            'Mendapatkan hadiah pulsa telepon tanpa batas',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Setiap poin XP dan koin yang Anda dapatkan dari kuis serta tantangan konservasi dapat dipakai memotong nominal tagihan air bulanan saat pembayaran mobile!',
                    ],
                ],
            ],
            [
                'id'          => 'course-3',
                'title'       => 'Gaya Hidup Air Berkelanjutan & Kebiasaan Gen-Z',
                'category'    => 'konservasi',
                'difficulty'  => 'Pemula',
                'readingTime' => '5 Menit',
                'summary'     => 'Tips efisiensi air modern: Aerator keran, teknik mandi efisien, dan mencuci pakaian tanpa buang galon percuma.',
                'xpReward'    => 110,
                'lessons'     => [
                    [
                        'id'                => 'lesson-3-1',
                        'title'             => 'Aerator Keran: Alat Kecil Penghemat 50% Air',
                        'readingTime'       => '2.5 Menit',
                        'summary'           => 'Teknologi gelembung udara mikroskopis yang menjaga tekanan semburan dengan debit air lebih hemat.',
                        'contentParagraphs' => [
                            'Aerator adalah saringan jaring kecil berulir yang dipasang di ujung moncong keran air dapur atau wastafel.',
                            'Alat ini mencampurkan udara ke dalam aliran air, memecah semburan menjadi ribuan bulir halus. Hasilnya: tekanan terasa tetap kencang dan tidak memercik, tetapi volume air yang keluar berkurang dari 12 L/menit menjadi hanya 5–6 L/menit.',
                            'Harganya sangat terjangkau di toko online maupun toko bangunan di Payakumbuh (sekitar Rp10.000–Rp20.000), namun dampaknya langsung terasa pada rekening pemakaian bulanan.',
                        ],
                        'practicalTips' => [
                            'Pasang aerator pada keran wastafel cuci tangan dan keran tempat mencuci piring.',
                            'Bersihkan saringan aerator dari kerak pasir tiap 3 bulan sekali.',
                        ],
                    ],
                    [
                        'id'                => 'lesson-3-2',
                        'title'             => 'Audit Mandi: Gayung vs Shower Efisien',
                        'readingTime'       => '2.5 Menit',
                        'summary'           => 'Perbandingan riil konsumsi air antara mandi bak gayung tradisional dan shower modern.',
                        'contentParagraphs' => [
                            'Mandi menggunakan gayung tradisional menghabiskan rata-rata 15–20 gayung per orang, setara dengan 60 hingga 90 liter air per sekali mandi.',
                            'Sedangkan mandi menggunakan kepala shower hemat air (flow rate 7 L/menit) dengan durasi 5 menit hanya menghabiskan 35 liter air—menghemat lebih dari 50%!',
                            'Jika Anda tetap menyukai mandi gayung, gunakan teknik basuh teratur dan jangan biarkan kran pengisi bak meluap saat mandi.',
                        ],
                        'practicalTips' => [
                            'Pasang timer musik 5 menit saat mandi shower untuk menjaga durasi tetap efisien.',
                            'Gunakan pelampung otomatis (radar toren) agar air bak tidak pernah tumpah ke lantai.',
                        ],
                    ],
                ],
                'quiz' => [
                    [
                        'id'            => 'q3-1',
                        'question'      => 'Bagaimana prinsip kerja alat aerator keran air?',
                        'options'       => [
                            'Mendinginkan suhu air secara instan',
                            'Mencampurkan udara ke dalam pancaran air sehingga semburan tetap mantap dengan volume air 40-50% lebih hemat',
                            'Menambah kaporit ke dalam air',
                            'Mematikan aliran air secara permanen',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Benar! Aerator memasukkan gelembung udara mikro ke aliran air sehingga sensasi tekanan tetap kuat namun volume air terpakai jauh lebih hemat.',
                    ],
                    [
                        'id'            => 'q3-2',
                        'question'      => 'Berapa perbandingan konsumsi air mandi gayung tradisional dibandingkan shower hemat air 5 menit?',
                        'options'       => [
                            'Mandi gayung rata-rata 60-90 liter vs Shower hemat air hanya 35 liter (hemat >50%)',
                            'Mandi gayung lebih hemat daripada shower',
                            'Keduanya sama persis 10 liter',
                            'Shower menghabiskan 500 liter per menit',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Mandi menggunakan gayung cenderung boros (15-20 gayung = 60-90 L), sedangkan kepala shower efisien membatasi debit air ke 7 L/menit sehingga 5 menit mandi hanya butuh 35 L.',
                    ],
                    [
                        'id'            => 'q3-3',
                        'question'      => 'Kapan waktu terbaik untuk menyiram tanaman pekarangan agar air terserap maksimal dan tidak menguap sia-sia?',
                        'options'       => [
                            'Tepat tengah hari saat matahari terik (12:00 siang)',
                            'Pagi hari sebelum pukul 08.00 atau sore hari setelah pukul 17.00',
                            'Hanya saat tengah malam',
                            'Kapan saja tanpa ada perbedaan',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Menyiram di pagi atau sore hari meminimalkan penguapan akibat terik matahari dan mencegah daun tanaman hangus terbakar.',
                    ],
                    [
                        'id'            => 'q3-4',
                        'question'      => 'Praktik penggunaan mesin cuci pakaian manakah yang paling efisien dalam menghemat air dan listrik?',
                        'options'       => [
                            'Mencuci 1 potong baju setiap kali selesai dipakai',
                            'Menjalankan siklus pencucian hanya ketika muatan pakaian telah mencapai kapasitas penuh (full load)',
                            'Menggunakan air mengalir terus menerus ke tabung mesin cuci',
                            'Mengulang siklus bilas hingga 5 kali',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Mencuci dengan muatan penuh memaksimalkan efisiensi pemakaian air per kilogram pakaian dan menghemat deterjen serta listrik.',
                    ],
                    [
                        'id'            => 'q3-5',
                        'question'      => 'Berapa durasi mandi ideal yang dianjurkan dalam kampanye konservasi air generasi muda (Smart Water)?',
                        'options'       => [
                            'Maksimal 5 menit dengan mematikan pancuran saat menyabuni badan',
                            'Minimal 30 menit',
                            '1 jam penuh',
                            'Tergantung musim',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Tantangan mandi 5 menit ("5-Minute Shower Challenge") dapat memangkas ratusan liter pemborosan air per rumah tangga setiap bulannya.',
                    ],
                    [
                        'id'            => 'q3-6',
                        'question'      => 'Apa keuntungan menggunakan ember dan spons dibandingkan selang mengalir langsung saat mencuci sepeda motor atau mobil?',
                        'options'       => [
                            'Menghemat hingga 100-150 liter air bersih per sekali cuci',
                            'Membuat bodi kendaraan lebih cepat berkarat',
                            'Air deterjen tidak bisa berbusa',
                            'Tidak ada perbedaan',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Selang air mengalir terus bisa menyemburkan 15 liter air per menit. Menggunakan ember dan lap mengontrol pemakaian air secara presisi.',
                    ],
                    [
                        'id'            => 'q3-7',
                        'question'      => 'Metode mencairkan daging/makanan beku (defrosting) manakah yang paling hemat air?',
                        'options'       => [
                            'Mengucurkan keran air mengalir deras ke atas daging selama 20 menit',
                            'Merendam makanan dalam mangkuk berisi air atau menurunkannya ke chiller kulkas semalam sebelumnya',
                            'Menyiram air panas mendidih terus menerus',
                            'Membiarkan daging di bawah keran taman',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Mencairkan makanan di dalam mangkuk tertutup atau chiller tidak menyia-nyiakan galon air pipa mengalir.',
                    ],
                    [
                        'id'            => 'q3-8',
                        'question'      => 'Air buangan dari kondensasi AC atau sisa cucian sayuran segar sebaiknya dimanfaatkan untuk apa?',
                        'options'       => [
                            'Dibuang langsung ke saluran pembuangan',
                            'Menyiram tanaman pot pekarangan atau mengepel lantai garasi',
                            'Diminum langsung tanpa dimasak',
                            'Disemprotkan ke kabel listrik',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Air kondensasi AC bebas mineral kapur dan air cucian sayur kaya unsur organik ringan, sangat ideal untuk menyiram bunga pekarangan.',
                    ],
                    [
                        'id'            => 'q3-9',
                        'question'      => 'Jika sebuah keran wastafel menetes 1 tetes per detik, berapa perkiraan air yang terbuang percuma dalam sebulan?',
                        'options'       => [
                            'Hanya 1 sendok teh',
                            'Lebih dari 500 hingga 1.000 liter air bersih',
                            'Tepat 0 liter',
                            '100.000 liter',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Tetesan halus 1 tetes per detik setara dengan sekitar 30 liter per hari atau hampir 1.000 liter (1 meter kubik penuh) per bulan!',
                    ],
                    [
                        'id'            => 'q3-10',
                        'question'      => 'Apa keuntungan nyata bagi pelanggan yang aktif mengumpulkan poin dari kuis dan tantangan hemat air ini?',
                        'options'       => [
                            'Poin otomatis menjadi saldo voucher diskon pembayaran rekening air pada aplikasi mobile Tirta Sago',
                            'Mendapatkan sertifikat kertas tanpa nilai',
                            'Mendapatkan kaos partai',
                            'Tidak ada keuntungan sama sekali',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Setiap poin XP yang dikumpulkan langsung dikonversi menjadi potongan diskon tagihan rekening air saat dibayar melalui aplikasi mobile!',
                    ],
                ],
            ],
            [
                'id'          => 'course-4',
                'title'       => 'Membaca Angka Meteran & Literasi Tagihan Tirta Sago',
                'category'    => 'meteran_tagihan',
                'difficulty'  => 'Menengah',
                'readingTime' => '6 Menit',
                'summary'     => 'Bedah dial angka hitam vs angka merah, konversi m³ ke liter, dan simulasi tarif progresif.',
                'xpReward'    => 140,
                'lessons'     => [
                    [
                        'id'                => 'lesson-4-1',
                        'title'             => 'Angka Hitam vs Angka Merah pada Register Meter',
                        'readingTime'       => '3 Menit',
                        'summary'           => 'Jangan salah membaca register meteran air mekanik saat mengonfirmasi hasil foto AI-OCR.',
                        'contentParagraphs' => [
                            'Pada kaca register meteran air terdapat 7–8 digit angka. Digit-digit ini terbagi menjadi dua warna:',
                            '1. Digit Berlatar Belakang HITAM: Menunjukkan satuan meter kubik (m³ atau 1.000 liter). Angka hitam inilah yang dicatat oleh petugas atau dibaca oleh modul AI-OCR aplikasi mobile untuk perhitungan tagihan resmi bulanan.',
                            '2. Digit Berlatar Belakang MERAH: Menunjukkan pecahan meter kubik (ratusan liter dan puluhan liter). Angka merah biasanya digunakan saat pengujian akurasi atau mendeteksi kebocoran kecil.',
                            'Contoh: Jika meteran menunjukkan [0 1 4 2] hitam dan [8 5] merah, artinya pemakaian akumulatif adalah 142 meter kubik (142 m³), dan dibulatkan menjadi 142 m³ pada sistem penagihan.',
                        ],
                        'practicalTips' => [
                            'Saat aplikasi mobile melakukan AI-OCR scan, perhatikan apakah angka hitam terbaca sesuai dengan fisik register.',
                            'Pastikan kaca penutup meteran selalu dibersihkan dari debu atau lumut agar pembacaan kamera tajam.',
                        ],
                    ],
                ],
                'quiz' => [
                    [
                        'id'            => 'q4-1',
                        'question'      => 'Warna latar belakang digit angka manakah pada meteran air yang dihitung sebagai satuan resmi kubikasi (m³) untuk rekening tagihan?',
                        'options'       => [
                            'Digit angka latar belakang Merah',
                            'Digit angka latar belakang Hitam',
                            'Jarum segitiga kecil',
                            'Logo pabrik meteran',
                        ],
                        'correctAnswer' => 1,
                        'explanation'   => 'Tepat sekali! Digit hitam menunjukkan satuan penuh meter kubik (m³ = 1.000 liter) yang menjadi dasar pencatatan rekening tagihan.',
                    ],
                    [
                        'id'            => 'q4-2',
                        'question'      => 'Apa fungsi utama angka digit berlatar belakang merah pada meteran air mekanik?',
                        'options'       => [
                            'Menunjukkan pecahan meter kubik (ratusan dan puluhan liter) untuk uji akurasi dan deteksi rembesan kecil',
                            'Menunjukkan jumlah denda keterlambatan',
                            'Menunjukkan nomor registrasi KTP pemilik rumah',
                            'Menunjukkan tanggal kadaluarsa meteran',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Digit merah mengukur pecahan di bawah 1 m³. Angka ini sangat berguna untuk memantau pemakaian detail dalam hitungan liter.',
                    ],
                    [
                        'id'            => 'q4-3',
                        'question'      => 'Jika register meteran Anda menunjukkan [0 1 5 4] berlatar hitam dan [7 8] berlatar merah, berapakah angka kubikasi yang dijadikan dasar rekening?',
                        'options'       => [
                            '154 m³',
                            '15.478 m³',
                            '78 m³',
                            '0 m³',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Hanya angka hitam [0 1 5 4] yang dicatat sebagai 154 m³ pada sistem billing resmi Tirta Sago.',
                    ],
                    [
                        'id'            => 'q4-4',
                        'question'      => 'Berapa tarif pemakaian air Blok I (kategori kebutuhan dasar 0–10 m³) pada skema tarif rumah tangga Tirta Sago?',
                        'options'       => [
                            'Rp 2.500 per meter kubik (m³)',
                            'Rp 25.000 per meter kubik (m³)',
                            'Gratis tanpa biaya',
                            'Rp 100.000 per meter kubik (m³)',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Pemakaian dasar 0-10 m³ disubsidi dengan tarif murah Rp 2.500/m³ agar seluruh warga Payakumbuh mampu memenuhi kebutuhan sanitasi dan minum sehari-hari.',
                    ],
                    [
                        'id'            => 'q4-5',
                        'question'      => 'Mengapa Perumda Air Minum Tirta Sago menerapkan sistem tarif progresif (Blok I s/d Blok IV)?',
                        'options'       => [
                            'Untuk mendorong warga bijak berhemat air dan mencegah pemborosan berlebihan oleh rumah tangga pemakai besar',
                            'Agar warga tidak boleh mandi',
                            'Untuk menaikkan pajak kendaraan',
                            'Secara acak tanpa alasan',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Tarif progresif memberikan subsidi pada pemakaian hemat dan mengenakan tarif lebih tinggi pada pemakaian boros demi keadilan ketersediaan air.',
                    ],
                    [
                        'id'            => 'q4-6',
                        'question'      => 'Bagaimana fitur AI-OCR pada aplikasi mobile Tirta Sago mempermudah pelanggan?',
                        'options'       => [
                            'Pelanggan cukup memfoto angka meteran, lalu AI membaca angka secara presisi dan otomatis mengisi laporan pemakaian mandiri',
                            'Mengubah foto meteran menjadi lukisan abstrak',
                            'Menghapus foto dari galeri HP',
                            'Memutar musik saat meteran difoto',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'AI-OCR membaca angka dial meter secara transparan, menghilangkan potensi salah catat angka oleh petugas manual.',
                    ],
                    [
                        'id'            => 'q4-7',
                        'question'      => 'Kapan tanggal jatuh tempo pembayaran tagihan rekening air Tirta Sago setiap bulannya?',
                        'options'       => [
                            'Tanggal 20 setiap bulan',
                            'Tanggal 1 Januari saja',
                            'Tanggal 31 Desember',
                            'Tidak ada batas tanggal',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Batas akhir pembayaran rekening air adalah tanggal 20 setiap bulan untuk menghindari pengenaan denda administrasi keterlambatan.',
                    ],
                    [
                        'id'            => 'q4-8',
                        'question'      => 'Komponen biaya apa saja yang membentuk total rekening tagihan air Tirta Sago setiap bulan?',
                        'options'       => [
                            'Biaya pemakaian air per kubik + biaya pemeliharaan meter + biaya administrasi',
                            'Hanya biaya pulsa telepon',
                            'Biaya tiket bioskop',
                            'Biaya bensin dinas',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Total tagihan terdiri dari biaya air bersih sesuai kubikasi pemakaian ditambah biaya administrasi dan pemeliharaan pipa meter.',
                    ],
                    [
                        'id'            => 'q4-9',
                        'question'      => 'Bagaimana cara menggunakan Koin Smart Water Anda untuk mendapatkan potongan harga tagihan di aplikasi mobile?',
                        'options'       => [
                            'Pilih opsi "Gunakan Koin Poin Diskon" pada menu pembayaran tagihan di aplikasi mobile Tirta Sago',
                            'Membawa koin fisik ke kantor bank',
                            'Mengirim surat pos ke walikota',
                            'Menelepon operator radio',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Saldo koin yang Anda kumpulkan dari Smart Water Academy langsung terhubung dengan akun aplikasi mobile dan siap dipotongkan saat check-out tagihan.',
                    ],
                    [
                        'id'            => 'q4-10',
                        'question'      => 'Berapa nilai konversi potongan harga yang Anda peroleh untuk setiap 1 Poin Smart Water?',
                        'options'       => [
                            '1 Poin = Potongan Rp 100,- pada total tagihan air bulanan',
                            '1 Poin = Rp 1,-',
                            '1 Poin = Rp 100.000,-',
                            'Poin tidak dapat dipotongkan',
                        ],
                        'correctAnswer' => 0,
                        'explanation'   => 'Setiap 1 Poin bernilai diskon Rp 100,-. Jadi jika Anda mengumpulkan 100 Poin dari kuis ini, Anda langsung hemat Rp 10.000 pada tagihan air mobile Anda!',
                    ],
                ],
            ],
        ];
        
        if (!function_exists('e')) {
            function e(string $str): string
            {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-beranda': {
        /**
         * Konfigurasi & Data Halaman Beranda - Smart Water Kota Payakumbuh
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Smart Water - Kota Payakumbuh',
            'description' => 'Portal Layanan Air, Edukasi Konservasi, dan Tantangan Hemat Air Kota Payakumbuh',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        // Tab aktif default adalah 'beranda'
        $currentTab = isset($_GET['page']) ? trim((string)$_GET['page']) : 'beranda';
        $allowedTabs = ['beranda', 'layanan', 'akademi', 'tantangan', 'tentang', 'profil', 'admin'];
        if (!in_array($currentTab, $allowedTabs, true)) {
            $currentTab = 'beranda';
        }
        
        // Simulasi status user dari session PHP (null jika belum login)
        $currentUser = $_SESSION['swc_user'] ?? null;
        
        // Menu Navigasi Header
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        // Koleksi Foto Lanskap Sumber Air & Danau untuk Hero Background Dinamis
        $HERO_BACKGROUNDS = [
            [
                'url'      => '../../assets/images/hero-lake-bright.jpg',
                'title'    => 'Danau & Sumber Air Alami',
                'location' => 'Lembah Pegunungan Sago',
            ],
            [
                'url'      => '../../assets/images/hero-lake-serene.jpg',
                'title'    => 'Hulu Mata Air Sikabu',
                'location' => 'Tangkapan Air Pegunungan',
            ],
            [
                'url'      => '../../assets/images/hero-lake.jpg',
                'title'    => 'DAS Batang Agam',
                'location' => 'Aliran Transmisi Alami',
            ],
            [
                'url'      => '../../assets/images/hero-lake-grass.jpg',
                'title'    => 'Dermaga & Ekosistem Perairan',
                'location' => 'Konservasi Air Bersih',
            ],
        ];
        
        // Daftar Target Capaian Kolektif Komunitas
        $COMMUNITY_TARGETS = [
            [
                'value'       => '1.000.000 L',
                'color_class' => 'text-cyan-900',
                'title'       => 'Target Air Terhemat / Bulan',
                'desc'        => 'Melalui adopsi shower hemat, aerator keran, dan pembiasaan baskom cuci piring.',
            ],
            [
                'value'       => '5.000+',
                'color_class' => 'text-emerald-800',
                'title'       => 'Detektif Kebocoran Mandiri',
                'desc'        => 'Rumah tangga yang rutin melakukan uji statis meteran dan pemeriksaan toren.',
            ],
            [
                'value'       => '< 2 Jam',
                'color_class' => 'text-sky-800',
                'title'       => 'Respon Dispatcher Pengaduan',
                'desc'        => 'Verifikasi awal tiket dan penugasan Tim Reaksi Cepat ke titik pipa bocor.',
            ],
            [
                'value'       => '100%',
                'color_class' => 'text-indigo-900',
                'title'       => 'Akurasi Pembacaan AI-OCR',
                'desc'        => 'Konfirmasi mandiri pelanggan menghilangkan sengketa tafsir angka kubikasi meteran.',
            ],
        ];
        
        /**
         * Helper escape HTML aman
         */
        if (!function_exists('e')) {
            function e(string $str): string
            {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-chat-ai': {
        /**
         * Konfigurasi & Data Halaman Chat AI (TirtaBot AI) - Smart Water Kota Payakumbuh
         * Sesuai persis dengan src/components/chat/AiChatbot.tsx & backend api/chat.php
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Chat AI TirtaBot - Smart Water Kota Payakumbuh',
            'description' => 'Asisten Kecerdasan Buatan TirtaBot AI (Gemini 2.5) untuk informasi tarif Tirta Sago, deteksi pipa bocor, panduan AI-OCR meteran, dan konservasi air Kota Payakumbuh.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'chat-ai';
        $currentUser = $_SESSION['swc_user'] ?? null;
        $userPoints = (int)($currentUser['points'] ?? ($_SESSION['swc_challenge_progress']['points'] ?? 380));
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $CHAT_QUICK_PROMPTS = [
            '💧 Cara cek kebocoran pipa tersembunyi?',
            '📊 Berapa tarif air Tirta Sago per kubik?',
            '📱 Bagaimana cara kerja AI-OCR meteran?',
            '🌱 Tips hemat air mudah di rumah?',
            '📝 Cara membuat laporan pengaduan pipa bocor?',
        ];
        
        $INITIAL_WELCOME_CONTENT = "Halo Sanak Warga Payakumbuh! 👋\n\nSaya **TirtaBot AI**, asisten cerdas Smart Water Kota Payakumbuh yang ditenagai oleh kecerdasan buatan.\n\nAda yang bisa saya bantu seputar penghematan air, cek pipa bocor, tarif kubikasi Tirta Sago, atau cara lapor pengaduan hari ini?";
        
        if (!function_exists('e')) {
            function e(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-login-daftar': {
        /**
         * Konfigurasi & Data Halaman Login & Daftar - Smart Water Kota Payakumbuh
         * Sesuai persis dengan src/components/auth/AuthPage.tsx & PayakumbuhIllustration.tsx
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Masuk & Daftar - Smart Water Kota Payakumbuh',
            'description' => 'Portal Autentikasi Masyarakat & Petugas PDAM Tirta Sago Smart Water Kota Payakumbuh dengan latar kaca buram pemandangan alam Payakumbuh.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '0812-7389-4902',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $allowedModes = ['login', 'register', 'forgot-password', 'admin-login'];
        $requestedMode = $_GET['mode'] ?? (function_exists('request') 
            ? (request()->query('mode') ?? (request()->is('register') || request()->is('daftar') ? 'register' : (request()->is('forgot-password') ? 'forgot-password' : 'login')))
            : 'login');
        $initialMode = in_array($requestedMode, $allowedModes, true) ? $requestedMode : 'login';
        
        $DEFAULT_RESIDENT_USER = [
            'id'               => 'user-01',
            'name'             => 'Warga Google Payakumbuh',
            'email'            => 'warga.google@gmail.com',
            'phone'            => '0812-7389-4902',
            'role'             => 'Pelanggan Terverifikasi 2A - Rumah Tangga Mandiri (Standar PDAM)',
            'district'         => 'Payakumbuh Barat',
            'address'          => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo, Kec. Payakumbuh Barat, Kota Payakumbuh',
            'meterNumber'      => '04-0821-4902',
            'tariffCategory'   => '2A - Rumah Tangga Mandiri (Standar PDAM)',
            'points'           => 380,
            'isAdmin'          => false,
            'joinedDate'       => 'Oktober 2026',
        ];
        
        $DEFAULT_ADMIN_USER = [
            'id'               => 'admin-ts-01',
            'name'             => 'Ir. Hendra Pratama, S.T.',
            'email'            => 'admin@tirtasago.id',
            'phone'            => '0811-6677-8899',
            'role'             => 'Kepala Seksi Distribusi & SCADA',
            'district'         => 'Payakumbuh Barat',
            'address'          => 'Kantor Pusat PDAM Tirta Sago, Jl. Imam Bonjol No. 14, Kota Payakumbuh',
            'meterNumber'      => 'ADM-TS-001',
            'tariffCategory'   => 'Pengelola Internal PDAM Tirta Sago',
            'points'           => 1850,
            'isAdmin'          => true,
            'staffId'          => 'NIP-TS-198402-089',
            'staffRole'        => 'Super Admin & Dispatcher Teknis',
            'department'       => 'Divisi Transmisi, Distribusi & Pelayanan Pelanggan',
            'joinedDate'       => 'Januari 2024',
        ];
        
        $currentUser = $_SESSION['swc_user'] ?? null;
        
        if (!function_exists('e')) {
            function e(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-pusat-layanan': {
        /**
         * Konfigurasi & Data Halaman Pusat Layanan - Smart Water Kota Payakumbuh
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Pusat Layanan - Smart Water Kota Payakumbuh',
            'description' => 'Informasi gangguan pipa resmi, pelaporan pengaduan air cepat tanggap, dan pelacakan status penanganan teknis Perumda Air Minum Tirta Sago.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'layanan';
        
        // Simulasi status user dari session PHP (null secara default, bisa toggle demo jika diinginkan)
        $currentUser = $_SESSION['swc_user'] ?? null;
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $DISTRICTS_PAYAKUMBUH = [
            'Payakumbuh Barat',
            'Payakumbuh Utara',
            'Payakumbuh Timur',
            'Payakumbuh Selatan',
            'Lampasi Tigo Nagori (Latina)',
        ];
        
        $DISTRICT_DROPDOWN_OPTIONS = [
            'Seluruh Kota Payakumbuh',
            'Payakumbuh Barat',
            'Payakumbuh Utara',
            'Payakumbuh Timur',
            'Payakumbuh Selatan',
            'Lampasi Tigo Nagori (Latina)',
        ];
        
        $INITIAL_COMPLAINTS = [
            [
                'id'          => 'TK-PYK-2026-8942',
                'userId'      => 'user-01',
                'userName'    => 'Rian Pratama',
                'userPhone'   => '0812-7654-3210',
                'category'    => 'pipa_bocor',
                'title'       => 'Rembesan Air Bersih di Bahu Jalan Dekat Pasar Ibuh',
                'description' => 'Terlihat genangan air jernih menyembur dari sambungan pipa bawah aspal di depan pertokoan blok B. Aliran cukup deras sejak pagi.',
                'district'    => 'Payakumbuh Barat',
                'address'     => 'Jl. Ahmad Yani No. 45, Dekat Simpang Pasar Ibuh',
                'status'      => 'SELESAI',
                'createdAt'   => '2026-10-17 07:45 WIB',
                'updatedAt'   => '2026-10-17 09:15 WIB',
                'updates'     => [
                    [
                        'timestamp' => '2026-10-17 07:45 WIB',
                        'status'    => 'BARU',
                        'note'      => 'Laporan pengaduan berhasil tercatat dalam sistem pusat layanan pelanggan.',
                        'actor'     => 'Sistem Otomatis',
                    ],
                    [
                        'timestamp' => '2026-10-17 08:30 WIB',
                        'status'    => 'DIPROSES',
                        'note'      => 'Laporan diverifikasi oleh operator. Tim Reaksi Cepat (TRC) Zona Barat telah diberangkatkan ke lokasi.',
                        'actor'     => 'Operator Pengaduan Payakumbuh',
                    ],
                    [
                        'timestamp' => '2026-10-17 09:15 WIB',
                        'status'    => 'SELESAI',
                        'note'      => 'Penggantian clamp pipa dan uji tekanan selesai dilakukan. Aliran air kembali normal.',
                        'actor'     => 'Tim Lapangan TRC-02',
                    ],
                ],
            ],
            [
                'id'          => 'TK-PYK-2026-7241',
                'userId'      => 'user-01',
                'userName'    => 'Rian Pratama',
                'userPhone'   => '0812-7389-4902',
                'category'    => 'meteran_rusak',
                'title'       => 'Pemeriksaan Jarum Dial Meteran Air Lambat',
                'description' => 'Pengecekan fisik penunjuk angka dial jarum meteran air karena terasa agak macet saat kran bak mandi dibuka.',
                'district'    => 'Payakumbuh Barat',
                'address'     => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo',
                'status'      => 'SELESAI',
                'createdAt'   => '2026-09-28 10:15 WIB',
                'updatedAt'   => '2026-09-29 14:00 WIB',
                'updates'     => [
                    [
                        'timestamp' => '2026-09-28 10:15 WIB',
                        'status'    => 'BARU',
                        'note'      => 'Laporan permohonan kalibrasi meteran masuk sistem.',
                        'actor'     => 'Sistem Otomatis',
                    ],
                    [
                        'timestamp' => '2026-09-29 09:30 WIB',
                        'status'    => 'DIPROSES',
                        'note'      => 'Teknisi meteran PDAM Tirta Sago memeriksa segel dan akurasi putaran dial.',
                        'actor'     => 'Teknisi Meter Wilayah Barat',
                    ],
                    [
                        'timestamp' => '2026-09-29 14:00 WIB',
                        'status'    => 'SELESAI',
                        'note'      => 'Pembersihan saringan strainer meteran selesai dilakukan. Akurasi jarum dial kembali presisi.',
                        'actor'     => 'Petugas Pengujian Akurasi',
                    ],
                ],
            ],
            [
                'id'          => 'TK-PYK-2026-8109',
                'userId'      => 'user-02',
                'userName'    => 'Ibu Siti Nurbaya',
                'userPhone'   => '0852-6389-1122',
                'category'    => 'kualitas_keruh',
                'title'       => 'Air Keran Mengandung Endapan Cokelat Halus',
                'description' => 'Sejak tadi malam air di bak penampungan agak kekuningan setelah hujan lebat di hulu.',
                'district'    => 'Payakumbuh Timur',
                'address'     => 'Kompleks Griya Tiakar Indah No. 12',
                'status'      => 'SELESAI',
                'createdAt'   => '2026-10-15 16:20 WIB',
                'updatedAt'   => '2026-10-16 11:30 WIB',
                'updates'     => [
                    [
                        'timestamp' => '2026-10-15 16:20 WIB',
                        'status'    => 'BARU',
                        'note'      => 'Tiket pengaduan diterbitkan dan dialokasikan ke Pengawas Kualitas Air.',
                        'actor'     => 'Sistem Otomatis',
                    ],
                    [
                        'timestamp' => '2026-10-16 08:00 WIB',
                        'status'    => 'DIPROSES',
                        'note'      => 'Petugas melakukan flushing pipa hydrant di ujung jalur Tiakar untuk membuang endapan.',
                        'actor'     => 'Petugas Distribusi Zona Timur',
                    ],
                    [
                        'timestamp' => '2026-10-16 11:30 WIB',
                        'status'    => 'SELESAI',
                        'note'      => 'Flushing selesai. Uji sampel kejernihan (NTU) memenuhi standar baku mutu air minum. Pelanggan telah konfirmasi air kembali bening.',
                        'actor'     => 'Supervisor Layanan Pelanggan',
                    ],
                ],
            ],
        ];
        
        if (!isset($_SESSION['swc_complaints']) || !is_array($_SESSION['swc_complaints'])) {
            $_SESSION['swc_complaints'] = $INITIAL_COMPLAINTS;
        }
        
        $complaints = $_SESSION['swc_complaints'];
        
        $ANNOUNCEMENTS = [
            [
                'id'                  => 'ANN-2026-03-01',
                'theme'               => 'orange',
                'category_label'      => 'pemeliharaan',
                'status_label'        => 'Dalam Penanganan',
                'status_type'         => 'dalam_penanganan',
                'publishedAt'         => '2026-10-17 08:30 WIB',
                'title'               => 'Optimalisasi Jaringan Pipa Distribusi Utama Jl. Soekarno-Hatta',
                'description'         => 'Pekerjaan interkoneksi pipa transmisi 250mm guna memperkuat tekanan aliran air ke area perumahan warga di Koto Nan Ampek dan Labuh Baru.',
                'districtsAffected'   => ['Payakumbuh Barat', 'Payakumbuh Utara'],
                'estimatedResolution' => '17 Oktober 2026, 17:00 WIB',
                'actionAdvice'        => 'Masyarakat diimbau menampung air bersih secukupnya. Armada tangki darurat siaga di posko kelurahan jika dibutuhkan.',
            ],
            [
                'id'                  => 'ANN-2026-03-02',
                'theme'               => 'green',
                'category_label'      => 'kualitas air',
                'status_label'        => 'Selesai',
                'status_type'         => 'selesai',
                'publishedAt'         => '2026-10-16 14:00 WIB',
                'title'               => 'Pembersihan Sedimen Bak Prasedimentasi Sumber Mata Air Sikabu',
                'description'         => 'Pembersihan rutin material pasir dan lumpur pasca hujan intensitas tinggi di hulu Gunung Sago.',
                'districtsAffected'   => ['Payakumbuh Timur', 'Payakumbuh Selatan'],
                'estimatedResolution' => 'Telah Selesai',
                'estimatedSub'        => '(Tekanan Normal Kembali)',
                'actionAdvice'        => 'Jika air keran awal terlihat keruh sesaat, buka keran pembuangan selama 1-2 menit hingga jernih.',
            ],
            [
                'id'                  => 'ANN-2026-03-03',
                'theme'               => 'blue',
                'category_label'      => 'info layanan',
                'status_label'        => 'Aktif',
                'status_type'         => 'aktif',
                'publishedAt'         => '2026-10-15 09:00 WIB',
                'title'               => 'Imbauan Waspada Pipa Tersumbat & Sosialisasi Pelaporan Cepat',
                'description'         => 'Layanan respons cepat tim reaksi cepat (TRC) siaga 24 jam untuk menindaklanjuti laporan kebocoran air di jalan umum atau meteran pelanggan.',
                'districtsAffected'   => ['Lampasi Tigo Nagori (Latina)', 'Payakumbuh Barat'],
                'estimatedResolution' => 'Berlaku Selama Musim Pancaroba',
                'actionAdvice'        => 'Gunakan fitur Buat Pengaduan di Smart Water untuk mendapatkan nomor tiket pelacakan otomatis.',
            ],
        ];
        
        // 6 Pertanyaan FAQ Resmi sesuai ServiceCenterView.tsx
        $FAQ_LIST = [
            [
                'question' => 'Bagaimana cara kerja AI-OCR pembacaan meteran di aplikasi mobile?',
                'answer'   => 'Aplikasi mobile Smart Water Customer dilengkapi modul kecerdasan buatan (AI-OCR) pada kamera handphone. Pelanggan cukup mengarahkan kamera ke deretan angka meteran air fisik. Sistem membaca angka kubikasi secara otomatis, lalu Anda mengonfirmasi kecocokan angka sebelum mengirimnya ke database Perumda Tirta Sago.',
            ],
            [
                'question' => 'Apakah saya bisa mengirim pembacaan meteran lewat website ini?',
                'answer'   => 'Pengiriman angka foto meteran resmi dioptimalkan melalui aplikasi mobile Smart Water Android/iOS untuk validasi AI-OCR dan geotagging GPS. Namun, di portal website ini Anda dapat memantau riwayat kubikasi bulanan, grafik tren pemakaian air, dan estimasi tagihan rekening air Anda.',
            ],
            [
                'question' => 'Bagaimana perhitungan tarif air minum di Kota Payakumbuh?',
                'answer'   => 'Tarif air minum dihitung berdasarkan golongan pelanggan (Rumah Tangga A/B/C, Niaga, Sosial, dan Industri) dengan sistem blok kubikasi progresif (Blok 1: 0–10 m³, Blok 2: 11–20 m³, dan Blok 3: >20 m³) untuk mendorong perilaku konservasi air di masyarakat.',
            ],
            [
                'question' => 'Berapa lama estimasi penanganan kebocoran pipa setelah saya melapor?',
                'answer'   => 'Tim Reaksi Cepat (TRC) Perumda Tirta Sago beroperasi 24 jam dengan standar peninjauan lokasi maksimal 2 jam setelah tiket diterbitkan. Lama penanganan fisik bergantung pada dimensi pipa (distribusi utama atau sambungan rumah) dan kondisi penggalian di lapangan.',
            ],
            [
                'question' => 'Berapa standar kebutuhan air bersih per orang per hari di wilayah perkotaan?',
                'answer'   => 'Berdasarkan standar Ditjen Cipta Karya Kementerian PUPR dan WHO, kebutuhan konsumsi air bersih rata-rata masyarakat perkotaan berkisar antara 100 hingga 120 liter per orang per hari untuk minum, memasak, mandi, dan sanitasi rumah tangga.',
            ],
            [
                'question' => 'Bagaimana cara mendapatkan poin dan badge di Smart Water?',
                'answer'   => 'Poin XP dan lencana prestasi dapat diperoleh dengan mengikuti kuis edukasi di Akademi Air, menyelesaikan tantangan hemat air mingguan, serta aktif melaporkan semburan pipa bocor dengan data foto akurat melalui formulir pengaduan.',
            ],
        ];
        
        // Kategori Pengaduan untuk ComplaintFormPage
        $COMPLAINT_CATEGORIES = [
            [
                'id'    => 'pipa_bocor',
                'label' => 'Kebocoran Pipa / Semburan Air',
                'desc'  => 'Pipa distribusi atau sambungan rumah pecah/bocor di jalan atau meteran',
                'badge' => 'Prioritas Cepat',
            ],
            [
                'id'    => 'air_mati',
                'label' => 'Air Tidak Mengalir / Mati Total',
                'desc'  => 'Aliran keran terhenti sama sekali di rumah tangga atau lingkungan',
                'badge' => 'Darurat',
            ],
            [
                'id'    => 'kualitas_keruh',
                'label' => 'Kualitas Air Keruh / Berbau',
                'desc'  => 'Air mengandung lumpur, endapan pasir, atau bau tidak wajar',
                'badge' => null,
            ],
            [
                'id'    => 'meteran_rusak',
                'label' => 'Meteran Rusak / Jarum Macet',
                'desc'  => 'Kaca meteran buram, jarum dial tidak berputar, atau segel terlepas',
                'badge' => null,
            ],
            [
                'id'    => 'tagihan',
                'label' => 'Klarifikasi Rekening Tagihan',
                'desc'  => 'Lonjakan angka kubikasi tidak wajar atau pertanyaan tarif',
                'badge' => null,
            ],
            [
                'id'    => 'lainnya',
                'label' => 'Informasi / Kendala Lainnya',
                'desc'  => 'Permohonan pengecekan teknis jaringan atau informasi layanan umum',
                'badge' => null,
            ],
        ];
        
        if (!function_exists('e')) {
            function e(string $str): string
            {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-tantangan': {
        /**
         * Konfigurasi & Data Halaman Tantangan - Smart Water Kota Payakumbuh
         * Sesuai persis dengan src/components/challenge/ChallengeView.tsx & src/data/mockData.ts
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Tantangan Hemat Air - Smart Water Kota Payakumbuh',
            'description' => 'Program tantangan komunitas hemat air Kota Payakumbuh lengkap dengan daftar misi aksi, progres interaktif, poin XP, dan koleksi badge.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'tantangan';
        
        if (!isset($_SESSION['swc_challenge_progress']) || !is_array($_SESSION['swc_challenge_progress'])) {
            $_SESSION['swc_challenge_progress'] = [
                'points'            => 380,
                'activeChallenges'  => ['challenge-7days'],
                'completedMissions' => ['m-7d-1', 'm-7d-2'],
                'badges'            => ['badge-sago-guardian'],
            ];
        }
        
        $currentUser = $_SESSION['swc_user'] ?? [
            'name'        => 'Warga Payakumbuh',
            'meterNumber' => '04-0821-4902',
        ];
        $challengeProgress = $_SESSION['swc_challenge_progress'];
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $WATER_CHALLENGES = [
            [
                'id'                => 'challenge-7days',
                'title'             => 'Tantangan 7 Hari Detoks Air Payakumbuh',
                'tagline'           => 'Bangun 4 kebiasaan mikro hemat air selama satu pekan penuh!',
                'difficulty'        => 'Mudah',
                'durationDays'      => 7,
                'totalXp'           => 250,
                'badgeRewardId'     => 'badge-water-hero',
                'goal'              => 'Menerapkan 4 kebiasaan hemat air harian untuk memangkas potensi pemborosan hingga 15% di rumah tangga.',
                'successIndicator'  => 'Selesaikan ke-4 misi aksi dan centang checklist verifikasi.',
                'participantsCount' => 342,
                'missions'          => [
                    [
                        'id'               => 'm-7d-1',
                        'title'            => 'Misi Mandi Cerdas 5 Menit',
                        'description'      => 'Batasi durasi mandi maksimal 5 menit atau gunakan maksimal 8 gayung selama 2 hari berturut-turut.',
                        'xp'               => 50,
                        'estimatedMinutes' => 5,
                    ],
                    [
                        'id'               => 'm-7d-2',
                        'title'            => 'Misi Matikan Keran Wudhu & Sikat Gigi',
                        'description'      => 'Jangan biarkan kran terus mengucur deras saat menggosok gigi atau membasuh tangan.',
                        'xp'               => 50,
                        'estimatedMinutes' => 3,
                    ],
                    [
                        'id'               => 'm-7d-3',
                        'title'            => 'Misi Cuci Piring Metode Baskom',
                        'description'      => 'Gunakan baskom atau wadah penampung saat membilas cucian piring, bukan air mengalir bebas.',
                        'xp'               => 70,
                        'estimatedMinutes' => 10,
                    ],
                    [
                        'id'               => 'm-7d-4',
                        'title'            => 'Misi Siram Tanaman Air Beras / Reused Water',
                        'description'      => 'Tampung air cucian sayur atau bilasan beras untuk menyiram pot tanaman atau pekarangan rumah.',
                        'xp'               => 80,
                        'estimatedMinutes' => 8,
                    ],
                ],
            ],
            [
                'id'                => 'challenge-zero-leak',
                'title'             => 'Operasi Zero-Leak Payakumbuh',
                'tagline'           => 'Audit seluruh kran, toren, dan meteran air di rumah Anda.',
                'difficulty'        => 'Sedang',
                'durationDays'      => 3,
                'totalXp'           => 300,
                'badgeRewardId'     => 'badge-leak-detective',
                'goal'              => 'Memastikan tidak ada kebocoran tersembunyi yang mengalirkan air sia-sia selama 24 jam.',
                'successIndicator'  => 'Lakukan uji statis meteran dan uji kebocoran kloset/toren.',
                'participantsCount' => 215,
                'missions'          => [
                    [
                        'id'               => 'm-zl-1',
                        'title'            => 'Uji Statis Jarum Meteran Air',
                        'description'      => 'Tutup semua kran selama 15 menit, periksa jarum kecil flow meteran apakah berhenti total.',
                        'xp'               => 100,
                        'estimatedMinutes' => 15,
                    ],
                    [
                        'id'               => 'm-zl-2',
                        'title'            => 'Inspeksi Pelampung Radar Tangki Toren',
                        'description'      => 'Naik atau cek penampung air toren untuk memastikan pelampung otomatis bekerja tanpa luber.',
                        'xp'               => 100,
                        'estimatedMinutes' => 15,
                    ],
                    [
                        'id'               => 'm-zl-3',
                        'title'            => 'Ganti atau Kencangkan Seal Keran Menetes',
                        'description'      => 'Cek seluruh keran dapur, kamar mandi, dan halaman. Pastikan tidak ada tetesan air saat kran tertutup rapat.',
                        'xp'               => 100,
                        'estimatedMinutes' => 20,
                    ],
                ],
            ],
            [
                'id'                => 'challenge-literacy',
                'title'             => 'Master Literasi Kubikasi Air',
                'tagline'           => 'Kuasai perhitungan m³, kenali pola konsumsi harian keluarga.',
                'difficulty'        => 'Sedang',
                'durationDays'      => 5,
                'totalXp'           => 280,
                'badgeRewardId'     => 'badge-sago-guardian',
                'goal'              => 'Meningkatkan kesadaran angka kubikasi meteran dan efisiensi pemakaian air.',
                'successIndicator'  => 'Selesaikan modul kursus meteran dan catat estimasi pemakaian air keluarga.',
                'participantsCount' => 178,
                'missions'          => [
                    [
                        'id'               => 'm-lit-1',
                        'title'            => 'Selesaikan Modul Membaca Angka Meteran',
                        'description'      => 'Pelajari perbedaan digit hitam dan merah di Smart Water Academy.',
                        'xp'               => 80,
                        'estimatedMinutes' => 10,
                    ],
                    [
                        'id'               => 'm-lit-2',
                        'title'            => 'Analisis Jejak Air Rumah Tangga di Profil Akun',
                        'description'      => 'Periksa laporan pemakaian air pribadi di menu profil untuk mengetahui konsumsi kubikasi keluarga.',
                        'xp'               => 100,
                        'estimatedMinutes' => 5,
                    ],
                    [
                        'id'               => 'm-lit-3',
                        'title'            => 'Foto & Periksa Meteran Fisik di Rumah',
                        'description'      => 'Catat angka kubikasi meteran di awal pekan dan akhir pekan untuk melihat laju rata-rata konsumsi.',
                        'xp'               => 100,
                        'estimatedMinutes' => 10,
                    ],
                ],
            ],
        ];
        
        $BADGE_DEFINITIONS = [
            [
                'id'          => 'badge-water-hero',
                'name'        => 'Pejuang Hemat Batang Agam',
                'description' => 'Menyelesaikan Tantangan 7 Hari Detoks Air dengan komitmen penuh.',
                'icon'        => 'Droplets',
                'category'    => 'challenge',
                'requirement' => 'Selesaikan seluruh misi pada Tantangan 7 Hari Detoks Air',
            ],
            [
                'id'          => 'badge-leak-detective',
                'name'        => 'Detektif Kebocoran Bersertifikat',
                'description' => 'Berhasil melakukan audit Zero-Leak mandiri pada meteran dan pipa rumah.',
                'icon'        => 'SearchCheck',
                'category'    => 'challenge',
                'requirement' => 'Selesaikan Operasi Zero-Leak Payakumbuh',
            ],
            [
                'id'          => 'badge-sago-guardian',
                'name'        => 'Penjaga Mata Air Sikabu',
                'description' => 'Menuntaskan seluruh materi konservasi dan kuis dasar air bersih.',
                'icon'        => 'ShieldCheck',
                'category'    => 'academy',
                'requirement' => 'Selesaikan kursus Siklus Air & Konservasi Batang Agam dengan nilai kuis 100%',
            ],
            [
                'id'          => 'badge-water-scholar',
                'name'        => 'Cendekia Air Payakumbuh',
                'description' => 'Menyelesaikan minimal 3 modul pembelajaran di Smart Water Academy.',
                'icon'        => 'GraduationCap',
                'category'    => 'academy',
                'requirement' => 'Selesaikan minimal 3 modul kursus di Smart Water Academy',
            ],
            [
                'id'          => 'badge-eco-champion',
                'name'        => 'Payakumbuh Eco Champion',
                'description' => 'Mencapai total akumulasi 500+ XP dan menginspirasi komunitas lokal.',
                'icon'        => 'Award',
                'category'    => 'community',
                'requirement' => 'Kumpulkan 500+ Poin XP dari aktivitas belajar dan misi komunitas',
            ],
        ];
        
        if (!function_exists('e')) {
            function e(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-tentang': {
        /**
         * Konfigurasi & Data Halaman Tentang - Smart Water Kota Payakumbuh
         * Sesuai persis dengan src/components/about/AboutView.tsx
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Tentang Ekosistem - Smart Water Kota Payakumbuh',
            'description' => 'Arsitektur Ekosistem Digital Smart Water Kota Payakumbuh yang menghubungkan Aplikasi Mobile Customer, Portal Komunitas, dan Web Admin SCADA PDAM Tirta Sago.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'tentang';
        $currentUser = $_SESSION['swc_user'] ?? null;
        $userPoints = (int)($currentUser['points'] ?? ($_SESSION['swc_challenge_progress']['points'] ?? 380));
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $ECOSYSTEM_PILLARS = [
            [
                'id'          => 'komponen-a',
                'badge'       => 'Komponen A',
                'title'       => 'Aplikasi Mobile Customer',
                'description' => 'Fokus pada mobilitas di lapangan: pengambilan foto register meteran air fisik dengan AI-OCR, validasi geotagging, konfirmasi angka oleh pelanggan, dan riwayat tagihan pribadi.',
                'highlight'   => false,
                'icon'        => 'smartphone',
                'features'    => [
                    'AI-OCR pembacaan dial meter',
                    'Pengambilan foto dengan timestamp GPS',
                    'Konfirmasi pembacaan mandiri',
                ],
            ],
            [
                'id'          => 'komponen-b',
                'badge'       => 'Komponen B (Portal Ini)',
                'title'       => 'Smart Water',
                'description' => 'Pusat layanan publik, pengaduan kebocoran transparan, edukasi akademi interaktif, laporan kubikasi air, serta aksi tantangan hemat air berbasis komunitas warga Kota Payakumbuh.',
                'highlight'   => true,
                'icon'        => 'globe',
                'features'    => [
                    'Smart Water Service Center & Pelacakan Tiket',
                    'Smart Water Academy & Kuis Edukasi',
                    'Smart Water Challenge, Poin & Badge',
                ],
            ],
            [
                'id'          => 'komponen-c',
                'badge'       => 'Komponen C',
                'title'       => 'Web Admin & Operator PDAM',
                'description' => 'Digunakan oleh jajaran teknis Perumda Tirta Sago untuk verifikasi pembacaan meter, pengelolaan rekening tagihan, pemantauan debit intake & reservoir, dan dispatching regu reaksi cepat.',
                'highlight'   => false,
                'icon'        => 'server',
                'features'    => [
                    'Verifikasi data OCR & billing',
                    'Telemetri level reservoir Sikabu',
                    'Manajemen tiket regu lapangan',
                ],
            ],
        ];
        
        if (!function_exists('e')) {
            function e(string $value): string
            {
                return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
            }
        }
        
        break;
    }
    case 'php-user': {
        /**
         * Konfigurasi & Data Halaman User / Profil Pelanggan - Smart Water Kota Payakumbuh
         * Sesuai 100% dengan src/components/profile/ProfileView.tsx, App.tsx, & mockData.ts
         */
        
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        
        $APP_META = [
            'title'       => 'Profil Pelanggan - Smart Water Kota Payakumbuh',
            'description' => 'Dashboard Mandiri Pelanggan Perumda Air Minum Tirta Sago: laporan kubikasi air, E-Kuitansi, simulator tagihan, riwayat pengaduan, progres belajar, dan lencana komunitas.',
            'city'        => 'Kota Payakumbuh, Sumatera Barat',
            'call_center' => '(0752) 92341',
            'email'       => 'layanan@tirtasago-payakumbuh.go.id',
            'year'        => '2026',
        ];
        
        $currentTab = 'profil';
        
        $NAV_ITEMS = [
            ['id' => 'beranda',   'label' => 'Beranda'],
            ['id' => 'layanan',   'label' => 'Pusat Layanan'],
            ['id' => 'akademi',   'label' => 'Akademi Air'],
            ['id' => 'tantangan', 'label' => 'Tantangan'],
            ['id' => 'tentang',   'label' => 'Tentang'],
        ];
        
        $DISTRICTS_PAYAKUMBUH = [
            'Payakumbuh Barat',
            'Payakumbuh Utara',
            'Payakumbuh Timur',
            'Payakumbuh Selatan',
            'Lampasi Tigo Nagori (Latina)',
        ];
        
        $COMPLAINT_CATEGORIES = [
            ['id' => 'pipa_bocor',    'label' => 'Kebocoran Pipa / Semburan Air'],
            ['id' => 'air_mati',      'label' => 'Air Tidak Mengalir / Mati Total'],
            ['id' => 'kualitas_keruh','label' => 'Kualitas Air Keruh / Berbau'],
            ['id' => 'meteran_rusak', 'label' => 'Meteran Rusak / Jarum Macet'],
            ['id' => 'tagihan',       'label' => 'Klarifikasi Rekening Tagihan'],
            ['id' => 'lainnya',       'label' => 'Informasi / Kendala Lainnya'],
        ];
        
        $ACADEMY_COURSES_SUMMARY = [
            [
                'id'         => 'course-1',
                'title'      => 'Siklus Air Payakumbuh & Konservasi Batang Agam',
                'difficulty' => 'Pemula',
                'xpReward'   => 120,
            ],
            [
                'id'         => 'course-2',
                'title'      => 'Detektif Kebocoran Rumah Tangga (Zero-Leak)',
                'difficulty' => 'Menengah',
                'xpReward'   => 150,
            ],
            [
                'id'         => 'course-3',
                'title'      => 'Gaya Hidup Air Berkelanjutan & Kebiasaan Gen-Z',
                'difficulty' => 'Pemula',
                'xpReward'   => 110,
            ],
            [
                'id'         => 'course-4',
                'title'      => 'Membaca Angka Meteran & Literasi Tagihan Tirta Sago',
                'difficulty' => 'Menengah',
                'xpReward'   => 140,
            ],
        ];
        
        $BADGE_DEFINITIONS = [
            [
                'id'          => 'badge-water-hero',
                'name'        => 'Pejuang Hemat Batang Agam',
                'description' => 'Menyelesaikan Tantangan 7 Hari Detoks Air dengan komitmen penuh.',
            ],
            [
                'id'          => 'badge-leak-detective',
                'name'        => 'Detektif Kebocoran Bersertifikat',
                'description' => 'Berhasil melakukan audit Zero-Leak mandiri pada meteran dan pipa rumah.',
            ],
            [
                'id'          => 'badge-sago-guardian',
                'name'        => 'Penjaga Mata Air Sikabu',
                'description' => 'Menuntaskan seluruh materi konservasi dan kuis dasar air bersih.',
            ],
            [
                'id'          => 'badge-water-scholar',
                'name'        => 'Cendekia Air Payakumbuh',
                'description' => 'Menyelesaikan minimal 3 modul pembelajaran di Smart Water Academy.',
            ],
            [
                'id'          => 'badge-eco-champion',
                'name'        => 'Payakumbuh Eco Champion',
                'description' => 'Mencapai total akumulasi 500+ XP dan menginspirasi komunitas lokal.',
            ],
        ];
        
        $DEMO_USERS = [
            'user-01' => [
                'id'               => 'user-01',
                'name'             => 'Warga Google Payakumbuh',
                'email'            => 'warga.google@gmail.com',
                'phone'            => '0812-7389-4902',
                'role'             => 'Pelanggan Terverifikasi 2A - Rumah Tangga Mandiri (Standar PDAM)',
                'district'         => 'Payakumbuh Barat',
                'address'          => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo, Kec. Payakumbuh Barat, Kota Payakumbuh',
                'meterNumber'      => '04-0821-4902',
                'tariffCategory'   => '2A - Rumah Tangga Mandiri (Standar PDAM)',
                'points'           => 380,
                'completedLessons' => ['lesson-1-1', 'lesson-1-2'],
                'completedQuizzes' => ['course-1' => 100, 'course-2' => 100],
                'badges'           => ['badge-sago-guardian'],
                'streakDays'       => 4,
                'joinedDate'       => 'Agustus 2026',
                'privacySettings'  => [
                    'showInLeaderboard'      => true,
                    'shareConservationStats' => true,
                    'twoFactorAuth'          => true,
                    'waNotifications'        => true,
                    'emailDigest'            => true,
                ],
                'personalWaterReport' => [
                    'currentMonthM3'         => 9.4,
                    'previousMonthM3'        => 11.2,
                    'currentBillEstimateIdr' => 47000,
                    'lastBillStatus'         => 'Lunas',
                    'lastPaymentDate'        => '10 Oktober 2026',
                    'dueDate'                => '20 Oktober 2026',
                    'dailyAverageLiters'     => 285,
                    'householdMembers'       => 3,
                    'standMeterCurrent'      => 1842,
                    'standMeterLastMonth'    => 1833,
                    'billingHistory'         => [
                        ['period' => 'Oktober 2026',   'm3' => 9.4,  'amountIdr' => 47000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-10-8942', 'paidDate' => '10 Oktober 2026'],
                        ['period' => 'September 2026', 'm3' => 11.2, 'amountIdr' => 56000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-09-8942', 'paidDate' => '10 September 2026'],
                        ['period' => 'Agustus 2026',   'm3' => 12.0, 'amountIdr' => 60000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-08-8942', 'paidDate' => '10 Agustus 2026'],
                        ['period' => 'Juli 2026',      'm3' => 12.2, 'amountIdr' => 61000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-07-8942', 'paidDate' => '10 Juli 2026'],
                        ['period' => 'Juni 2026',      'm3' => 13.5, 'amountIdr' => 67500, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-06-8942', 'paidDate' => '09 Juni 2026'],
                        ['period' => 'Mei 2026',       'm3' => 14.1, 'amountIdr' => 70500, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-05-8942', 'paidDate' => '10 Mei 2026'],
                    ],
                ],
            ],
            'user-02' => [
                'id'               => 'user-02',
                'name'             => 'Ibu Siti Nurbaya',
                'email'            => 'siti.nurbaya@payakumbuh.community',
                'phone'            => '0852-6389-1122',
                'role'             => 'Keluarga Mandiri Payakumbuh Timur (4 Jiwa)',
                'district'         => 'Payakumbuh Timur',
                'address'          => 'Kompleks Griya Tiakar Indah No. 12, Kec. Payakumbuh Timur, Kota Payakumbuh',
                'meterNumber'      => '02-0199-3118',
                'tariffCategory'   => '2B - Rumah Tangga Menengah (PDAM)',
                'points'           => 490,
                'completedLessons' => ['lesson-1-1', 'lesson-1-2', 'lesson-3-1', 'lesson-3-2'],
                'completedQuizzes' => ['course-1' => 100, 'course-3' => 100],
                'badges'           => ['badge-water-hero', 'badge-sago-guardian'],
                'streakDays'       => 6,
                'joinedDate'       => 'Juni 2026',
                'privacySettings'  => [
                    'showInLeaderboard'      => true,
                    'shareConservationStats' => false,
                    'twoFactorAuth'          => true,
                    'waNotifications'        => true,
                    'emailDigest'            => true,
                ],
                'personalWaterReport' => [
                    'currentMonthM3'         => 14.8,
                    'previousMonthM3'        => 16.5,
                    'currentBillEstimateIdr' => 79760,
                    'lastBillStatus'         => 'Lunas',
                    'lastPaymentDate'        => '8 Oktober 2026',
                    'dueDate'                => '20 Oktober 2026',
                    'dailyAverageLiters'     => 490,
                    'householdMembers'       => 4,
                    'standMeterCurrent'      => 2410,
                    'standMeterLastMonth'    => 2395,
                    'billingHistory'         => [
                        ['period' => 'Oktober 2026',   'm3' => 14.8, 'amountIdr' => 74000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-10-3118', 'paidDate' => '08 Oktober 2026'],
                        ['period' => 'September 2026', 'm3' => 16.5, 'amountIdr' => 82500, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-09-3118', 'paidDate' => '08 September 2026'],
                        ['period' => 'Agustus 2026',   'm3' => 17.2, 'amountIdr' => 86000, 'status' => 'Lunas', 'invoiceNo' => 'INV-PYK-2026-08-3118', 'paidDate' => '09 Agustus 2026'],
                    ],
                ],
            ],
        ];
        
        $INITIAL_USER_COMPLAINTS = [
            [
                'id'          => 'TK-PYK-2026-8942',
                'userId'      => 'user-01',
                'userName'    => 'Warga Google Payakumbuh',
                'userPhone'   => '0812-7389-4902',
                'category'    => 'pipa_bocor',
                'title'       => 'Rembesan Air Bersih di Bahu Jalan Dekat Pasar Ibuh',
                'description' => 'Terlihat genangan air jernih menyembur dari sambungan pipa bawah aspal di depan pertokoan blok B. Aliran cukup deras sejak pagi.',
                'district'    => 'Payakumbuh Barat',
                'address'     => 'Jl. Ahmad Yani No. 45, Dekat Simpang Pasar Ibuh',
                'status'      => 'SELESAI',
                'createdAt'   => '2026-10-17 07:45 WIB',
            ],
            [
                'id'          => 'TK-PYK-2026-7241',
                'userId'      => 'user-01',
                'userName'    => 'Warga Google Payakumbuh',
                'userPhone'   => '0812-7389-4902',
                'category'    => 'meteran_rusak',
                'title'       => 'Pemeriksaan Jarum Dial Meteran Air Lambat',
                'description' => 'Pengecekan fisik penunjuk angka dial jarum meteran air karena terasa agak macet saat kran bak mandi dibuka.',
                'district'    => 'Payakumbuh Barat',
                'address'     => 'Jl. Tan Malaka No. 42, Kel. Balai Nan Duo',
                'status'      => 'DIPROSES',
                'createdAt'   => '2026-09-28 10:15 WIB',
            ],
            [
                'id'          => 'TK-PYK-2026-8109',
                'userId'      => 'user-02',
                'userName'    => 'Ibu Siti Nurbaya',
                'userPhone'   => '0852-6389-1122',
                'category'    => 'kualitas_keruh',
                'title'       => 'Air Keran Mengandung Endapan Cokelat Halus',
                'description' => 'Sejak tadi malam air di bak penampungan agak kekuningan setelah hujan lebat di hulu.',
                'district'    => 'Payakumbuh Timur',
                'address'     => 'Kompleks Griya Tiakar Indah No. 12',
                'status'      => 'SELESAI',
                'createdAt'   => '2026-10-15 16:20 WIB',
            ],
        ];
        
        // Query params opsional untuk uji cepat persona atau tamu
        if (isset($_GET['action']) && $_GET['action'] === 'logout') {
            $_SESSION['swc_user'] = null;
            header('Location: ' . $SWC_PAGE_URL);
            exit;
        }
        if (isset($_GET['action']) && $_GET['action'] === 'login_demo') {
            $pid = $_GET['persona'] ?? 'user-01';
            $_SESSION['swc_user'] = $DEMO_USERS[$pid] ?? $DEMO_USERS['user-01'];
            header('Location: ' . $SWC_PAGE_URL);
            exit;
        }
        
        if (!array_key_exists('swc_user', $_SESSION)) {
            $_SESSION['swc_user'] = $DEMO_USERS['user-01'];
        }
        if (!isset($_SESSION['swc_complaints']) || !is_array($_SESSION['swc_complaints'])) {
            $_SESSION['swc_complaints'] = $INITIAL_USER_COMPLAINTS;
        }
        
        $currentUser = $_SESSION['swc_user'];
        $allComplaints = $_SESSION['swc_complaints'];
        
        function calculateGolongan2ABill(float $volume): array
        {
            $v = max(0.0, $volume);
            $b1M3 = min($v, 10.0);
            $b1Cost = (int) round($b1M3 * 5000);
        
            $b2M3 = max(0.0, min($v - 10.0, 10.0));
            $b2Cost = (int) round($b2M3 * 6200);
        
            $b3M3 = max(0.0, $v - 20.0);
            $b3Cost = (int) round($b3M3 * 7800);
        
            return [
                'b1M3'     => round($b1M3, 1),
                'b1Cost'   => $b1Cost,
                'b2M3'     => round($b2M3, 1),
                'b2Cost'   => $b2Cost,
                'b3M3'     => round($b3M3, 1),
                'b3Cost'   => $b3Cost,
                'adminFee' => 0,
                'total'    => $b1Cost + $b2Cost + $b3Cost,
            ];
        }
        
        if (!function_exists('e')) {
            function e(string $str): string
            {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
        }
        
        function formatRupiah(int $val): string
        {
            return 'Rp ' . number_format($val, 0, ',', '.');
        }
        
        break;
    }
}
