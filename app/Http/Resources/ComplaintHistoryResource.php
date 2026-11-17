<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'timestamp' => $this->created_at ? $this->created_at->format('d M Y H:i') . ' WIB' : null,
            'status'    => $this->status,
            'note'      => $this->note,
            'actor'     => $this->actor_name,
            'actor_id'  => $this->actor_user_id,
        ];
    }
}
