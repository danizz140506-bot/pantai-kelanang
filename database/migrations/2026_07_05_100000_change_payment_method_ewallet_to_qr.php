<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Rename the "E-Wallet" payment method to "QR" (FR-08). Handled with raw SQL
     * because MySQL ENUM columns cannot be altered through the schema builder.
     * SQLite (the test database) is skipped — its enum already comes from the
     * updated create_payments_table migration.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Widen the enum, migrate any existing rows, then drop the old value.
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash','Card','E-Wallet','QR') NOT NULL");
        DB::statement("UPDATE payments SET payment_method = 'QR' WHERE payment_method = 'E-Wallet'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash','Card','QR') NOT NULL");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash','Card','E-Wallet','QR') NOT NULL");
        DB::statement("UPDATE payments SET payment_method = 'E-Wallet' WHERE payment_method = 'QR'");
        DB::statement("ALTER TABLE payments MODIFY payment_method ENUM('Cash','Card','E-Wallet') NOT NULL");
    }
};
