<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use App\Models\FinanceTransaction;
use App\Services\FinanceService;
use App\Enums\FinanceTransactionTypeEnum;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinanceTransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $query = FinanceTransaction::with(['account', 'user'])
            ->whereHas('account', function($q) use ($boardingSchoolId) {
                $q->where('boarding_school_id', $boardingSchoolId);
            });

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }
        if ($request->filled('account_id')) {
            $query->where('finance_account_id', $request->account_id);
        }

        $transactions = $query->latest('date')
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        $accounts = FinanceAccount::where('boarding_school_id', $boardingSchoolId)->get();

        return Inertia::render('Dashboard/Finance/Transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'filters' => $request->only(['start_date', 'end_date', 'account_id']),
        ]);
    }

    public function export(Request $request)
    {
         $user = $request->user();
         $boardingSchoolId = $user->boardingSchools()->first()?->id;
         
         if (!$boardingSchoolId) abort(403);

         return \Maatwebsite\Excel\Facades\Excel::download(
             new \App\Exports\FinanceTransactionExport(
                 $boardingSchoolId,
                 $request->start_date,
                 $request->end_date,
                 $request->account_id
             ),
             'Laporan_Arus_Kas_' . now()->format('Y-m-d_His') . '.xlsx'
         );
    }
    
    // Manual Transaction Store (for adjustments or general cash entries)
    public function store(Request $request, FinanceService $financeService)
    {
         $request->validate([
            'finance_account_id' => 'required|exists:finance_accounts,id',
            'type' => 'required|in:'.implode(',', array_column(FinanceTransactionTypeEnum::cases(), 'value')),
            'amount' => 'required|numeric|min:0',
            'description' => 'required|string|max:255',
        ]);
        
        // Auth check for account ownership
        
        try {
            $financeService->recordTransaction(
                $request->finance_account_id, // Pass ID as slug (service handles numeric check)
                $request->amount,
                FinanceTransactionTypeEnum::tryFrom($request->type),
                $request->description,
                $request->user(),
            );
            
            return back()->with('success', 'Transaksi berhasil dicatat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mencatat transaksi: ' . $e->getMessage());
        }
    }
}
