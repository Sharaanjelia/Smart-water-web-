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
            'address'         => $this->address,
            'location'        => trim(($this->district ? $this->district . ' - ' : '') . $this->address),
            'status'          => $this->status,
            'priority'        => $this->priority,
            'userName'        => $this->reporter_name,
            'userPhone'       => $this->contact_phone,
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
