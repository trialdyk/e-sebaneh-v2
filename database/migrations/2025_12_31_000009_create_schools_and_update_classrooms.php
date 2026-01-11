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
        // 1. Schools (Master Data: SD, MI, SMP, MTs, etc.)
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('short_name'); // SD, SMP, etc.
            $table->timestamps();
        });

        // 2. School Levels (Master Data: Kelas 1, Kelas 7, etc.)
        Schema::create('school_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Kelas 1, Kelas 7
            $table->integer('order_level'); // 1, 2, 3...
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('school_levels');
        Schema::dropIfExists('schools');
    }
};
