<?php

namespace App\Models;

use App\Enums\DigiflazzStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionDiggieFlazz extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'buyer_sku_code',
        'customer_no',
        'status',
        'price',
        'admin_fee',
        'message',
        'ref_id',
        'sn',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'status' => DigiflazzStatusEnum::class,
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }
}
