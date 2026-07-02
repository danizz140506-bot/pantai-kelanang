<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ORDER_ITEM model (SDD 4.1) — represents a single line within an order.
 * Operations: addLineItem(), calculateSubtotal(), updateQuantity().
 * Supports FR-04.
 */
class OrderItem extends Model
{
    protected $primaryKey = 'order_item_id';

    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'menu_id',
        'quantity',
        'subtotal',
        'special_instructions',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
        ];
    }

    /** Each line item belongs to exactly one order. */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    /** Each order line refers to exactly one menu item. */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_id', 'menu_id');
    }

    /** calculateSubtotal() — quantity × menu item price. */
    public function calculateSubtotal(): float
    {
        $this->subtotal = $this->quantity * $this->menuItem->getPrice();

        return (float) $this->subtotal;
    }
}
