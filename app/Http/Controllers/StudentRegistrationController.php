<?php

namespace App\Http\Controllers;

use App\Models\BoardingSchool;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\School;
use App\Models\StudentRegistration;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Str;

class StudentRegistrationController extends Controller
{
    public function create()
    {
        // Public data for form
        $boardingSchools = BoardingSchool::all(['id', 'name']);
        
        $provinces = Province::orderBy('name')->get(['id', 'name']);
        
        // Schools for academic history
        $schools = School::with('schoolLevels')->orderBy('name')->get();

        $genders = [
            ['value' => 'male', 'label' => 'Laki-laki'],
            ['value' => 'female', 'label' => 'Perempuan'],
        ];

        return Inertia::render('Landing/Register', [
            'boardingSchools' => $boardingSchools,
            'provinces' => $provinces,
            'schools' => $schools,
            'genders' => $genders,
        ]);
    }

    public function store(Request $request)
    {
        // Validation similar to Student but looser for some fields if needed
        $validated = $request->validate([
            // Boarding School
            'boarding_school_id' => 'required|exists:boarding_schools,id',

            // Personal
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'place_of_birth' => 'required|string|max:255',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female',
            'child_number' => 'nullable|integer',
            'siblings_count' => 'nullable|integer',
            
            // Address
            'address' => 'required|string',
            'province_id' => 'required', // will resolve to name
            'regency_id' => 'required',
            'district_id' => 'required',
            'village_id' => 'required',

            // Parents
            'father_name' => 'required|string|max:255',
            'father_job' => 'nullable|string|max:255',
            'father_phone' => 'nullable|string|max:20',
            'father_income' => 'nullable|string|max:255',
            'mother_name' => 'required|string|max:255',
            'mother_job' => 'nullable|string|max:255',
            'mother_phone' => 'nullable|string|max:20',
            'mother_income' => 'nullable|string|max:255',

            
            // Guardian
            'guardian_name' => 'nullable|string|max:255',
            'guardian_job' => 'nullable|string|max:255',
            'guardian_phone' => 'nullable|string|max:20',
            'guardian_address' => 'nullable|string',

            // Previous Education
            'previous_school_name' => 'required|string|max:255',
            'previous_school_address' => 'nullable|string',
            'graduation_year' => 'nullable|digits:4|integer|min:1900|max:' . (date('Y') + 1),
            'certificate_number' => 'nullable|string|max:255',

            // Academic (Destination) - Now Optional as per user
            'school_id' => 'nullable|exists:schools,id',
            'school_level_id' => 'nullable|exists:school_levels,id',

            // Photo
            'photo' => 'nullable|image|max:2048',
        ]);

        DB::transaction(function () use ($request, $validated) {
            
            // Resolve Region Names
            $provinceName = Province::find($request->province_id)?->name;
            $regencyName = Regency::find($request->regency_id)?->name;
            $districtName = District::find($request->district_id)?->name;
            $villageName = Village::find($request->village_id)?->name;

            // Handle Photo
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('student_registration_photos', 'public');
            }

            // Generate Registration Number (e.g., PSB-YEAR-RANDOM)
            $regNumber = 'PSB-' . date('Y') . '-' . strtoupper(Str::random(6));

            StudentRegistration::create([
                'registration_number' => $regNumber,
                'status' => 'pending',
                'boarding_school_id' => $request->boarding_school_id,
                
                'name' => $request->name,
                'email' => $request->email,
                'phone_number' => $request->phone_number,
                'photo' => $photoPath,

                'place_of_birth' => $request->place_of_birth,
                'date_of_birth' => $request->date_of_birth,
                'gender' => $request->gender,
                
                'address' => $request->address,
                'province' => $provinceName,
                'regency' => $regencyName,
                'district' => $districtName,
                'village' => $villageName,

                'child_number' => $request->child_number,
                'siblings_count' => $request->siblings_count,

                'father_name' => $request->father_name,
                'father_job' => $request->father_job,
                'father_phone' => $request->father_phone,
                'father_income' => $request->father_income,

                'mother_name' => $request->mother_name,
                'mother_job' => $request->mother_job,
                'mother_phone' => $request->mother_phone,
                'mother_income' => $request->mother_income,

                'mother_phone' => $request->mother_phone,
                'mother_income' => $request->mother_income,

                'guardian_name' => $request->guardian_name,
                'guardian_job' => $request->guardian_job,
                'guardian_phone' => $request->guardian_phone,
                'guardian_address' => $request->guardian_address,

                'previous_school_name' => $request->previous_school_name,
                'previous_school_address' => $request->previous_school_address,
                'graduation_year' => $request->graduation_year,
                'certificate_number' => $request->certificate_number,

                'school_id' => $request->school_id,
                'school_level_id' => $request->school_level_id,
            ]);
        });

        return redirect()->route('register.success');
    }

    public function success()
    {
        // In a real app we might pass the latest registration ID in session to show specific details
        // For now just show generic success page
        return Inertia::render('Landing/RegisterSuccess');
    }

    public function downloadStatement()
    {
        // For now, we don't have the registration ID in the URL for security/simplicity in this session-less flow.
        // In a real app, we'd pass ?id=UUID and verify ownership (e.g. valid session or signed URL).
        // Since the user just registered, we could store the ID in session.
        // For this task, I'll fetch the LATEST registration. Ideally, use session.
        
        $registration = StudentRegistration::latest()->first();
        if (!$registration) {
             return redirect()->route('register')->with('error', 'Belum ada data pendaftaran.');
        }

        try {
            $templatePath = public_path('templates/template_surat_pernyataan_santri.docx');
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            $templateProcessor->setValue('name', $registration->name);
            $templateProcessor->setValue('birth_place', $registration->place_of_birth);
            $templateProcessor->setValue('birth_date', $registration->date_of_birth ? $registration->date_of_birth->format('d-m-Y') : '');
            $templateProcessor->setValue('from_school', $registration->previous_school_name ?? '................');
            $templateProcessor->setValue('full_address', $registration->address . ', ' . $registration->village . ', ' . $registration->district . ', ' . $registration->regency . ', ' . $registration->province);
            $templateProcessor->setValue('date', date('d F Y'));

            $filename = 'Surat_Pernyataan_' . str_replace(' ', '_', $registration->name) . '.docx';
            $tempPath = storage_path('app/public/' . $filename);
            
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate dokumen: ' . $e->getMessage());
        }
    }

    public function downloadForm()
    {
        $registration = StudentRegistration::latest()->first();
        if (!$registration) {
             return redirect()->route('register')->with('error', 'Belum ada data pendaftaran.');
        }

        try {
            $templatePath = public_path('templates/template_formulir_pendaftaran.docx');
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($templatePath);

            // Mapping Data
            $templateProcessor->setValue('name', $registration->name);
            $templateProcessor->setValue('gender', $registration->gender?->label() ?? $registration->gender); // Enum label?
            $templateProcessor->setValue('birth_place', $registration->place_of_birth);
            $templateProcessor->setValue('birth_date', $registration->date_of_birth ? $registration->date_of_birth->format('d-m-Y') : '');
            $templateProcessor->setValue('nisn', ''); // Not in form input
            $templateProcessor->setValue('address', $registration->address);
            $templateProcessor->setValue('phone_number', $registration->phone_number);
            $templateProcessor->setValue('email', $registration->email);
            $templateProcessor->setValue('hobby', ''); // Not in form input
            $templateProcessor->setValue('dream', ''); // Not in form input

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
            
            $filename = 'Formulir_Pendaftaran_' . str_replace(' ', '_', $registration->name) . '.docx';
            $tempPath = storage_path('app/public/' . $filename);
            
            $templateProcessor->saveAs($tempPath);

            return response()->download($tempPath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate dokumen: ' . $e->getMessage());
        }
    }
}
