<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TariffTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'tariff_id',
        'tier_number',
        'tier_name',
        'min_consumption',
        'max_consumption',
        'rate_per_m3',
    ];

    protected $casts = [
        'tariff_id' => 'integer',
        'tier_number' => 'integer',
        'min_consumption' => 'integer',
        'max_consumption' => 'integer',
        'rate_per_m3' => 'decimal:2',
    ];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }
}
