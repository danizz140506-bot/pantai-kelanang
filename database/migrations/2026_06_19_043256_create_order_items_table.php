<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ORDER_ITEM entity (SDD 5.7) — stores the individual line items within
     * each order. Composition with ORDERS: items cannot exist without a parent
     * order (cascade on delete).
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id('order_item_id');                                       // PK
            $table->foreignId('order_id')->constrained('orders', 'order_id')->cascadeOnDelete();
            $table->foreignId('menu_id')->constrained('menu_items', 'menu_id');
            $table->integer('quantity');                                       // quantity ordered
            $table->decimal('subtotal', 8, 2);                                 // quantity x price
            $table->text('special_instructions')->nullable();                  // e.g. "less spicy"
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
