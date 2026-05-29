<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_FARMER = 'farmer';
    public const ROLE_DA_ADMIN = 'da_admin';
    public const ROLE_LGU_VALIDATOR = 'lgu_validator';

    public const STAFF_ROLES = [
        self::ROLE_DA_ADMIN,
        self::ROLE_LGU_VALIDATOR,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'role',
        'municipality',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
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
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function isDaAdmin(): bool
    {
        return $this->role === self::ROLE_DA_ADMIN || $this->isConfiguredAdminFallback();
    }

    public function isLguValidator(): bool
    {
        return $this->role === self::ROLE_LGU_VALIDATOR;
    }

    public function hasStaffRole(): bool
    {
        return $this->isDaAdmin() || $this->isLguValidator();
    }

    public function isActiveStaff(): bool
    {
        return (bool) $this->is_active && $this->hasStaffRole();
    }

    public function normalizedMunicipality(): ?string
    {
        $municipality = trim((string) $this->municipality);

        return $municipality !== '' ? strtoupper($municipality) : null;
    }

    public function isConfiguredAdminFallback(): bool
    {
        $adminEmail = trim((string) config('app.admin_email'));

        return $adminEmail !== '' && hash_equals($adminEmail, (string) $this->email);
    }
}
