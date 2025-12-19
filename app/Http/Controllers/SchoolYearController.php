<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolYear\StoreSchoolYearRequest;
use App\Http\Requests\SchoolYear\UpdateSchoolYearRequest;
use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SchoolYearController extends Controller
{
    /**
     * Display a listing of school years
     */
    public function index(): Response
    {
        $schoolYears = SchoolYear::latest()->get();

        return Inertia::render('Dashboard/SchoolYear/Index', [
            'schoolYears' => $schoolYears,
        ]);
    }

    /**
     * Store a newly created school year
     */
    public function store(StoreSchoolYearRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $schoolYear = SchoolYear::create([
            'name' => $validated['name'],
            'is_active' => false, // Default to inactive
        ]);

        // If is_active is true, activate this school year
        if ($request->boolean('is_active')) {
            $schoolYear->activate();
        }

        return back()->with('success', 'Tahun ajaran berhasil ditambahkan!');
    }

    /**
     * Update the specified school year
     */
    public function update(UpdateSchoolYearRequest $request, SchoolYear $schoolYear): RedirectResponse
    {
        $validated = $request->validated();

        $schoolYear->update([
            'name' => $validated['name'],
        ]);

        // If is_active is true, activate this school year
        if ($request->boolean('is_active')) {
            $schoolYear->activate();
        } elseif ($schoolYear->is_active && ! $request->boolean('is_active')) {
            // If currently active but set to inactive
            $schoolYear->update(['is_active' => false]);
        }

        return back()->with('success', 'Tahun ajaran berhasil diperbarui!');
    }

    /**
     * Activate specific school year
     */
    public function activate(SchoolYear $schoolYear): RedirectResponse
    {
        $schoolYear->activate();

        return back()->with('success', "Tahun ajaran {$schoolYear->name} berhasil diaktifkan!");
    }

    /**
     * Remove the specified school year
     */
    public function destroy(SchoolYear $schoolYear): RedirectResponse
    {
        try {
            // Check if school year is active
            if ($schoolYear->is_active) {
                return back()->withErrors([
                    'delete' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif!',
                ]);
            }

            $schoolYear->delete();

            return back()->with('success', 'Tahun ajaran berhasil dihapus!');
        } catch (\Exception $e) {
            return back()->withErrors([
                'delete' => 'Gagal menghapus tahun ajaran. Data masih digunakan di tabel lain.',
            ]);
        }
    }
}
