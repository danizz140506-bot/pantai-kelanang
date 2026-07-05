<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Persist the customer's pre-ordered menu items with the reservation (FR-01),
     * so the waiter's order screen can pre-fill the cart when the booked guest
     * arrives instead of re-entering everything by hand. Stored as JSON
     * ([{menu_id, quantity}, ...]); nullable for walk-in reservations without a
     * pre-order.
     */
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->json('preorder_items')->nullable()->after('deposit_amount');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn('preorder_items');
        });
    }
};
