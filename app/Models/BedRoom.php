<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class BedRoom extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_school_id',
        'name',
        'capacity',
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_bed_rooms')
            ->withPivot('school_year_id')
            ->withTimestamps();
    }
}
