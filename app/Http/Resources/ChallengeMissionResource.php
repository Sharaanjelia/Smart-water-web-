<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeMissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $identifier = $this->mission_code ?: (string)$this->id;

        return [
            'id'           => $identifier,
            'mission_id'   => $this->id,
            'mission_code' => $this->mission_code,
            'challenge_id' => $this->challenge_id,
            'title'        => $this->title,
            'description'  => $this->tip ?? $this->title,
            'tip'          => $this->tip,
            'xp'           => (int)$this->xp,
            'order'        => (int)$this->order_index,
            'order_index'  => (int)$this->order_index,
        ];
    }
}
