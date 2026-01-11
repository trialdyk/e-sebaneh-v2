<?php

namespace App\Observers;

use App\Enums\FinanceAccountTypeEnum;
use App\Models\BoardingSchool;
use App\Models\FinanceAccount;

class BoardingSchoolObserver
{
    /**
     * Handle the BoardingSchool "created" event.
     */
    public function created(BoardingSchool $boardingSchool): void
    {
        // Automatically create system finance accounts for new boarding school
        
        // 1. Saldo Santri - Money for student withdrawals via RFID
        FinanceAccount::create([
            'boarding_school_id' => $boardingSchool->id,
            'slug' => 'student-balance',
            'name' => 'Saldo Santri',
            'type' => FinanceAccountTypeEnum::INCOME,
            'description' => 'Saldo santri untuk penarikan RFID. Bertambah saat topup, berkurang saat penarikan.',
            'is_system' => true,
            'balance' => 0,
            'pending_balance' => 0,
        ]);

        // 2. Tabungan Santri - Long-term student savings
        FinanceAccount::create([
            'boarding_school_id' => $boardingSchool->id,
            'slug' => 'student-savings',
            'name' => 'Tabungan Santri',
            'type' => FinanceAccountTypeEnum::INCOME,
            'description' => 'Tabungan jangka panjang santri.',
            'is_system' => true,
            'balance' => 0,
            'pending_balance' => 0,
        ]);

        // 3. Tagihan Santri - Invoice revenue from student bill payments
        FinanceAccount::create([
            'boarding_school_id' => $boardingSchool->id,
            'slug' => 'student-invoices',
            'name' => 'Tagihan Santri',
            'type' => FinanceAccountTypeEnum::INCOME,
            'description' => 'Pendapatan dari pembayaran tagihan/SPP santri.',
            'is_system' => true,
            'balance' => 0,
            'pending_balance' => 0,
        ]);
    }

    /**
     * Handle the BoardingSchool "updated" event.
     */
    public function updated(BoardingSchool $boardingSchool): void
    {
        //
    }

    /**
     * Handle the BoardingSchool "deleted" event.
     */
    public function deleted(BoardingSchool $boardingSchool): void
    {
        //
    }

    /**
     * Handle the BoardingSchool "restored" event.
     */
    public function restored(BoardingSchool $boardingSchool): void
    {
        //
    }

    /**
     * Handle the BoardingSchool "force deleted" event.
     */
    public function forceDeleted(BoardingSchool $boardingSchool): void
    {
        //
    }
}
