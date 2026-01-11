<?php

namespace App\Models;

use App\Enums\GenderEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentRegistration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'registration_number',
        'boarding_school_id',
        'status',
        'name',
        'email',
        'phone_number',
        'photo',
        'place_of_birth',
        'date_of_birth',
        'gender',
        'address',
        'province',
        'regency',
        'district',
        'village',
        'child_number',
        'siblings_count',
        'father_name',
        'father_job',
        'father_phone',
        'father_income',
        'mother_name',
        'mother_job',
        'mother_phone',
        'mother_income',
        'guardian_name',
        'guardian_job',
        'guardian_phone',
        'guardian_address',
        'previous_school_name',
        'previous_school_address',
        'graduation_year',
        'certificate_number',
        'school_id',
        'school_level_id',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'gender' => GenderEnum::class,
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function schoolLevel(): BelongsTo
    {
        return $this->belongsTo(SchoolLevel::class);
    }
}
