<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property int $user_id
 * @property string $device_uuid
 * @property string|null $device_name
 * @property string $device_os
 * @property string $device_type
 * @property bool $is_active
 * @property \Carbon\Carbon|null $last_used_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, JwtToken> $tokens
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PlaybackSession> $playbackSessions
 */
class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_uuid',
        'device_name',
        'device_os',
        'device_type',
        'is_active',
        'last_used_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_used_at' => 'datetime',
    ];

    /** @return BelongsTo<User, UserDevice> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<JwtToken> */
    public function tokens(): HasMany
    {
        return $this->hasMany(JwtToken::class, 'device_id');
    }

    /** @return HasMany<PlaybackSession> */
    public function playbackSessions(): HasMany
    {
        return $this->hasMany(PlaybackSession::class, 'device_id');
    }
}
