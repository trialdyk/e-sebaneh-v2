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
        // Table for storing student data (biodata santri)
        Schema::create('students', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->string('student_id')->unique(); // NIS
            $table->string('rfid')->unique()->nullable();
            $table->enum('status', ['registered', 'active', 'on_leave', 'alumni', 'dropped_out'])->default('registered');

            // Personal Info
            $table->string('place_of_birth')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female'])->nullable();
            $table->text('address')->nullable();
            $table->string('province')->nullable();
            $table->string('regency')->nullable();
            $table->string('district')->nullable();
            $table->string('village')->nullable();
            $table->integer('child_number')->nullable();
            $table->integer('siblings_count')->nullable();
            $table->string('economic_status')->nullable();
            $table->string('child_status')->nullable();
            $table->decimal('savings', 15, 2)->default(0);

            // Father Info
            $table->string('father_name')->nullable();
            $table->date('father_birth_date')->nullable();
            $table->string('father_last_edu')->nullable();
            $table->string('father_job')->nullable();
            $table->string('father_income')->nullable();
            $table->string('father_phone')->nullable();

            // Mother Info
            $table->string('mother_name')->nullable();
            $table->date('mother_birth_date')->nullable();
            $table->string('mother_last_edu')->nullable();
            $table->string('mother_job')->nullable();
            $table->string('mother_income')->nullable();
            $table->string('mother_phone')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
