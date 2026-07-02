<?php

namespace App\Http\Controllers;

use App\Models\TableInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Table Management Component (SDD 3.1) — real-time table availability display
 * (FR-02) and digital table assignment (FR-03).
 */
class TableController extends Controller
{
    /** Floor view of all tables, ordered by table number. */
    public function index(): View
    {
        return view('tables.index', [
            'tables' => TableInfo::orderBy('table_number')
                ->get(['table_id', 'table_number', 'capacity', 'status']),
        ]);
    }

    /**
     * Live table statuses as JSON (FR-02). Polled by the floor view so the
     * display refreshes automatically without a manual page reload (NFR-01).
     */
    public function status(): JsonResponse
    {
        return response()->json(
            TableInfo::orderBy('table_number')
                ->get(['table_id', 'table_number', 'capacity', 'status'])
        );
    }

    /**
     * Assign / seat a party at an available table (FR-03). The table moves
     * from "Available" to "Occupied"; subsequent orders link to this table.
     */
    public function assign(TableInfo $table): JsonResponse
    {
        if (! $table->checkAvailability()) {
            return response()->json([
                'ok' => false,
                'message' => "Table {$table->table_number} is not available.",
            ], 422);
        }

        $table->assignTable();

        return response()->json([
            'ok' => true,
            'message' => "Table {$table->table_number} assigned.",
            'table' => $table->only('table_id', 'table_number', 'capacity', 'status'),
        ]);
    }

    /**
     * Release an occupied/reserved table back to "Available". (Billing also
     * releases a table automatically on payment — FR-08.)
     */
    public function release(TableInfo $table): JsonResponse
    {
        $table->updateStatus('Available');

        return response()->json([
            'ok' => true,
            'message' => "Table {$table->table_number} released.",
            'table' => $table->only('table_id', 'table_number', 'capacity', 'status'),
        ]);
    }
}
