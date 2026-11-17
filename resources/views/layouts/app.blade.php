<?php
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    @session_start();
}

$currentUser = session('swc_user') ?? $_SESSION['swc_user'] ?? ($currentUser ?? null);
$activeRole = session('swc_role') ?? $_SESSION['swc_role'] ?? ($currentUser['role'] ?? ($currentUser['isAdmin'] ?? false ? 'admin' : 'customer'));
$currentTab = $currentTab ?? 'meter-reading';
$legacySharedPath = resource_path('views/shared');

require_once $legacySharedPath . '/brand_logo.php';

// Inisialisasi metadata dan konfigurasi jika belum ada
$APP_META = $APP_META ?? [
    'title'       => 'Smart Water Kota Payakumbuh',
    'description' => 'Portal Layanan, Edukasi, dan Aksi Hemat Air Kota Payakumbuh',
    'city'        => 'Kota Payakumbuh, Sumatera Barat',
    'call_center' => '0812-7389-4902',
    'email'       => 'layanan@tirtasago-payakumbuh.go.id',
    'year'        => 2026,
];

$NAV_ITEMS = [
    ['id' => 'beranda',       'label' => 'Beranda'],
    ['id' => 'meter-reading', 'label' => 'Lapor Meter'],
    ['id' => 'layanan',       'label' => 'Pusat Layanan'],
    ['id' => 'akademi',       'label' => 'Akademi Air'],
    ['id' => 'tantangan',     'label' => 'Tantangan'],
    ['id' => 'tentang',       'label' => 'Tentang'],
];

$SHARED_HEADER_OPTIONS = [
    'activeNav' => true,
    'user'      => $currentUser,
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Smart Water') - Perumda Tirta Sago Payakumbuh</title>

  <!-- Google Fonts: Plus Jakarta Sans -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />

  <!-- Tailwind CSS -->
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

  <!-- Shared CSS -->
  <link rel="stylesheet" href="{{ asset('assets/css/shared.css') }}" />

  <style>
    /* Styling Komponen Form & Card */
    .card {
      background-color: #ffffff;
      border-radius: 1.5rem;
      border: 1px solid #e2e8f0;
      padding: 1.75rem sm:padding: 2.25rem;
      box-shadow: 0 4px 20px -2px rgba(12, 30, 60, 0.05);
    }
    .card-heading {
      font-size: 1.5rem;
      font-weight: 800;
      color: #0f172a;
      letter-spacing: -0.025em;
    }
    .section-description {
      font-size: 0.875rem;
      color: #64748b;
      margin-top: 0.35rem;
      margin-bottom: 1.5rem;
      line-height: 1.6;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 1.25rem;
    }
    .form-field {
      display: flex;
      flex-direction: column;
      gap: 0.375rem;
    }
    .form-field label {
      font-size: 0.75rem;
      font-weight: 700;
      color: #334155;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border-radius: 0.75rem;
      border: 1px solid #cbd5e1;
      background-color: #f8fafc;
      color: #0f172a;
      font-size: 0.875rem;
      font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
      transition: all 0.2s ease;
    }
    .form-control:focus {
      outline: none;
      border-color: #0284c7;
      background-color: #ffffff;
      box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
    }
    .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.75rem 1.5rem;
      border-radius: 0.75rem;
      font-size: 0.8125rem;
      font-weight: 700;
      transition: all 0.15s ease;
      cursor: pointer;
      border: none;
    }
    .btn-primary {
      background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
    }
    .btn-primary:hover {
      background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
      transform: translateY(-1px);
      box-shadow: 0 6px 16px rgba(2, 132, 199, 0.35);
    }
  </style>
  @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans min-h-screen flex flex-col">

  <!-- Header Navbar Global -->
  <?php require $legacySharedPath . '/header.php'; ?>

  <!-- Main Content Container -->
  <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-6">

    <!-- Flash Notification Success -->
    @if(session('success'))
      <div class="p-4 sm:p-5 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-start gap-3 shadow-xs animate-fade-zoom">
        <svg class="w-5 h-5 text-emerald-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
        </svg>
        <div class="text-xs sm:text-sm font-semibold">
          {{ session('success') }}
        </div>
      </div>
    @endif

    <!-- Flash Notification Errors -->
    @if(isset($errors) && $errors->any())
      <div class="p-4 sm:p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs animate-fade-zoom">
        <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>
        <div class="text-xs sm:text-sm space-y-1">
          <strong class="font-bold block">Mohon periksa formulir Anda:</strong>
          <ul class="list-disc pl-5 space-y-0.5 font-medium">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      </div>
    @endif

    @yield('content')
  </main>

  <!-- Footer Global -->
  <?php require $legacySharedPath . '/footer.php'; ?>

  <!-- TirtaBot AI Chatbot Widget -->
  <?php require $legacySharedPath . '/chatbot.php'; ?>

  <!-- Smart Water Client-Side Reactive Store (Firebase-Ready) -->
  <script src="{{ asset('assets/js/swc-store.js') }}"></script>

  @stack('scripts')
</body>
</html>
