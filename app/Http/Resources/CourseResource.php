<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $identifier = $this->course_code ?: (string)$this->id;

        $lessons = $this->relationLoaded('lessons')
            ? $this->lessons
            : $this->lessons()->orderBy('order_index')->get();

        return [
            'id'                => $identifier,
            'course_id'         => $this->id,
            'course_code'       => $this->course_code,
            'title'             => $this->title,
            'slug'              => $this->slug,
            'category'          => $this->category?->slug ?? 'konservasi-dasar',
            'categoryName'      => $this->category?->name ?? 'Konservasi & Efisiensi Air',
            'description'       => $this->description,
            'difficulty'        => $this->level ?? 'Pemula',
            'level'             => $this->level ?? 'Pemula',
            'estimatedMinutes'  => (int)$this->estimated_minutes,
            'estimated_minutes' => (int)$this->estimated_minutes,
            'readingTime'       => ($this->estimated_minutes ? $this->estimated_minutes . ' Menit' : '15 Menit'),
            'xpReward'          => (int)$this->xp_reward,
            'xp_reward'         => (int)$this->xp_reward,
            'badgeReward'       => $this->badge_reward,
            'badge_reward'      => $this->badge_reward,
            'is_published'      => (bool)$this->is_published,
            'lessons'           => LessonResource::collection($lessons)->resolve(),
        ];
    }
}
