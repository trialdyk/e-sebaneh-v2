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
        Schema::table('student_registrations', function (Blueprint $table) {
            // Guardian (Wali)
            $table->string('guardian_name')->nullable()->after('mother_income');
            $table->string('guardian_job')->nullable()->after('guardian_name');
            $table->string('guardian_phone')->nullable()->after('guardian_job');
            $table->text('guardian_address')->nullable()->after('guardian_phone');

            // Previous Education
            $table->string('previous_school_name')->nullable()->after('guardian_address');
            $table->text('previous_school_address')->nullable()->after('previous_school_name');
            $table->year('graduation_year')->nullable()->after('previous_school_address');
            $table->string('certificate_number')->nullable()->after('graduation_year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_registrations', function (Blueprint $table) {
            $table->dropColumn([
                'guardian_name', 
                'guardian_job', 
                'guardian_phone', 
                'guardian_address',
                'previous_school_name', 
                'previous_school_address', 
                'graduation_year', 
                'certificate_number'
            ]);
        });
    }
};
