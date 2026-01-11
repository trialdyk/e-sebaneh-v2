<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update old account names to new standardized names
        DB::table('finance_accounts')
            ->where('slug', 'student-balances-liability')
            ->update([
                'name' => 'Saldo Santri',
                'slug' => 'student-balance',
                'description' => 'Saldo santri untuk penarikan RFID. Bertambah saat topup, berkurang saat penarikan.',
            ]);

        DB::table('finance_accounts')
            ->where('slug', 'invoice-revenue')
            ->update([
                'name' => 'Tagihan Santri',
                'slug' => 'student-invoices',
                'description' => 'Pendapatan dari pembayaran tagihan/SPP santri.',
            ]);

        DB::table('finance_accounts')
            ->where('slug', 'student-savings-liability')
            ->update([
                'name' => 'Tabungan Santri',
                'slug' => 'student-savings',
                'description' => 'Tabungan jangka panjang santri.',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to old names if needed
        DB::table('finance_accounts')
            ->where('slug', 'student-balance')
            ->update([
                'name' => 'Kas Utama Saldo Santri',
                'slug' => 'student-balances-liability',
                'description' => 'Akun penampung saldo santri. Bertambah saat Topup, berkurang saat Penarikan/Pembayaran.',
            ]);

        DB::table('finance_accounts')
            ->where('slug', 'student-invoices')
            ->update([
                'name' => 'Pendapatan Tagihan',
                'slug' => 'invoice-revenue',
                'description' => 'Akun penampung pendapatan dari pembayaran tagihan santri.',
            ]);

        DB::table('finance_accounts')
            ->where('slug', 'student-savings')
            ->update([
                'name' => 'Saldo Tabungan Santri',
                'slug' => 'student-savings-liability',
                'description' => 'Akun penampung tabungan jangka panjang santri.',
            ]);
    }
};
