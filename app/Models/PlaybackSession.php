<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property int $video_id
 * @property int $device_id
 * @property \Illuminate\Support\Carbon $started_at
 * @property int $last_position_seconds
 * @property bool $completed
 * @property-read User $user
 * @property-read Video $video
 * @property-read UserDevice $device
 */
class PlaybackSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'video_id',
        'device_id',
        'started_at',
        'last_position_seconds',
        'completed',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'last_position_seconds' => 'integer',
        'completed' => 'boolean',
    ];

    /** @return BelongsTo<User, PlaybackSession> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Video, PlaybackSession> */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /** @return BelongsTo<UserDevice, PlaybackSession> */
    public function device(): BelongsTo
    {
        return $this->belongsTo(UserDevice::class, 'device_id');
    }
}
