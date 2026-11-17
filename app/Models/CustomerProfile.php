<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_id',
        'meter_number',
        'tariff_category',
        'phone',
        'address',
        'district',
        'zone',
        'last_meter_value',
        'current_meter_value',
        'wallet_balance',
        'points',
    ];

    protected $casts = [
        'last_meter_value' => 'decimal:2',
        'current_meter_value' => 'decimal:2',
        'wallet_balance' => 'decimal:2',
        'points' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
