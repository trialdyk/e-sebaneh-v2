<?php

namespace App\Models;

use App\Enums\TopupStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TopupUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'student_id',
        'invoice_status',
        'amount',
        'paid_amount',
        'fee_amount',
        'expiry_date',
        'payment_method',
        'payment_url',
        'payment_id',
        'payment_reference',
        'doku_payment_details',
        'proof_of_transfer',
        'paid_at',
        'is_cancelled',
        'cancelled_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'expiry_date' => 'datetime',
        'paid_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'is_cancelled' => 'boolean',
        'doku_payment_details' => 'array',
        'invoice_status' => TopupStatusEnum::class,
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
