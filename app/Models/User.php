<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * USER model (SDD 4.1) — holds staff account data and authentication details.
 * Operations: login(), logout(), validateCredentials(), getRole() — login/logout
 * are handled through Laravel's Auth facade; getRole() is exposed below.
 *
 * Supports FR-10 (User Login & Role-Based Access Control).
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'username',
        'password',
        'full_name',
        'role',
        'phone_number',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /** Orders taken by this user (waiter). */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'user_id', 'user_id');
    }

    /** getRole() — returns the authenticated user's role. */
    public function getRole(): string
    {
        return $this->role;
    }

    /** Convenience check used by the role middleware. */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}
