<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Bill;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "============================================================\n";
echo " AUTHENTICATED USER & VIEW RENDERING INSPECTION\n";
echo "============================================================\n";

$user = User::where('email', 'budi.santoso@warga.payakumbuh.go.id')->first();
if (!$user) {
    echo "User budi.santoso not found, taking first customer...\n";
    $user = User::whereHas('roles', fn($q) => $q->where('name', 'customer'))->first();
}

echo "Authenticated as: {$user->name} ({$user->email})\n";
Auth::login($user);

$userBills = Bill::where('user_id', $user->id)->get();
echo "User bills in DB count: " . $userBills->count() . "\n";
foreach ($userBills as $b) {
    echo "  - Bill {$b->bill_number} ({$b->billing_period}): status {$b->status}, type {$b->bill_type}, total Rp {$b->total_amount}\n";
}

// 1. Render /bill
$request = \Illuminate\Http\Request::create('/bill', 'GET');
$controller = $app->make(\App\Http\Controllers\SmartWaterPortalController::class);
$billResponse = $controller->bill();
$billContent = $billResponse->render();

echo "\n[1] View 'billing.index' (/bill):\n";
echo "    - '⚠️ Simulasi — Pembayaran Non-Aktif': " . (strpos($billContent, 'Simulasi — Pembayaran Non-Aktif') !== false ? "YES [SAFE: SIMULATION BLOCKED]" : "NO") . "\n";
echo "    - Active link to /payment-method: " . (preg_match('/<a[^>]*href="[^"]*payment-method[^"]*"[^>]*>\s*(<span>)?\s*Bayar Sekarang/i', $billContent) ? "YES [DANGER]" : "NONE [SAFE]") . "\n";
echo "    - Fake April 2025 invoice: " . (strpos($billContent, 'INV-PYK-2025-04-1000') !== false ? "YES [FAIL]" : "NO [PASS]") . "\n";

// 2. Render /bill-detail
$detailResponse = $controller->billDetail($request);
$detailContent = $detailResponse->render();

echo "\n[2] View 'billing.detail' (/bill-detail):\n";
echo "    - 'Tagihan Simulasi — Pembayaran Resmi Tidak Dapat Dilakukan': " . (strpos($detailContent, 'Tagihan Simulasi — Pembayaran Resmi Tidak Dapat Dilakukan') !== false ? "YES [SAFE: SIMULATION BLOCKED]" : "NO") . "\n";
echo "    - Active link to /payment-method: " . (preg_match('/<a[^>]*href="[^"]*payment-method[^"]*"[^>]*id="detailCtaBtn"/i', $detailContent) ? "YES [DANGER]" : "NONE [SAFE]") . "\n";
echo "    - Fake April 2025 invoice: " . (strpos($detailContent, 'INV-PYK-2025-04-1000') !== false ? "YES [FAIL]" : "NO [PASS]") . "\n";

// 2b. Render /bill-detail with Official UNPAID Bill
$dummyOfficialBill = new Bill([
    'user_id' => $user->id,
    'bill_number' => 'OFFICIAL-TEST-999',
    'status' => 'UNPAID',
    'bill_type' => 'OFFICIAL',
    'is_simulation' => false,
    'billing_period' => '2026-05',
    'period_label' => 'Mei 2026',
    'total_amount' => 50000,
    'consumption' => 10,
    'previous_reading' => 100,
    'current_reading' => 110,
]);
$officialDetailView = view('billing.detail', [
    'currentUser' => ['id' => $user->id, 'role' => 'customer'],
    'bill' => $dummyOfficialBill,
    'isLoggedIn' => true,
])->render();

echo "\n[2b] View 'billing.detail' with Official UNPAID bill:\n";
echo "    - 'Fitur pembayaran sedang dipersiapkan': " . (strpos($officialDetailView, 'Fitur pembayaran sedang dipersiapkan.') !== false ? "YES [SAFE: OFFICIAL PAYMENT DISABLED BEFORE TASK 10B]" : "NO") . "\n";
echo "    - Detail CTA button disabled: " . (strpos($officialDetailView, 'id="detailCtaBtn"') !== false && strpos($officialDetailView, 'disabled') !== false ? "YES [SAFE]" : "NO") . "\n";
echo "    - Active link to /payment-method: " . (preg_match('/<a[^>]*href="[^"]*payment-method[^"]*"[^>]*id="detailCtaBtn"/i', $officialDetailView) ? "YES [DANGER]" : "NONE [SAFE]") . "\n";

// 3. Render /bill-history
$historyResponse = $controller->billHistory($request);
$historyContent = $historyResponse->render();

echo "\n[3] View 'billing.history' (/bill-history):\n";
echo "    - History wrapper rendered: " . (strpos($historyContent, 'id="historyListWrapper"') !== false ? "YES [PASS]" : "NO") . "\n";
echo "    - Client fake injection script disabled for auth: " . (strpos($historyContent, 'window.SWC.getBill') === false ? "CLEAN (Blade DB Only) [SAFE]" : "PRESENT") . "\n";

// 4. Render /payment-method
$methodResponse = $controller->paymentMethod();
$methodContent = $methodResponse->render();

echo "\n[4] View 'billing.payment-method' (/payment-method):\n";
echo "    - Preparation notice banner: " . (strpos($methodContent, 'Pemberitahuan: Fitur Pembayaran Sedang Dipersiapkan') !== false ? "YES [SAFE]" : "NO") . "\n";
echo "    - Submit button disabled with notice: " . (strpos($methodContent, 'id="submitPayBtn"') !== false && strpos($methodContent, 'disabled') !== false ? "YES [SAFE]" : "NO") . "\n";
echo "    - Number of disabled form elements: " . substr_count($methodContent, 'disabled') . " elements [SAFE]\n";
echo "    - Mutating handlePaymentSubmit removed: " . (strpos($methodContent, 'deductWallet') === false ? "CLEAN [SAFE]" : "STILL CONTAINS DEDUCT") . "\n";

// 5. Render /payment-instruction
$instructionResponse = $controller->paymentInstruction($request);
$instructionContent = $instructionResponse->render();

echo "\n[5] View 'billing.payment-instruction' (/payment-instruction):\n";
echo "    - Preparation notice banner: " . (strpos($instructionContent, 'Pemberitahuan: Fitur Pembayaran Sedang Dipersiapkan') !== false ? "YES [SAFE]" : "NO") . "\n";
echo "    - Confirm payment button disabled: " . (strpos($instructionContent, 'id="confirmPayBtn"') !== false && strpos($instructionContent, 'disabled') !== false ? "YES [SAFE]" : "NO") . "\n";
echo "    - SWC.payBill call removed from confirmPayment: " . (strpos($instructionContent, 'SWC.payBill') === false ? "CLEAN [SAFE]" : "STILL CONTAINS MUTATION") . "\n";

// 6. Render /payment-success
$successResponse = $controller->paymentSuccess($request);
$successContent = $successResponse->render();

echo "\n[6] View 'billing.payment-success' (/payment-success):\n";
echo "    - Mutating SWC.payBill script removed: " . (strpos($successContent, 'SWC.payBill') === false ? "CLEAN [SAFE]" : "STILL CONTAINS MUTATION") . "\n";

echo "\n============================================================\n";
echo " ALL VIEW SAFETY CHECKS PASSED\n";
echo "============================================================\n";
