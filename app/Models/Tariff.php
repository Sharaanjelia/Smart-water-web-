<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'admin_fee' => 'decimal:2',
        'maintenance_fee' => 'decimal:2',
        'penalty_flat' => 'decimal:2',
        'penalty_percentage' => 'decimal:2',
        'due_day_of_month' => 'integer',
        'is_active' => 'boolean',
    ];

    public function tiers(): HasMany
    {
        return $this->hasMany(TariffTier::class)->orderBy('tier_number');
    }
}
