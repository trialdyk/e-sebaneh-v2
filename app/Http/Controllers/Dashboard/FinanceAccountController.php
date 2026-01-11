<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\FinanceAccountTypeEnum;
use App\Http\Controllers\Controller;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class FinanceAccountController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        // Assuming Admin Pondok has exactly one boarding school attached or we pick the first one
        // Ideally handled via middleware/scope, but here explicit:
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        if (!$boardingSchoolId) {
            abort(403, 'User not assigned to any Boarding School');
        }

        $accounts = FinanceAccount::where('boarding_school_id', $boardingSchoolId)
            ->orderBy('is_system', 'desc') // System accounts first
            ->orderBy('name', 'asc')
            ->get();

        return Inertia::render('Dashboard/Finance/Accounts/Index', [
            'accounts' => $accounts,
            'types' => FinanceAccountTypeEnum::cases(),
        ]);
    }

    public function show(Request $request, FinanceAccount $account)
    {
        $transactions = $account->transactions()
            ->latest('date')
            ->latest('created_at')
            ->paginate(10);

        return Inertia::render('Dashboard/Finance/Accounts/Show', [
            'account' => $account,
            'transactions' => $transactions,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|in:income,expense,general',
            'description' => 'nullable|string',
        ]);

        FinanceAccount::create([
            'boarding_school_id' => $boardingSchoolId,
            'name' => $request->name,
            'type' => $request->type ?? 'general', // Default to general
            'description' => $request->description,
            'is_system' => false,
            'balance' => 0,
        ]);

        return back()->with('success', 'Pos Keuangan berhasil dibuat.');
    }

    public function update(Request $request, FinanceAccount $account)
    {
        // Add authorization check: $account->boarding_school_id matches user
        if ($account->is_system) {
            return back()->with('error', 'Pos Keuangan Sistem tidak dapat diubah.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $account->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Pos Keuangan berhasil diperbarui.');
    }

    public function destroy(FinanceAccount $account)
    {
        if ($account->is_system) {
            return back()->with('error', 'Pos Keuangan Sistem tidak dapat dihapus.');
        }

        // Check for transactions? Probably shouldn't delete if has history.
        if ($account->transactions()->exists()) {
             return back()->with('error', 'Pos Keuangan memiliki riwayat transaksi dan tidak dapat dihapus.');
        }

        $account->delete();

        return back()->with('success', 'Pos Keuangan berhasil dihapus.');
    }

    public function export(FinanceAccount $account)
    {
        $filename = 'Transaksi_' . str_replace(' ', '_', $account->name) . '_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\FinanceTransactionExport(
                $account->boarding_school_id,
                null, // No start date filter
                null, // No end date filter
                $account->id
            ),
            $filename
        );
    }
}
