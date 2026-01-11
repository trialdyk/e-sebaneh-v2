<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentWithdrawHistory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentWithdrawHistoryExport;

class StudentWithdrawHistoryController extends Controller
{
    /**
     * Display transaction history with filters (Tab 3).
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $query = StudentWithdrawHistory::query()
            ->with('student.user')
            ->whereHas('student', fn($q) => $q->where('boarding_school_id', $boardingSchoolId));

        // Filters
        if ($request->filled('student_id') && $request->student_id !== 'all') {
            $query->where('student_id', $request->student_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        // Summary (based on filtered data)
        $summary = [
            'total_topup' => (clone $query)->whereIn('type', ['topup', 'topup_by_admin'])->sum('amount'),
            'total_withdraw' => (clone $query)->where('type', 'withdraw')->sum('amount'),
        ];

        $history = $query->latest('date')->latest('id')->paginate(15)->withQueryString();

        // Get students for filter dropdown
        $students = Student::where('boarding_school_id', $boardingSchoolId)
            ->with('user')
            ->orderBy('student_id')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'name' => $s->user->name,
                'student_id' => $s->student_id,
            ]);

        return Inertia::render('Dashboard/Finance/StudentBalance', [
            'history' => $history,
            'summary' => $summary,
            'students' => $students,
            'filters' => $request->only(['student_id', 'start_date', 'end_date', 'type']),
            'tab' => 'history',
        ]);
    }

    /**
     * Export transaction history to Excel.
     */
    public function export(Request $request)
    {
        $filters = $request->only(['student_id', 'start_date', 'end_date', 'type']);
        
        $fileName = 'Riwayat-Transaksi-Saldo';
        
        if ($request->filled('start_date')) {
            $fileName .= '-' . $request->start_date;
        }
        
        if ($request->filled('end_date')) {
            $fileName .= '-sd-' . $request->end_date;
        }
        
        $fileName .= '.xlsx';

        return Excel::download(new StudentWithdrawHistoryExport($filters), $fileName);
    }
}
