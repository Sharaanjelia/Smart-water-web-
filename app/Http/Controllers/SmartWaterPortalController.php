<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\BillDetail;
use App\Models\Complaint;
use App\Models\CustomerProfile;
use App\Models\MeterReading;
use App\Models\User;
use App\Services\AuthService;
use App\Services\BillingService;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterCustomerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SmartWaterPortalController extends Controller
{
    public function __construct(
        protected ?BillingService $billingService = null
    ) {
        $this->billingService = $billingService ?? app(BillingService::class);
    }

    /**
     * Dapatkan data sesi role dan user saat ini dari Laravel Auth / Database.
     */
    protected function getSessionData(): array
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $user = Auth::user();

        if ($user) {
            $formatted = AuthService::formatUserData($user);
            $_SESSION['swc_user'] = $formatted;
            $_SESSION['swc_role'] = $formatted['role'];
            return [
                'currentUser' => $formatted,
                'activeRole'  => $formatted['role'],
            ];
        }

        $sessionUser = session('swc_user') ?? $_SESSION['swc_user'] ?? null;
        $activeRole = session('swc_role') ?? $_SESSION['swc_role'] ?? 'guest';

        if ($sessionUser && is_array($sessionUser)) {
            $dbUser = null;
            if (!empty($sessionUser['id'])) {
                $dbUser = User::find($sessionUser['id']);
            } elseif (!empty($sessionUser['email'])) {
                $dbUser = User::where('email', $sessionUser['email'])->first();
            }

            if ($dbUser) {
                $formatted = AuthService::syncSession($dbUser);
                return [
                    'currentUser' => $formatted,
                    'activeRole'  => $formatted['role'],
                ];
            }
        }

        return [
            'currentUser' => $sessionUser,
            'activeRole'  => $activeRole,
        ];
    }

    /**
     * Beralih role (Hanya diizinkan jika akun memiliki hak akses di database).
     */
    public function switchRole(string $role): RedirectResponse
    {
        $role = in_array($role, ['admin', 'customer'], true) ? $role : 'customer';

        $data = $this->getSessionData();
        if (empty($data['currentUser'])) {
            return redirect(url('/admin-login'))->with('error', 'Silakan masuk terlebih dahulu.');
        }

        $user = Auth::user();
        if (!$user && !empty($data['currentUser']['id'])) {
            $user = User::find($data['currentUser']['id']);
        }

        if ($role === 'admin') {
            if (!$user || !$user->isStaff()) {
                return redirect(url('/user'))->with('error', 'Akses ditolak: Anda tidak memiliki wewenang administrator.');
            }
            session(['swc_role' => 'admin']);
            $_SESSION['swc_role'] = 'admin';
            return redirect(url('/admin-dashboard'))->with('success', 'Beralih ke mode Petugas / Admin SCADA.');
        }

        session(['swc_role' => 'customer']);
        $_SESSION['swc_role'] = 'customer';
        return redirect(url('/user'))->with('success', 'Beralih ke mode Pelanggan.');
    }

    /**
     * Masuk langsung ke Portal Petugas / Admin SCADA (Akses Demo Kedinasan).
     */
    public function enterAdminDirect(): RedirectResponse
    {
        $sessionData = $this->getSessionData();
        $currentUser = Auth::user() ?? (!empty($sessionData['currentUser']['id']) ? User::find($sessionData['currentUser']['id']) : null);

        // Pencegahan Eskalasi Hak Akses (Privilege Escalation):
        // Jika pelanggan aktif mencoba masuk melalui rute demo petugas, tolak dan kembalikan ke portal pelanggan
        if ($currentUser && !$currentUser->isStaff()) {
            return redirect(url('/user'))->with('error', 'Akses ditolak: Akun pelanggan tidak diizinkan masuk ke Portal Petugas SCADA.');
        }

        $staffUser = AuthService::authenticateStaff('', '', true);

        if (!$staffUser) {
            return redirect(url('/admin-login'))->with('error', 'Akun petugas kedinasan tidak ditemukan di database.');
        }

        AuthService::syncSession($staffUser);

        return redirect(url('/admin-dashboard'))->with('success', 'Selamat datang di Portal Petugas & SCADA PDAM Tirta Sago.');
    }

    /**
     * Tampilkan Halaman Login Khusus Petugas PDAM / SCADA
     */
    public function adminLoginForm()
    {
        $data = $this->getSessionData();
        if ($data['activeRole'] === 'admin' || $data['activeRole'] === 'staff') {
            return redirect(url('/admin-dashboard'));
        }
        return view('auth.admin-login');
    }

    /**
     * Proses Login Khusus Petugas PDAM
     */
    public function handleAdminLogin(LoginRequest $request): RedirectResponse
    {
        $isDemo = !empty($request->input('is_demo'));
        $identifier = trim((string)$request->input('identifier', ''));
        $password = (string)$request->input('password', '');

        $staffUser = AuthService::authenticateStaff($identifier, $password, $isDemo);

        if (!$staffUser) {
            return redirect()->back()->with('error', 'NIP atau kata sandi dinas Anda salah.');
        }

        AuthService::syncSession($staffUser);

        return redirect(url('/admin-dashboard'))->with('success', 'Selamat datang di Portal Operasional SCADA PDAM Tirta Sago.');
    }

    /**
     * Logout sesi aplikasi dan bersihkan semua storage sesi.
     */
    public function logout(Request $request): RedirectResponse
    {
        $wasAdmin = (session('swc_role') === 'admin' || ($_SESSION['swc_role'] ?? '') === 'admin' || $request->query('to') === 'admin');

        AuthService::performLogout();

        if ($wasAdmin) {
            return redirect(url('/admin-login'))->with('success', 'Anda telah keluar dari Portal Petugas PDAM.');
        }

        return redirect(url('/login'))->with('success', 'Anda berhasil keluar dari sistem Smart Water.');
    }

    /**
     * Fallback Login handler via HTTP Form POST
     */
    public function handleLogin(LoginRequest $request): RedirectResponse
    {
        $identifier = trim((string)$request->input('identifier', ''));
        $password = (string)$request->input('password', '');

        $userData = AuthService::attemptLogin($identifier, $password);
        if (!$userData) {
            return redirect()->back()->with('error', 'Kombinasi identitas dan kata sandi Anda salah.');
        }

        $dest = in_array($userData['role'], ['admin', 'staff'], true) ? url('/admin-dashboard') : url('/report-meter');

        return redirect($dest)->with('success', 'Selamat datang kembali, ' . ($userData['name'] ?? 'Pelanggan') . '!');
    }

    /**
     * Fallback Register handler via HTTP Form POST
     */
    public function handleRegister(RegisterCustomerRequest $request): RedirectResponse
    {
        $user = AuthService::registerCustomer($request->validated());
        AuthService::syncSession($user);

        return redirect(url('/report-meter'))->with('success', 'Pendaftaran akun berhasil! Selamat datang di Smart Water.');
    }

    /**
     * Pastikan pengguna memiliki peran customer untuk mengakses fitur pelanggan.
     */
    protected function ensureCustomerRole(array $data)
    {
        if (empty($data['currentUser'])) {
            return redirect(url('/login'))->with('error', 'Silakan masuk sebagai Pelanggan untuk mengakses fitur ini.');
        }
        return null;
    }

    /**
     * Pastikan pengguna memiliki peran admin untuk mengakses portal operasional.
     */
    protected function ensureAdminRole(array $data)
    {
        if (empty($data['currentUser'])) {
            return redirect(url('/admin-login'))->with('error', 'Halaman ini khusus untuk Petugas & Operator PDAM. Silakan masuk dengan akun kedinasan Anda.');
        }

        if (!in_array($data['activeRole'] ?? '', ['admin', 'staff'], true)) {
            return redirect(url('/user'))->with('error', 'Akses ditolak: Anda tidak memiliki wewenang administrator.');
        }

        return null;
    }

    // 1. Forgot Password Screen
    public function forgotPassword()
    {
        return view('auth.forgot-password');
    }

    // 2. Report Meter Landing Screen
    public function reportMeter()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('meter.report', array_merge($data, [
            'currentTab' => 'report-meter',
        ]));
    }

    // 3. Scanning Meter Simulation Screen
    public function scanningMeter()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('meter.scanning', array_merge($data, [
            'currentTab' => 'report-meter',
        ]));
    }

    // 4. Meter Result Screen
    public function meterResult(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        $scannedValue = $request->query('scanned', '01019');
        $previousValue = $request->query('previous', '01000');

        return view('meter.result', array_merge($data, [
            'currentTab'    => 'report-meter',
            'scannedValue'  => $scannedValue,
            'previousValue' => $previousValue,
            'accuracy'      => 98.4,
        ]));
    }

    // 5. Meter Confirmation Screen
    public function meterConfirmation(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        $scannedValue = $request->query('scanned', '01019');
        $previousValue = '01000';

        return view('meter.confirmation', array_merge($data, [
            'currentTab'    => 'report-meter',
            'scannedValue'  => $scannedValue,
            'previousValue' => $previousValue,
        ]));
    }

    // 6. Bill Screen
    public function bill()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }

        $user = Auth::user();
        if (!$user && !empty($data['currentUser']['id'])) {
            $user = User::find($data['currentUser']['id']);
        }

        $activeBill = null;
        $billHistory = collect();
        $paidBills = collect();

        if ($user) {
            $activeBill = $this->billingService->getUnpaidBillForUser($user->id);
            $billHistory = $this->billingService->getBillHistoryForUser($user->id, 15);
            $paidBills = Bill::with(['details', 'latestPayment'])
                ->where('user_id', $user->id)
                ->where('status', 'PAID')
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('billing.index', array_merge($data, [
            'currentTab'   => 'bill',
            'activeBill'   => $activeBill,
            'billHistory'  => $billHistory,
            'paidBills'    => $paidBills,
            'isLoggedIn'   => ($user !== null),
        ]));
    }

    // 7. Bill Detail Screen
    public function billDetail(Request $request, ?Bill $bill = null)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }

        $user = Auth::user();
        if (!$user && !empty($data['currentUser']['id'])) {
            $user = User::find($data['currentUser']['id']);
        }

        $billId = $bill?->id ?? $request->query('id') ?? $request->query('bill_id');

        if ($billId) {
            $selectedBill = Bill::with(['details', 'latestPayment', 'user.customerProfile', 'meterReading'])->find($billId);
            if (!$selectedBill) {
                abort(404, 'Tagihan tidak ditemukan.');
            }
            // IDOR Protection: User can only view their own bill unless admin/staff
            if ($user && !$user->isAdmin() && !$user->isStaff() && $selectedBill->user_id !== $user->id) {
                abort(403, 'Anda tidak memiliki hak akses untuk melihat tagihan ini.');
            }
        } else {
            // Default to user's active unpaid bill, or latest bill
            $selectedBill = null;
            if ($user) {
                $selectedBill = $this->billingService->getUnpaidBillForUser($user->id)
                    ?? Bill::with(['details', 'latestPayment', 'user.customerProfile', 'meterReading'])
                        ->where('user_id', $user->id)
                        ->latest('id')
                        ->first();
            }
        }

        return view('billing.detail', array_merge($data, [
            'currentTab' => 'bill',
            'bill'       => $selectedBill,
            'isLoggedIn' => ($user !== null),
        ]));
    }

    // 8. Bill History Screen
    public function billHistory(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }

        $user = Auth::user();
        if (!$user && !empty($data['currentUser']['id'])) {
            $user = User::find($data['currentUser']['id']);
        }

        $historyBills = collect();
        if ($user) {
            $historyBills = Bill::with(['details', 'latestPayment'])
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate(15);
        }

        return view('billing.history', array_merge($data, [
            'currentTab'   => 'bill',
            'historyBills' => $historyBills,
            'isLoggedIn'   => ($user !== null),
        ]));
    }

    // 9. Payment Method Selection Screen
    public function paymentMethod()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('billing.payment-method', array_merge($data, [
            'currentTab' => 'bill',
        ]));
    }

    // 10. Payment Instruction Screen
    public function paymentInstruction(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        $method = $request->query('method', 'bca_va');
        return view('billing.payment-instruction', array_merge($data, [
            'currentTab' => 'bill',
            'method'     => $method,
        ]));
    }

    // 11. Payment Success Screen
    public function paymentSuccess(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        $method = $request->query('method', 'BCA Virtual Account');
        return view('billing.payment-success', array_merge($data, [
            'currentTab' => 'bill',
            'method'     => $method,
        ]));
    }

    // 12. Water Condition / Outage Info Screen
    public function waterCondition()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('customer.water-condition', array_merge($data, [
            'currentTab' => 'water-condition',
        ]));
    }

    // 13. Notification Center Screen
    public function notification()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('customer.notification', array_merge($data, [
            'currentTab' => 'notification',
        ]));
    }

    // 14. Profile Screen
    public function profile()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('customer.profile', array_merge($data, [
            'currentTab' => 'profile',
        ]));
    }

    // 15. Wallet Screen
    public function wallet()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureCustomerRole($data)) {
            return $guard;
        }
        return view('customer.wallet', array_merge($data, [
            'currentTab' => 'wallet',
        ]));
    }

    // 16. Settings Screen
    public function settings()
    {
        $data = $this->getSessionData();
        if (empty($data['currentUser'])) {
            return redirect(url('/login'))->with('error', 'Silakan masuk untuk mengakses halaman pengaturan.');
        }
        return view('customer.settings', array_merge($data, [
            'currentTab' => 'settings',
        ]));
    }

    // 17. Admin Dashboard Screen
    public function adminDashboard(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureAdminRole($data)) {
            return $guard;
        }
        $subTab = $request->query('tab', 'verify');
        if (!in_array($subTab, ['verify', 'bills', 'customers'], true)) {
            $subTab = 'verify';
        }

        $pendingReportsCount = MeterReading::where('status', 'Menunggu Validasi')
            ->orWhere('status', 'PENDING')
            ->count();
        $totalComplaintsCount = Complaint::count();
        $totalCustomersCount = CustomerProfile::count();

        return view('admin.dashboard', array_merge($data, [
            'currentTab'           => 'admin-dashboard',
            'subTab'               => $subTab,
            'pendingReportsCount'  => $pendingReportsCount,
            'totalComplaintsCount' => $totalComplaintsCount,
            'totalCustomersCount'  => $totalCustomersCount,
        ]));
    }

    // 18. Water Monitoring SCADA Dashboard Screen
    public function waterMonitoring()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureAdminRole($data)) {
            return $guard;
        }
        return view('admin.water-monitoring', array_merge($data, [
            'currentTab' => 'water-monitoring',
        ]));
    }

    // 19. Reservoir Detail Screen
    public function reservoirDetail()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureAdminRole($data)) {
            return $guard;
        }
        return view('admin.reservoir-detail', array_merge($data, [
            'currentTab' => 'water-monitoring',
        ]));
    }

    // 20. Distribution Network Screen
    public function distributionNetwork()
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureAdminRole($data)) {
            return $guard;
        }
        return view('admin.distribution-network', array_merge($data, [
            'currentTab' => 'water-monitoring',
        ]));
    }

    // 21. Distribution Sector Detail Screen
    public function distributionDetail(Request $request)
    {
        $data = $this->getSessionData();
        if ($guard = $this->ensureAdminRole($data)) {
            return $guard;
        }
        $sector = $request->query('sector', 'DIST-C');
        return view('admin.distribution-detail', array_merge($data, [
            'currentTab' => 'water-monitoring',
            'sector'     => $sector,
        ]));
    }
}
