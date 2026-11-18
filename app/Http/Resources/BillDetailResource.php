<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_type' => $this->item_type,
            'description' => $this->description,
            'volume' => (float) $this->volume,
            'rate' => (float) $this->rate,
            'subtotal' => (float) $this->subtotal,
        ];
    }
}
