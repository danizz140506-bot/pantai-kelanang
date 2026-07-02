<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * MENU_ITEM model (SDD 4.1) — represents a sellable menu item.
 * Operations: addItem(), updateItem(), checkAvailability(), getPrice().
 * Supports FR-04, FR-07.
 */
class MenuItem extends Model
{
    protected $primaryKey = 'menu_id';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'availability',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'availability' => 'boolean',
        ];
    }

    /** A menu item may appear in many order lines (1 — 0..*). */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'menu_id', 'menu_id');
    }

    /** checkAvailability() — is this item currently sellable? */
    public function checkAvailability(): bool
    {
        return (bool) $this->availability;
    }

    /** getPrice() — current price of the item. */
    public function getPrice(): float
    {
        return (float) $this->price;
    }
}
