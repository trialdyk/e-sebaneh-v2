<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Enums\StudentStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Student extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'boarding_school_id',
        'student_id',
        'rfid',
        'status',
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
        'economic_status',
        'child_status',
        'savings',

        // Father
        'father_name',
        'father_birth_date',
        'father_last_edu',
        'father_job',
        'father_income',
        'father_phone',

        // Mother
        'mother_name',
        'mother_birth_date',
        'mother_last_edu',
        'mother_job',
        'mother_income',
        'mother_phone',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'father_birth_date' => 'date',
        'mother_birth_date' => 'date',
        'status' => StudentStatusEnum::class,
        'gender' => GenderEnum::class,
        'savings' => 'decimal:2',
    ];

    // ==================== RELATIONSHIPS ====================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'student_guardians')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'student_classrooms')
            ->withPivot('school_year_id')
            ->withTimestamps();
    }

    public function currentClassroom()
    {
        return $this->hasOne(StudentClassroom::class)->latestOfMany();
    }

    public function bedRooms(): BelongsToMany
    {
        return $this->belongsToMany(BedRoom::class, 'student_bed_rooms')
            ->withPivot('school_year_id')
            ->withTimestamps();
    }

    public function schools(): BelongsToMany
    {
        return $this->belongsToMany(School::class, 'student_schools')
            ->withPivot('school_level_id', 'school_year_id')
            ->withTimestamps();
    }

    public function diseases()
    {
        return $this->hasMany(Disease::class);
    }

    public function permissions()
    {
        return $this->hasMany(StudentPermission::class);
    }

    public function violations()
    {
        return $this->hasMany(StudentViolation::class);
    }

    public function memorizes()
    {
        return $this->hasMany(StudentMemorize::class);
    }

    // ==================== ATTRIBUTES ====================

    public function getFormattedSavingsAttribute(): string
    {
        return 'Rp '.number_format($this->savings, 0, ',', '.');
    }

    // ==================== SCOPES ====================

    public function scopeActive($query)
    {
        return $query->where('status', StudentStatusEnum::ACTIVE);
    }

    /**
     * Get the invoices payments made by the student
     */
    public function invoicePayments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(StudentInvoicePayment::class);
    }
}
