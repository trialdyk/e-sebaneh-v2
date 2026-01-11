<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SavingsTransaction;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SavingsTransactionsExport;

class StudentSavingsController extends Controller
{
    /**
     * Display student savings management page (Tab 4).
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
        $totalSavings = Student::where('boarding_school_id', $boardingSchoolId)->sum('savings');
        $studentCount = Student::where('boarding_school_id', $boardingSchoolId)->count();
        $averageSavings = $studentCount > 0 ? $totalSavings / $studentCount : 0;

        $classrooms = Classroom::where('boarding_school_id', $boardingSchoolId)
            ->orderBy('name')
            ->get();

        return Inertia::render('Dashboard/Finance/StudentSavings', [
            'students' => $students,
            'summary' => [
                'total_savings' => $totalSavings,
                'student_count' => $studentCount,
                'average_savings' => $averageSavings,
            ],
            'classrooms' => $classrooms,
            'filters' => $request->only(['search', 'classroom_id', 'rfid']),
        ]);
    }

    /**
     * Deposit (Setor) savings.
     */
    public function deposit(Request $request, Student $student)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:500',
        ], [
            'amount.required' => 'Jumlah setoran wajib diisi',
            'amount.min' => 'Jumlah setoran minimal Rp 1',
        ]);

        try {
            DB::transaction(function () use ($validated, $student) {
                $adminName = auth()->user()->name ?? 'Admin';

                $student->increment('savings', $validated['amount']);

                SavingsTransaction::create([
                    'student_id' => $student->id,
                    'type' => 'deposit',
                    'amount' => $validated['amount'],
                    'balance_after' => $student->fresh()->savings,
                    'notes' => $validated['description'] ?? "Setor tabungan oleh {$adminName}",
                ]);
            });

            return back()->with('success', 'Tabungan berhasil disetor sebesar Rp ' . number_format($validated['amount'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyetor tabungan: ' . $e->getMessage());
        }
    }

    /**
     * Withdraw (Tarik) savings.
     */
    public function withdraw(Request $request, Student $student)
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

        // Validate savings balance
        if ($student->savings < $validated['amount']) {
            return back()->withErrors(['amount' => 'Saldo tabungan tidak mencukupi. Saldo tabungan saat ini: Rp ' . number_format($student->savings, 0, ',', '.')]);
        }

        try {
            DB::transaction(function () use ($validated, $student) {
                $adminName = auth()->user()->name ?? 'Admin';

                $student->decrement('savings', $validated['amount']);

                SavingsTransaction::create([
                    'student_id' => $student->id,
                    'type' => 'withdrawal',
                    'amount' => $validated['amount'],
                    'balance_after' => $student->fresh()->savings,
                    'notes' => $validated['description'] ?? "Tarik tabungan oleh {$adminName}",
                ]);
            });

            return back()->with('success', 'Tabungan berhasil ditarik sebesar Rp ' . number_format($validated['amount'], 0, ',', '.'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menarik tabungan: ' . $e->getMessage());
        }
    }

    /**
     * Get transaction history for a student.
     */
    public function history(Request $request, Student $student)
    {
        $query = SavingsTransaction::where('student_id', $student->id);

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $transactions = $query->latest()->paginate(15)->withQueryString();

        // Summary
        $summary = [
            'total_deposit' => (clone $query)->where('type', 'deposit')->sum('amount'),
            'total_withdrawal' => (clone $query)->where('type', 'withdrawal')->sum('amount'),
        ];

        return response()->json([
            'transactions' => $transactions,
            'summary' => $summary,
            'student' => $student->load('user'),
        ]);
    }

    /**
     * Export savings transaction history to Excel.
     */
    public function export(Request $request, Student $student)
    {
        $filters = $request->only(['start_date', 'end_date', 'type']);
        
        $fileName = "tabungan-{$student->student_id}.xlsx";

        return Excel::download(new SavingsTransactionsExport($student->id, $filters), $fileName);
    }
}
