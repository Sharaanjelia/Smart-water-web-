<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'course_code',
        'title',
        'slug',
        'description',
        'level',
        'estimated_minutes',
        'xp_reward',
        'badge_reward',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'estimated_minutes' => 'integer',
        'xp_reward' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AcademyCategory::class, 'category_id');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class)->orderBy('order_index');
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(UserQuizResult::class);
    }
}
