<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PAYMENT entity (SDD 5.8) — stores all payment transactions; supports
     * automated billing (FR-07), multiple payment methods (FR-08), and the
     * optional Apply Discount feature.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id('payment_id');                                          // PK
            $table->foreignId('order_id')->constrained('orders', 'order_id');
            $table->decimal('subtotal', 10, 2);                                // before discount
            $table->decimal('discount_amount', 8, 2)->default(0);              // discount (0 if none)
            $table->decimal('total_amount', 10, 2);                            // final paid after discount
            $table->enum('payment_method', ['Cash', 'Card', 'E-Wallet']);
            $table->enum('payment_status', ['Successful', 'Failed', 'Refunded'])->default('Successful');
            $table->dateTime('payment_date');                                  // date & time of payment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
