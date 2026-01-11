<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BoardingSchool;
use App\Models\Classroom;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use App\Models\BedRoom;
use App\Models\FinanceAccount;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get role names as array
        $roles = $user->roles ?? [];
        if ($roles instanceof \Illuminate\Database\Eloquent\Collection) {
            $roles = $roles->pluck('name')->toArray();
        }

        // Super Admin Dashboard
        if (in_array('super-admin', $roles)) {
            return $this->superAdminDashboard();
        }

        // Admin Pondok Dashboard
        if (in_array('admin-pondok', $roles)) {
            return $this->adminPondokDashboard($user);
        }

        // Default dashboard (fallback)
        return Inertia::render('Dashboard/Index', [
            'stats' => [],
        ]);
    }

    private function superAdminDashboard()
    {
        $stats = [
            'boarding_schools' => BoardingSchool::count(),
            'students' => Student::count(),
            'teachers' => Teacher::count(),
            'users' => User::count(),
        ];

        $boardingSchools = BoardingSchool::withCount('students')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function ($school) {
                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'students' => $school->students_count,
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'boardingSchools' => $boardingSchools,
        ]);
    }

    private function adminPondokDashboard(User $user)
    {
        $boardingSchoolId = $user->boardingSchools()->first()?->id;

        if (!$boardingSchoolId) {
            return Inertia::render('Dashboard/Index', ['stats' => []]);
        }

        // Main stats
        $stats = [
            'students' => Student::where('boarding_school_id', $boardingSchoolId)->where('status', 'active')->count(),
            'classrooms' => Classroom::where('boarding_school_id', $boardingSchoolId)->count(),
            'teachers' => Teacher::where('boarding_school_id', $boardingSchoolId)->count(),
            'bed_rooms' => BedRoom::where('boarding_school_id', $boardingSchoolId)->count(),
        ];

        // Finance stats
        $financeAccounts = FinanceAccount::where('boarding_school_id', $boardingSchoolId)->get();
        $totalBalance = $financeAccounts->sum('balance');
        $pendingBalance = $financeAccounts->sum('pending_balance');

        $stats['total_balance'] = $totalBalance;
        $stats['pending_balance'] = $pendingBalance;

        // Gender distribution
        $genderData = [
            'male' => Student::where('boarding_school_id', $boardingSchoolId)
                ->where('status', 'active')
                ->where('gender', 'male')
                ->count(),
            'female' => Student::where('boarding_school_id', $boardingSchoolId)
                ->where('status', 'active')
                ->where('gender', 'female')
                ->count(),
        ];

        // Age distribution
        $students = Student::where('boarding_school_id', $boardingSchoolId)
            ->where('status', 'active')
            ->get();

        $ages = ['7-12' => 0, '13-15' => 0, '16-20' => 0];
        foreach ($students as $student) {
            $age = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->age : 0;
            if ($age >= 7 && $age <= 12) $ages['7-12']++;
            elseif ($age >= 13 && $age <= 15) $ages['13-15']++;
            elseif ($age >= 16 && $age <= 20) $ages['16-20']++;
        }

        $totalStudents = $students->count();
        $ageData = [
            [
                'label' => '7-12 tahun',
                'value' => $ages['7-12'],
                'color' => 'bg-blue-500',
                'percentage' => $totalStudents > 0 ? round(($ages['7-12'] / $totalStudents) * 100) : 0,
            ],
            [
                'label' => '13-15 tahun',
                'value' => $ages['13-15'],
                'color' => 'bg-green-500',
                'percentage' => $totalStudents > 0 ? round(($ages['13-15'] / $totalStudents) * 100) : 0,
            ],
            [
                'label' => '16-20 tahun',
                'value' => $ages['16-20'],
                'color' => 'bg-purple-500',
                'percentage' => $totalStudents > 0 ? round(($ages['16-20'] / $totalStudents) * 100) : 0,
            ],
        ];

        // Recent permissions (last 5)
        $recentPermissions = \App\Models\StudentPermission::whereHas('student', function ($q) use ($boardingSchoolId) {
            $q->where('boarding_school_id', $boardingSchoolId);
        })
            ->with('student.user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'student' => $permission->student->user->name ?? 'N/A',
                    'reason' => $permission->reason,
                    'status' => $permission->return_date ? 'approved' : 'pending',
                ];
            });

        // Recent violations (last 5)
        $recentViolations = \App\Models\StudentViolation::whereHas('student', function ($q) use ($boardingSchoolId) {
            $q->where('boarding_school_id', $boardingSchoolId);
        })
            ->with('student.user')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($violation) {
                return [
                    'id' => $violation->id,
                    'student' => $violation->student->user->name ?? 'N/A',
                    'violation' => $violation->violation,
                    'points' => $violation->points ?? 0,
                ];
            });

        return Inertia::render('Dashboard/Index', [
            'stats' => $stats,
            'genderData' => $genderData,
            'ageData' => $ageData,
            'recentPermissions' => $recentPermissions,
            'recentViolations' => $recentViolations,
        ]);
    }
}
