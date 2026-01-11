<?php

namespace App\Models;

use App\Enums\ProductTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'category',
        'brand',
        'price',
        'seller_price',
        'admin_fee',
        'is_active',
        'type',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'seller_price' => 'decimal:2',
        'admin_fee' => 'decimal:2',
        'is_active' => 'boolean',
        'type' => ProductTypeEnum::class,
    ];
}
