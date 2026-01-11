<?php

namespace App\Models;

use App\Enums\InvoiceForGenderEnum;
use App\Enums\InvoiceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'boarding_school_id',
        'name',
        'amount',
        'description',
        'for_gender',
        'type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'for_gender' => InvoiceForGenderEnum::class,
        'type' => InvoiceTypeEnum::class,
    ];

    public function boardingSchool(): BelongsTo
    {
        return $this->belongsTo(BoardingSchool::class);
    }

    public function classrooms(): BelongsToMany
    {
        return $this->belongsToMany(Classroom::class, 'invoice_classrooms')
            ->withTimestamps();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
