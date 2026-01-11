<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Student;
use App\Models\StudentWithdrawHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class StudentBalanceController extends Controller
{
    /**
     * Display student balance management page (Tab 1).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $query = Student::query()
            ->with(['user', 'currentClassroom.classroom'])
            ->where('boarding_school_id', $boardingSchoolId);

        // Filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('student_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('classroom_id') && $request->classroom_id !== 'all') {
            $query->whereHas('currentClassroom', fn($q) => $q->where('classroom_id', $request->classroom_id));
        }

        if ($request->filled('rfid')) {
            $query->where('rfid', 'like', "%{$request->rfid}%");
        }

        $students = $query->latest()->paginate(15)->withQueryString();

        // Summary
        $totalBalance = User::whereHas('student', function ($q) use ($boardingSchoolId) {
            $q->where('boarding_school_id', $boardingSchoolId);
        })->sum('balance');

        $studentCount = Student::where('boarding_school_id', $boardingSchoolId)->count();
        $averageBalance = $studentCount > 0 ? $totalBalance / $studentCount : 0;

        $classrooms = Classroom::where('boarding_school_id', $boardingSchoolId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard/Finance/StudentBalance', [
            'students' => $students,
            'summary' => [
                'total_balance' => $totalBalance,
                'student_count' => $studentCount,
                'average_balance' => $averageBalance,
            ],
            'classrooms' => $classrooms,
            'filters' => $request->only(['search', 'classroom_id', 'rfid']),
        ]);
    }

    /**
     * Topup student balance by admin.
     */
    public function topup(Request $request, Student $student, \App\Services\FinanceService $financeService)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Jumlah topup wajib diisi',
            'amount.min' => 'Jumlah topup minimal Rp 1',
        ]);

        try {
            DB::transaction(function () use ($validated, $student, $financeService) {
                $adminName = auth()->user()->name ?? 'Admin';

                $student->user->increment('balance', $validated['amount']);

                $history = StudentWithdrawHistory::create([
                    'student_id' => $student->id,
                    'amount' => $validated['amount'],
                    'type' => 'topup_by_admin',
                    'date' => now(),
                    'description' => $validated['description'] ?? "Topup oleh {$adminName}",
                ]);

                // Finance System Record
                $financeService->recordTransaction(
                    accountSlug: 'student-balance',
                    amount: $validated['amount'],
                    type: \App\Enums\FinanceTransactionTypeEnum::CREDIT, // Liability increases (Money In to System Control)
                    description: "Topup Saldo Santri: {$student->name} ({$history->description})",
                    user: auth()->user(),
                    reference: $history,
                    boardingSchoolId: $student->boarding_school_id
                );
            });

            return back()->with('success', 'Saldo berhasil ditambahkan sebesar Rp ' . number_format($validated['amount'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan saldo: ' . $e->getMessage());
        }
    }

    /**
     * Withdraw student balance by admin.
     */
    public function withdraw(Request $request, Student $student, \App\Services\FinanceService $financeService)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'pin' => 'required|string|size:6',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Jumlah penarikan wajib diisi',
            'amount.min' => 'Jumlah penarikan minimal Rp 1',
            'pin.required' => 'PIN wajib diisi',
            'pin.size' => 'PIN harus 6 digit',
        ]);

        // Validate PIN
        if ($student->user->pin_atm !== $validated['pin']) {
            return back()->withErrors(['pin' => 'PIN yang Anda masukkan salah']);
        }

        // Validate balance
        if ($student->user->balance < $validated['amount']) {
            return back()->withErrors(['amount' => 'Saldo tidak mencukupi. Saldo saat ini: Rp ' . number_format($student->user->balance, 0, ',', '.')]);
        }

        // Check daily limit
        $withdrawLimit = \App\Models\StudentWithdrawLimit::where('boarding_school_id', $student->boarding_school_id)->first();
        
        $todayWithdraw = StudentWithdrawHistory::where('student_id', $student->id)
            ->where('type', 'withdraw')
            ->whereDate('date', today())
            ->sum('amount');

        if ($withdrawLimit && ($todayWithdraw + $validated['amount']) > $withdrawLimit->limit) {
            $remaining = max(0, $withdrawLimit->limit - $todayWithdraw);
            return back()->withErrors(['amount' => 'Melebihi batas penarikan harian. Sisa limit: Rp ' . number_format($remaining, 0, ',', '.')]);
        }

        try {
            DB::transaction(function () use ($validated, $student, $financeService) {
                $adminName = auth()->user()->name ?? 'Admin';

                $student->user->decrement('balance', $validated['amount']);

                $history = StudentWithdrawHistory::create([
                    'student_id' => $student->id,
                    'amount' => $validated['amount'],
                    'type' => 'withdraw',
                    'date' => now(),
                    'description' => $validated['description'] ?? "Penarikan oleh {$adminName}",
                ]);

                // Finance System Record
                $financeService->recordTransaction(
                    accountSlug: 'student-balance',
                    amount: $validated['amount'],
                    type: \App\Enums\FinanceTransactionTypeEnum::DEBIT, // Liability decreases (Money Out from System Control)
                    description: "Penarikan Saldo Santri: {$student->name} ({$history->description})",
                    user: auth()->user(),
                    reference: $history,
                    boardingSchoolId: $student->boarding_school_id
                );
            });

            return back()->with('success', 'Saldo berhasil ditarik sebesar Rp ' . number_format($validated['amount'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menarik saldo: ' . $e->getMessage());
        }
    }

    /**
     * Update student PIN ATM.
     */
    public function updatePin(Request $request, Student $student)
    {
        $validated = $request->validate([
            'pin' => 'required|string|size:6',
        ], [
            'pin.required' => 'PIN wajib diisi',
            'pin.size' => 'PIN harus 6 digit',
        ]);

        try {
            $student->user->update(['pin_atm' => $validated['pin']]);

            return back()->with('success', 'PIN berhasil diperbarui');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memperbarui PIN: ' . $e->getMessage());
        }
    }
}
