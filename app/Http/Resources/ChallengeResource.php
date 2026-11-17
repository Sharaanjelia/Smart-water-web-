<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $identifier = $this->challenge_code ?: (string)$this->id;

        $missions = $this->relationLoaded('missions')
            ? $this->missions
            : $this->missions()->orderBy('order_index')->get();

        return [
            'id'            => $identifier,
            'challenge_id'  => $this->id,
            'challenge_code'=> $this->challenge_code,
            'title'         => $this->title,
            'slug'          => $this->slug,
            'subtitle'      => $this->subtitle,
            'tagline'       => $this->subtitle ?: $this->title,
            'description'   => $this->description,
            'difficulty'    => $this->level ?? 'Mudah',
            'level'         => $this->level ?? 'Mudah',
            'durationDays'  => (int)$this->duration_days,
            'duration_days' => (int)$this->duration_days,
            'totalXp'       => (int)$this->xp_total,
            'xp_total'      => (int)$this->xp_total,
            'badgeRewardId' => $this->badge_code,
            'badge_code'    => $this->badge_code,
            'badgeTitle'    => $this->badge_title,
            'badge_title'   => $this->badge_title,
            'is_active'     => (bool)$this->is_active,
            'missions'      => ChallengeMissionResource::collection($missions)->resolve(),
        ];
    }
}
