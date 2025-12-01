<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property int $center_id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property string $password
 * @property bool $is_active
 * @property string|null $profile_photo_url
 * @property \Carbon\Carbon|null $last_login_at
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'center_id',
        'name',
        'phone',
        'email',
        'password',
        'is_active',
        'profile_photo_url',
        'last_login_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /** @return BelongsToMany<Center, User> */
    public function centers(): BelongsToMany
    {
        return $this->belongsToMany(Center::class, 'user_centers');
    }

    /** @return BelongsToMany<Role> */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /** @return HasMany<UserDevice> */
    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class);
    }

    /** @return HasMany<JwtToken> */
    public function tokens(): HasMany
    {
        return $this->hasMany(JwtToken::class);
    }

    /** @return HasMany<Enrollment> */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /** @return HasMany<PlaybackSession> */
    public function playbackSessions(): HasMany
    {
        return $this->hasMany(PlaybackSession::class);
    }

    /** @return HasMany<AuditLog> */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /** Automatically hash password */
    public function setPasswordAttribute(string $value): void
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
