<?php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Reporting Component (SDD 3.1) — daily sales report generation for the owner
 * (FR-09): total revenue, transaction count, and the most popular menu items
 * for a selected day, aggregated from the PAYMENT and ORDER_ITEM tables.
 */
class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['date' => ['nullable', 'date']]);
        $date = $request->input('date', today()->toDateString());

        $payments = Payment::where('payment_status', 'Successful')
            ->whereDate('payment_date', $date)
            ->get();

        // Revenue is the full bill value (deposit + balance) = subtotal − discount,
        // since total_amount now stores only the balance collected at the counter.
        $revenue = (float) $payments->sum(fn ($p) => (float) $p->subtotal - (float) $p->discount_amount);
        $transactions = $payments->count();
        $average = $transactions > 0 ? $revenue / $transactions : 0.0;

        $popular = OrderItem::whereIn('order_id', $payments->pluck('order_id'))
            ->select('menu_id', DB::raw('SUM(quantity) as qty'))
            ->groupBy('menu_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->with('menuItem:menu_id,name')
            ->get();

        return view('reports.index', [
            'date' => $date,
            'revenue' => $revenue,
            'transactions' => $transactions,
            'average' => $average,
            'popular' => $popular,
        ]);
    }
}
