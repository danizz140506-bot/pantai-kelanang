<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * User Management Component (SDD 3.1, 6.6) — the owner creates, edits, and
 * deactivates staff accounts and assigns roles (FR-10). Deactivation is a soft
 * delete so historical records referencing the user are preserved.
 */
class UserController extends Controller
{
    private const ROLES = ['Owner', 'Waiter', 'Cashier', 'Kitchen Staff'];

    public function index(): View
    {
        return view('users.index', [
            'active' => User::orderBy('role')->orderBy('full_name')->get(),
            'deactivated' => User::onlyTrashed()->orderBy('full_name')->get(),
            'roles' => self::ROLES,
        ]);
    }

    /** Create a new staff account (FR-10 — a unique user ID is assigned on insert). */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(self::ROLES)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create($data);

        return back()->with('status', "Staff account '{$data['username']}' created.");
    }

    /** Update an existing staff account. */
    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($user->user_id, 'user_id')],
            'full_name' => ['required', 'string', 'max:255'],
            'role' => ['required', Rule::in(self::ROLES)],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        // Only change the password when a new one is provided.
        if (blank($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        return back()->with('status', "Staff account '{$user->username}' updated.");
    }

    /** Deactivate (soft delete) a staff account. */
    public function deactivate(Request $request, User $user): RedirectResponse
    {
        if ($user->user_id === $request->user()->user_id) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        if ($user->role === 'Owner' && User::where('role', 'Owner')->count() <= 1) {
            return back()->with('error', 'Cannot deactivate the only Owner account.');
        }

        $user->delete();

        return back()->with('status', "Staff account '{$user->username}' deactivated.");
    }

    /** Reactivate a previously deactivated account. */
    public function reactivate(User $user): RedirectResponse
    {
        $user->restore();

        return back()->with('status', "Staff account '{$user->username}' reactivated.");
    }
}
