<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentWithdrawHistory;
use App\Models\StudentWithdrawLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentWithdrawController extends Controller
{
    /**
     * Get student data by RFID for withdraw interface.
     */
    public function showByRfid(Request $request)
    {
        $validated = $request->validate([
            'rfid' => 'required|string',
        ]);

        $student = Student::with('user')
            ->where('rfid', $validated['rfid'])
            ->first();

        if (!$student || !$student->user) {
            return response()->json([
                'error' => 'Santri dengan RFID tersebut tidak ditemukan'
            ], 404);
        }

        // Get today's total withdraw
        $todayWithdraw = StudentWithdrawHistory::where('student_id', $student->id)
            ->where('type', 'withdraw')
            ->whereDate('date', today())
            ->sum('amount');

        // Get withdraw limit
        $withdrawLimit = StudentWithdrawLimit::where('boarding_school_id', $student->boarding_school_id)
            ->first();

        return response()->json([
            'student' => [
                'id' => $student->id,
                'name' => $student->user->name,
                'student_id' => $student->student_id,
                'photo' => $student->photo,
                'balance' => $student->user->balance,
            ],
            'today_withdraw' => $todayWithdraw,
            'limit' => $withdrawLimit?->limit ?? 0,
            'remaining_limit' => max(0, ($withdrawLimit?->limit ?? 0) - $todayWithdraw),
        ]);
    }

    /**
     * Process withdraw via RFID scan.
     */
    /**
     * Process withdraw via RFID scan.
     */
    public function processWithdraw(Request $request, \App\Services\FinanceService $financeService)
    {
        $validated = $request->validate([
            'rfid' => 'required|string',
            'amount' => 'required|numeric|min:1',
            'pin' => 'required|string|size:6',
        ], [
            'rfid.required' => 'RFID wajib diisi',
            'amount.required' => 'Jumlah penarikan wajib diisi',
            'amount.min' => 'Jumlah minimal Rp 1',
            'pin.required' => 'PIN wajib diisi',
            'pin.size' => 'PIN harus 6 digit',
        ]);

        $student = Student::with('user')->where('rfid', $validated['rfid'])->first();

        if (!$student || !$student->user) {
            return response()->json([
                'error' => 'Santri tidak ditemukan'
            ], 404);
        }

        // Validate PIN
        if ($student->user->pin_atm !== $validated['pin']) {
            return response()->json([
                'error' => 'PIN yang Anda masukkan salah'
            ], 422);
        }

        // Check daily limit
        $withdrawLimit = StudentWithdrawLimit::where('boarding_school_id', $student->boarding_school_id)->first();
        $todayWithdraw = StudentWithdrawHistory::where('student_id', $student->id)
            ->where('type', 'withdraw')
            ->whereDate('date', today())
            ->sum('amount');

        if ($withdrawLimit && ($todayWithdraw + $validated['amount']) > $withdrawLimit->limit) {
            $remaining = max(0, $withdrawLimit->limit - $todayWithdraw);
            return response()->json([
                'error' => 'Melebihi batas penarikan harian. Sisa limit: Rp ' . number_format($remaining, 0, ',', '.')
            ], 422);
        }

        // Check balance
        if ($student->user->balance < $validated['amount']) {
            return response()->json([
                'error' => 'Saldo tidak mencukupi. Saldo saat ini: Rp ' . number_format($student->user->balance, 0, ',', '.')
            ], 422);
        }

        // Process withdraw
        try {
            DB::transaction(function () use ($validated, $student, $financeService) {
                $student->user->decrement('balance', $validated['amount']);

                $history = StudentWithdrawHistory::create([
                    'student_id' => $student->id,
                    'amount' => $validated['amount'],
                    'type' => 'withdraw',
                    'date' => now(),
                    'description' => 'Penarikan via RFID',
                ]);

                // Finance System Record
                $financeService->recordTransaction(
                    accountSlug: 'student-balance',
                    amount: $validated['amount'],
                    type: \App\Enums\FinanceTransactionTypeEnum::DEBIT, // Liability decreases, so DEBIT is correct for liability/income? 
                    // Wait, 'student-balance' is INCOME type in Observer.
                    // For INCOME account: Credit increases (Revenue), Debit decreases (Refund/Withdrawal).
                    // So DEBIT is correct for withdrawal.
                    description: "Penarikan Saldo Santri (RFID): {$student->name}",
                    user: null, // Performed by system/student via RFID kiosk technically, or auth user if logged in kiosk
                    reference: $history,
                    boardingSchoolId: $student->boarding_school_id
                );
            });

            return response()->json([
                'success' => true,
                'message' => 'Penarikan berhasil sebesar Rp ' . number_format($validated['amount'], 0, ',', '.'),
                'new_balance' => $student->user->fresh()->balance,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan pada server: ' . $e->getMessage()
            ], 500);
        }
    }
}
