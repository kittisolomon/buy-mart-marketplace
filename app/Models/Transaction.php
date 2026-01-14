<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    public $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'payment_id',
        'type',
        'status',
        'amount',
        'currency_code',
        'reference_id',
        'is_reconciled',
    ];

    protected $casts = [
        'is_reconciled' => 'boolean',
        'amount' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
} 