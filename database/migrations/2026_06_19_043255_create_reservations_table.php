<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * RESERVATION entity (SDD 5.4) — stores all customer reservations along
     * with deposit payment information (FR-01).
     */
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id('reservation_id');                                          // PK
            $table->foreignId('customer_id')->constrained('customers', 'customer_id');
            $table->foreignId('table_id')->constrained('table_info', 'table_id');
            $table->date('reservation_date');                                      // date of reservation
            $table->time('arrival_time');                                          // expected arrival
            $table->integer('pax');                                                // number of guests
            $table->decimal('deposit_amount', 8, 2);                               // 50% deposit
            $table->enum('deposit_status', ['Pending', 'Paid', 'Refunded'])->default('Pending');
            // 'Pending' = created but deposit not yet paid (SDD 5.2 alternative flow);
            // becomes 'Confirmed' once the deposit is paid.
            $table->enum('status', ['Pending', 'Confirmed', 'Cancelled', 'Completed'])->default('Pending');
            $table->timestamps();                                                  // created_at + updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
