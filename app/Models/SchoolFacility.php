<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolFacility extends Model
{
    protected $fillable = [
        'boarding_school_id',
        'name',
    ];

    /**
     * Boarding School (many-to-one)
     */
    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }
}
