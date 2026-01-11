<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to change enum to string because doctrine/dbal might be missing
        // Modifying column to VARCHAR allows any string, including 'general'
        DB::statement("ALTER TABLE finance_accounts MODIFY COLUMN type VARCHAR(255) NOT NULL DEFAULT 'general'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to enum (might be risky if data exists that doesn't fit, but safe for dev)
        // Adjust enum values as they were originally
        DB::statement("ALTER TABLE finance_accounts MODIFY COLUMN type ENUM('income', 'expense') NOT NULL");
    }
};
