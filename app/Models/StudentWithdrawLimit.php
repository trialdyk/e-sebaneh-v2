<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentWithdrawLimit extends Model
{
    use HasFactory;

    protected $table = 'student_withdraw_limits';

    protected $fillable = [
        'boarding_school_id',
        'limit',
    ];

    protected $casts = [
        'limit' => 'decimal:2',
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }
}
