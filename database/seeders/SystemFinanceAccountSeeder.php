<?php

namespace Database\Seeders;

use App\Enums\FinanceAccountTypeEnum;
use App\Models\BoardingSchool;
use App\Models\FinanceAccount;
use Illuminate\Database\Seeder;

class SystemFinanceAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $boardingSchools = BoardingSchool::all();

        foreach ($boardingSchools as $school) {
            // 1. Saldo Santri - Money for student withdrawals via RFID
            FinanceAccount::firstOrCreate(
                [
                    'boarding_school_id' => $school->id,
                    'slug' => 'student-balance',
                ],
                [
                    'name' => 'Saldo Santri',
                    'type' => FinanceAccountTypeEnum::INCOME,
                    'description' => 'Saldo santri untuk penarikan RFID. Bertambah saat topup, berkurang saat penarikan.',
                    'is_system' => true,
                    'balance' => 0,
                    'pending_balance' => 0,
                ]
            );

            // 2. Tabungan Santri - Long-term student savings
            FinanceAccount::firstOrCreate(
                [
                    'boarding_school_id' => $school->id,
                    'slug' => 'student-savings',
                ],
                [
                    'name' => 'Tabungan Santri',
                    'type' => FinanceAccountTypeEnum::INCOME,
                    'description' => 'Tabungan jangka panjang santri.',
                    'is_system' => true,
                    'balance' => 0,
                    'pending_balance' => 0,
                ]
            );

            // 3. Tagihan Santri - Invoice revenue from student bill payments
            FinanceAccount::firstOrCreate(
                [
                    'boarding_school_id' => $school->id,
                    'slug' => 'student-invoices',
                ],
                [
                    'name' => 'Tagihan Santri',
                    'type' => FinanceAccountTypeEnum::INCOME,
                    'description' => 'Pendapatan dari pembayaran tagihan/SPP santri.',
                    'is_system' => true,
                    'balance' => 0,
                    'pending_balance' => 0,
                ]
            );
        }
    }
}
