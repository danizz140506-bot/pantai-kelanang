<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * CUSTOMER model (SDD 4.1) — holds guest information captured during online
 * reservation. Operations: register(), updateDetails(), getReservationHistory().
 * Supports FR-01.
 */
class Customer extends Model
{
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'name',
        'phone_number',
        'email',
    ];

    /** A customer may make many reservations (1 — 0..*). */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'customer_id', 'customer_id');
    }

    /** getReservationHistory() — all reservations made by this customer. */
    public function getReservationHistory()
    {
        return $this->reservations()->latest('created_at')->get();
    }
}
