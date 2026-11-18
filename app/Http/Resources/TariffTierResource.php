<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TariffTierResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tier_number' => $this->tier_number,
            'tier_name' => $this->tier_name,
            'min_consumption' => $this->min_consumption,
            'max_consumption' => $this->max_consumption,
            'rate_per_m3' => (float) $this->rate_per_m3,
        ];
    }
}
