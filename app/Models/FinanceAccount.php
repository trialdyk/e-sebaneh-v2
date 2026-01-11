<?php

namespace App\Models;

use App\Enums\FinanceAccountTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinanceAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_school_id',
        'name',
        'type',
        'balance',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'type' => FinanceAccountTypeEnum::class,
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(FinanceTransaction::class);
    }
}
