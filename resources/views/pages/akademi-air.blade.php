<?php
/**
 * Halaman Utama Akademi Air - Smart Water Kota Payakumbuh (Versi PHP Lengkap)
 */
require_once $legacySharedPath . '/brand_logo.php';
?>
<!doctype html>
<html lang="id">
  <head>
    <base href="{{ $legacyBaseHref }}" />
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= e($APP_META['title']) ?></title>
    <meta name="description" content="<?= e($APP_META['description']) ?>" />
    <meta property="og:title" content="<?= e($APP_META['title']) ?>" />
    <meta property="og:description" content="<?= e($APP_META['description']) ?>" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="../../assets/css/shared.css" />
    <link rel="stylesheet" href="../../assets/css/pages/akademi-air.css" />
  </head>
  <body class="bg-slate-50 text-slate-800 antialiased font-sans selection:bg-cyan-200 selection:text-cyan-900">
    <div class="min-h-screen flex flex-col bg-slate-50 text-slate-800 antialiased selection:bg-cyan-200 selection:text-cyan-900">
      
      <!-- Top Header -->
      <?php
$SHARED_HEADER_OPTIONS = [
    'handler' => 'handleAcademyNav',
    'logoHref' => url('/beranda'),
    'activeNav' => true,
    'user' => $_SESSION['swc_user'] ?? (function_exists('session') ? session('swc_user') : null),
];
require $legacySharedPath . '/header.php';
?>

      <main class="flex-1">
        <!-- MAIN ACADEMY CATALOG VIEW -->
        <div id="academy-main-view" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">

          <!-- Top Hero / Banner Section -->
          <div class="group relative overflow-hidden rounded-3xl text-white p-6 sm:p-10 shadow-xl border border-sky-900/30">
            <!-- Background Image: Danau Biru Jernih Alami (Terang, Jernih, dan Jelas) -->
            <div class="absolute inset-0 z-0">
              <img
                src="<?= asset('assets/images/hero-lake-bright.jpg') ?>"
                alt="Smart Water Academy"
                class="w-full h-full object-cover object-[center_45%] group-hover:scale-105 transition-transform duration-700 ease-out"
              />
              <!-- Scrim Halus: Gelap hanya di kiri untuk keterbacaan teks, sisi kanan transparan penuh agar foto terlihat sangat jernih -->
              <div class="absolute inset-0 bg-gradient-to-r from-slate-950/85 via-slate-950/40 to-transparent"></div>
              <div class="absolute inset-0 bg-sky-950/10 pointer-events-none"></div>
            </div>

            <div class="relative z-10 max-w-2xl space-y-3">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-950/50 text-cyan-300 text-xs font-semibold border border-cyan-400/40 backdrop-blur-md shadow-xs">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M21.42 10.922a1 1 0 0 0-.019-1.838L12.83 5.18a2 2 0 0 0-1.66 0L2.6 9.08a1 1 0 0 0 0 1.832l8.57 3.908a2 2 0 0 0 1.66 0z"/>
                  <path d="M22 10v6"/><path d="M6 12.5V16a6 3 0 0 0 12 0v-3.5"/>
                </svg>
                <span>Pusat Edukasi &amp; Literasi Konservasi</span>
              </div>
              <h1 class="text-2xl sm:text-4xl font-extrabold tracking-tight text-white leading-tight drop-shadow-md">
                Smart Water Academy Payakumbuh
              </h1>
              <p class="text-xs sm:text-sm text-slate-100 font-medium leading-relaxed drop-shadow-sm max-w-xl">
                Tingkatkan pengetahuan tentang siklus air Gunung Sago, deteksi kebocoran mandiri di rumah, dan kuasai kebiasaan hemat air dengan modul interaktif &amp; kuis berhadiah XP.
              </p>
            </div>
          </div>

          <!-- Main Course Catalog -->
          <div class="space-y-6">
            <!-- Search & Filter Bar -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
              <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                <?php foreach ($ACADEMY_CATEGORIES as $idx => $cat): ?>
                  <button
                    type="button"
                    data-cat-id="<?= e($cat['id']) ?>"
                    onclick="selectCategoryFilter('<?= e($cat['id']) ?>')"
                    class="academy-cat-btn px-3 py-1.5 rounded-lg whitespace-nowrap shrink-0 transition-colors font-medium cursor-pointer <?= $idx === 0
                      ? 'bg-slate-900 text-white shadow-xs'
                      : 'bg-white text-slate-600 hover:text-slate-900 border border-slate-200' ?>"
                  >
                    <?= e($cat['label']) ?>
                  </button>
                <?php endforeach; ?>
              </div>

              <div class="relative w-full md:w-72">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input
                  type="text"
                  placeholder="Cari materi belajar..."
                  oninput="filterAcademySearch(this.value)"
                  class="w-full pl-9 pr-4 py-2 text-xs bg-white rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 text-slate-900"
                />
              </div>
            </div>

            <!-- Courses Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <?php foreach ($ACADEMY_COURSES as $course): ?>
                <?php
                  $courseId     = $course['id'];
                  $quizScore    = $academyProgress['completedQuizzes'][$courseId] ?? null;
                  $isPassed     = $quizScore !== null && $quizScore >= 70;
                  $lessonsTotal = count($course['lessons']);
                  $doneCount    = 0;
                  foreach ($course['lessons'] as $ls) {
                      if (in_array($ls['id'], $academyProgress['completedLessons'] ?? [], true)) {
                          $doneCount++;
                      }
                  }
                  $pct = $lessonsTotal > 0 ? round(($doneCount / $lessonsTotal) * 100) : 0;
                ?>
                <div
                  data-course-card="<?= e($courseId) ?>"
                  data-course-cat="<?= e($course['category']) ?>"
                  data-course-title="<?= e($course['title']) ?>"
                  data-course-summary="<?= e($course['summary']) ?>"
                  class="bg-white rounded-2xl border border-slate-200 hover:border-cyan-300 shadow-xs hover:shadow-md transition-all duration-200 p-6 flex flex-col justify-between space-y-5"
                >
                  <div class="space-y-3">
                    <div class="flex items-center justify-between text-xs text-slate-500">
                      <div class="flex items-center gap-2">
                        <span class="font-semibold text-cyan-800 uppercase tracking-wide text-[11px]">
                          <?= e(str_replace('_', ' ', $course['category'])) ?>
                        </span>
                        <span>&middot;</span>
                        <span><?= e($course['difficulty']) ?></span>
                        <span>&middot;</span>
                        <span class="flex items-center gap-1">
                          <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
                          </svg>
                          <?= e($course['readingTime']) ?>
                        </span>
                      </div>
                      <span class="font-mono font-bold text-cyan-900 bg-cyan-50 px-2 py-0.5 rounded text-[11px]">
                        +<?= (int)$course['xpReward'] ?> XP
                      </span>
                    </div>

                    <h3 class="text-lg font-bold text-slate-900 leading-snug">
                      <?= e($course['title']) ?>
                    </h3>

                    <p class="text-xs text-slate-600 leading-relaxed">
                      <?= e($course['summary']) ?>
                    </p>

                    <div class="pt-2 space-y-1.5">
                      <div class="flex justify-between text-[11px] text-slate-500">
                        <span class="course-read-count">
                          <?= $doneCount ?> dari <?= $lessonsTotal ?> materi dibaca
                        </span>
                        <?php if ($quizScore !== null): ?>
                          <span class="course-quiz-status font-semibold text-emerald-700 font-mono">
                            Skor Kuis: <?= (int)$quizScore ?>%
                          </span>
                        <?php else: ?>
                          <span class="course-quiz-status text-slate-400">
                            Kuis Belum Diikuti
                          </span>
                        <?php endif; ?>
                      </div>
                      <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                        <div
                          class="course-progress-bar bg-cyan-600 h-full rounded-full transition-all"
                          style="width: <?= $pct ?>%;"
                        ></div>
                      </div>
                    </div>
                  </div>

                  <div class="pt-4 border-t border-slate-100 flex items-center justify-between gap-3">
                    <button
                      type="button"
                      onclick="openLessonReaderModal('<?= e($courseId) ?>')"
                      class="px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-800 bg-slate-100 hover:bg-slate-200 transition-colors flex items-center gap-1.5 cursor-pointer"
                    >
                      <svg class="w-3.5 h-3.5 text-cyan-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>
                      </svg>
                      <span>Baca Materi (<?= $lessonsTotal ?>)</span>
                    </button>

                    <button
                      type="button"
                      onclick="openQuizPageView('<?= e($courseId) ?>')"
                      class="course-quiz-btn px-3.5 py-2 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer <?= $isPassed
                        ? 'bg-emerald-50 text-emerald-800 border border-emerald-200 hover:bg-emerald-100'
                        : 'bg-cyan-600 text-white hover:bg-cyan-700 shadow-xs' ?>"
                    >
                      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
                      </svg>
                      <span><?= $isPassed ? 'Lulus (' . (int)$quizScore . '%) · Ulangi' : 'Ikuti Kuis' ?></span>
                    </button>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>

            <div id="academy-empty-state" class="hidden bg-white rounded-2xl border border-slate-200 p-8 text-center text-xs text-slate-500">
              Tidak ada modul edukasi yang sesuai dengan pencarian atau kategori yang dipilih.
            </div>
          </div>

          <!-- Kalkulator Simulasi Penggunaan Air (WaterCalculator.tsx) -->
          
<?php
/**
 * Kalkulator Simulasi Penggunaan Air Rumah Tangga Kota Payakumbuh
 * Sesuai persis dengan src/components/academy/WaterCalculator.tsx
 */
?>
<section id="water-calculator-section" class="bg-white rounded-2xl border border-slate-200 shadow-sm p-6 lg:p-8 space-y-8">
  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-100 pb-5">
    <div>
      <div class="flex items-center gap-2">
        <div class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-800 flex items-center justify-center font-bold">
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="16" height="20" x="4" y="2" rx="2"/><line x1="8" x2="16" y1="6" y2="6"/><line x1="16" x2="16" y1="14" y2="18"/><path d="M16 10h.01"/><path d="M12 10h.01"/><path d="M8 10h.01"/><path d="M12 14h.01"/><path d="M8 14h.01"/><path d="M12 18h.01"/><path d="M8 18h.01"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold tracking-tight text-slate-900">
          Kalkulator Simulasi Penggunaan Air
        </h3>
      </div>
      <p class="text-xs text-slate-500 mt-1">
        Hitung perkiraan konsumsi air harian keluarga Anda berdasarkan asumsi aktivitas rumah tangga di Kota Payakumbuh.
      </p>
    </div>

    <button
      type="button"
      onclick="resetWaterCalculator()"
      class="self-start sm:self-auto px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:text-slate-900 hover:bg-slate-50 flex items-center gap-1.5 transition-colors cursor-pointer"
    >
      <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
      </svg>
      <span>Kembalikan Default Asumsi</span>
    </button>
  </div>

  <!-- Assumptions Notice -->
  <div class="p-3.5 bg-sky-50/70 border border-sky-200 rounded-xl text-xs text-sky-900 flex items-start gap-2.5">
    <svg class="w-4 h-4 text-sky-700 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
    </svg>
    <p class="leading-relaxed">
      <strong>Transparansi Nilai Asumsi:</strong> Angka perhitungan di bawah merupakan estimasi empiris standar kebiasaan rumah tangga (SNI 03-7065-2005 &amp; Pedoman Teknis PDAM), bukan hasil pengukuran sensor fisik aktual. Gunakan simulasi ini sebagai panduan refleksi kebiasaan harian.
    </p>
  </div>

  <!-- 2-Column Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
    <!-- Left Inputs (7 Cols) -->
    <div class="lg:col-span-7 space-y-6">
      <!-- Input 1: Family Members -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <label class="text-xs font-semibold text-slate-800 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span>Jumlah Anggota Keluarga di Rumah</span>
          </label>
          <span class="text-sm font-bold font-mono text-cyan-900 bg-cyan-50 px-2 py-0.5 rounded border border-cyan-200">
            <span id="calc-members-label">4</span> Orang
          </span>
        </div>
        <input
          id="calc-members-range"
          type="range"
          min="1"
          max="8"
          value="4"
          oninput="setCalcFamilyMembers(this.value)"
          class="w-full accent-cyan-600 cursor-pointer"
        />
        <div class="flex justify-between text-[10px] text-slate-400 font-mono">
          <span>1 orang (kost)</span>
          <span>4 orang (standar)</span>
          <span>8 orang (besar)</span>
        </div>
      </div>

      <!-- Input 2: Bathing Method -->
      <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-800 block">
          Metode &amp; Kebiasaan Mandi Utama
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
          <button
            type="button"
            data-calc-bath="gayung"
            onclick="setCalcBathMethod('gayung')"
            class="calc-bath-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600"
          >
            <span class="text-xs font-semibold block text-slate-800">Bak &amp; Gayung</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block leading-tight">~75 Liter / mandi</span>
          </button>

          <button
            type="button"
            data-calc-bath="shower_biasa"
            onclick="setCalcBathMethod('shower_biasa')"
            class="calc-bath-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white"
          >
            <span class="text-xs font-semibold block text-slate-800">Shower Biasa</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block leading-tight">~10 L / menit</span>
          </button>

          <button
            type="button"
            data-calc-bath="shower_hemat"
            onclick="setCalcBathMethod('shower_hemat')"
            class="calc-bath-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white"
          >
            <span class="text-xs font-semibold block text-slate-800">Shower Aerator</span>
            <span class="text-[11px] text-emerald-600 mt-0.5 block leading-tight font-medium">~5.5 L / menit (Hemat)</span>
          </button>
        </div>
      </div>

      <!-- Input 3: Teeth brushing -->
      <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-800 block">
          Kebiasaan Saat Menyikat Gigi &amp; Cuci Muka
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <button
            type="button"
            data-calc-teeth="kran_mengalir"
            onclick="setCalcTeethHabit('kran_mengalir')"
            class="calc-teeth-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600"
          >
            <span class="text-xs font-semibold block text-slate-800">Kran Dibiarkan Mengalir</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">~6 Liter terbuang / menit</span>
          </button>

          <button
            type="button"
            data-calc-teeth="gelas"
            onclick="setCalcTeethHabit('gelas')"
            class="calc-teeth-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white"
          >
            <span class="text-xs font-semibold block text-slate-800">Gelas Kumur / Tutup Kran</span>
            <span class="text-[11px] text-emerald-600 font-medium mt-0.5 block">~0.5 Liter (Efisien)</span>
          </button>
        </div>
      </div>

      <!-- Input 4: Dish washing -->
      <div class="space-y-2">
        <label class="text-xs font-semibold text-slate-800 block">
          Metode Mencuci Piring Rumah Tangga
        </label>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
          <button
            type="button"
            data-calc-dish="kran_bebas"
            onclick="setCalcDishMethod('kran_bebas')"
            class="calc-dish-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-cyan-600 bg-cyan-50/60 ring-1 ring-cyan-600"
          >
            <span class="text-xs font-semibold block text-slate-800">Air Mengucur Bebas</span>
            <span class="text-[11px] text-slate-500 mt-0.5 block">~90 Liter / hari</span>
          </button>

          <button
            type="button"
            data-calc-dish="baskom"
            onclick="setCalcDishMethod('baskom')"
            class="calc-dish-btn p-3 rounded-xl border text-left transition-all cursor-pointer border-slate-200 hover:border-slate-300 bg-white"
          >
            <span class="text-xs font-semibold block text-slate-800">Metode 2 Baskom Rendam/Bilas</span>
            <span class="text-[11px] text-emerald-600 font-medium mt-0.5 block">~35 Liter / hari (Hemat 60%)</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Right Output Dashboard (5 Cols) -->
    <div class="lg:col-span-5 bg-slate-50 rounded-2xl border border-slate-200 p-6 space-y-6">
      <div>
        <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-800 block">
          Hasil Estimasi Konsumsi
        </span>
        <div class="mt-2 flex items-baseline gap-2">
          <span id="calc-out-daily" class="text-4xl font-extrabold tracking-tight text-slate-900 font-mono tabular-nums">
            823
          </span>
          <span class="text-sm font-semibold text-slate-500">Liter / Hari</span>
        </div>
        <p class="text-xs text-slate-500 mt-1">
          Rata-rata:
          <strong id="calc-out-per-person" class="text-slate-800 font-mono">206 L</strong>
          / orang / hari (Standar WHO: 120 L)
        </p>
      </div>

      <!-- Monthly Volume & Cost -->
      <div class="grid grid-cols-2 gap-3 pt-4 border-t border-slate-200">
        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
          <span class="text-[11px] text-slate-500 block">Volume Bulanan</span>
          <span id="calc-out-m3" class="text-xl font-bold text-slate-900 font-mono tabular-nums">24.7 m³</span>
          <span class="text-[10px] text-slate-400 block mt-0.5">30 hari</span>
        </div>

        <div class="p-3 bg-white rounded-xl border border-slate-200/80">
          <span class="text-[11px] text-slate-500 block">Estimasi Tagihan</span>
          <span id="calc-out-bill" class="text-xl font-bold text-slate-900 font-mono tabular-nums">Rp 115.550</span>
          <span class="text-[10px] text-slate-400 block mt-0.5">Termasuk abonemen</span>
        </div>
      </div>

      <!-- Potential Savings Box -->
      <div class="p-4 bg-emerald-50 rounded-xl border border-emerald-200 space-y-3">
        <div class="flex items-center gap-1.5 text-emerald-800">
          <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          </svg>
          <h5 class="text-xs font-bold uppercase tracking-wide">Peluang Penghematan Nyata</h5>
        </div>
        <p class="text-xs text-emerald-900/90 leading-relaxed">
          Jika keluarga Anda beralih ke shower aerator dan metode baskom cuci piring:
        </p>
        <div class="space-y-1.5 pt-1">
          <div class="flex justify-between items-center text-xs">
            <span class="text-emerald-800">Air Terhemat per Bulan:</span>
            <span id="calc-out-saved-liters" class="font-bold font-mono text-emerald-900">+13.980 Liter</span>
          </div>
          <div class="flex justify-between items-center text-xs">
            <span class="text-emerald-800">Potensi Hemat Tagihan:</span>
            <span id="calc-out-saved-idr" class="font-bold font-mono text-emerald-900">Rp 71.150 / bulan</span>
          </div>
        </div>
      </div>

      <div class="text-[11px] text-slate-500 leading-relaxed pt-1">
        Gunakan hasil kalkulasi ini saat mengikuti <strong class="text-slate-700">Tantangan 7 Hari Detoks Air</strong> untuk memantau penurunan pemakaian air nyata di meteran Anda.
      </div>
    </div>
  </div>
</section>


        </div>

        <!-- FULL-PAGE INTERACTIVE QUIZ VIEW -->
        
<?php
/**
 * Full-page Interactive 10-Question Quiz View
 * Sesuai persis dengan src/components/academy/QuizPageView.tsx
 */
?>
<div id="quiz-page-wrapper" class="hidden min-h-screen bg-slate-50/70 pb-24">
  <!-- 1. STICKY TOP APP BAR -->
  <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-xs">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3.5 flex items-center justify-between gap-3">
      <button
        type="button"
        onclick="closeQuizPageView()"
        class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
      >
        <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        <span class="hidden sm:inline">Kembali ke Akademi</span>
        <span class="sm:hidden">Kembali</span>
      </button>

      <!-- Center Title Badge -->
      <div class="flex items-center gap-2 text-center">
        <span class="text-[11px] font-bold uppercase tracking-wider text-cyan-800 bg-cyan-50 px-2.5 py-1 rounded-lg border border-cyan-200">
          Ujian 10 Soal
        </span>
        <span id="quiz-course-title" class="text-xs font-bold text-slate-800 truncate max-w-[180px] sm:max-w-xs">
          -
        </span>
      </div>

      <!-- Live Rewards Indicator -->
      <div class="flex items-center gap-2">
        <div class="flex items-center gap-1 px-2.5 py-1 rounded-xl bg-amber-50 text-amber-800 border border-amber-200 font-mono text-xs font-bold">
          <svg class="w-3.5 h-3.5 text-amber-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="8" cy="8" r="6"/><path d="M18.09 10.37A6 6 0 1 1 10.34 18"/><path d="M7 6h1v4"/>
          </svg>
          <span>+<span id="quiz-live-points">0</span> Poin</span>
        </div>

        <div
          id="quiz-live-streak-badge"
          class="hidden sm:flex items-center gap-1 px-2.5 py-1 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-extrabold animate-pulse"
        >
          <svg class="w-3.5 h-3.5 text-rose-500 fill-rose-500" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
          </svg>
          <span>Streak <span id="quiz-live-streak-count">0</span>x</span>
        </div>
      </div>
    </div>

    <!-- Dynamic Progress Bar -->
    <div id="quiz-progress-track" class="w-full bg-slate-100 h-1.5 relative overflow-hidden">
      <div
        id="quiz-progress-bar"
        class="h-full bg-gradient-to-r from-cyan-500 via-sky-500 to-emerald-500 transition-all duration-300"
        style="width: 10%;"
      ></div>
    </div>
  </header>

  <!-- 2. MAIN QUIZ CONTAINER -->
  <div class="max-w-3xl mx-auto px-4 sm:px-6 pt-6">

    <!-- SCREEN A: QUESTION VIEW -->
    <div id="quiz-question-screen" class="space-y-6 animate-fade-zoom">
      <!-- Question Header Card -->
      <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-4 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-48 h-48 bg-cyan-100/40 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex items-center justify-between gap-3 flex-wrap">
          <div class="inline-flex items-center gap-2">
            <span id="quiz-q-number" class="w-7 h-7 rounded-full bg-cyan-600 text-white text-xs font-black flex items-center justify-center">
              1
            </span>
            <span class="text-xs font-bold text-slate-400">
              dari <span id="quiz-q-total">10</span> Pertanyaan
            </span>
          </div>

          <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-[11px] font-semibold">
            <svg class="w-3.5 h-3.5 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
              <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
            </svg>
            <span>Benar = +10 Poin Diskon (Rp 1.000)</span>
          </div>
        </div>

        <h2 id="quiz-q-prompt" class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug">
          -
        </h2>

        <!-- Streak Bonus Banner -->
        <div
          id="quiz-streak-bonus-banner"
          class="hidden p-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-sm animate-fade-zoom"
        >
          <svg class="w-4 h-4 fill-white" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2">
            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
          </svg>
          <span>Combo Beruntun <span id="quiz-streak-banner-num">3</span>x! Bonus Ekstra XP &amp; Koin Diskon Aktif!</span>
        </div>
      </div>

      <!-- Options Grid -->
      <div id="quiz-options-list" class="space-y-3"></div>

      <!-- Confirm Button Wrapper -->
      <div id="quiz-confirm-wrap" class="pt-2">
        <button
          id="quiz-confirm-btn"
          type="button"
          disabled
          onclick="confirmQuizAnswer()"
          class="w-full py-4 rounded-2xl font-extrabold text-sm shadow-sm transition-all flex items-center justify-center gap-2 bg-slate-200 text-slate-400 cursor-not-allowed"
        >
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9.937 15.5A2 2 0 0 0 8.5 14.063l-6.135-1.582a.5.5 0 0 1 0-.962L8.5 9.936A2 2 0 0 0 9.937 8.5l1.582-6.135a.5.5 0 0 1 .963 0L14.063 8.5A2 2 0 0 0 15.5 9.937l6.135 1.581a.5.5 0 0 1 0 .964L15.5 14.063a2 2 0 0 0-1.437 1.437l-1.582 6.135a.5.5 0 0 1-.963 0z"/>
          </svg>
          <span id="quiz-confirm-label">Pilih Salah Satu Jawaban di Atas</span>
        </button>
      </div>

      <!-- Explanation & Next Action Wrapper -->
      <div id="quiz-explanation-wrap" class="hidden space-y-4 animate-fade-zoom">
        <div id="quiz-explanation-box" class="p-5 rounded-2xl border">
          <div id="quiz-explanation-heading" class="flex items-center gap-2 font-bold text-xs mb-1.5"></div>
          <p id="quiz-explanation-text" class="text-xs leading-relaxed opacity-90 pl-6"></p>
        </div>

        <button
          id="quiz-next-btn"
          type="button"
          onclick="nextQuizQuestion()"
          class="w-full py-4 rounded-2xl bg-slate-900 hover:bg-slate-800 text-white font-extrabold text-sm shadow-md flex items-center justify-center gap-2 transition-colors cursor-pointer"
        >
          <span id="quiz-next-label">Lanjut ke Soal 2 dari 10</span>
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

    <!-- SCREEN B: CELEBRATION & VICTORY PAGE -->
    <div id="quiz-result-screen" class="hidden space-y-8 animate-fade-zoom">
      <!-- Victory Banner -->
      <div class="bg-gradient-to-br from-slate-950 via-cyan-950 to-slate-900 text-white rounded-3xl p-6 sm:p-10 text-center relative overflow-hidden shadow-xl space-y-6">
        <div class="relative z-10 max-w-lg mx-auto space-y-4">
          <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-tr from-amber-400 to-yellow-200 text-slate-950 flex items-center justify-center shadow-lg">
            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
            </svg>
          </div>

          <div class="space-y-1">
            <span class="text-xs font-bold uppercase tracking-wider text-cyan-300">
              Evaluasi 10 Soal Tuntas
            </span>
            <h2 id="quiz-result-title" class="text-2xl sm:text-3xl font-black text-white">
              Luar Biasa, Sanak Warga!
            </h2>
            <p id="quiz-result-subtitle" class="text-xs sm:text-sm text-slate-300">-</p>
          </div>

          <!-- Score & Badge stats -->
          <div class="grid grid-cols-3 gap-3 pt-2">
            <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
              <span class="text-[10px] text-slate-300 uppercase block font-semibold">Skor Ujian</span>
              <strong id="quiz-res-score" class="text-xl sm:text-2xl font-black font-mono text-cyan-300">0%</strong>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
              <span class="text-[10px] text-slate-300 uppercase block font-semibold">Benar</span>
              <strong id="quiz-res-correct" class="text-xl sm:text-2xl font-black font-mono text-emerald-400">0/10</strong>
            </div>

            <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-xs border border-white/10">
              <span class="text-[10px] text-slate-300 uppercase block font-semibold">Combo Streak</span>
              <strong id="quiz-res-streak" class="text-xl sm:text-2xl font-black font-mono text-amber-300">0x</strong>
            </div>
          </div>
        </div>
      </div>

      <!-- MOBILE APP DISCOUNT CARD -->
      <div class="bg-gradient-to-br from-emerald-900 via-teal-900 to-slate-900 rounded-3xl p-6 sm:p-8 text-white border-2 border-emerald-500/50 shadow-lg space-y-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-16 -mt-16 w-64 h-64 bg-emerald-400/20 rounded-full blur-3xl pointer-events-none"></div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/15 pb-4">
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500 text-slate-950 flex items-center justify-center font-bold shadow-md shrink-0">
              <svg class="w-6 h-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="20" x="5" y="2" rx="2" ry="2"/><path d="M12 18h.01"/>
              </svg>
            </div>
            <div>
              <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-300">
                Sinkronisasi Mobile App Tirta Sago
              </span>
              <h3 class="text-lg font-black text-white">
                Voucher Diskon Rekening Air Aktif
              </h3>
            </div>
          </div>

          <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-400/20 text-emerald-200 border border-emerald-400/30 text-xs font-semibold">
            <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Tersinkron Otomatis</span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="p-4 rounded-2xl bg-white/10 border border-white/10 space-y-1">
            <span class="text-[11px] text-slate-300 block font-medium">Poin Diperoleh dari Kuis</span>
            <div class="flex items-baseline gap-2">
              <span id="quiz-res-coins" class="text-3xl font-black font-mono text-amber-300">+0</span>
              <span class="text-xs text-slate-300 font-semibold">Koin Smart Water</span>
            </div>
            <p class="text-[11px] text-emerald-300 pt-1">
              ✓ Telah ditambahkan langsung ke profil Anda
            </p>
          </div>

          <div class="p-4 rounded-2xl bg-white/10 border border-white/10 space-y-1">
            <span class="text-[11px] text-slate-300 block font-medium">Nilai Potongan Tagihan di Mobile</span>
            <div class="flex items-baseline gap-2">
              <span id="quiz-res-discount" class="text-3xl font-black font-mono text-emerald-300">Rp 0,-</span>
              <span class="text-xs text-slate-300 font-semibold">(1 Poin = Rp 100)</span>
            </div>
            <p class="text-[11px] text-slate-300 pt-1">
              Dipotongkan saat bayar tagihan di aplikasi mobile
            </p>
          </div>
        </div>

        <!-- Voucher Code Box -->
        <div class="p-4 rounded-2xl bg-slate-950/60 border border-white/10 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div>
            <span class="text-[10px] text-slate-400 uppercase font-bold block">
              Kode Voucher / Token Pembayaran Mobile
            </span>
            <code id="quiz-voucher-code" class="text-sm font-mono font-bold text-cyan-300 tracking-wider">
              DISC-TIRTA-2026
            </code>
          </div>

          <div class="flex items-center gap-2">
            <button
              type="button"
              onclick="copyQuizVoucher()"
              class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer"
            >
              <span id="quiz-copy-btn-label">Salin Kode</span>
            </button>

            <button
              type="button"
              onclick="simulateMobileBillDiscount()"
              class="px-4 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 text-xs font-extrabold flex items-center gap-1.5 transition-colors shadow-md cursor-pointer"
            >
              <span>Simulasi Potongan Tagihan</span>
            </button>
          </div>
        </div>

        <div
          id="quiz-sim-result-box"
          class="hidden p-4 rounded-2xl bg-emerald-500/20 border border-emerald-400/40 text-xs text-emerald-100 space-y-1.5 animate-fade-zoom"
        >
          <div class="flex items-center gap-2 font-bold text-white">
            <svg class="w-4 h-4 text-emerald-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"/><path d="m9 12 2 2 4-4"/>
            </svg>
            <span>Simulasi Mobile App Berhasil!</span>
          </div>
          <p id="quiz-sim-result-text" class="text-slate-200 text-[11px] leading-relaxed"></p>
        </div>

        <div class="text-[11px] text-slate-300 leading-relaxed bg-white/5 p-3.5 rounded-xl border border-white/10">
          💡 <strong>Cara Pakai di Mobile:</strong> Buka aplikasi mobile Tirta Sago (di Android/iOS) dengan nomor sambungan pelanggan yang sama (<strong><?= e($currentUser['meterNumber'] ?? '10294821') ?></strong>). Pada layar pembayaran tagihan bulanan, centang opsi <em>"Gunakan Koin Poin Smart Water"</em> untuk potongan harga langsung.
        </div>
      </div>

      <?php if (!$currentUser): ?>
        <div class="bg-amber-50 rounded-2xl border border-amber-200 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 text-xs">
          <div class="space-y-1">
            <strong class="text-amber-950 font-bold block">
              Ingin Menyimpan Poin &amp; Voucher Ini Permanen?
            </strong>
            <p class="text-amber-800">
              Masuk atau daftarkan akun Anda agar poin kuis ini otomatis tersimpan ke nomor meteran rumah Anda.
            </p>
          </div>
          <a
            href="<?= url('/login') ?>"
            class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-bold whitespace-nowrap shadow-xs cursor-pointer text-center"
          >
            Masuk / Daftar Akun
          </a>
        </div>
      <?php endif; ?>

      <!-- QUESTION REVIEW BREAKDOWN -->
      <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-xs space-y-6">
        <div>
          <h3 class="text-base font-bold text-slate-900">Kilas Balik 10 Soal Ujian</h3>
          <p class="text-xs text-slate-500">
            Pelajari kembali rangkuman jawaban yang benar untuk memperkuat wawasan konservasi air Anda.
          </p>
        </div>
        <div id="quiz-review-list" class="space-y-4"></div>
      </div>

      <!-- Action Buttons -->
      <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2">
        <button
          type="button"
          onclick="restartQuiz()"
          class="w-full sm:w-auto px-6 py-3 rounded-2xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer"
        >
          <svg class="w-4 h-4 text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>
          </svg>
          <span>Ulangi Kuis 10 Soal</span>
        </button>

        <button
          type="button"
          onclick="closeQuizPageView()"
          class="w-full sm:w-auto px-8 py-3.5 rounded-2xl bg-cyan-600 hover:bg-cyan-500 text-white text-xs font-extrabold flex items-center justify-center gap-2 transition-colors shadow-md cursor-pointer"
        >
          <span>Kembali ke Katalog Modul Akademi</span>
          <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m9 18 6-6-6-6"/>
          </svg>
        </button>
      </div>

    </div>
  </div>
</div>


      </main>

      <!-- Footer -->
      <?php require $legacySharedPath . '/footer.php'; ?>

      <!-- Lesson Reader Modal -->
      
<?php
/**
 * Modal Pembaca Materi Edukasi Smart Water Academy
 * Sesuai persis dengan src/components/academy/LessonReaderModal.tsx
 */
?>
<div
  id="lesson-reader-modal"
  class="hidden fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 backdrop-blur-sm"
>
  <div class="bg-white rounded-2xl max-w-3xl w-full border border-slate-200 shadow-2xl overflow-hidden flex flex-col max-h-[92vh] animate-fade-zoom">
    
    <!-- Header -->
    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
      <div>
        <div class="flex items-center gap-2 text-xs text-slate-500">
          <span class="font-semibold text-cyan-800 uppercase tracking-wider text-[11px]">
            Smart Water Academy
          </span>
          <span>&middot;</span>
          <span id="reader-course-category" class="capitalize">-</span>
        </div>
        <h3 id="reader-course-title" class="text-lg font-bold text-slate-900 mt-0.5">-</h3>
      </div>
      <button
        type="button"
        onclick="closeLessonReaderModal()"
        class="p-1.5 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer"
        aria-label="Tutup modal materi"
      >
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
        </svg>
      </button>
    </div>

    <!-- Lesson Sub-Navigation Tabs -->
    <div class="px-6 py-2.5 bg-slate-50/50 border-b border-slate-100 flex items-center gap-2 overflow-x-auto text-xs">
      <span class="text-[11px] text-slate-400 font-semibold uppercase tracking-wider shrink-0 mr-1">
        Bagian:
      </span>
      <div id="reader-lesson-tabs" class="flex items-center gap-2"></div>
    </div>

    <!-- Editorial Content Body -->
    <div class="p-6 sm:p-8 overflow-y-auto space-y-6 text-slate-700 text-sm leading-relaxed">
      <div class="border-b border-slate-100 pb-4">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-1">
          <svg class="w-3.5 h-3.5 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
          </svg>
          <span>Estimasi Waktu Baca: <span id="reader-lesson-time">-</span></span>
          <span>&middot;</span>
          <span>Modul #<span id="reader-lesson-num">1</span></span>
        </div>
        <h2 id="reader-lesson-title" class="text-xl font-bold text-slate-900 tracking-tight">-</h2>
        <p id="reader-lesson-summary" class="text-xs text-slate-500 mt-1 italic">-</p>
      </div>

      <!-- Paragraphs -->
      <div id="reader-lesson-paragraphs" class="space-y-4"></div>

      <!-- Practical Tips Box -->
      <div id="reader-tips-box" class="hidden p-4 rounded-xl bg-cyan-50/70 border border-cyan-200/80 space-y-2">
        <div class="flex items-center gap-2 text-cyan-900 font-bold text-xs uppercase tracking-wide">
          <svg class="w-4 h-4 text-amber-500 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>
          </svg>
          <span>Tips Praktis Rumah Tangga Payakumbuh</span>
        </div>
        <ul id="reader-tips-list" class="space-y-1.5 pl-6 list-disc text-xs text-slate-700"></ul>
      </div>

      <!-- Case Study Box -->
      <div id="reader-casestudy-box" class="hidden p-4 rounded-xl bg-slate-50 border border-slate-200 space-y-2">
        <div class="flex items-center gap-2 text-slate-800 font-bold text-xs">
          <svg class="w-4 h-4 text-cyan-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="m19 21-7-4-7 4V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16z"/>
          </svg>
          <span id="reader-casestudy-title">-</span>
        </div>
        <p id="reader-casestudy-scenario" class="text-xs text-slate-600 leading-relaxed">-</p>
        <div class="pt-2 border-t border-slate-200 text-xs text-emerald-800 font-medium">
          <strong>Hikmah Pelajaran:</strong> <span id="reader-casestudy-learned">-</span>
        </div>
      </div>
    </div>

    <!-- Modal Footer -->
    <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
      <button
        id="reader-prev-btn"
        type="button"
        onclick="prevReaderLesson()"
        class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs text-slate-600 hover:text-slate-900 hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-1.5 cursor-pointer"
      >
        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="m12 19-7-7 7-7"/><path d="M19 12H5"/>
        </svg>
        <span>Materi Sebelumnya</span>
      </button>

      <div class="flex items-center gap-3">
        <button
          id="reader-open-quiz-btn"
          type="button"
          onclick="switchReaderToQuiz()"
          class="px-3.5 py-2 rounded-lg border border-cyan-200 bg-white text-xs font-semibold text-cyan-800 hover:bg-cyan-50 flex items-center gap-1.5 cursor-pointer"
        >
          <svg class="w-3.5 h-3.5 text-amber-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>
          </svg>
          <span id="reader-open-quiz-label">Buka Kuis (10 Soal)</span>
        </button>

        <button
          id="reader-next-btn"
          type="button"
          onclick="nextReaderLesson()"
          class="px-4 py-2 rounded-lg text-xs font-semibold text-white bg-slate-900 hover:bg-slate-800 flex items-center gap-1.5 transition-colors cursor-pointer"
        >
          <span id="reader-next-label">Selesai &amp; Lanjut Materi</span>
          <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>
          </svg>
        </button>
      </div>
    </div>

  </div>
</div>



      <!-- TirtaBot AI Chatbot -->
      <?php require $legacySharedPath . '/chatbot.php'; ?>

    </div>

    <script>
      window.SWC_COURSES = <?= json_encode($ACADEMY_COURSES, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      window.SWC_ACADEMY_PROGRESS = <?= json_encode($academyProgress, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
      window.SWC_METER_SUFFIX = <?= json_encode($currentUser['meterNumber'] ?? '2026') ?>;
    </script>
    <script src="../../assets/js/pages/akademi-air.js"></script>
  </body>
</html>
