<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function hasTwoFactorEnabled(): bool
    {
        return (bool) $this->two_factor_enabled && ! is_null($this->two_factor_confirmed_at) && ! empty($this->two_factor_secret);
    }

    /**
     * Get user-specific pepper.
     * Each user has a unique pepper based on global pepper + user ID + email.
     * This prevents hash copying between users (even if hacker has DB access).
     *
     * Note: Uses email as primary identifier (always available), ID as secondary.
     */
    private function getUserSpecificPepper(): string
    {
        $globalPepper = (string) (config('security.password_pepper') ?: '');
        
        if ($globalPepper === '') {
            return '';
        }

        // User-specific pepper: global pepper + email (primary) + ID (secondary)
        // Email is always available when setting password (even for new users)
        // ID may be null for new users, but email is unique and sufficient
        $email = (string) ($this->email ?? '');
        $userId = $this->id ?? 0;
        
        // Use email as primary identifier (always unique)
        // Add ID for extra uniqueness (when available)
        $userIdentifier = $email . '|' . $userId;
        
        return hash_hmac('sha256', $userIdentifier, $globalPepper);
    }

    /**
     * Hash password with user-specific pepper before storing.
     *
     * SECURITY:
     * - Each user has a unique pepper (based on ID + email)
     * - Even if hacker copies hash from user A to user B, it won't work
     * - Because user B's pepper is different from user A's pepper
     *
     * IMPORTANT:
     * - When AUTH_PASSWORD_PEPPER changes, existing passwords become invalid unless rotated.
     * - User ID or email change will invalidate password (user must reset).
     */
    public function setPasswordAttribute(string $value): void
    {
        $userPepper = $this->getUserSpecificPepper();
        $toHash = $userPepper !== ''
            ? hash_hmac('sha256', $value, $userPepper)
            : $value;

        $this->attributes['password'] = Hash::make($toHash);
    }

    /**
     * Get all sites that this user is a member of.
     */
    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class, 'site_memberships')
            ->withPivot(['role', 'status'])
            ->withTimestamps();
    }

    /**
     * Get all site memberships for this user.
     */
    public function siteMemberships(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SiteMembership::class);
    }

    /**
     * Get the user's role for a specific site.
     */
    public function getSiteRole(Site $site): ?string
    {
        $membership = $this->siteMemberships()
            ->where('site_id', $site->id)
            ->where('status', 'active')
            ->first();

        return $membership?->role;
    }
}
