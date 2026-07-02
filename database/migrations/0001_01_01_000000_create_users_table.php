<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // USER entity (SDD 5.1) — stores all staff accounts; supports
        // authentication and role-based access control (FR-10).
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');                 // PK, auto-increment
            $table->string('username')->unique();  // unique login username
            $table->string('password');            // hashed password
            $table->string('full_name');           // full name of the user
            $table->enum('role', ['Owner', 'Waiter', 'Cashier', 'Kitchen Staff']);
            $table->string('phone_number')->nullable();
            $table->rememberToken();
            $table->timestamps();                  // created_at + updated_at
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('username')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
