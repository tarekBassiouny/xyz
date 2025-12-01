<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\PlaybackSession;
use App\Models\User;
use App\Models\UserDevice;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaybackSessionFactory extends Factory
{
    protected $model = PlaybackSession::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'video_id' => Video::factory(),
            'device_id' => UserDevice::factory(),
            'started_at' => now()->subMinutes(rand(1, 500)),
            'last_position_seconds' => rand(5, 2000),
            'completed' => $this->faker->boolean(20),
        ];
    }
}
