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
        // Table for classrooms (Kelas)
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('level')->nullable();
            $table->timestamps();
        });

        // Pivot table for student enrollment in classrooms
        Schema::create('student_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['student_id', 'classroom_id', 'school_year_id']);
        });

        // Table for bedrooms/dormitories (Kamar/Asrama)
        Schema::create('bed_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->integer('capacity')->nullable();
            $table->timestamps();
        });

        // Pivot table for student assignment to bedrooms
        Schema::create('student_bed_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bed_room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_bed_rooms');
        Schema::dropIfExists('bed_rooms');
        Schema::dropIfExists('student_classrooms');
        Schema::dropIfExists('classrooms');
    }
};
