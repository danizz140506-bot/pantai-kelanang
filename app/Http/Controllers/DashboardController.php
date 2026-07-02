<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\TableInfo;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Routes an authenticated user to their role-specific dashboard (FR-10).
 * Each role lands on the screen relevant to their responsibilities, as
 * defined in the SDD user interface design (Section 6).
 */
class DashboardController extends Controller
{
    private const ROLES = ['Owner', 'Waiter', 'Cashier', 'Kitchen Staff'];

    public function index(Request $request): View
    {
        return match ($request->user()->role) {
            'Waiter'        => view('dashboard.waiter', [
                'availableTables' => TableInfo::where('status', 'Available')->count(),
                'ordersToday' => Order::whereDate('order_date', today())->count(),
            ]),
            'Cashier'       => view('dashboard.cashier', [
                'awaiting' => Order::whereDoesntHave('payment', fn ($q) => $q->where('payment_status', 'Successful'))->count(),
                'salesToday' => (float) Payment::where('payment_status', 'Successful')
                    ->whereDate('payment_date', today())->get()
                    ->sum(fn ($p) => (float) $p->subtotal - (float) $p->discount_amount),
            ]),
            'Kitchen Staff' => view('dashboard.kitchen', [
                'activeOrders' => Order::whereIn('status', ['Preparing', 'Ready'])->count(),
            ]),
            default         => $this->owner(),   // Owner (SDD 6.6)
        };
    }

    /** Owner dashboard — daily sales report + staff management (SDD 6.6, FR-09/FR-10). */
    private function owner(): View
    {
        $today = today();

        // Today's settled payments drive the headline figures.
        $paidToday = Payment::where('payment_status', 'Successful')
            ->whereDate('payment_date', $today)
            ->get();

        $revenueToday = (float) $paidToday->sum(fn ($p) => (float) $p->subtotal - (float) $p->discount_amount);
        $transactionsToday = $paidToday->count();

        // Last 7 days revenue for the sales chart.
        $chart = collect(range(6, 0))->map(function (int $offset) {
            $day = today()->subDays($offset);
            $revenue = (float) Payment::where('payment_status', 'Successful')
                ->whereDate('payment_date', $day)
                ->get()
                ->sum(fn ($p) => (float) $p->subtotal - (float) $p->discount_amount);

            return ['label' => $day->format('D'), 'date' => $day->format('j M'), 'revenue' => round($revenue, 2)];
        })->values();

        return view('dashboard.owner', [
            'metrics' => [
                'sales' => $revenueToday,
                'orders' => Order::whereDate('order_date', $today)->count(),
                'avgOrder' => $transactionsToday > 0 ? $revenueToday / $transactionsToday : 0.0,
                'activeStaff' => User::count(),
            ],
            'chart' => $chart,
            'active' => User::orderByRaw("FIELD(role, 'Owner','Cashier','Waiter','Kitchen Staff')")->orderBy('full_name')->get(),
            'deactivated' => User::onlyTrashed()->orderBy('full_name')->get(),
            'roles' => self::ROLES,
        ]);
    }
}
