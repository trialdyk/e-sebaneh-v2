<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentMemorize extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'surah_id',
        'juz',
        'page_start',
        'page_end',
        'grade',
        'notes',
        'memorize_date',
    ];

    protected $casts = [
        'memorize_date' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(Teacher::class);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }
}
