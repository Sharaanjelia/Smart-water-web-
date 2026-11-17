<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChallengeSubmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'participant_id' => $this->participant_id,
            'mission_id'     => $this->mission_id,
            'mission_code'   => $this->mission?->mission_code,
            'is_completed'   => (bool)$this->is_completed,
            'proof_notes'    => $this->proof_notes,
            'proof_photo_url'=> $this->proof_photo_path ? asset('storage/' . $this->proof_photo_path) : null,
            'completed_at'   => $this->completed_at ? $this->completed_at->toIso8601String() : null,
        ];
    }
}
