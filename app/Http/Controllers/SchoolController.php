<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolRequest;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class SchoolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $schools = School::query()
            ->with(['schoolLevels' => function ($query) {
                $query->orderBy('order_level');
            }])
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('short_name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Schools/Index', [
            'schools' => $schools,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Dashboard/Schools/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SchoolRequest $request)
    {
        DB::transaction(function () use ($request) {
            $school = School::create([
                'name' => $request->name,
                'short_name' => $request->short_name,
            ]);

            if ($request->has('levels')) {
                foreach ($request->levels as $level) {
                    $school->schoolLevels()->create([
                        'name' => $level['name'],
                        'order_level' => $level['order_level'],
                    ]);
                }
            }
        });

        return redirect()->route('schools.index')->with('success', 'School created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(School $school)
    {
        $school->load(['schoolLevels' => function ($query) {
            $query->orderBy('order_level');
        }]);

        return Inertia::render('Dashboard/Schools/Edit', [
            'school' => $school,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SchoolRequest $request, School $school)
    {
        DB::transaction(function () use ($request, $school) {
            $school->update([
                'name' => $request->name,
                'short_name' => $request->short_name,
            ]);

            // Sync levels needs careful handling since it's a HasMany relation, not BelongsToMany
            // Strategy: Delete all existing and re-create, OR update existing and create new / delete removed.
            // For simplicity in master data, we can wipe and recreate if IDs are not critical for history yet.
            // However, existing data might link to IDs.
            // Better strategy: Use updateOrCreate logic or deleteNotIn logic.

            // Current input 'levels' from frontend.
            $inputLevels = collect($request->levels);

            // Get existing IDs
            $existingIds = $school->schoolLevels()->pluck('id')->toArray();
            $inputIds = $inputLevels->pluck('id')->filter()->toArray();

            // Delete removed levels
            $toDelete = array_diff($existingIds, $inputIds);
            $school->schoolLevels()->whereIn('id', $toDelete)->delete();

            // Update or Create
            foreach ($inputLevels as $levelData) {
                if (isset($levelData['id'])) {
                    $school->schoolLevels()->where('id', $levelData['id'])->update([
                        'name' => $levelData['name'],
                        'order_level' => $levelData['order_level'],
                    ]);
                } else {
                    $school->schoolLevels()->create([
                        'name' => $levelData['name'],
                        'order_level' => $levelData['order_level'],
                    ]);
                }
            }
        });

        return redirect()->route('schools.index')->with('success', 'School updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(School $school)
    {
        $school->schoolLevels()->delete();
        $school->delete();

        return redirect()->back()->with('success', 'School deleted successfully.');
    }
}
