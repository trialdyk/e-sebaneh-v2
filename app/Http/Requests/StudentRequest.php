<?php

namespace App\Http\Requests;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();

        // For admin-pondok, only allow their boarding schools
        $boardingSchoolRule = ['required', 'exists:boarding_schools,id'];

        if ($user->hasRole('admin-pondok')) {
            $boardingSchoolIds = $user->boardingSchools()->pluck('boarding_schools.id')->toArray();
            // Since it's auto-assigned in controller, we make it nullable/optional in request validation validation for admin-pondok
            // BUT, if we remove the input from frontend, it won't be in request.
            // So we change rule to nullable or simply remove 'required'.
            $boardingSchoolRule = ['nullable', 'exists:boarding_schools,id', Rule::in($boardingSchoolIds)];
        }

        return [
            // Student Bio
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->student?->user_id)],
            'student_id' => ['required', 'string', 'max:255', Rule::unique('students', 'student_id')->ignore($this->student)],
            'rfid' => ['nullable', 'string', 'max:255', Rule::unique('students', 'rfid')->ignore($this->student)],
            'status' => ['required', Rule::enum(StudentStatusEnum::class)],
            'place_of_birth' => ['required', 'string', 'max:255'],
            'date_of_birth' => ['required', 'date'],
            'gender' => ['required', Rule::enum(GenderEnum::class)],
            'address' => ['required', 'string'],

            // Regional
            'province_id' => ['nullable', 'exists:provinces,id'],
            'regency_id' => ['nullable', 'exists:regencies,id'],
            'district_id' => ['nullable', 'exists:districts,id'],
            'village_id' => ['nullable', 'exists:villages,id'],

            // Legacy Region (String) - optional but kept for backward compat or fallback
            'province' => ['nullable', 'string'],
            'regency' => ['nullable', 'string'],
            'district' => ['nullable', 'string'],
            'village' => ['nullable', 'string'],

            // Family
            'child_number' => ['nullable', 'integer'],
            'siblings_count' => ['nullable', 'integer'],
            'economic_status' => ['nullable', 'string'],
            'child_status' => ['nullable', 'string'],

            // Father
            'father_name' => ['nullable', 'string', 'max:255'],
            'father_birth_date' => ['nullable', 'date'],
            'father_last_edu' => ['nullable', 'string'],
            'father_job' => ['nullable', 'string'],
            'father_income' => ['nullable', 'string'],
            'father_phone' => ['nullable', 'string'],

            // Mother
            'mother_name' => ['nullable', 'string', 'max:255'],
            'mother_birth_date' => ['nullable', 'date'],
            'mother_last_edu' => ['nullable', 'string'],
            'mother_job' => ['nullable', 'string'],
            'mother_income' => ['nullable', 'string'],
            'mother_phone' => ['nullable', 'string'],

            // Relations
            'boarding_school_id' => $boardingSchoolRule,
            'classroom_id' => ['nullable', 'exists:classrooms,id'],
            'bed_room_id' => ['nullable', 'exists:bed_rooms,id'],
            'school_id' => ['nullable', 'exists:schools,id'],
            'school_level_id' => ['nullable', 'exists:school_levels,id'],
            'photo' => ['nullable', 'image', 'max:2048'],
            
            // User fields (passed to User model)
            'phone_number' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'boarding_school_id.in' => 'Anda hanya bisa memilih pondok yang Anda kelola.',
        ];
    }
}
