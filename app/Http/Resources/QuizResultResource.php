<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuizResultResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'course_id'   => $this->course_id,
            'course_code' => $this->course?->course_code,
            'score'       => (int)$this->score,
            'is_passed'   => (bool)$this->is_passed,
            'xp_earned'   => (int)$this->xp_earned,
            'completed_at'=> $this->completed_at ? $this->completed_at->toIso8601String() : null,
        ];
    }
}
