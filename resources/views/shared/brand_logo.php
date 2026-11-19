<?php
/**
 * Komponen BrandLogo - Smart Water
 * Mendukung ukuran 'sm', 'md', 'lg' dan subtitle opsional
 */
if (!function_exists('renderBrandLogo')) {
function renderBrandLogo(string $size = 'md', bool $showSubtitle = false, string $href = '', string $extraClass = ''): void
{
    if ($href === '' || $href === 'index.php' || str_contains($href, 'php-beranda')) {
        $href = function_exists('url') ? url('/beranda') : '/beranda';
    }
    $isSm = $size === 'sm';
    $isLg = $size === 'lg';

    $iconSize  = $isSm ? 'w-8 h-8' : ($isLg ? 'w-11 h-11' : 'w-9 h-9');
    $titleSize = $isSm ? 'text-sm' : ($isLg ? 'text-lg' : 'text-base');
    $uid       = uniqid('logo_');
    ?>
    <a
      href="<?= e($href) ?>"
      class="inline-flex items-center gap-2.5 cursor-pointer select-none group <?= e($extraClass) ?>"
    >
      <!-- Brand Icon: Teardrop with nested green leaf -->
      <div class="relative <?= $iconSize ?> shrink-0">
        <svg viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full drop-shadow-xs">
          <defs>
            <linearGradient id="dropGrad_<?= $uid ?>" x1="20" y1="10" x2="80" y2="90" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#38BDF8" />
              <stop offset="60%" stop-color="#0284C7" />
              <stop offset="100%" stop-color="#0369A1" />
            </linearGradient>
            <linearGradient id="leafGrad_<?= $uid ?>" x1="10" y1="40" x2="60" y2="95" gradientUnits="userSpaceOnUse">
              <stop offset="0%" stop-color="#34D399" />
              <stop offset="50%" stop-color="#10B981" />
              <stop offset="100%" stop-color="#059669" />
            </linearGradient>
          </defs>
          <!-- Water Droplet Body -->
          <path
            d="M50 8C50 8 82 45 82 66C82 82.5 67.7 94 50 94C32.3 94 18 82.5 18 66C18 45 50 8 50 8Z"
            fill="url(#dropGrad_<?= $uid ?>)"
          />
          <!-- Inner droplet shine -->
          <path
            d="M58 26C68 38 72 50 71 62"
            stroke="white"
            stroke-width="4"
            stroke-linecap="round"
            stroke-opacity="0.45"
          />
          <!-- Green Leaf nestled in bottom-left -->
          <path
            d="M20 72C20 72 26 48 48 44C48 44 48 68 34 78C26 84 20 72 20 72Z"
            fill="url(#leafGrad_<?= $uid ?>)"
          />
          <!-- Leaf vein -->
          <path
            d="M23 70C30 63 38 56 46 47"
            stroke="white"
            stroke-width="2"
            stroke-linecap="round"
            stroke-opacity="0.6"
          />
        </svg>
      </div>

      <!-- Brand Typography -->
      <div class="flex flex-col leading-tight">
        <span class="font-extrabold text-[#0F2C59] tracking-tight <?= $titleSize ?>">
          Smart Water
        </span>
        <?php if ($showSubtitle): ?>
          <span class="text-[10px] text-slate-500 font-normal">
            Portal Layanan, Edukasi, dan Aksi Hemat Air
          </span>
        <?php endif; ?>
      </div>
    </a>
    <?php
}
}
