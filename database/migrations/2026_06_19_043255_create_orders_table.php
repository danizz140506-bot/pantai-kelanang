<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * ORDERS entity (SDD 5.6) — stores all customer orders; supports digital
     * order management (FR-04), the KDS (FR-05), and status tracking (FR-06).
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id('order_id');                                          // PK
            $table->foreignId('table_id')->constrained('table_info', 'table_id');
            $table->foreignId('user_id')->constrained('users', 'user_id');   // waiter who took the order
            $table->dateTime('order_date');                                  // date & time placed
            $table->enum('status', ['Preparing', 'Ready', 'Served'])->default('Preparing');
            $table->decimal('total_amount', 10, 2)->default(0);              // order total in RM
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
