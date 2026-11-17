<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChallengeSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'participant_id',
        'mission_id',
        'is_completed',
        'proof_notes',
        'proof_photo_path',
        'completed_at',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function participant(): BelongsTo
    {
        return $this->belongsTo(ChallengeParticipant::class, 'participant_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(ChallengeMission::class, 'mission_id');
    }
}
