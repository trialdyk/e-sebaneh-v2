<?php

namespace App\Http\Controllers;

use App\Exports\TeachersExport;
use App\Http\Requests\TeacherRequest;
use App\Imports\TeachersImport;
use App\Models\BoardingSchool;
use App\Models\Position;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class TeacherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = auth()->user();
        
        $query = Teacher::query()
            ->with(['user', 'position', 'boardingSchool']);
            
        // Scope to admin's boarding schools
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('boarding_school_id', $boardingSchoolIds);
        }
        
        // Search by name
        if ($request->filled('search')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            });
        }
        
        // Filter by position
        if ($request->filled('position_id')) {
            $query->where('position_id', $request->position_id);
        }

        $teachers = $query->latest()->paginate(10)->withQueryString();
        
        $positions = Position::orderBy('name')->get();

        return Inertia::render('Dashboard/Teacher/Index', [
            'teachers' => $teachers,
            'positions' => $positions,
            'filters' => $request->only(['search', 'position_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $user = auth()->user();
        
        $positions = Position::orderBy('name')->get();
        
        $query = BoardingSchool::select('id', 'name');
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('id', $boardingSchoolIds);
        }
        
        $boardingSchools = $query->get();

        return Inertia::render('Dashboard/Teacher/Create', [
            'positions' => $positions,
            'boardingSchools' => $boardingSchools,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TeacherRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Create user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'password' => Hash::make($data['password']),
                'gender' => $data['gender'] ?? null,
            ]);
            
            // Assign default role to teacher user
            $user->assignRole('teacher');
            
            // Get boarding school ID
            $boardingSchoolId = $data['boarding_school_id'] ?? auth()->user()->boardingSchools()->first()?->id;
            
            // Create teacher profile
            Teacher::create([
                'user_id' => $user->id,
                'boarding_school_id' => $boardingSchoolId,
                'position_id' => $data['position_id'],
                'nip' => $data['nip'] ?? null,
            ]);
            
            DB::commit();
            
            return redirect()->route('dashboard.teachers.index')
                ->with('success', 'Pegawai berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Gagal menambahkan pegawai: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Teacher $teacher): Response
    {
        $teacher->load(['user', 'position', 'boardingSchool']);
        
        return Inertia::render('Dashboard/Teacher/Show', [
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Teacher $teacher): Response
    {
        $user = auth()->user();
        
        $teacher->load(['user', 'position']);
        
        $positions = Position::orderBy('name')->get();
        
        $query = BoardingSchool::select('id', 'name');
        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
            $query->whereIn('id', $boardingSchoolIds);
        }
        
        $boardingSchools = $query->get();

        return Inertia::render('Dashboard/Teacher/Edit', [
            'teacher' => $teacher,
            'positions' => $positions,
            'boardingSchools' => $boardingSchools,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TeacherRequest $request, Teacher $teacher)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            
            // Update user
            $user = $teacher->user;
            $user->update([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? null,
                'gender' => $data['gender'] ?? $user->gender,
            ]);
            
            // Update password if provided
            if (!empty($data['password'])) {
                $user->update([
                    'password' => Hash::make($data['password'])
                ]);
            }
            
            // Update teacher
            $teacher->update([
                'position_id' => $data['position_id'],
                'nip' => $data['nip'] ?? null,
            ]);
            
            DB::commit();
            
            return redirect()->route('dashboard.teachers.index')
                ->with('success', 'Data pegawai berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withErrors([
                'error' => 'Gagal memperbarui pegawai: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Teacher $teacher)
    {
        try {
            // Delete user (will cascade delete teacher)
            $teacher->user->delete();
            
            return redirect()->route('dashboard.teachers.index')
                ->with('success', 'Data pegawai berhasil dihapus.');
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Gagal menghapus pegawai: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Export teachers to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search', 'position_id']);
        
        $fileName = 'Data_Pegawai_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return Excel::download(new TeachersExport($filters), $fileName);
    }

    /**
     * Download Excel template for import
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_Pegawai.xlsx';
        
        return Excel::download(new TeachersExport([], true), $fileName);
    }

    /**
     * Import teachers from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120', // Max 5MB
        ], [
            'file.required' => 'File Excel wajib dipilih',
            'file.mimes' => 'File harus berformat .xlsx atau .xls',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        try {
            $import = new TeachersImport();
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            $errors = $import->errors();

            // If there are failures or errors
            if ($failures->isNotEmpty()) {
                $errorMessages = [];
                foreach ($failures as $failure) {
                    $row = $failure->row();
                    $errors = $failure->errors();
                    $values = $failure->values();
                    
                    $valuesString = implode(', ', array_slice($values, 0, 3));
                    if (count($values) > 3) {
                        $valuesString .= '...';
                    }
                    
                    $errorMessages[] = "Baris {$row}: " . implode(', ', $errors) . " (Data: {$valuesString})";
                }

                return back()->with([
                    'error' => 'Terdapat kesalahan pada ' . $failures->count() . ' baris data',
                    'import_errors' => $errorMessages,
                ]);
            }

            if (!empty($errors)) {
                return back()->with([
                    'error' => 'Terdapat error saat import',
                    'import_errors' => array_map(fn($e) => $e->getMessage(), $errors),
                ]);
            }

            return redirect()->route('dashboard.teachers.index')
                ->with('success', 'Data pegawai berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}
