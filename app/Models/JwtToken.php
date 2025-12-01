<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $device_id
 * @property string $token
 * @property string $refresh_token
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon $refresh_expires_at
 * @property bool $revoked
 * @property-read User $user
 * @property-read UserDevice $device
 */
class JwtToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_id',
        'token',
        'refresh_token',
        'expires_at',
        'refresh_expires_at',
        'revoked',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'refresh_expires_at' => 'datetime',
        'revoked' => 'boolean',
    ];

    /** @return BelongsTo<User, JwtToken> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<UserDevice, JwtToken> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class);
    }
}
