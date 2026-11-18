<?php

namespace App\Http\Controllers;

use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Services\BillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BillController extends Controller
{
    public function __construct(protected BillingService $billingService)
    {
    }

    /**
     * Display a listing of the bills for current user or all bills for admin.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isStaff()) {
            $bills = Bill::with(['details', 'latestPayment'])
                ->orderBy('id', 'desc')
                ->paginate($request->input('per_page', 15));
        } else {
            $bills = $this->billingService->getBillHistoryForUser(
                $user->id,
                $request->input('per_page', 10)
            );
        }

        return response()->json([
            'status' => 'success',
            'data' => BillResource::collection($bills),
            'pagination' => [
                'current_page' => $bills->currentPage(),
                'last_page' => $bills->lastPage(),
                'per_page' => $bills->perPage(),
                'total' => $bills->total(),
            ],
        ]);
    }

    /**
     * Get the latest unpaid/active bill for the authenticated user.
     */
    public function active(Request $request): JsonResponse
    {
        $bill = $this->billingService->getUnpaidBillForUser($request->user()->id);

        if (!$bill) {
            return response()->json([
                'status' => 'success',
                'message' => 'Tidak ada tagihan yang belum dibayar.',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => new BillResource($bill),
        ]);
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): JsonResponse
    {
        Gate::authorize('view', $bill);

        $bill->load(['details', 'payments.histories']);

        return response()->json([
            'status' => 'success',
            'data' => new BillResource($bill),
        ]);
    }
}
