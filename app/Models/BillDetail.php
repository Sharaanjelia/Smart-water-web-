<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'item_type',
        'description',
        'volume',
        'rate',
        'subtotal',
    ];

    protected $casts = [
        'volume' => 'decimal:2',
        'rate' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }
}
