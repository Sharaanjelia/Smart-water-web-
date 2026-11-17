# Halaman Chat AI TirtaBot

View Laravel untuk **TirtaBot AI** Smart Water Kota Payakumbuh (`AiChatbot.tsx`), lengkap dengan antarmuka chat interaktif dan endpoint backend PHP:
View halaman ini berada di `resources/views/pages/chat-ai.blade.php`.

- **Ruang Kerja Chat AI Tersemat + Floating Widget Melayang (`includes/chatbot.php`)**: Keduanya tersinkronisasi secara real-time melalui riwayat `localStorage`.
- **5 Quick Prompts Interaktif**: Cek kebocoran tersembunyi, tarif kubikasi Tirta Sago, panduan AI-OCR meteran, tips hemat air, dan cara lapor pengaduan.
- **Format Markdown Lengkap**: Mendukung judul `##`/`###`, bullet points, numbered list, inline code, **bold**, badge model Gemini 2.5, indikator mengetik *"TirtaBot sedang berpikir..."*, tombol minimize/maximize, reset riwayat chat, dan badge pesan belum dibaca.
- **Backend PHP (`api/chat.php`)**: Terintegrasi langsung dengan Google **Gemini 2.5 Flash** API (via environment variable `GEMINI_API_KEY`) dan dilengkapi respons fallback cerdas berbahasa Indonesia apabila offline.

Konfigurasi halaman berada di `app/LegacyConfig.php`. Navbar/footer bersama berada di `resources/views/shared/includes/`, aset di `public/assets/`, dan logika chatbot di `app/LegacyApi/chat/`, dengan wrapper API di `public/api/`.

## Cara Menjalankan
Jalankan `php artisan serve` dari root proyek, lalu buka `http://localhost:8000/chat-ai`. Set `GEMINI_API_KEY` jika ingin mengaktifkan koneksi Gemini.
