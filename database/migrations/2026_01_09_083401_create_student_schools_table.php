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
        Schema::create('student_schools', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            // Assuming schools and school_levels tables exist based on user request context. 
            // If strictly following previous analysis, we might need to verify their existence or use constrained('schools') if standard naming applies.
            // User mentioned "ambil dari tabel schools dan school_levels".
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_level_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'school_id', 'school_level_id', 'school_year_id'], 'student_school_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_schools');
    }
};
