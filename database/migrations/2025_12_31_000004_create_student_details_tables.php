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
        // Table for Surah master data
        Schema::create('surahs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('arabic_name')->nullable();
            $table->integer('total_ayat')->nullable();
            $table->timestamps();
        });

        // Table for linking students to guardians (users)
        Schema::create('student_guardians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('relationship')->nullable(); // ayah/ibu/wali
            $table->timestamps();

            $table->unique(['user_id', 'student_id']);
        });

        // Table for student permissions (Izin/Sakit)
        Schema::create('student_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('type')->nullable(); // sick/permit/etc
            $table->text('reason');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->string('duration')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();
        });

        // Table for student violations (Pelanggaran)
        Schema::create('student_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->date('violation_date');
            $table->text('description');
            $table->text('punishment')->nullable();
            $table->integer('points')->default(0);
            $table->timestamps();
        });

        // Table for student memorization records (Hafalan)
        Schema::create('student_memorizes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->foreignId('surah_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('juz')->nullable();
            $table->integer('page_start')->nullable();
            $table->integer('page_end')->nullable();
            $table->string('grade')->nullable();
            $table->text('notes')->nullable();
            $table->date('memorize_date')->nullable();
            $table->timestamps();
        });

        // Table for student disease history
        Schema::create('diseases', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->string('disease_name');
            $table->date('diagnosed_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table for tracking students who left the school
        Schema::create('student_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->enum('out_type', ['graduated', 'dropped_out', 'transferred', 'deceased']);
            $table->date('out_date');
            $table->text('reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_outs');
        Schema::dropIfExists('diseases');
        Schema::dropIfExists('student_memorizes');
        Schema::dropIfExists('student_violations');
        Schema::dropIfExists('student_permissions');
        Schema::dropIfExists('student_guardians');
        Schema::dropIfExists('surahs');
    }
};
