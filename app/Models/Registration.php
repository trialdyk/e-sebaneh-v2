<?php

namespace App\Models;

use App\Enums\RegistrationStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'boarding_school_id',
        'school_year_id',
        'registration_number',
        'name',
        'nisn',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'address',
        'father_name',
        'father_phone',
        'mother_name',
        'mother_phone',
        'previous_school',
        'registration_fee',
        'status',
        'notes',
        'payment_proof',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'registration_fee' => 'decimal:2',
        'status' => RegistrationStatusEnum::class,
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function schoolYear(): BelongsTo
    {
        return $this->belongsTo(SchoolYear::class);
    }
}
