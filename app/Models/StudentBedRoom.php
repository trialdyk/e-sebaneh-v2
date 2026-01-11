<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class StudentBedRoom extends Pivot
{
    public $incrementing = true;

    protected $table = 'student_bed_rooms';

    protected $fillable = [
        'student_id',
        'bed_room_id',
        'school_year_id',
    ];
}
