<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * TABLE_INFO entity (SDD 5.3) — stores all physical tables with their
     * real-time status; supports availability display & assignment (FR-02, FR-03).
     */
    public function up(): void
    {
        Schema::create('table_info', function (Blueprint $table) {
            $table->id('table_id');                                       // PK
            $table->integer('table_number');                              // physical table number
            $table->integer('capacity');                                  // max guests
            $table->enum('status', ['Available', 'Reserved', 'Occupied'])
                  ->default('Available');                                 // current status
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_info');
    }
};
