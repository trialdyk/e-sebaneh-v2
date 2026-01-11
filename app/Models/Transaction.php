<?php

namespace App\Models;

use App\Enums\TransactionTypeEnum;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Transaction extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'invoice_id',
        'type',
        'ref_id',
        'balance_before',
        'balance_after',
    ];

    protected $casts = [
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'type' => TransactionTypeEnum::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function topupUser(): HasOne
    {
        return $this->hasOne(TopupUser::class);
    }

    public function withdrawal(): HasOne
    {
        return $this->hasOne(Withdrawal::class);
    }

    public function diggieFlazz(): HasOne
    {
        return $this->hasOne(TransactionDiggieFlazz::class);
    }
}
