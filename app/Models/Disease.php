<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Disease extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'disease_name',
        'diagnosed_date',
        'notes',
    ];

    protected $casts = [
        'diagnosed_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
