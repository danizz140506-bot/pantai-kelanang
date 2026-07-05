<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * RESERVATION model (SDD 4.1) — represents a booking and its deposit.
 * Operations: createReservation(), payDeposit(), confirmReservation(),
 * cancelReservation(). Supports FR-01.
 */
class Reservation extends Model
{
    protected $primaryKey = 'reservation_id';

    protected $fillable = [
        'customer_id',
        'table_id',
        'reservation_date',
        'arrival_time',
        'pax',
        'deposit_amount',
        'preorder_items',
        'deposit_status',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'reservation_date' => 'date',
            'deposit_amount' => 'decimal:2',
            'preorder_items' => 'array',
        ];
    }

    /** Each reservation belongs to exactly one customer. */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'customer_id');
    }

    /** Each reservation is for exactly one table. */
    public function table(): BelongsTo
    {
        return $this->belongsTo(TableInfo::class, 'table_id', 'table_id');
    }

    /** createReservation() — create a new booking in the unconfirmed Pending state (SDD 5.2). */
    public static function createReservation(array $attributes): self
    {
        return static::create(array_merge($attributes, [
            'deposit_status' => 'Pending',
            'status' => 'Pending',
        ]));
    }

    /** payDeposit() — mark the 50% deposit as paid once the gateway confirms (FR-01). */
    public function payDeposit(): bool
    {
        $this->deposit_status = 'Paid';

        return $this->save();
    }

    /** confirmReservation() — mark confirmed once the deposit is paid. */
    public function confirmReservation(): bool
    {
        $this->deposit_status = 'Paid';
        $this->status = 'Confirmed';

        return $this->save();
    }

    /** cancelReservation() — cancel the booking. */
    public function cancelReservation(): bool
    {
        $this->status = 'Cancelled';

        return $this->save();
    }
}
