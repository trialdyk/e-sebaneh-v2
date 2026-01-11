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
        // Table for invoices (Tagihan)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->enum('for_gender', ['male', 'female', 'all']);
            $table->enum('type', ['by_classroom', 'by_gender', 'specific_students', 'all_students'])->default('by_classroom');
            $table->timestamps();
        });

        // Pivot table for assigning invoices to classrooms
        Schema::create('invoice_classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Table for recording invoice payments by students
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount_paid', 15, 2);
            $table->enum('status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->string('payment_method')->nullable();
            $table->timestamp('payment_date')->nullable();
            $table->timestamps();
        });

        // Table for student balance history (Riwayat Saldo Santri)
        Schema::create('student_withdraw_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 15, 2);
            $table->enum('type', ['topup', 'topup_by_admin', 'withdraw']);
            $table->text('description')->nullable();
            $table->dateTime('date');
            $table->timestamps();
        });

        // Table for withdrawal limits configuration per boarding school
        Schema::create('student_withdraw_limits', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->decimal('limit', 15, 2)->nullable();
            $table->timestamps();
        });

        // Table for student savings transactions (Tabungan)
        Schema::create('savings_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('student_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['deposit', 'withdrawal']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Table for boarding school finance accounts (Kas/Bank Pondok)
        Schema::create('finance_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('boarding_school_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->enum('type', ['income', 'expense']); // or asset/liability type? Simplified for cash accounts
            $table->decimal('balance', 15, 2)->default(0);
            $table->timestamps();
        });

        // Table for finance transactions (Arus Kas Pondok)
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('finance_account_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 15, 2);
            $table->text('description');
            $table->date('date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('finance_transactions');
        Schema::dropIfExists('finance_accounts');
        Schema::dropIfExists('savings_transactions');
        Schema::dropIfExists('student_withdraw_limits');
        Schema::dropIfExists('student_withdraw_histories');
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_classrooms');
        Schema::dropIfExists('invoices');
    }
};
