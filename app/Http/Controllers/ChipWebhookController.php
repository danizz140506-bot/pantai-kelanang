<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Services\ChipService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * CHIP webhook receiver (FR-01 reliability backstop). CHIP notifies this
 * endpoint server-to-server when a purchase is paid, so a reservation is
 * confirmed even if the customer's browser never completes the redirect back.
 *
 * Security: we do NOT trust the webhook body. We take the purchase id and
 * independently re-verify its status directly with CHIP (authenticated with
 * our secret key), so a forged webhook cannot confirm an unpaid reservation.
 */
class ChipWebhookController extends Controller
{
    public function handle(Request $request, ChipService $chip): JsonResponse
    {
        // The purchase id can arrive at the top level or nested under "data".
        $purchaseId = $request->input('id') ?? $request->input('data.id');

        if (! $purchaseId) {
            return response()->json(['ignored' => 'no purchase id'], 200);
        }

        // Re-verify with CHIP — this is the source of truth, not the payload.
        try {
            $purchase = $chip->getPurchase($purchaseId);
        } catch (\Throwable $e) {
            return response()->json(['error' => 'verify failed'], 200);
        }

        if (! $purchase['paid']) {
            return response()->json(['ignored' => 'not paid'], 200);
        }

        $reservation = Reservation::find($purchase['reference']);

        // Confirm only a still-pending reservation (idempotent — safe on retries).
        if ($reservation && $reservation->deposit_status !== 'Paid') {
            DB::transaction(function () use ($reservation) {
                $reservation->payDeposit();
                $reservation->confirmReservation();
                $reservation->table?->updateStatus('Reserved');
            });
        }

        return response()->json(['ok' => true], 200);
    }
}
