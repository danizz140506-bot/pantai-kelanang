<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ORDER model (SDD 4.1) — represents a customer order routed to the kitchen.
 * Operations: createOrder(), submitToKDS(), updateStatus(), calculateTotal().
 * Supports FR-04, FR-05, FR-06.
 */
class Order extends Model
{
    protected $primaryKey = 'order_id';

    public $timestamps = false;

    protected $fillable = [
        'table_id',
        'user_id',
        'order_date',
        'status',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'datetime',
            'total_amount' => 'decimal:2',
        ];
    }

    /** Each order is linked to one table. */
    public function table(): BelongsTo
    {
        return $this->belongsTo(TableInfo::class, 'table_id', 'table_id');
    }

    /** Each order is recorded by exactly one user (waiter). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /** An order contains one or more line items (composition, 1 — 1..*). */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    /** Each order produces exactly one payment record (1 — 1). */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    /** updateStatus() — move the order through Preparing → Ready → Served. */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;

        return $this->save();
    }

    /** calculateTotal() — recompute and persist the order total from its items. */
    public function calculateTotal(): float
    {
        $total = $this->orderItems()->sum('subtotal');
        $this->total_amount = $total;
        $this->save();

        return (float) $total;
    }
}
