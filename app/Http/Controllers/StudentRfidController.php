<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\SchoolYear;
use App\Models\Student;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StudentRfidController extends Controller
{
    /**
     * Display the RFID assignment page.
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
            ->with(['user', 'currentClassroom.classroom'])
            ->when($user->hasRole('admin-pondok'), function ($query) use ($user) {
                $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');
                $query->whereIn('boarding_school_id', $boardingSchoolIds);
            })
            // Filter by RFID status (default: unregistered)
            ->when($request->filled('rfid_status'), function ($query) use ($request) {
                if ($request->rfid_status === 'registered') {
                    $query->whereNotNull('rfid');
                } elseif ($request->rfid_status === 'unregistered') {
                    $query->whereNull('rfid');
                }
                // 'all' shows everything
            }, function ($query) {
                // Default: show only unregistered
                $query->whereNull('rfid');
            })
            // Filter by classroom
            ->when($request->classroom_id && $request->classroom_id !== 'all', function ($query, $classroomId) {
                $query->whereHas('currentClassroom', function ($q) use ($classroomId) {
                    $q->where('classroom_id', $classroomId);
                });
            })
            // Search by name or student_id
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->whereHas('user', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%");
                    })->orWhere('student_id', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Dashboard/StudentRfid/Index', [
            'students' => $students,
            'classrooms' => $classrooms,
            'filters' => $request->only(['search', 'classroom_id', 'rfid_status']),
        ]);
    }

    /**
     * Update student's RFID.
     */
    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'rfid' => 'required|string|max:255|unique:students,rfid,'.$student->id,
        ], [
            'rfid.required' => 'RFID wajib diisi',
            'rfid.unique' => 'RFID sudah terdaftar untuk santri lain',
        ]);

        $student->update(['rfid' => $validated['rfid']]);

        return back()->with('success', 'RFID berhasil didaftarkan.');
    }
}
