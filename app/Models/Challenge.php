<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_code',
        'title',
        'slug',
        'subtitle',
        'description',
        'level',
        'duration_days',
        'xp_total',
        'badge_code',
        'badge_title',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'duration_days' => 'integer',
        'xp_total' => 'integer',
    ];

    public function missions(): HasMany
    {
        return $this->hasMany(ChallengeMission::class)->orderBy('order_index');
    }

    public function participants(): HasMany
    {
        return $this->hasMany(ChallengeParticipant::class);
    }
}
