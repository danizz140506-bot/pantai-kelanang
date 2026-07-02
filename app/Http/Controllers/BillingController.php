<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Reservation;
use App\Services\ChipService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Billing & Payment Component (SDD 3.1) — automated bill generation (FR-07),
 * optional discount, and multi-method payment processing (FR-08). On a
 * successful payment the bill is settled and the table is released (FR-08).
 */
class BillingController extends Controller
{
    /** Service tax rate applied to the bill (SST 6%, FR-07 — applicable taxes). */
    private const TAX_RATE = 0.06;

    /** Orders awaiting payment (no successful payment yet). */
    public function index(): View
    {
        $orders = Order::with('table')
            ->whereDoesntHave('payment', fn ($q) => $q->where('payment_status', 'Successful'))
            ->orderByDesc('order_id')
            ->get();

        return view('billing.index', ['orders' => $orders]);
    }

    /** Billing screen for a single order — itemised bill, deposit credit, discount. */
    public function show(Order $order): View
    {
        $this->ensureUnpaid($order);
        $order->load(['orderItems.menuItem', 'table']);

        $reservation = $this->reservationFor($order);

        return view('billing.show', [
            'order' => $order,
            'deposit' => $reservation ? (float) $reservation->deposit_amount : 0.0,
            'taxRate' => self::TAX_RATE,
        ]);
    }

    /** Generate the bill, credit any deposit, process the balance, and settle (FR-07, FR-08). */
    public function store(Request $request, Order $order, ChipService $chip): RedirectResponse
    {
        $this->ensureUnpaid($order);

        $data = $request->validate([
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:Cash,Card,E-Wallet'],
        ]);

        $subtotal = (float) $order->total_amount;
        $discount = min((float) ($data['discount_amount'] ?? 0), $subtotal);

        // Service tax (SST 6%) is charged on the discounted amount (FR-07).
        $tax = round(($subtotal - $discount) * self::TAX_RATE, 2);

        // Credit the deposit already paid online for this table's reservation (FR-01).
        $reservation = $this->reservationFor($order);
        $deposit = $reservation ? (float) $reservation->deposit_amount : 0.0;

        // The balance is what the cashier actually collects at the counter.
        // (tax is derivable on the receipt as (subtotal − discount) × TAX_RATE.)
        $balance = round(max(0, $subtotal - $discount + $tax - $deposit), 2);

        // Cash (or a fully-covered balance) settles directly; card / e-wallet use CHIP.
        if ($data['payment_method'] === 'Cash' || $balance <= 0.0) {
            $status = 'Successful';
        } else {
            $status = $chip->charge($balance, $data['payment_method'], ['order_id' => $order->order_id])['status'];
        }

        DB::transaction(function () use ($order, $reservation, $subtotal, $discount, $balance, $data, $status) {
            Payment::updateOrCreate(
                ['order_id' => $order->order_id],
                [
                    'subtotal' => $subtotal,          // full bill before discount
                    'discount_amount' => $discount,   // promotional discount only
                    'total_amount' => $balance,       // balance collected (deposit = subtotal − discount − balance)
                    'payment_method' => $data['payment_method'],
                    'payment_status' => $status,
                    'payment_date' => now(),
                ]
            );

            if ($status === 'Successful') {
                $order->updateStatus('Served');            // meal complete
                $order->table?->updateStatus('Available'); // FR-08: free the table
                $reservation?->update(['status' => 'Completed']); // deposit consumed
            }
        });

        if ($status !== 'Successful') {
            return back()->with('error', 'Payment failed. Please try again or use another method.');
        }

        return redirect()->route('billing.receipt', $order)->with('status', 'paid');
    }

    /** Printable receipt for a settled order. */
    public function receipt(Order $order): View
    {
        abort_unless($order->payment && $order->payment->payment_status === 'Successful', 404);
        $order->load(['orderItems.menuItem', 'table', 'payment', 'user']);

        return view('billing.receipt', ['order' => $order]);
    }

    /**
     * The active confirmed reservation whose paid deposit applies to this
     * order's table (matched by table). Returns null for walk-in orders.
     */
    private function reservationFor(Order $order): ?Reservation
    {
        return Reservation::where('table_id', $order->table_id)
            ->where('status', 'Confirmed')
            ->where('deposit_status', 'Paid')
            ->orderBy('reservation_date')
            ->orderBy('reservation_id')
            ->first();
    }

    /** Guard: an already-settled order cannot be billed again. */
    private function ensureUnpaid(Order $order): void
    {
        abort_if(
            $order->payment && $order->payment->payment_status === 'Successful',
            404,
            'This order has already been paid.'
        );
    }
}
