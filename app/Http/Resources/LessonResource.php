<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LessonResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $identifier = $this->lesson_code ?: (string)$this->id;

        return [
            'id'          => $identifier,
            'lesson_id'   => $this->id,
            'lesson_code' => $this->lesson_code,
            'title'       => $this->title,
            'duration'    => $this->duration ?: '3 Menit',
            'readingTime' => $this->duration ?: '3 Menit',
            'order'       => (int)$this->order_index,
            'order_index' => (int)$this->order_index,
            'content'     => $this->content,
        ];
    }
}
