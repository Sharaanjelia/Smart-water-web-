<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreManualPaymentRequest;
use App\Http\Requests\VerifyPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Bill;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PaymentController extends Controller
{
    public function __construct(protected PaymentService $paymentService)
    {
    }

    /**
     * List payments (admin sees all, customer sees own).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin() || $user->isStaff()) {
            $payments = $this->paymentService->listPayments(
                $request->input('status'),
                $request->input('per_page', 15)
            );
        } else {
            $payments = Payment::with(['bill', 'verifier'])
                ->where('user_id', $user->id)
                ->orderBy('id', 'desc')
                ->paginate($request->input('per_page', 10));
        }

        return response()->json([
            'status' => 'success',
            'data' => PaymentResource::collection($payments),
            'pagination' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * Submit a manual payment with proof photo.
     */
    public function store(StoreManualPaymentRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $bill = Bill::findOrFail($validated['bill_id']);

        $payment = $this->paymentService->submitManualPayment(
            $bill,
            $request->user()->id,
            $validated,
            $request->file('proof_photo')
        );

        return response()->json([
            'status' => 'success',
            'message' => 'Bukti pembayaran berhasil diunggah. Menunggu verifikasi dari Admin PDAM.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    /**
     * Show single payment.
     */
    public function show(Payment $payment): JsonResponse
    {
        Gate::authorize('view', $payment);

        $payment->load(['bill.details', 'histories', 'verifier']);

        return response()->json([
            'status' => 'success',
            'data' => new PaymentResource($payment),
        ]);
    }

    /**
     * Admin/Staff verifies or rejects the payment.
     */
    public function verify(VerifyPaymentRequest $request, Payment $payment): JsonResponse
    {
        Gate::authorize('verify', $payment);

        $status = $request->input('status');
        $verifierId = $request->user()->id;

        if ($status === 'VERIFIED') {
            $this->paymentService->verifyPayment($payment, $verifierId, $request->input('notes'));
            $message = 'Pembayaran berhasil diverifikasi. Status tagihan berubah menjadi LUNAS (PAID).';
        } else {
            $this->paymentService->rejectPayment($payment, $verifierId, $request->input('rejection_reason'));
            $message = 'Pembayaran telah ditolak. Status tagihan dikembalikan menjadi UNPAID.';
        }

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => new PaymentResource($payment->fresh(['bill', 'histories', 'verifier'])),
        ]);
    }
}
