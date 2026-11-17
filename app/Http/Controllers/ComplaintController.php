<?php

namespace App\Http\Controllers;

use App\Http\Requests\Complaint\AssignComplaintRequest;
use App\Http\Requests\Complaint\ResolveComplaintRequest;
use App\Http\Requests\Complaint\RespondComplaintRequest;
use App\Http\Requests\Complaint\StoreComplaintRequest;
use App\Http\Requests\Complaint\UpdateComplaintStatusRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use App\Models\User;
use App\Services\ComplaintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class ComplaintController extends Controller
{
    public function __construct(
        protected ComplaintService $complaintService
    ) {}

    /**
     * Format Model Complaint Eloquent via ComplaintResource (backward-compatible static helper).
     */
    public static function formatTicket(Complaint $complaint): array
    {
        return (new ComplaintResource($complaint))->resolve();
    }

    /**
     * Ambil daftar seluruh tiket pengaduan aktif.
     */
    public function index(Request $request): JsonResponse
    {
        $status   = $request->filled('status') ? $request->input('status') : null;
        $district = $request->filled('district') ? $request->input('district') : null;
        $userId   = null;

        $user = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        if ($request->boolean('mine') && $user) {
            $userId = $user->id;
        }

        $complaints = $this->complaintService->getComplaints($status, $district, $userId);

        return response()->json([
            'ok'         => true,
            'complaints' => ComplaintResource::collection($complaints),
        ]);
    }

    /**
     * Buat tiket pengaduan baru ke database MySQL via ComplaintService.
     */
    public function store(StoreComplaintRequest $request): JsonResponse
    {
        $user = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);

        $complaint = $this->complaintService->createComplaint($request->validated(), $user);
        $allComplaints = $this->complaintService->getComplaints();

        return response()->json([
            'ok'         => true,
            'ticket'     => new ComplaintResource($complaint),
            'complaints' => ComplaintResource::collection($allComplaints),
        ]);
    }

    /**
     * Endpoint simulasi perkembangan tiket pengaduan otomatis.
     */
    public function simulateProgress(Request $request): JsonResponse
    {
        $ticketId = $request->input('ticketId');
        $complaint = $this->complaintService->findComplaint($ticketId);

        if (!$complaint) {
            return response()->json([
                'ok'      => false,
                'message' => 'Tiket pengaduan tidak ditemukan di sistem database.',
            ], 404);
        }

        $nextStatus = match ($complaint->status) {
            'BARU'                  => 'DIPROSES',
            'DIPROSES'              => 'MENUNGGU TINDAK LANJUT',
            'MENUNGGU TINDAK LANJUT'=> 'SELESAI',
            default                 => 'SELESAI',
        };

        $note = match ($nextStatus) {
            'DIPROSES'              => 'Petugas TRC PDAM telah ditugaskan dan meluncur ke lokasi kendala.',
            'MENUNGGU TINDAK LANJUT'=> 'Tim teknis sedang melakukan penggantian sparepart katup pipa distribusi.',
            'SELESAI'               => 'Perbaikan selesai tuntas. Aliran air kembali mengalir jernih dan lancar.',
            default                 => 'Pembaruan status operasional lapangan.',
        };

        $actor = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        $updated = $this->complaintService->updateStatus($complaint->id, $nextStatus, $note, $actor);

        return response()->json([
            'ok'            => true,
            'message'       => "Status tiket #{$updated->ticket_number} berhasil diperbarui menjadi \"{$nextStatus}\"!",
            'updatedTicket' => new ComplaintResource($updated),
        ]);
    }

    /**
     * Perbarui status tiket oleh Petugas / Admin SCADA via ComplaintService.
     */
    public function updateStatus(UpdateComplaintStatusRequest $request, string $id): JsonResponse
    {
        $complaint = $this->complaintService->findComplaint($id);
        if (!$complaint) {
            return response()->json(['ok' => false, 'message' => 'Tiket pengaduan tidak ditemukan.'], 404);
        }

        $actor = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        if (!$actor) {
            return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
        }

        if (!Gate::forUser($actor)->allows('update', $complaint)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Anda tidak memiliki wewenang memperbarui tiket yang ditugaskan kepada petugas lain.',
            ], 403);
        }

        $status = $request->input('status', 'DIPROSES');
        $note   = $request->input('note');

        $updated = $this->complaintService->updateStatus($id, $status, $note, $actor);

        return response()->json([
            'ok'            => true,
            'message'       => "Tiket {$updated->ticket_number} berhasil diperbarui ke status \"{$status}\"!",
            'updatedTicket' => new ComplaintResource($updated),
        ]);
    }

    /**
     * Lihat detail satu tiket pengaduan dengan validasi wewenang akses.
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $complaint = $this->complaintService->findComplaint($id);
        if (!$complaint) {
            return response()->json(['ok' => false, 'error' => 'Tiket pengaduan tidak ditemukan.'], 404);
        }

        $user = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);

        // Perlindungan Data Ownership (Anti-IDOR) via ComplaintPolicy:
        if (!Gate::forUser($user)->allows('view', $complaint)) {
            if (!$user) {
                return response()->json([
                    'ok'    => false,
                    'error' => 'Autentikasi diperlukan: Silakan masuk untuk melihat tiket privat ini.',
                ], 401);
            }

            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Anda tidak memiliki wewenang melihat tiket milik pengguna lain.',
            ], 403);
        }

        return response()->json([
            'ok'        => true,
            'complaint' => new ComplaintResource($complaint),
        ]);
    }

    /**
     * Penugasan petugas/teknisi TRC untuk menangani pengaduan via ComplaintService.
     */
    public function assign(AssignComplaintRequest $request, string $id): JsonResponse
    {
        $actor = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        $complaint = $this->complaintService->findComplaint($id);
        if (!$complaint) {
            return response()->json(['ok' => false, 'error' => 'Tiket pengaduan tidak ditemukan.'], 404);
        }

        if (!$actor) {
            return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
        }

        if (!Gate::forUser($actor)->allows('assign', $complaint)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Anda tidak memiliki wewenang menugaskan tiket yang menjadi tanggung jawab petugas lain.',
            ], 403);
        }

        $staffId = (int)$request->input('staff_id', $actor?->id);
        $technician = User::find($staffId) ?? $actor;

        $updated = $this->complaintService->assignTechnician(
            $id,
            $staffId,
            $request->input('note'),
            $actor
        );

        return response()->json([
            'ok'            => true,
            'message'       => "Tiket berhasil ditugaskan kepada {$technician->name}.",
            'updatedTicket' => new ComplaintResource($updated),
        ]);
    }

    /**
     * Petugas memberikan tanggapan/catatan penanganan pada tiket via ComplaintService.
     */
    public function respond(RespondComplaintRequest $request, string $id): JsonResponse
    {
        $actor = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        $complaint = $this->complaintService->findComplaint($id);
        if (!$complaint) {
            return response()->json(['ok' => false, 'error' => 'Tiket pengaduan tidak ditemukan.'], 404);
        }

        if (!$actor) {
            return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
        }

        if (!Gate::forUser($actor)->allows('respond', $complaint)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Anda tidak memiliki wewenang menanggapi tiket yang ditugaskan kepada petugas lain.',
            ], 403);
        }

        $note = trim((string)$request->input('note', $request->input('message', '')));

        $updated = $this->complaintService->addResponse($id, $note, $actor);

        return response()->json([
            'ok'            => true,
            'message'       => 'Tanggapan berhasil ditambahkan ke riwayat penanganan tiket.',
            'updatedTicket' => new ComplaintResource($updated),
        ]);
    }

    /**
     * Menyelesaikan atau menolak tiket pengaduan secara tuntas via ComplaintService.
     */
    public function resolve(ResolveComplaintRequest $request, string $id): JsonResponse
    {
        $actor = Auth::user() ?? (session('swc_user.id') ? User::find(session('swc_user.id')) : null);
        $complaint = $this->complaintService->findComplaint($id);
        if (!$complaint) {
            return response()->json(['ok' => false, 'error' => 'Tiket pengaduan tidak ditemukan.'], 404);
        }

        if (!$actor) {
            return response()->json(['ok' => false, 'error' => 'Autentikasi diperlukan.'], 401);
        }

        if (!Gate::forUser($actor)->allows('resolve', $complaint)) {
            return response()->json([
                'ok'    => false,
                'error' => 'Akses ditolak: Anda tidak memiliki wewenang menyelesaikan tiket yang ditugaskan kepada petugas lain.',
            ], 403);
        }

        $status = strtoupper((string)$request->input('status', 'SELESAI'));

        if ($status === 'DITOLAK') {
            $updated = $this->complaintService->rejectComplaint(
                $id,
                $request->input('rejection_reason'),
                $actor
            );

            return response()->json([
                'ok'            => true,
                'message'       => "Tiket {$updated->ticket_number} telah ditolak.",
                'updatedTicket' => new ComplaintResource($updated),
            ]);
        }

        $updated = $this->complaintService->resolveComplaint(
            $id,
            $request->input('resolution_notes', $request->input('note')),
            $actor
        );

        return response()->json([
            'ok'            => true,
            'message'       => "Tiket {$updated->ticket_number} berhasil diselesaikan.",
            'updatedTicket' => new ComplaintResource($updated),
        ]);
    }
}
