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
        'slug',
        'photo',
        'address',
        'description',
        'phone',
        'email',
        'balance',
        'letter_head_name',
        'letter_secretary_name',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
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
     * Students (one-to-many)
     */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /**
     * Teachers (one-to-many)
     */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /**
     * Classrooms (one-to-many)
     */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    /**
     * Bed Rooms (one-to-many)
     */
    public function bedRooms(): HasMany
    {
        return $this->hasMany(BedRoom::class);
    }

    /**
     * Scope for with counts
     */
    public function scopeWithCounts($query)
    {
        return $query->withCount(['admins', 'facilities', 'students', 'teachers', 'classrooms']);
    }
}
