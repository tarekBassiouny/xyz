<?php

declare(strict_types=1);

namespace App\Services\Playback;

use App\Models\PlaybackSession;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Video;
use Illuminate\Support\Carbon;

class PlaybackSessionService
{
    public function startSession(User $user, Video $video, UserDevice $device): PlaybackSession
    {
        return PlaybackSession::create([
            'user_id' => $user->id,
            'video_id' => $video->id,
            'device_id' => $device->id,
            'started_at' => Carbon::now(),
            'progress_percent' => 0,
            'is_full_play' => false,
        ]);
    }
}
