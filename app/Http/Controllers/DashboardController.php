<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Routes an authenticated user to their role-specific dashboard (FR-10).
 * Each role lands on the screen relevant to their responsibilities, as
 * defined in the SDD user interface design (Section 6).
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $role = $request->user()->role;

        return match ($role) {
            'Owner'        => view('dashboard.owner'),
            'Waiter'       => view('dashboard.waiter'),
            'Cashier'      => view('dashboard.cashier'),
            'Kitchen Staff' => view('dashboard.kitchen'),
            default        => view('dashboard.owner'),
        };
    }
}
