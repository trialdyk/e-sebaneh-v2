<?php

namespace App\Services;

use App\Enums\FinanceTransactionTypeEnum;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Record a financial transaction.
     *
     * @param string $accountSlug The slug of the Finance Account (or ID, but slug preferred for system accounts)
     * @param float $amount The transaction amount
     * @param FinanceTransactionTypeEnum $type Credit or Debit
     * @param string $description Description of the transaction
     * @param User|null $user The user performing the action (admin)
     * @param Model|null $reference The related model (e.g., StudentWithdrawHistory, InvoicePayment)
     * @param string|null $boardingSchoolId Optional, needed if resolving account by slug
     * @return FinanceTransaction
     * @throws \Exception
     */
    public function recordTransaction(
        string $accountSlug,
        float $amount,
        FinanceTransactionTypeEnum $type,
        string $description,
        ?User $user = null,
        ?Model $reference = null,
        ?string $boardingSchoolId = null
    ): FinanceTransaction {
        return DB::transaction(function () use ($accountSlug, $amount, $type, $description, $user, $reference, $boardingSchoolId) {
            // 1. Resolve Account
            $query = FinanceAccount::query();

            // Check if it looks like a UDID or ID, otherwise start with slug
            if (is_numeric($accountSlug)) {
                 $query->where('id', $accountSlug);
            } else {
                 $query->where('slug', $accountSlug);
                 if ($boardingSchoolId) {
                     $query->where('boarding_school_id', $boardingSchoolId);
                 } elseif ($user && $user->boardingSchools()->exists()) {
                     // Try to guess from user's scope if not provided? Better to be explicit.
                     // For now, let's assume system accounts might be scoped or we need exact ID if slug isn't unique globally (it is unique per school in logic)
                 }
            }
            
            $account = $query->first();

            if (!$account) {
                // If not found by slug, maybe it was passed as ID directly? 
                // Let's assume strict usage for now.
                throw new \Exception("Finance Account not found: {$accountSlug}");
            }

            // 2. Update Account Balance
            // Credit = Money In (Income/Liability inc), Debit = Money Out (Expense/Liability dec)
            // For Income-type accounts (Liability like Kas Santri): Credit increases balance, Debit decreases.
            // For Expense-type: Debit increases balance (expense), Credit decreases (refund).
            // Let's stick to simple ledger: 
            // Credit adds to balance, Debit subtracts from balance.
            // The interpretation depends on account type.

            if ($type === FinanceTransactionTypeEnum::CREDIT) {
                $account->increment('balance', $amount);
            } else {
                $account->decrement('balance', $amount);
            }

            // 3. Create Transaction Record
            return FinanceTransaction::create([
                'finance_account_id' => $account->id,
                'user_id' => $user?->id,
                'type' => $type,
                'amount' => $amount,
                'description' => $description,
                'date' => now(),
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
            ]);
        });
    }
}
