<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ChallengeMission extends Model
{
    use HasFactory;

    protected $fillable = [
        'challenge_id',
        'mission_code',
        'order_index',
        'title',
        'xp',
        'tip',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'xp' => 'integer',
    ];

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(ChallengeSubmission::class, 'mission_id');
    }
}
