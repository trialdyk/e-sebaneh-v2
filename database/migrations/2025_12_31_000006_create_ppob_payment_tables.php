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
        // Main ledger table for all financial transactions
        Schema::create('transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('invoice_id')->unique(); // INV-xxx
            $table->string('type'); // topup, withdraw, transfer, ppob, student_topup
            $table->string('ref_id')->nullable();
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->timestamps();
        });

        // Table for user wallet top-up records
        Schema::create('topup_users', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('student_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('invoice_status', ['pending', 'paid', 'failed', 'canceled'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->timestamp('expiry_date');
            $table->string('payment_method')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('payment_id')->nullable();
            $table->string('payment_reference')->nullable();
            $table->json('doku_payment_details')->nullable();
            $table->string('proof_of_transfer')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_cancelled')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();
        });

        // Table for user fund withdrawals (Tarik Saldo)
        Schema::create('withdrawals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->decimal('amount', 15, 2);
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2);
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_holder');
            $table->text('admin_notes')->nullable();
            $table->string('proof_of_transfer')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // Global settings for withdrawals (fees, limits)
        Schema::create('withdrawal_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('fee_percentage')->default(0);
            $table->decimal('minimum_fee', 15, 2)->default(0);
            $table->decimal('minimum_amount', 15, 2)->default(50000);
            $table->decimal('maximum_amount', 15, 2)->nullable();
            $table->timestamps();
        });

        // Table for PPOB products (Pulsa, Data, PLN, etc.)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name');
            $table->string('category');
            $table->string('brand');
            $table->decimal('price', 15, 2);
            $table->decimal('seller_price', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('type');
            $table->timestamps();
        });

        // Table for Digiflazz PPOB transaction details
        Schema::create('transaction_diggie_flazzs', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('transaction_id')->constrained()->cascadeOnDelete();
            $table->string('buyer_sku_code');
            $table->string('customer_no');
            $table->enum('status', ['pending', 'success', 'failed']);
            $table->decimal('price', 15, 2);
            $table->decimal('admin_fee', 15, 2)->default(0);
            $table->text('message')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('sn')->nullable();
            $table->timestamps();
        });

        // Table for Payment Method fees configuration
        Schema::create('payment_fees', function (Blueprint $table) {
            $table->id();
            $table->string('payment_method')->unique();
            $table->string('payment_method_name');
            $table->decimal('fee_amount', 15, 2)->default(0);
            $table->decimal('fee_percentage', 5, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Table for manual transfer bank accounts
        Schema::create('bankings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('account_name');
            $table->string('account_number');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bankings');
        Schema::dropIfExists('payment_fees');
        Schema::dropIfExists('transaction_diggie_flazzs');
        Schema::dropIfExists('products');
        Schema::dropIfExists('withdrawal_settings');
        Schema::dropIfExists('withdrawals');
        Schema::dropIfExists('topup_users');
        Schema::dropIfExists('transactions');
    }
};
