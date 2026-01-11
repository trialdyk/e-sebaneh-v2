<?php

namespace App\Models;

use App\Enums\WithdrawalStatusEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Withdrawal extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'transaction_id',
        'user_id',
        'status',
        'amount',
        'fee_amount',
        'net_amount',
        'bank_name',
        'bank_account_number',
        'bank_account_holder',
        'admin_notes',
        'proof_of_transfer',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'processed_at' => 'datetime',
        'status' => WithdrawalStatusEnum::class,
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
