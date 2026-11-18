<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_number',
        'bill_id',
        'user_id',
        'amount',
        'payment_method',
        'bank_name',
        'sender_name',
        'sender_account_number',
        'proof_photo_path',
        'reference_number',
        'status',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'verified_at' => 'datetime',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(PaymentHistory::class);
    }
}
