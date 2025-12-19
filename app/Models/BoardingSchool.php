<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BoardingSchool extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'photo',
        'address',
        'description',
    ];

    protected $appends = [
        'photo_url',
    ];

    /**
     * Get photo URL
     */
    public function getPhotoUrlAttribute(): ?string
    {
        if (! $this->photo) {
            return null;
        }

        return Storage::disk('public')->url($this->photo);
    }

    /**
     * Admins (many-to-many dengan User)
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'admin_boarding_schools')
            ->withTimestamps();
    }

    /**
     * Facilities (one-to-many)
     */
    public function facilities(): HasMany
    {
        return $this->hasMany(SchoolFacility::class);
    }

    /**
     * Scope for with counts
     */
    public function scopeWithCounts($query)
    {
        return $query->withCount(['admins', 'facilities']);
    }
}
