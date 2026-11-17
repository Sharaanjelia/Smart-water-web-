<?php
/**
 * Backend PHP Endpoint: api/chat.php
 * Endpoint chatbot TirtaBot AI untuk versi PHP Pusat Layanan.
 */
header('Content-Type: application/json; charset=utf-8');

$requestMethod = $_SERVER['REQUEST_METHOD'] ?? (function_exists('request') ? request()->method() : 'GET');
if ($requestMethod !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
if ((!$rawInput || $rawInput === '') && function_exists('request')) {
    $rawInput = request()->getContent();
}
$data = json_decode($rawInput, true);

$message = isset($data['message']) ? trim((string)$data['message']) : '';
$history = isset($data['history']) && is_array($data['history']) ? $data['history'] : [];

if ($message === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Pesan (message) wajib diisi.']);
    exit;
}

function generateFallbackResponse(string $userPrompt): string
{
    $p = mb_strtolower($userPrompt, 'UTF-8');

    if (str_contains($p, 'bocor') || str_contains($p, 'pipa') || str_contains($p, 'pecah') || str_contains($p, 'lapor') || str_contains($p, 'pengaduan')) {
        return "Halo Sanak! Jika Anda menemukan pipa bocor, air mati, atau meteran bermasalah:\n\n"
            . "1. **Di Rumah:** Lakukan *Uji Statis*—tutup semua keran, periksa apakah jarum merah meteran tetap berputar. Jika berputar, periksa seal kloset atau sambungan toren.\n"
            . "2. **Lapor Resmi di Halaman Ini:** Klik tombol **\"Buat Pengaduan Gangguan Baru\"**, pilih kategori & kecamatan Anda di Payakumbuh, lalu unggah foto bukti. Anda akan langsung memperoleh Nomor Tiket (misal `TK-PYK-2026-XXXX`) untuk dipantau oleh Tim Reaksi Cepat (TRC) Tirta Sago!";
    }

    if (str_contains($p, 'tarif') || str_contains($p, 'tagihan') || str_contains($p, 'bayar') || str_contains($p, 'biaya')) {
        return "Tarif air Perumda Air Minum Tirta Sago Kota Payakumbuh menggunakan sistem progresif hemat air:\n\n"
            . "- **Blok I (0–10 m³):** Rp 2.500 / m³ (Kebutuhan pokok bersubsidi)\n"
            . "- **Blok II (11–20 m³):** Rp 3.200 / m³\n"
            . "- **Blok III (21–30 m³):** Rp 4.500 / m³\n"
            . "- **Blok IV (>30 m³):** Rp 6.000 / m³\n\n"
            . "💡 **Tips:** Jika menemukan lonjakan tagihan tidak wajar, gunakan kategori **Klarifikasi Rekening Tagihan** pada formulir pengaduan Pusat Layanan.";
    }

    if (str_contains($p, 'meter') || str_contains($p, 'ocr') || str_contains($p, 'foto') || str_contains($p, 'catat')) {
        return "Untuk pencatatan angka meteran air resmi bulanan:\n"
            . "- Dilakukan melalui **Aplikasi Mobile Smart Water** dengan fitur **AI-OCR**.\n"
            . "- Cukup arahkan kamera smartphone ke dial meteran air, AI akan otomatis membaca angka hitam (kubikasi).\n"
            . "- Bila kaca meteran buram atau jarum dial macet, ajukan pengaduan kategori **Meteran Rusak / Jarum Macet** di Pusat Layanan ini.";
    }

    if (str_contains($p, 'tips') || str_contains($p, 'hemat') || str_contains($p, 'cara')) {
        return "Berikut 4 langkah cepat hemat air di rumah:\n"
            . "1. **Pasang Aerator Keran:** Memecah aliran air dengan gelembung udara, menghemat hingga 50% tanpa mengurangi kenyamanan cuci tangan.\n"
            . "2. **Gunakan Baskom Saat Cuci Piring:** Hemat hingga 30 liter dibandingkan membiarkan air keran mengalir terus menerus.\n"
            . "3. **Mandi Efisien:** Gunakan shower (maks 5-7 menit) alih-alih berendam atau gayung berulang kali.\n"
            . "4. **Manfaatkan Air Bekas Cucian Beras:** Gunakan untuk menyiram tanaman halaman rumah.";
    }

    return "Halo Sanak Warga Payakumbuh! Saya **TirtaBot**, asisten cerdas Komunitas Smart Water.\n\n"
        . "Ada yang bisa saya bantu di Pusat Layanan seputar:\n"
        . "- 💧 **Cara membuat pengaduan pipa bocor & pelacakan tiket**\n"
        . "- 📊 **Perhitungan tarif air & simulasi tagihan Tirta Sago**\n"
        . "- 📱 **Cara kerja AI-OCR meteran di aplikasi mobile**\n"
        . "- 📢 **Info pemeliharaan jaringan per kecamatan di Payakumbuh**\n\n"
        . "Silakan tanyakan pertanyaan Anda!";
}

$apiKey = getenv('GEMINI_API_KEY');
if (!$apiKey) {
    $laravelEnv = dirname(__DIR__, 2) . '/.env';
    if (is_file($laravelEnv)) {
        $lines = file($laravelEnv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines ?: [] as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            if (str_starts_with(trim($line), 'GEMINI_API_KEY=')) {
                $apiKey = trim(substr(trim($line), strlen('GEMINI_API_KEY=')), " \t\n\r\0\x0B\"'");
                break;
            }
        }
    }
}

if ($apiKey && function_exists('curl_init')) {
    $systemInstruction = "Anda adalah \"TirtaBot\", asisten AI resmi yang ramah, cerdas, solutif, dan berwawasan luas dari platform Smart Water Kota Payakumbuh (didukung ekosistem Perumda Air Minum Tirta Sago). Gunakan bahasa Indonesia santun dan format markdown rapi.";

    $contents = [];
    foreach (array_slice($history, -6) as $item) {
        if (!empty($item['content']) && in_array($item['role'] ?? '', ['user', 'model'], true)) {
            $contents[] = [
                'role'  => $item['role'],
                'parts' => [['text' => (string)$item['content']]],
            ];
        }
    }
    $contents[] = [
        'role'  => 'user',
        'parts' => [['text' => $message]],
    ];

    $payload = [
        'system_instruction' => [
            'parts' => [['text' => $systemInstruction]],
        ],
        'contents' => $contents,
        'generationConfig' => [
            'temperature'     => 0.7,
            'maxOutputTokens' => 800,
        ],
    ];

    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . urlencode($apiKey);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response !== false && $httpCode >= 200 && $httpCode < 300) {
        $json = json_decode($response, true);
        $replyText = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;
        if ($replyText) {
            echo json_encode(['reply' => $replyText, 'source' => 'gemini']);
            exit;
        }
    }
}

echo json_encode([
    'reply'  => generateFallbackResponse($message),
    'source' => 'fallback',
]);
