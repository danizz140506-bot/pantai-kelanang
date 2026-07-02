<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\TableInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Order Management Component (SDD 3.1) — digital order taking by waitstaff
 * (FR-04) and transmission to the Kitchen Display System (FR-05). Order status
 * tracking (FR-06) is shared with the Kitchen module.
 */
class OrderController extends Controller
{
    /** Order-taking screen for a specific table (FR-04). */
    public function create(TableInfo $table): View
    {
        $menu = MenuItem::where('availability', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        $menuFlat = $menu->map(fn (MenuItem $m) => [
            'menu_id' => $m->menu_id,
            'name' => $m->name,
            'price' => (float) $m->price,
            'category' => $m->category,
        ])->values();

        return view('orders.create', [
            'table' => $table,
            'menuFlat' => $menuFlat,
            'tables' => TableInfo::orderBy('table_number')->get(['table_id', 'table_number', 'status']),
        ]);
    }

    /** Persist a new order with its line items and send it to the kitchen (FR-04, FR-05). */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'table_id' => ['required', 'integer', 'exists:table_info,table_id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_id' => ['required', 'integer', 'exists:menu_items,menu_id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:99'],
            'items.*.special_instructions' => ['nullable', 'string', 'max:255'],
        ]);

        $order = DB::transaction(function () use ($data, $request) {
            $table = TableInfo::lockForUpdate()->findOrFail($data['table_id']);

            // Taking an order seats the party — the table becomes Occupied (FR-03),
            // whether it was Available (walk-in) or Reserved (booked guest arriving).
            if ($table->status !== 'Occupied') {
                $table->updateStatus('Occupied');
            }

            $order = Order::create([
                'table_id' => $table->table_id,
                'user_id' => $request->user()->user_id,
                'order_date' => now(),
                'status' => 'Preparing',
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $line) {
                $item = MenuItem::findOrFail($line['menu_id']);
                abort_unless($item->checkAvailability(), 422, "{$item->name} is no longer available.");

                // Subtotal is computed server-side from the trusted DB price.
                OrderItem::create([
                    'order_id' => $order->order_id,
                    'menu_id' => $item->menu_id,
                    'quantity' => $line['quantity'],
                    'subtotal' => $line['quantity'] * $item->getPrice(),
                    'special_instructions' => $line['special_instructions'] ?? null,
                ]);
            }

            $order->calculateTotal();

            return $order;
        });

        return response()->json([
            'ok' => true,
            'message' => "Order #{$order->order_id} sent to the kitchen.",
            'redirect' => route('orders.index'),
        ]);
    }

    /** Waiter's list of today's orders with their live status (FR-06). */
    public function index(): View
    {
        return view('orders.index');
    }

    /** JSON feed of today's orders and statuses, polled by the waiter view (FR-06). */
    public function feed(): JsonResponse
    {
        $orders = Order::with('table:table_id,table_number')
            ->whereDate('order_date', today())
            ->orderByDesc('order_id')
            ->limit(50)
            ->get();

        return response()->json($orders->map(fn (Order $o) => [
            'order_id' => $o->order_id,
            'table_number' => $o->table?->table_number,
            'status' => $o->status,
            'total_amount' => number_format((float) $o->total_amount, 2),
            'time' => $o->order_date->format('h:i A'),
        ]));
    }
}
