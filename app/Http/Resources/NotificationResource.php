<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $timeStr = null;
        if ($this->created_at) {
            $diffInHours = $this->created_at->diffInHours(now());
            if ($diffInHours < 24) {
                $timeStr = 'Hari ini, ' . $this->created_at->format('H:i') . ' WIB';
            } else {
                $timeStr = $this->created_at->format('d M Y H:i') . ' WIB';
            }
        }

        return [
            'id'         => $this->id,
            'user_id'    => $this->user_id,
            'type'       => $this->type,
            'title'      => $this->title,
            'desc'       => $this->desc,
            'link'       => $this->link,
            'isRead'     => (bool)$this->is_read,
            'is_read'    => (bool)$this->is_read,
            'time'       => $timeStr,
            'read_at'    => $this->read_at ? $this->read_at->format('Y-m-d H:i') . ' WIB' : null,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
        ];
    }
}
