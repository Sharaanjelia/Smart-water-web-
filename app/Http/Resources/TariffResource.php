<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TariffResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_code' => $this->category_code,
            'category_name' => $this->category_name,
            'description' => $this->description,
            'admin_fee' => (float) $this->admin_fee,
            'maintenance_fee' => (float) $this->maintenance_fee,
            'penalty_flat' => (float) $this->penalty_flat,
            'penalty_percentage' => (float) $this->penalty_percentage,
            'due_day_of_month' => $this->due_day_of_month,
            'is_active' => (bool) $this->is_active,
            'tiers' => TariffTierResource::collection($this->whenLoaded('tiers')),
        ];
    }
}
