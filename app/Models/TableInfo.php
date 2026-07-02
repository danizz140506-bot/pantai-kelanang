<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * TABLE_INFO model (SDD 4.1) — represents a physical restaurant table and its
 * live status. Operations: checkAvailability(), updateStatus(), assignTable().
 * Supports FR-02, FR-03.
 */
class TableInfo extends Model
{
    protected $table = 'table_info';

    protected $primaryKey = 'table_id';

    public $timestamps = false;

    protected $fillable = [
        'table_number',
        'capacity',
        'status',
    ];

    /** A table may be reserved many times across dates (1 — 0..*). */
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class, 'table_id', 'table_id');
    }

    /** A table may have many orders over time (1 — 0..*). */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'table_id', 'table_id');
    }

    /** checkAvailability() — is this table currently free? */
    public function checkAvailability(): bool
    {
        return $this->status === 'Available';
    }

    /** updateStatus() — set a new table status. */
    public function updateStatus(string $status): bool
    {
        $this->status = $status;

        return $this->save();
    }

    /** assignTable() — mark the table Occupied when a party is seated. */
    public function assignTable(): bool
    {
        return $this->updateStatus('Occupied');
    }
}
