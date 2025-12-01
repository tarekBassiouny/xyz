<?php

namespace Database\Seeders;

use App\Models\PlaybackSession;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Video;
use Illuminate\Database\Seeder;

class PlaybackSessionSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $devices = UserDevice::where('user_id', $user->id)->get();
            $videos = Video::inRandomOrder()->take(5)->get();

            foreach ($videos as $video) {
                PlaybackSession::factory()->create([
                    'user_id' => $user->id,
                    'video_id' => $video->id,
                    'device_id' => $devices->random()->id ?? UserDevice::factory()->create(['user_id' => $user->id])->id,
                ]);
            }
        }
    }
}
