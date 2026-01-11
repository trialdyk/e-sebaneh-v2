<?php

namespace App\Http\Controllers;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use App\Http\Requests\StudentRequest;
use App\Models\BedRoom;
use App\Models\BoardingSchool;
use App\Models\Classroom;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\School;
use App\Models\SchoolLevel;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\User;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // Get classrooms for filter dropdown
        $classrooms = Classroom::query()
            ->when($user->hasRole('admin-pondok'), function ($query) use ($user) {
                $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
                $query->whereIn('boarding_school_id', $boardingSchoolIds);
            })
            ->where('school_year_id', SchoolYear::getActiveSchoolYear()?->id)
            ->get(['id', 'name']);

        $students = Student::query()
            ->with(['user', 'boardingSchool', 'currentClassroom.classroom'])
            // Scope to admin's boarding schools only
            ->when($user->hasRole('admin-pondok'), function ($query) use ($user) {
                $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
                $query->whereIn('boarding_school_id', $boardingSchoolIds);
            })
            // Search by name or student_id
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })->orWhere('student_id', 'like', "%{$search}%");
                });
            })
            // Filter by RFID
            ->when($request->rfid, function ($query, $rfid) {
                $query->where('rfid', 'like', "%{$rfid}%");
            })
            // Filter by status (default to 'active')
            ->when($request->filled('status') && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            }, function ($query) use ($request) {
                // Default to active if no status filter or status is 'all'
                if (!$request->filled('status')) {
                    $query->where('status', 'active');
                }
            })
            // Filter by gender
            ->when($request->gender && $request->gender !== 'all', function ($query, $gender) {
                $query->where('gender', $gender);
            })
            // Filter by classroom
            ->when($request->classroom_id && $request->classroom_id !== 'all', function ($query, $classroomId) {
                $query->whereHas('currentClassroom', function ($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/Student/Index', [
            'students' => $students,
            'classrooms' => $classrooms,
            'filters' => $request->only(['search', 'rfid', 'status', 'gender', 'classroom_id']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();

        // 1. Boarding Schools
        $boardingSchoolQuery = BoardingSchool::select('id', 'name');
        if ($user->hasRole('admin-pondok')) {
             $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
             $boardingSchoolQuery->whereIn('id', $boardingSchoolIds);
        }
        $boardingSchools = $boardingSchoolQuery->get();

        // 2. Enum Options
        // 2. Enum Options
        $genders = [
            ['value' => 'male', 'label' => 'Laki-laki'],
            ['value' => 'female', 'label' => 'Perempuan'],
        ];
        
        $statuses = [
            ['value' => 'active', 'label' => 'Aktif'],
            ['value' => 'inactive', 'label' => 'Tidak Aktif'],
            ['value' => 'graduated', 'label' => 'Lulus'],
            ['value' => 'dropped_out', 'label' => 'Keluar/DO'],
        ];

        // 3. Regions (Initial Provinces)
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        // 4. Academic Data
        $activeSchoolYear = SchoolYear::getActiveSchoolYear();
        
        // Fetch Classrooms & Bedrooms (Ideally filtered by Boarding School on frontend, passing all for now or optimize via API)
        // Optimization: Pass empty, let frontend fetch via API when Boarding School selected?
        // For now, let's pass all accessible ones.
        $classrooms = Classroom::when($user->hasRole('admin-pondok'), fn($q) => $q->whereIn('boarding_school_id', $boardingSchoolIds ?? []))
            ->with('schoolYear')
            ->where('school_year_id', $activeSchoolYear?->id)
            ->get(['id', 'name', 'boarding_school_id']);
            
        $bedRooms = BedRoom::when($user->hasRole('admin-pondok'), fn($q) => $q->whereIn('boarding_school_id', $boardingSchoolIds ?? []))
            ->get(['id', 'name', 'boarding_school_id']);

        // 5. Formal Schools
        $schools = School::with('schoolLevels')->orderBy('name')->get();

        return Inertia::render('Dashboard/Student/Create', [
            'boardingSchools' => $boardingSchools,
            'genders' => $genders,
            'statuses' => $statuses,
            'provinces' => $provinces,
            'classrooms' => $classrooms,
            'bedRooms' => $bedRooms,
            'schools' => $schools,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StudentRequest $request)
    {
        DB::transaction(function () use ($request) {
            // 0. Handle Photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('student_photos', 'public');
            }

            // 1. Create User
            // Auto-generate email if not provided
            $email = $request->email ?: strtolower(str_replace(' ', '', $request->student_id)) . '@student.pondok.id';
            
            $user = User::create([
                'name' => $request->name,
                'email' => $email,
                'password' => Hash::make('password'), 
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'photo' => $photoPath,
                'balance' => 0, // Default balance
            ]);
            $user->assignRole('student');

            // 2. Resolve Region Names
            $provinceName = Province::find($request->province_id)?->name;
            $regencyName = Regency::find($request->regency_id)?->name;
            $districtName = District::find($request->district_id)?->name;
            $villageName = Village::find($request->village_id)?->name;

            // 3. Create Student
            $studentData = $request->validated();
            
            // Auto-assign Boarding School for 'admin-pondok'
            // User requested that ONLY admin-pondok can input students.
            if (!auth()->user()->hasRole('admin-pondok')) {
                abort(403, 'Hanya Admin Pondok yang dapat menambahkan santri.');
            }
            $boardingSchoolId = auth()->user()->boardingSchools()->first()?->id;

            // Remove non-student table fields (pivots, region IDs, etc)
            $excludedFields = [
                'province_id', 'regency_id', 'district_id', 'village_id',
                'classroom_id', 'bed_room_id', 'school_id', 'school_level_id',
                'photo', 'email', 'name', 'phone_number', // User fields
                'boarding_school_id', // Handle manually
            ];
            
            $studentInput = array_diff_key($studentData, array_flip($excludedFields));
            
            $student = Student::create(array_merge($studentInput, [
                'user_id' => $user->id,
                'province' => $provinceName,
                'regency' => $regencyName,
                'district' => $districtName,
                'village' => $villageName,
                'boarding_school_id' => $boardingSchoolId,
            ]));

            // 4. Handle Pivots (Academic History)
            $activeSchoolYear = SchoolYear::getActiveSchoolYear();

            if ($activeSchoolYear) {
                // Classroom
                if ($request->filled('classroom_id')) {
                    $student->classrooms()->attach($request->classroom_id, ['school_year_id' => $activeSchoolYear->id]);
                }

                // Bedroom
                if ($request->filled('bed_room_id')) {
                    $student->bedRooms()->attach($request->bed_room_id, ['school_year_id' => $activeSchoolYear->id]);
                }

                // Formal School
                if ($request->filled('school_id') && $request->filled('school_level_id')) {
                    $student->schools()->attach($request->school_id, [
                        'school_level_id' => $request->school_level_id,
                        'school_year_id' => $activeSchoolYear->id
                    ]);
                }
            }
        });

        return redirect()->route('dashboard.students.index')->with('success', 'Student created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load([
            'user', 
            'boardingSchool',
            'classrooms.schoolYear',
            'bedRooms.schoolYear',
            'schools.schoolLevels',
            'currentClassroom.classroom',
            'diseases',
            'permissions',
            'violations',
            'memorizes.surah',
            'memorizes.teacher.user',
        ]);

        return Inertia::render('Dashboard/Student/Show', [
            'student' => $student,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $user = auth()->user();
        $student->load(['user', 'classrooms', 'bedRooms', 'schools']);

        // 1. Boarding Schools (Same as create)
        $boardingSchoolQuery = BoardingSchool::select('id', 'name');
        if ($user->hasRole('admin-pondok')) {
             $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
             $boardingSchoolQuery->whereIn('id', $boardingSchoolIds);
        }
        $boardingSchools = $boardingSchoolQuery->get();

        // 2. Options
        // 2. Options
        $genders = [
            ['value' => 'male', 'label' => 'Laki-laki'],
            ['value' => 'female', 'label' => 'Perempuan'],
        ];
        
        $statuses = [
            ['value' => 'active', 'label' => 'Aktif'],
            ['value' => 'inactive', 'label' => 'Tidak Aktif'],
            ['value' => 'graduated', 'label' => 'Lulus'],
            ['value' => 'dropped_out', 'label' => 'Keluar/DO'],
        ];
        $provinces = Province::orderBy('name')->get(['id', 'name']);
        
        // 3. Init Regions based on saved data
        // We need to resolve IDs back from names if we want to pre-fill standard dropdowns.
        // Or if the frontend component handles "Name" vs "ID", but standard dropdowns usually need IDs.
        // Querying IDs from names:
        $currentProvince = Province::where('name', $student->province)->first();
        $currentRegency = Regency::where('name', $student->regency)->where('province_id', $currentProvince?->id)->first();
        $currentDistrict = District::where('name', $student->district)->where('regency_id', $currentRegency?->id)->first();
        $currentVillage = Village::where('name', $student->village)->where('district_id', $currentDistrict?->id)->first();

        // Load dependent regions for the form to display initial lists
        $initialRegencies = $currentProvince ? Regency::where('province_id', $currentProvince->id)->orderBy('name')->get(['id', 'name']) : [];
        $initialDistricts = $currentRegency ? District::where('regency_id', $currentRegency->id)->orderBy('name')->get(['id', 'name']) : [];
        $initialVillages = $currentDistrict ? Village::where('district_id', $currentDistrict->id)->orderBy('name')->get(['id', 'name']) : [];

        // 4. Current Academic Data (For form pre-fill)
        $activeSchoolYear = SchoolYear::getActiveSchoolYear();
        
        // Find current pivots for active year
        $currentClassroomPivot = $student->classrooms()->wherePivot('school_year_id', $activeSchoolYear?->id)->first();
        $currentBedRoomPivot = $student->bedRooms()->wherePivot('school_year_id', $activeSchoolYear?->id)->first();
        $currentSchoolPivot = $student->schools()->wherePivot('school_year_id', $activeSchoolYear?->id)->first();

        // 5. Lists
         $classrooms = Classroom::when($user->hasRole('admin-pondok'), fn($q) => $q->whereIn('boarding_school_id', $boardingSchoolIds ?? []))
            ->where('school_year_id', $activeSchoolYear?->id)
            ->get(['id', 'name', 'boarding_school_id']);
        $bedRooms = BedRoom::when($user->hasRole('admin-pondok'), fn($q) => $q->whereIn('boarding_school_id', $boardingSchoolIds ?? []))
            ->get(['id', 'name', 'boarding_school_id']);
        $schools = School::with('schoolLevels')->orderBy('name')->get();

        return Inertia::render('Dashboard/Student/Edit', [
            'student' => $student,
            'boardingSchools' => $boardingSchools,
            'genders' => $genders,
            'statuses' => $statuses,
            'provinces' => $provinces,
            'initialRegencies' => $initialRegencies,
            'initialDistricts' => $initialDistricts,
            'initialVillages' => $initialVillages,
            'regionIds' => [
                'province_id' => $currentProvince?->id,
                'regency_id' => $currentRegency?->id,
                'district_id' => $currentDistrict?->id,
                'village_id' => $currentVillage?->id,
            ],
            'academicIds' => [
                'classroom_id' => $currentClassroomPivot?->id, // This returns the Classroom model, so getting ID is correct
                'bed_room_id' => $currentBedRoomPivot?->id,
                'school_id' => $currentSchoolPivot?->id,
                'school_level_id' => $currentSchoolPivot?->pivot?->school_level_id,
            ],
            'classrooms' => $classrooms,
            'bedRooms' => $bedRooms,
            'schools' => $schools,
            'activeSchoolYear' => $activeSchoolYear,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StudentRequest $request, Student $student)
    {
        DB::transaction(function () use ($request, $student) {
            // 1. Update User
            $user = $student->user;
            
            $photoPath = $user->photo;
            if ($request->hasFile('photo')) {
                if ($photoPath) Storage::disk('public')->delete($photoPath);
                $photoPath = $request->file('photo')->store('student_photos', 'public');
            }

            $user->update([
                'name' => $request->name,
                'gender' => $request->gender,
                'phone_number' => $request->phone_number,
                'photo' => $photoPath,
            ]);
            
            // Email update handling (check uniqueness or if changed) - logic handled by Request validation usually, 
            // but we need to update it here manually if validation passed
            if ($request->email !== $user->email) {
                $user->update(['email' => $request->email]);
            }

            // 2. Resolve Region Names
            $provinceName = Province::find($request->province_id)?->name ?? $student->province;
            $regencyName = Regency::find($request->regency_id)?->name ?? $student->regency;
            $districtName = District::find($request->district_id)?->name ?? $student->district;
            $villageName = Village::find($request->village_id)?->name ?? $student->village;

            // 3. Update Student
            $studentData = $request->validated();
            $excludedFields = [
                'province_id', 'regency_id', 'district_id', 'village_id',
                'classroom_id', 'bed_room_id', 'school_id', 'school_level_id',
                'photo', 'email', 'name', 'phone_number', 
            ];
            $studentInput = array_diff_key($studentData, array_flip($excludedFields));
            
            $student->update(array_merge($studentInput, [
                'province' => $provinceName,
                'regency' => $regencyName,
                'district' => $districtName,
                'village' => $villageName,
            ]));

            // 4. Handle Pivots (Update for ACTIVE School Year Only)
            $activeSchoolYear = SchoolYear::getActiveSchoolYear();

            if ($activeSchoolYear) {
                // Determine sync or update logic. 
                // syncWithPivotValues could work but we want to target specific school_year_id.
                // Safest is to detach for current year then attach, OR update existing row.
                
                // Classroom
                if ($request->filled('classroom_id')) {
                    // Detach any existing for this year
                    $student->classrooms()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                    // Attach new
                    $student->classrooms()->attach($request->classroom_id, ['school_year_id' => $activeSchoolYear->id]);
                } elseif ($request->has('classroom_id')) { // present but null/empty -> clear
                     $student->classrooms()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                }

                // BedRoom
                if ($request->filled('bed_room_id')) {
                    $student->bedRooms()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                    $student->bedRooms()->attach($request->bed_room_id, ['school_year_id' => $activeSchoolYear->id]);
                } elseif ($request->has('bed_room_id')) {
                     $student->bedRooms()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                }

                // Formal School
                 if ($request->filled('school_id') && $request->filled('school_level_id')) {
                    $student->schools()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                    $student->schools()->attach($request->school_id, [
                        'school_level_id' => $request->school_level_id,
                        'school_year_id' => $activeSchoolYear->id
                    ]);
                } elseif ($request->has('school_id')) {
                     $student->schools()->wherePivot('school_year_id', $activeSchoolYear->id)->detach();
                }
            }
        });

        return redirect()->route('dashboard.students.index')->with('success', 'Student updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        DB::transaction(function() use ($student) {
            $student->user->delete();
            $student->delete();
        });

        return redirect()->back()->with('success', 'Student deleted successfully.');
    }

    /**
     * Export students to Excel
     */
    public function export(Request $request)
    {
        $filters = $request->only(['search']);
        
        $fileName = 'Data_Santri_' . now()->format('Y-m-d_His') . '.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentsExport($filters), $fileName);
    }

    /**
     * Download Excel template for import
     */
    public function downloadTemplate()
    {
        $fileName = 'Template_Import_Santri.xlsx';
        
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\StudentsExport([], true), $fileName);
    }

    /**
     * Import students from Excel
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
            $import = new \App\Imports\StudentsImport();
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('file'));

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

            return redirect()->route('dashboard.students.index')
                ->with('success', 'Data santri berhasil diimpor!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }
}

