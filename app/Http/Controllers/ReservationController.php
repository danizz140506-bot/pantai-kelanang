<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\MenuItem;
use App\Models\Reservation;
use App\Models\TableInfo;
use App\Services\ChipService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Reservation Component (SDD 3.1) — public, guest-facing online table
 * reservation with a 50% deposit paid through CHIP (FR-01). Uses the
 * redirect-and-verify flow: the customer pays on the CHIP hosted checkout,
 * is redirected back, and the server confirms the payment before the
 * reservation is created and the table marked Reserved.
 */
class ReservationController extends Controller
{
    /** The restaurant trades 8:00 AM – 10:00 PM. */
    private const OPEN_TIME = '08:00';

    private const CLOSE_TIME = '22:00';

    /** Bookings open today and up to this many days ahead. */
    private const MAX_ADVANCE_DAYS = 2;

    /** Public reservation form. */
    public function create(): View
    {
        $menu = MenuItem::where('availability', true)
            ->orderBy('category')->orderBy('name')->get();

        $menuFlat = $menu->map(fn (MenuItem $m) => [
            'menu_id' => $m->menu_id,
            'name' => $m->name,
            'price' => (float) $m->price,
            'category' => $m->category,
        ])->values();

        $tables = TableInfo::orderBy('table_number')
            ->get(['table_id', 'table_number', 'capacity', 'status']);

        return view('reservations.create', [
            'dateOptions' => $this->dateOptions(),
            'timeOptions' => $this->timeOptions(),
            'menu' => $menu->groupBy('category'),
            'menuFlat' => $menuFlat,
            'tables' => $tables,
        ]);
    }

    /** Validate the booking, hold it in the session, and send the guest to CHIP. */
    public function store(Request $request, ChipService $chip): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'reservation_date' => ['required', 'date', 'after_or_equal:today', 'before_or_equal:'.now()->addDays(self::MAX_ADVANCE_DAYS)->toDateString()],
            'arrival_time' => ['required', 'date_format:H:i', 'after_or_equal:'.self::OPEN_TIME, 'before_or_equal:'.self::CLOSE_TIME],
            'pax' => ['required', 'integer', 'min:1', 'max:6'],
            'table_id' => ['required', 'integer', 'exists:table_info,table_id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menu_items,menu_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        // checkAvailability() on the selected table — it must be free and large
        // enough for the party (SDD 5.2). Otherwise: "No table available".
        $table = TableInfo::find($data['table_id']);
        if (! $table || ! $table->checkAvailability() || $table->capacity < $data['pax']) {
            return back()->withInput()->with('error', 'No table available: the selected table is no longer free for that party size. Please choose another.');
        }

        // Compute the deposit as 50% of the pre-ordered total (server-side).
        $total = 0.0;
        foreach ($data['items'] as $line) {
            $item = MenuItem::find($line['menu_id']);
            if ($item && $item->checkAvailability()) {
                $total += $line['quantity'] * $item->getPrice();
            }
        }
        $deposit = round($total * 0.5, 2);

        if ($deposit <= 0) {
            return back()->withInput()->with('error', 'Please select at least one menu item to calculate the deposit.');
        }

        // Match the customer by phone (create if none), then create the RESERVATION
        // record up front in the Pending state — before the deposit is paid (SDD 5.2).
        $reservation = DB::transaction(function () use ($data, $table, $deposit) {
            $customer = Customer::firstOrCreate(
                ['phone_number' => $data['phone_number']],
                ['name' => $data['name'], 'email' => $data['email'] ?? null],
            );

            return Reservation::createReservation([
                'customer_id' => $customer->customer_id,
                'table_id' => $table->table_id,
                'reservation_date' => $data['reservation_date'],
                'arrival_time' => $data['arrival_time'],
                'pax' => $data['pax'],
                'deposit_amount' => $deposit,
            ]);
        });

        // Invoke payDeposit() through the gateway. Without live CHIP keys, simulate.
        if (! $chip->isConfigured()) {
            return redirect()->route('reservations.return', ['reservation' => $reservation->reservation_id]);
        }

        $purchase = $chip->createPurchase(
            $deposit,
            ['name' => $data['name'], 'phone' => $data['phone_number'], 'email' => $data['email'] ?? ''],
            route('reservations.return', ['reservation' => $reservation->reservation_id]),
            route('reservations.return', ['reservation' => $reservation->reservation_id, 'failed' => 1]),
        );

        session(['reservation_payment_pid' => $purchase['id']]);

        return redirect()->away($purchase['checkout_url']);
    }

    /**
     * Redirect-return handler: verify the deposit payment, then confirm the
     * reservation. On failure the reservation is left Pending (SDD 5.2 alt flow).
     */
    public function return(Request $request, Reservation $reservation, ChipService $chip): View
    {
        // Already settled — show the confirmation.
        if ($reservation->deposit_status === 'Paid') {
            return view('reservations.result', ['ok' => true, 'reservation' => $reservation->load(['table', 'customer'])]);
        }

        // Explicit failure redirect from the gateway → leave the reservation Pending.
        if ($request->boolean('failed')) {
            return view('reservations.result', ['ok' => false]);
        }

        // Verify the deposit was actually paid before confirming anything.
        if (! $chip->isConfigured()) {
            $paid = true; // development simulation — no live gateway
        } else {
            $pid = session('reservation_payment_pid');
            $paid = $pid ? $chip->getPurchase($pid)['paid'] : false;
        }

        if (! $paid) {
            return view('reservations.result', ['ok' => false]); // reservation stays Pending
        }

        DB::transaction(function () use ($reservation) {
            $reservation->payDeposit();          // deposit_status → Paid
            $reservation->confirmReservation();  // status → Confirmed
            $reservation->table?->updateStatus('Reserved'); // FR-02: table now Reserved
        });

        session()->forget('reservation_payment_pid');

        return view('reservations.result', [
            'ok' => true,
            'reservation' => $reservation->fresh()->load(['table', 'customer']),
        ]);
    }

    /** Selectable reservation dates: today through MAX_ADVANCE_DAYS ahead. */
    private function dateOptions(): array
    {
        $tags = ['Today', 'Tomorrow'];

        return collect(range(0, self::MAX_ADVANCE_DAYS))->map(function (int $offset) use ($tags) {
            $d = now()->addDays($offset);
            $tag = $tags[$offset] ?? $d->format('l'); // "Today" / "Tomorrow" / weekday name

            return [
                'value' => $d->toDateString(),
                'label' => "{$tag} — {$d->format('j M')}",
            ];
        })->all();
    }

    /** Selectable arrival time slots in 30-minute intervals across opening hours. */
    private function timeOptions(): array
    {
        $slots = [];
        $cursor = \Illuminate\Support\Carbon::createFromFormat('H:i', self::OPEN_TIME);
        $close = \Illuminate\Support\Carbon::createFromFormat('H:i', self::CLOSE_TIME);

        while ($cursor->lte($close)) {
            $slots[] = ['value' => $cursor->format('H:i'), 'label' => $cursor->format('g:i A')];
            $cursor->addMinutes(30);
        }

        return $slots;
    }
}
