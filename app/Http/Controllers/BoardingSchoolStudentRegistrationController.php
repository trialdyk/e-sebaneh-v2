<?php

namespace App\Http\Controllers;

use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Barryvdh\DomPDF\Facade\Pdf;
use Inertia\Inertia;
use Illuminate\Support\Str;

class BoardingSchoolStudentRegistrationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        // Assume user is admin-pondok and has boarding schools
        $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id');

        $registrations = StudentRegistration::query()
            ->whereIn('boarding_school_id', $boardingSchoolIds)
            // Filter by status
            ->when($request->status, function ($q, $status) {
                if ($status !== 'all') {
                    $q->where('status', $status);
                }
            })
            // Search
            ->when($request->search, function ($q, $search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Dashboard/StudentRegistration/Index', [
            'registrations' => $registrations,
            'filters' => $request->only(['status', 'search']),
        ]);
    }

    public function show(StudentRegistration $registration)
    {
        // Scope check
        $user = auth()->user();
        if (!$user->boardingSchools->contains($registration->boarding_school_id)) {
            abort(403);
        }

        $registration->load(['boardingSchool', 'school', 'schoolLevel']);

        return Inertia::render('Dashboard/StudentRegistration/Show', [
            'registration' => $registration,
        ]);
    }

    public function accept(StudentRegistration $registration)
    {
        // Scope check
        $user = auth()->user();
        if (!$user->boardingSchools->contains($registration->boarding_school_id)) {
            abort(403);
        }

        if ($registration->status !== 'pending') {
            return back()->with('error', 'Status pendaftaran sudah tidak pending.');
        }

        DB::transaction(function () use ($registration) {
            // 1. Create User
            // Generate email if needed
            $email = $registration->email;
            if (empty($email)) {
                // Generate dummy email from name
                $email = strtolower(str_replace(' ', '', $registration->name)) . rand(100,999) . '@student.pondok.id';
            }
            
            // Check if email exists
            if (User::where('email', $email)->exists()) {
                 $email = strtolower(str_replace(' ', '', $registration->name)) . rand(1000,9999) . '@student.pondok.id';
            }

            $user = User::create([
                'name' => $registration->name,
                'email' => $email,
                'password' => Hash::make('password'), // Default password
                'gender' => $registration->gender,
                'phone_number' => $registration->phone_number,
                'photo' => $registration->photo,
                'balance' => 0,
            ]);
            $user->assignRole('student');

            // 2. Create Student
            // Generate Student ID (NIS) - Logic might need refinement, using random for now or based on Reg Number
            $studentId = 'NIS-' . date('Y') . '-' . rand(1000, 9999);
            
            $student = Student::create([
                'user_id' => $user->id,
                'boarding_school_id' => $registration->boarding_school_id,
                'student_id' => $studentId, 
                'status' => \App\Enums\StudentStatusEnum::ACTIVE,
                
                // Copy Data
                'place_of_birth' => $registration->place_of_birth,
                'date_of_birth' => $registration->date_of_birth,
                'gender' => $registration->gender, // Will be cast to Enum by Student model
                'address' => $registration->address,
                'province' => $registration->province,
                'regency' => $registration->regency,
                'district' => $registration->district,
                'village' => $registration->village,

                'child_number' => $registration->child_number,
                'siblings_count' => $registration->siblings_count,

                'father_name' => $registration->father_name,
                'father_job' => $registration->father_job,
                'father_phone' => $registration->father_phone,
                'father_income' => $registration->father_income,

                'mother_name' => $registration->mother_name,
                'mother_job' => $registration->mother_job,
                'mother_phone' => $registration->mother_phone,
                'mother_income' => $registration->mother_income,
            ]);

            // 3. Academic Pivot (Formal School)
            $activeSchoolYear = SchoolYear::getActiveSchoolYear();
            if ($activeSchoolYear && $registration->school_id && $registration->school_level_id) {
                $student->schools()->attach($registration->school_id, [
                    'school_level_id' => $registration->school_level_id,
                    'school_year_id' => $activeSchoolYear->id
                ]);
            }

            // 4. Update Registration Status
            $registration->update(['status' => 'accepted']);
        });

        return redirect()->route('dashboard.student-registrations.index')->with('success', 'Santri berhasil diterima.');
    }

    public function downloadPdf(StudentRegistration $registration)
    {
        // Scope check
        $user = auth()->user();
        if (!$user->boardingSchools->contains($registration->boarding_school_id)) {
            abort(403);
        }

        try {
            // Re-use the formulir pendaftaran template or a specific one for admin
            // Assuming admin wants the full form
            $templatePath = public_path('templates/template_formulir_pendaftaran.docx');
            if (!file_exists($templatePath)) {
                return back()->with('error', 'Template form pendaftaran tidak ditemukan.');
            }

            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Mapping Data - similar to public downloadForm but reusing logic
            // Ideally extract this mapping to a Service or Helper
            $registration->load(['school', 'schoolLevel']);

            $templateProcessor->setValue('name', $registration->name);
            $templateProcessor->setValue('gender', $registration->gender?->label() ?? $registration->gender); 
            $templateProcessor->setValue('birth_place', $registration->place_of_birth);
            $templateProcessor->setValue('birth_date', $registration->date_of_birth ? $registration->date_of_birth->format('d-m-Y') : '');
            $templateProcessor->setValue('nisn', ''); 
            $templateProcessor->setValue('address', $registration->address);
            $templateProcessor->setValue('phone_number', $registration->phone_number);
            $templateProcessor->setValue('email', $registration->email);
            $templateProcessor->setValue('hobby', ''); 
            $templateProcessor->setValue('dream', ''); 

            // Parents
            $templateProcessor->setValue('father_name', $registration->father_name);
            $templateProcessor->setValue('father_job', $registration->father_job);
            $templateProcessor->setValue('mother_name', $registration->mother_name);
            $templateProcessor->setValue('mother_job', $registration->mother_job);
            
            // Guardian (Logic: Default to Father if empty)
            $guardianName = $registration->guardian_name ?: $registration->father_name;
            $guardianAddress = $registration->guardian_address ?: $registration->address; // As requested, if empty use student address
            $guardianPhone = $registration->guardian_phone ?: $registration->father_phone;

            $templateProcessor->setValue('guardian_name', $guardianName); 
            $templateProcessor->setValue('guardian_address', $guardianAddress); 
            $templateProcessor->setValue('guardian_phone', $guardianPhone);

            // Education
            $templateProcessor->setValue('from_school', $registration->previous_school_name ?? ''); 
            $templateProcessor->setValue('schoo_address', $registration->previous_school_address ?? ''); 
            $templateProcessor->setValue('pass_year', $registration->graduation_year ?? ''); 
            $templateProcessor->setValue('certificate_number', $registration->certificate_number ?? ''); 

            // Footer
            $templateProcessor->setValue('date', date('d F Y'));
            
            $filename = 'Pendaftaran_' . $registration->registration_number . '.docx';
            $tempPath = storage_path('app/public/' . $filename);
            
            // Ensure directory exists
            if (!file_exists(dirname($tempPath))) {
                mkdir(dirname($tempPath), 0755, true);
            }
            
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate dokumen: ' . $e->getMessage());
        }
    }
}
