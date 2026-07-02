<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Kitchen (KDS) Component (SDD 3.1) — displays incoming orders on the Kitchen
 * Display System (FR-05) and manages order-status updates through
 * Preparing → Ready → Served (FR-06).
 */
class KitchenController extends Controller
{
    /** Kitchen Display System screen. */
    public function index(): View
    {
        return view('kds.index');
    }

    /**
     * Live feed of active kitchen orders (Preparing, Ready), polled by the KDS
     * so new orders appear within seconds of submission (FR-05, NFR-01).
     */
    public function feed(): JsonResponse
    {
        $orders = Order::with(['table:table_id,table_number', 'orderItems.menuItem:menu_id,name'])
            ->whereIn('status', ['Preparing', 'Ready'])
            ->orderBy('order_id')
            ->get();

        return response()->json($orders->map(fn (Order $o) => [
            'order_id' => $o->order_id,
            'table_number' => $o->table?->table_number,
            'status' => $o->status,
            'placed_at' => $o->order_date->toIso8601String(),
            'items' => $o->orderItems->map(fn ($it) => [
                'name' => $it->menuItem?->name,
                'quantity' => $it->quantity,
                'special_instructions' => $it->special_instructions,
            ])->values(),
        ]));
    }

    /** Advance an order through Preparing → Ready → Served (FR-06). */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:Preparing,Ready,Served'],
        ]);

        $order->updateStatus($data['status']);

        return response()->json([
            'ok' => true,
            'message' => "Order #{$order->order_id} marked {$data['status']}.",
            'status' => $order->status,
        ]);
    }
}
