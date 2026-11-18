<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_number' => $this->payment_number,
            'bill_id' => $this->bill_id,
            'user_id' => $this->user_id,
            'amount' => (float) $this->amount,
            'payment_method' => $this->payment_method,
            'bank_name' => $this->bank_name,
            'sender_name' => $this->sender_name,
            'sender_account_number' => $this->sender_account_number,
            'proof_photo_url' => $this->proof_photo_path ? asset('storage/' . $this->proof_photo_path) : null,
            'reference_number' => $this->reference_number,
            'status' => $this->status,
            'verified_by' => $this->verified_by,
            'verified_at' => $this->verified_at?->toIso8601String(),
            'rejection_reason' => $this->rejection_reason,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toIso8601String(),
            'histories' => PaymentHistoryResource::collection($this->whenLoaded('histories')),
        ];
    }
}
