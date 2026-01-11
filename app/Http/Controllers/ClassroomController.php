<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class ClassroomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        $query = Classroom::query()
            ->with(['boardingSchool', 'schoolYear', 'teacher', 'students'])
            ->withCount('students');

        // Scope to admin's boarding schools
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('boarding_school_id', $boardingSchoolIds);
        }

        // Search by name
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter by School Year
        if ($request->filled('school_year_id')) {
            $query->where('school_year_id', $request->school_year_id);
        }

        $classrooms = $query->latest()->paginate(10)->withQueryString();

        $schoolYears = SchoolYear::orderBy('name')->get();

        return Inertia::render('Dashboard/Classroom/Index', [
            'classrooms' => $classrooms,
            'schoolYears' => $schoolYears,
            'filters' => $request->only(['search', 'school_year_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        $schoolYears = SchoolYear::latest()->get();
        
        // Get Teachers (Users with Teacher role/profile in this boarding school)
        // Adjusting query based on TeacherController logic:
        $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
        
        $teachers = User::whereHas('teacher', function($q) use ($boardingSchoolIds) {
            $q->whereIn('boarding_school_id', $boardingSchoolIds);
        })->get()->map(function($user) {
            return [
                'id' => $user->id, // We use User ID as teacher_id based on migration analysis
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return Inertia::render('Dashboard/Classroom/Create', [
            'schoolYears' => $schoolYears,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ClassroomRequest $request)
    {
        $data = $request->validated();
        
        // Assign boarding school from auth user
        // Assuming admin-pondok manages one or we pick the first one for now if multiple, 
        // or it should be part of the request if they manage multiple.
        // For now, defaulting to first boarding school of admin.
        $boardingSchoolId = auth()->user()->boardingSchools()->first()?->id;
        
        if (!$boardingSchoolId) {
            return back()->withErrors(['error' => 'Anda tidak memiliki akses ke Pesantren manapun.']);
        }

        $data['boarding_school_id'] = $boardingSchoolId;

        Classroom::create($data);

        return redirect()->route('dashboard.classrooms.index')
            ->with('success', 'Kelas berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Classroom $classroom)
    {
        // For now, maybe just redirect to index or show students? 
        // User asked to ignore detail feature for now.
        return redirect()->route('dashboard.classrooms.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Classroom $classroom): Response
    {
        $user = auth()->user();
        
        $schoolYears = SchoolYear::latest()->get(); // Show all for edit history
        
        $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
        
        $teachers = User::whereHas('teacher', function($q) use ($boardingSchoolIds) {
            $q->whereIn('boarding_school_id', $boardingSchoolIds);
        })->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return Inertia::render('Dashboard/Classroom/Edit', [
            'classroom' => $classroom,
            'schoolYears' => $schoolYears,
            'teachers' => $teachers,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($request->validated());

        return redirect()->route('dashboard.classrooms.index')
            ->with('success', 'Kelas berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Classroom $classroom)
    {
        try {
            $classroom->delete();
            
            return redirect()->route('dashboard.classrooms.index')
                ->with('success', 'Kelas berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal menghapus kelas.']);
        }
    }
}
