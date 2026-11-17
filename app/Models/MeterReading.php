<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MeterReading extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_number',
        'user_id',
        'meter_number',
        'reading',
        'previous_reading',
        'consumption',
        'estimated_cost',
        'customer_name',
        'zone',
        'photo_path',
        'status',
        'validated_by',
        'validated_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'reading' => 'float',
        'previous_reading' => 'float',
        'consumption' => 'float',
        'estimated_cost' => 'float',
        'validated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
