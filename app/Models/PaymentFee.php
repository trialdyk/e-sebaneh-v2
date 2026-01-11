<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentFee extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_method',
        'payment_method_name',
        'fee_amount',
        'fee_percentage',
        'is_active',
    ];

    protected $casts = [
        'fee_amount' => 'decimal:2',
        'fee_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];
}
