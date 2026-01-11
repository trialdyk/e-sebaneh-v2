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
        Schema::table('boarding_schools', function (Blueprint $table) {
            $table->string('letter_head_name')->nullable()->after('balance');
            $table->string('letter_secretary_name')->nullable()->after('letter_head_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boarding_schools', function (Blueprint $table) {
            $table->dropColumn(['letter_head_name', 'letter_secretary_name']);
        });
    }
};
