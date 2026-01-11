<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Teacher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'boarding_school_id',
        'position_id',
        'nip',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class, 'teacher_id', 'user_id');
        // Note: classroom.teacher_id references users.id (as per schema), so we map it via user_id
        // However, standard relation might be tricky.
        // If classroom.teacher_id is User ID, then User hasMany Classrooms.
        // Teacher hasOne User.
        // Teacher->classrooms() is User->classrooms().
        // Correct relationship should be defined in User model if FK is to users.
        // But here we can define it if needed.
    }
}
