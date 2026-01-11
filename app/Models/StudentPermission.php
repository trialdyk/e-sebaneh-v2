<?php

namespace App\Models;

use App\Enums\StudentPermissionStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentPermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'type',
        'reason',
        'start_date',
        'end_date',
        'duration',
        'status',
        'returned_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'returned_at' => 'datetime',
        'status' => StudentPermissionStatusEnum::class,
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
