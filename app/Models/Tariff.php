<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_code',
        'category_name',
        'description',
        'admin_fee',
        'maintenance_fee',
        'penalty_flat',
        'penalty_percentage',
        'due_day_of_month',
        'is_active',
        'is_simulation',
        'approval_status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'admin_fee' => 'decimal:2',
        'maintenance_fee' => 'decimal:2',
        'penalty_flat' => 'decimal:2',
        'penalty_percentage' => 'decimal:2',
        'due_day_of_month' => 'integer',
        'is_active' => 'boolean',
        'is_simulation' => 'boolean',
        'approved_at' => 'datetime',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(TariffTier::class)->orderBy('tier_number');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isOfficial(): bool
    {
        return !$this->is_simulation && $this->approval_status === 'APPROVED_PDAM';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'APPROVED_PDAM';
    }
}
