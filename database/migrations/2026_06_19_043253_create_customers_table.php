<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * CUSTOMER entity (SDD 5.2) — stores customer information for
     * online reservations (FR-01).
     */
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');                 // PK
            $table->string('name');                    // full name of the customer
            $table->string('phone_number');            // contact number for confirmation
            $table->string('email')->nullable();       // customer's email address
            $table->timestamps();                      // created_at + updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
