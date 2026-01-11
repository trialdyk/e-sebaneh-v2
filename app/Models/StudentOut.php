<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'out_type',
        'out_date',
        'reason',
    ];

    protected $casts = [
        'out_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
