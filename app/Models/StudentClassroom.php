<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentClassroom extends Pivot
{
    // If it has 'id', use Model or incrementing Pivot
    public $incrementing = true;

    protected $table = 'student_classrooms';

    protected $fillable = [
        'student_id',
        'classroom_id',
        'school_year_id',
    ];

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }
}
