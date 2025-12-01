<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $phone
 * @property string $country_code
 * @property string $otp
 * @property string $token
 * @property \Carbon\Carbon $expires_at
 * @property bool $is_used
 * @property int|null $user_id
 * @property-read User|null $user
 */
class OtpCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'phone',
        'country_code',
        'otp',
        'token',
        'expires_at',
        'is_used',
        'user_id',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    /** @return BelongsTo<User, OtpCode> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
