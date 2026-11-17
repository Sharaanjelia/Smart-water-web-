<?php

namespace App\Http\Controllers;

use App\Http\Requests\Meter\StoreMeterReadingRequest;
use App\Http\Requests\Meter\ValidateMeterReadingRequest;
use App\Http\Resources\MeterReadingResource;
use App\Models\MeterReading;
use App\Models\User;
use App\Services\MeterReadingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class MeterReadingController extends Controller
{
    public function __construct(
        protected MeterReadingService $meterReadingService
    ) {}

    /**
     * Format Model MeterReading Eloquent via MeterReadingResource (backward-compatible static helper).
     */
    public static function formatReport(MeterReading $reading): array
    {
        return (new MeterReadingResource($reading))->resolve();
    }

    /**
     * Tampilan Halaman Lapor Meter Mandiri
     */
    public function index(): View
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $user = Auth::user();
        $meterNumber = '01000';
        $customerName = 'Budi Santoso';
        $zone = 'DIST-A Sektor Utara';
        $tariff = '2A - Rumah Tangga Mandiri';
        $lastReading = 1000.0;

        if ($user && $user->customerProfile) {
            $cp = $user->customerProfile;
            $meterNumber  = $cp->meter_number ?: $meterNumber;
            $customerName = $user->name;
            $zone         = $cp->zone ?: $zone;
            $tariff       = $cp->tariff_category ?: $tariff;
            $lastReading  = (float)($cp->current_meter_value ?: $cp->last_meter_value ?: $lastReading);
        }

        return view('meter.index', [
            'meterNumber'    => $meterNumber,
            'customerName'   => $customerName,
            'zone'           => $zone,
            'tariff'         => $tariff,
            'lastReading'    => $lastReading,
            'suggestedMin'   => $lastReading,
            'suggestedMax'   => $lastReading + 80.0,
            'estimatedCostPerM3' => 7800,
            'adminFee'       => 2600,
            'currentUser'    => session('swc_user'),
            'currentTab'     => 'meter-reading',
        ]);
    }

    /**
     * API: Ambil seluruh daftar laporan meter (untuk admin & store).
     * Menerapkan isolasi kepemilikan data (RBAC):
     * - Tamu (Guest): menerima array kosong demi privasi warga.
     * - Pelanggan (Customer): hanya melihat laporan stand meter miliknya sendiri.
     * - Petugas / Admin: melihat seluruh antrean verifikasi stand meter kota.
     */
    public function apiList(Request $request): JsonResponse
    {
        $user = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);

        // Jika pengunjung belum masuk sesi akun (guest), lindungi privasi seluruh warga kota
        if (!$user) {
            return response()->json([
                'ok'      => true,
                'reports' => [],
            ]);
        }

        $userId = null;

        // Jika user adalah pelanggan biasa (bukan staf/admin), batasi hanya laporannya sendiri
        if (!$user->isStaff()) {
            $userId = $user->id;
        }

        $reports = $this->meterReadingService->getReadings($userId);

        return response()->json([
            'ok'      => true,
            'reports' => MeterReadingResource::collection($reports),
        ]);
    }

    /**
     * API: Simpan laporan pembacaan meteran baru ke MySQL via MeterReadingService.
     */
    public function apiStore(StoreMeterReadingRequest $request): JsonResponse
    {
        $user = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);

        $reading = $this->meterReadingService->createReading($request->validated(), $user);

        return response()->json([
            'ok'      => true,
            'message' => 'Laporan stand meter berhasil dicatat ke sistem pusat.',
            'report'  => new MeterReadingResource($reading),
        ]);
    }

    /**
     * API: Validasi / Persetujuan laporan meter oleh Petugas SCADA via MeterReadingService.
     */
    public function apiValidate(ValidateMeterReadingRequest $request): JsonResponse
    {
        $id     = trim((string)$request->input('id', ''));
        $action = strtoupper(trim((string)$request->input('action', 'APPROVE')));
        $reason = trim((string)$request->input('reason', ''));

        $validator = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);

        $reading = $this->meterReadingService->findReading($id);
        if (!$reading) {
            return response()->json(['ok' => false, 'message' => 'Laporan meter tidak ditemukan.'], 404);
        }

        if (!$validator) {
            return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
        }

        // Perlindungan Kebijakan Otorisasi via MeterReadingPolicy
        if (!Gate::forUser($validator)->allows('validate', $reading)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Hanya petugas SCADA yang berhak memvalidasi laporan meter.',
            ], 403);
        }

        $validated = $this->meterReadingService->validateReading($id, $action, $reason, $validator);

        if ($action === 'APPROVE' || $action === 'TERVERIFIKASI AI') {
            return response()->json([
                'ok'      => true,
                'message' => "Laporan {$validated->report_number} berhasil disetujui!",
                'report'  => new MeterReadingResource($validated),
            ]);
        }

        return response()->json([
            'ok'      => true,
            'message' => "Laporan {$validated->report_number} telah ditolak.",
            'report'  => new MeterReadingResource($validated),
        ]);
    }
}
