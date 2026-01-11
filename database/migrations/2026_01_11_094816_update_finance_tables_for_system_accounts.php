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
        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->boolean('is_system')->default(false)->after('type');
            $table->string('slug')->nullable()->after('name'); // unique constrained per boarding school handled in code/logic
            $table->text('description')->nullable()->after('slug');
        });

        Schema::table('finance_transactions', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->after('finance_account_id');
            $table->nullableMorphs('reference'); // reference_type, reference_id
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('finance_transactions', function (Blueprint $table) {
             $table->dropMorphs('reference');
             $table->dropForeign(['user_id']);
             $table->dropColumn('user_id');
        });

        Schema::table('finance_accounts', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'slug', 'description']);
        });
    }
};
