<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolYear extends Model
{
    protected $fillable = [
        'name',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the currently active school year
     */
    public static function getActiveSchoolYear(): ?self
    {
        return static::where('is_active', true)->first();
    }

    /**
     * Activate this school year (and deactivate others)
     */
    public function activate(): void
    {
        // Deactivate all other school years
        static::query()->update(['is_active' => false]);

        // Activate this one
        $this->update(['is_active' => true]);
    }

    /**
     * Scope for active school year
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for latest school years
     */
    public function scopeLatest($query)
    {
        return $query->orderByDesc('name');
    }
}
