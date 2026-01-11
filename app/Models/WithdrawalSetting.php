<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WithdrawalSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'fee_percentage',
        'minimum_fee',
        'minimum_amount',
        'maximum_amount',
    ];

    protected $casts = [
        'minimum_fee' => 'decimal:2',
        'minimum_amount' => 'decimal:2',
        'maximum_amount' => 'decimal:2',
        'fee_percentage' => 'integer',
    ];
}
