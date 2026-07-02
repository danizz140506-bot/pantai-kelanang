<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MENU_ITEM entity (SDD 5.5) — stores the restaurant's menu items used
     * during order-taking (FR-04) and bill generation (FR-07).
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id('menu_id');                                           // PK
            $table->string('name');                                          // item name
            $table->text('description')->nullable();                         // brief description
            $table->decimal('price', 8, 2);                                  // price in RM
            $table->enum('category', ['Main Dish', 'Side', 'Drink', 'Dessert']);
            $table->boolean('availability')->default(true);                  // currently available?
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
