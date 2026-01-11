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
        Schema::create('student_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Nama tagihan
            $table->integer('amount'); // Jumlah tagihan
            $table->text('description')->nullable();
            $table->enum('type', ['all_students', 'by_classroom', 'by_gender', 'specific_students']);
            $table->enum('for_gender', ['male', 'female'])->nullable(); // Only for by_gender type
            $table->timestamps();
            $table->softDeletes(); // Soft delete untuk history
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_invoices');
    }
};
