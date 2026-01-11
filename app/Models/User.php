<?php

namespace App\Models;

use App\Enums\GenderEnum;
use App\Traits\UploadTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, UploadTrait;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number',
        'gender',
        'pin_atm',
        'saldo',
        'profile_photo',
        'google_id',
        'google_token',
        'two_factor_enabled',
        'two_factor_code',
        'two_factor_expires_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'pin_atm',
        'two_factor_code',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_expires_at' => 'datetime',
            'password' => 'hashed',
            'gender' => GenderEnum::class,
            'saldo' => 'decimal:2',
            'two_factor_enabled' => 'boolean',
        ];
    }

    /**
     * Appended attributes
     */
    protected $appends = [
        'profile_photo_url',
        'gender_label',
    ];

    // ==================== ACCESSORS ====================

    /**
     * Get profile photo URL
     */
    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo) {
            return null;
        }

        return Storage::url($this->profile_photo);
    }

    /**
     * Get gender label in Indonesian
     */
    public function getGenderLabelAttribute(): ?string
    {
        return $this->gender?->label();
    }

    // ==================== HELPERS ====================

    /**
     * Check if user has linked Google account
     */
    public function hasGoogleLinked(): bool
    {
        return ! empty($this->google_id);
    }

    /**
     * Get formatted balance
     */
    public function getFormattedSaldoAttribute(): string
    {
        return 'Rp '.number_format($this->saldo, 0, ',', '.');
    }

    // ==================== RELATIONSHIPS ====================

    /**
     * Boarding Schools that this user manages
     */
    public function boardingSchools()
    {
        return $this->belongsToMany(BoardingSchool::class, 'admin_boarding_schools')
            ->withTimestamps();
    }

    /**
     * Student profile (if user is a student)
     */
    public function student()
    {
        return $this->hasOne(Student::class);
    }

    /**
     * Teacher profile (if user is a teacher)
     */
    public function teacher()
    {
        return $this->hasOne(Teacher::class);
    }

    /**
     * Students that this user guardians (if user is a parent)
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_guardians')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    // ==================== SCOPES ====================

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    /**
     * Scope for users with specific role
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->role($role);
    }

    /**
     * Scope for searching users
     */
    public function scopeSearch($query, ?string $search)
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone_number', 'like', "%{$search}%");
        });
    }

    // ==================== STATIC METHODS ====================

    /**
     * Find user by Google ID
     */
    public static function findByGoogleId(string $googleId): ?self
    {
        return static::where('google_id', $googleId)->first();
    }

    /**
     * Find user by email
     */
    public static function findByEmail(string $email): ?self
    {
        return static::where('email', $email)->first();
    }
}
