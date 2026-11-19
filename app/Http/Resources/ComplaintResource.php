<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Ambil attachment pertama jika ada
        $attachment = $this->relationLoaded('attachments')
            ? $this->attachments->first()
            : $this->attachments()->first();

        $imageUrl = null;
        if ($attachment && !empty($attachment->file_path)) {
            $imageUrl = asset('storage/' . $attachment->file_path);
        }

        // Ambil histori perkembangan tiket
        $histories = $this->relationLoaded('histories')
            ? $this->histories
            : $this->histories()->orderBy('id')->get();

        $updates = $histories->map(function ($h) {
            return [
                'timestamp' => $h->created_at ? $h->created_at->format('d M Y H:i') . ' WIB' : null,
                'status'    => $h->status,
                'note'      => $h->note,
                'actor'     => $h->actor_name,
            ];
        })->values()->toArray();

        if (empty($updates)) {
            $updates = [
                [
                    'timestamp' => $this->created_at ? $this->created_at->format('d M Y H:i') . ' WIB' : now()->format('d M Y H:i') . ' WIB',
                    'status'    => $this->status,
                    'note'      => 'Laporan tercatat di sistem pusat layanan.',
                    'actor'     => 'Sistem Otomatis',
                ],
            ];
        }

        $categorySlug = $this->category?->slug ?? 'pipa_bocor';
        $categoryName = $this->category?->name ?? 'Pipa Bocor / Pecah';

        // ---------------------------------------------------------------------
        // PROTEKSI PII (PRIVAT & PUBLIK)
        // PII lengkap hanya dapat dilihat oleh:
        // 1. Pemilik pengaduan ($this->user_id === $currentUser->id)
        // 2. Admin PDAM ($currentUser->isAdmin())
        // 3. Petugas Lapangan yang ditugaskan resmi ($this->assigned_to === $currentUser->id)
        // ---------------------------------------------------------------------
        $currentUser = $request?->user() ?? \Illuminate\Support\Facades\Auth::user();
        if (!$currentUser && function_exists('session') && session()->has('swc_user.id')) {
            $currentUser = \App\Models\User::find(session('swc_user.id'));
        }

        $canViewFullPii = false;
        if ($currentUser) {
            if ($currentUser->isAdmin()) {
                $canViewFullPii = true;
            } elseif ($this->user_id && (int)$currentUser->id === (int)$this->user_id) {
                $canViewFullPii = true;
            } elseif ($currentUser->isFieldOfficer() && (int)$this->assigned_to === (int)$currentUser->id) {
                $canViewFullPii = true;
            }
        }

        $userName  = $this->reporter_name;
        $userPhone = $this->contact_phone;
        $address   = $this->address;
        $location  = trim(($this->district ? $this->district . ' - ' : '') . $this->address);

        if (!$canViewFullPii) {
            // Mask Nama Lengkap Pelapor (contoh: "Budi Santoso" -> "B**i S*****o")
            if (!empty($this->reporter_name)) {
                $words = explode(' ', trim($this->reporter_name));
                $maskedWords = [];
                foreach ($words as $w) {
                    $len = mb_strlen($w);
                    if ($len <= 2) {
                        $maskedWords[] = mb_substr($w, 0, 1) . '*';
                    } else {
                        $maskedWords[] = mb_substr($w, 0, 1) . str_repeat('*', max(1, $len - 2)) . mb_substr($w, -1, 1);
                    }
                }
                $userName = implode(' ', $maskedWords);
            } else {
                $userName = 'Warga Kota';
            }

            // Mask Nomor HP / WhatsApp
            if (!empty($this->contact_phone)) {
                $cleanPhone = preg_replace('/[^0-9]/', '', $this->contact_phone);
                if (strlen($cleanPhone) >= 8) {
                    $userPhone = substr($cleanPhone, 0, 4) . '-****-' . substr($cleanPhone, -4);
                } else {
                    $userPhone = '08**-****-****';
                }
            } else {
                $userPhone = '08**-****-****';
            }

            // Mask Alamat Lengkap
            $address  = $this->district ? "Wilayah {$this->district} (Detail Disamarkan)" : "Kota Payakumbuh";
            $location = $this->district ?: "Kota Payakumbuh";
        }

        return [
            'id'              => $this->ticket_number,
            'ticketId'        => $this->ticket_number,
            'ticket_id'       => $this->id,
            'title'           => $this->title,
            'category'        => $categorySlug,
            'categoryName'    => $categoryName,
            'categoryBadge'   => 'bg-rose-500/20 text-rose-300 border border-rose-500/30',
            'desc'            => $this->description,
            'description'     => $this->description,
            'district'        => $this->district,
            'address'         => $address,
            'location'        => $location,
            'status'          => $this->status,
            'priority'        => $this->priority,
            'userName'        => $userName,
            'userPhone'       => $userPhone,
            'imageUrl'        => $imageUrl,
            'hasPhoto'        => !empty($imageUrl),
            'assignedTo'      => $this->assignedStaff?->name,
            'assignedStaffId' => $this->assigned_to,
            'assignedAt'      => $this->assigned_at ? $this->assigned_at->format('Y-m-d H:i') . ' WIB' : null,
            'resolvedAt'      => $this->resolved_at ? $this->resolved_at->format('Y-m-d H:i') . ' WIB' : null,
            'resolutionNotes' => $this->resolution_notes,
            'rejectionReason' => $this->rejection_reason,
            'createdAt'       => $this->created_at ? $this->created_at->format('Y-m-d H:i') . ' WIB' : null,
            'updatedAt'       => $this->updated_at ? $this->updated_at->format('Y-m-d H:i') . ' WIB' : null,
            'updates'         => $updates,
        ];
    }
}
