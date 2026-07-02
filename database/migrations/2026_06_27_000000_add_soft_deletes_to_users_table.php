<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds soft-delete support to the USER table so that the Owner can deactivate a
 * staff account (SDD 6.6 — Manage User Accounts, FR-10) without deleting the
 * historical records (e.g. orders) that reference the user. A deactivated user
 * is excluded from authentication automatically.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
