<?php

namespace App\Http\Controllers;

use App\Models\StudentWithdrawLimit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentWithdrawLimitController extends Controller
{
    /**
     * Display the withdraw limit configuration page.
     */
    public function index()
    {
        $user = auth()->user();

        // Get boarding school for admin-pondok
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $withdrawLimit = StudentWithdrawLimit::query()
            ->where('boarding_school_id', $boardingSchoolId)
            ->first();

        return Inertia::render('Dashboard/Finance/StudentWithdrawLimit', [
            'withdrawLimit' => $withdrawLimit,
        ]);
    }

    /**
     * Update the withdraw limit.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        $validated = $request->validate([
            'limit' => 'required|numeric|min:0',
        ], [
            'limit.required' => 'Batas penarikan wajib diisi',
            'limit.min' => 'Batas penarikan minimal 0',
        ]);

        StudentWithdrawLimit::query()->updateOrCreate(
            ['boarding_school_id' => $boardingSchoolId],
            $validated
        );

        return back()->with('success', 'Batas penarikan berhasil diperbarui.');
    }
}
