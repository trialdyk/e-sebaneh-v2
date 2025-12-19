<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Regency extends Model 
{
    use HasFactory;

    protected $table = 'regencies';
    public $incrementing = false;
    public $keyType = 'char';
    protected $primaryKey = 'id';
    protected $fillable = ['id','province_id','name'];
    protected $guarded = [];
    /**
     * Get the province that owns the regency
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    /**
     * Get all of the districts for the regency
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }
}
