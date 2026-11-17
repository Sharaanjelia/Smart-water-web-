<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MeterReadingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statusMap = [
            'Menunggu Validasi' => 'PENDING',
            'PENDING'           => 'PENDING',
            'Terverifikasi AI'  => 'APPROVED',
            'APPROVED'          => 'APPROVED',
            'Ditolak'           => 'REJECTED',
            'REJECTED'          => 'REJECTED',
        ];

        $statusColor = match ($this->status) {
            'Terverifikasi AI', 'APPROVED' => 'emerald',
            'Ditolak', 'REJECTED'          => 'rose',
            default                        => 'amber',
        };

        $photoUrl = null;
        if (!empty($this->photo_path)) {
            $photoUrl = asset($this->photo_path);
        } elseif (!empty($this->image_path)) {
            $photoUrl = asset($this->image_path);
        }

        return [
            'id'              => $this->report_number,
            'reportId'        => $this->id,
            'report_number'   => $this->report_number,
            'meterNumber'     => $this->meter_number,
            'customerName'    => $this->customer_name,
            'customerZone'    => $this->zone ?? 'DIST-A Sektor Utara',
            'ocrReading'      => (float)$this->reading,
            'previousReading' => (float)$this->previous_reading,
            'consumption'     => (float)$this->consumption,
            'estimatedCost'   => (int)$this->estimated_cost,
            'status'          => $this->status,
            'badgeStatus'     => $statusMap[$this->status] ?? 'PENDING',
            'badgeColor'      => $statusColor,
            'submittedAt'     => $this->created_at ? $this->created_at->format('d M Y H:i') . ' WIB' : null,
            'validatedAt'     => $this->validated_at ? $this->validated_at->format('d M Y H:i') . ' WIB' : null,
            'validatedBy'     => $this->validator?->name,
            'rejectionReason' => $this->rejection_reason,
            'photoUrl'        => $photoUrl,
            'notes'           => $this->notes,
        ];
    }
}
