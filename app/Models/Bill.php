<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'meter_reading_id',
        'user_id',
        'customer_id',
        'meter_number',
        'billing_period',
        'period_label',
        'tariff_category',
        'bill_type',
        'is_simulation',
        'previous_reading',
        'current_reading',
        'consumption',
        'water_charge',
        'admin_fee',
        'maintenance_fee',
        'penalty_fee',
        'total_amount',
        'due_date',
        'status',
        'tariff_snapshot',
        'paid_at',
    ];

    protected $casts = [
        'previous_reading' => 'decimal:2',
        'current_reading' => 'decimal:2',
        'consumption' => 'decimal:2',
        'water_charge' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'maintenance_fee' => 'decimal:2',
        'penalty_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'is_simulation' => 'boolean',
        'tariff_snapshot' => 'array',
        'paid_at' => 'datetime',
    ];

    public function isOfficial(): bool
    {
        return $this->bill_type === 'OFFICIAL' && !$this->is_simulation;
    }

    public function isSimulation(): bool
    {
        return $this->bill_type === 'SIMULATION' || $this->is_simulation;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(BillDetail::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
