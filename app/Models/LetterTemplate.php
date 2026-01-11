<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LetterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_school_id',
        'name',
        'code',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function letters(): HasMany
    {
        return $this->hasMany(Letter::class, 'template_id');
    }
}
