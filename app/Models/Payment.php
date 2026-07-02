<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PAYMENT model (SDD 4.1) — represents a billing transaction.
 * Operations: generateBill(), applyDiscount(), processPayment(), getReceipt().
 * Supports FR-07, FR-08.
 */
class Payment extends Model
{
    protected $primaryKey = 'payment_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'subtotal',
        'discount_amount',
        'total_amount',
        'payment_method',
        'payment_status',
        'payment_date',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    /** Each payment settles exactly one order (1 — 1). */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /** applyDiscount() — apply a discount and recompute the final total. */
    public function applyDiscount(float $discount): void
    {
        $this->discount_amount = $discount;
        $this->total_amount = (float) $this->subtotal - $discount;
    }
}
