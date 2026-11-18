<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_id' => $this->payment_id,
            'previous_status' => $this->previous_status,
            'new_status' => $this->new_status,
            'actor_id' => $this->actor_id,
            'actor_role' => $this->actor_role,
            'action_note' => $this->action_note,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
