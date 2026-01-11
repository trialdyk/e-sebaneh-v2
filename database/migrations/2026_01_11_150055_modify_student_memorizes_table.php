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
        Schema::table('student_memorizes', function (Blueprint $table) {
            $table->renameColumn('page_start', 'verse_start');
            $table->renameColumn('page_end', 'verse_end');
            $table->dropColumn('grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_memorizes', function (Blueprint $table) {
            $table->renameColumn('verse_start', 'page_start');
            $table->renameColumn('verse_end', 'page_end');
            $table->string('grade')->nullable();
        });
    }
};
