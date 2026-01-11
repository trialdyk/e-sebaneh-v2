<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'boarding_school_id',
        'name',
        'amount',
        'description',
        'type',
        'for_gender',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    /**
     * Get the boarding school that owns the invoice
     */
    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    /**
     * Get the classrooms for this invoice (for by_classroom type)
     */
    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'student_invoice_classroom');
    }

    /**
     * Get the students for this invoice (for specific_students type)
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_invoice_student');
    }

    /**
     * Get all payments for this invoice
     */
    public function payments(): HasMany
    {
        return $this->hasMany(StudentInvoicePayment::class);
    }

    /**
     * Scope to filter invoices for a specific boarding school
     */
    public function scopeForBoardingSchool($query, $boardingSchoolId)
    {
        return $query->where('boarding_school_id', $boardingSchoolId);
    }

    /**
     * Scope to get invoices applicable to a specific student
     */
    public function scopeForStudent($query, Student $student)
    {
        $classroomIds = $student->classrooms->pluck('id');

        return $query->where('boarding_school_id', $student->boarding_school_id)
            ->where(function ($q) use ($student, $classroomIds) {
                $q->where('type', 'all_students')
                    ->orWhere(function ($sub) use ($student) {
                        $sub->where('type', 'by_gender')
                            ->where('for_gender', $student->gender);
                    })
                    ->orWhere(function ($sub) use ($classroomIds) {
                        if ($classroomIds->isNotEmpty()) {
                            $sub->where('type', 'by_classroom')
                                ->whereHas('classrooms', function ($classroom) use ($classroomIds) {
                                    $classroom->whereIn('classrooms.id', $classroomIds);
                                });
                        }
                    })
                    ->orWhere(function ($sub) use ($student) {
                        $sub->where('type', 'specific_students')
                            ->whereHas('students', function ($studentQuery) use ($student) {
                                $studentQuery->where('students.id', $student->id);
                            });
                    });
            });
    }

    /**
     * Get total amount paid for this invoice
     */
    public function getTotalPaidAttribute(): int
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Get remaining amount to be paid
     */
    public function getRemainingAmountAttribute(): int
    {
        return max(0, $this->amount - $this->total_paid);
    }

    /**
     * Check if invoice is fully paid
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->total_paid >= $this->amount;
    }

    /**
     * Get total paid for a specific student
     */
    public function getTotalPaidByStudent($studentId): int
    {
        return $this->payments()->where('student_id', $studentId)->sum('amount');
    }

    /**
     * Check if a specific student has paid this invoice
     */
    public function isPaidByStudent($studentId): bool
    {
        return $this->getTotalPaidByStudent($studentId) >= $this->amount;
    }
}
