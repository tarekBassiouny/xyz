<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserDeviceFactory extends Factory
{
    protected $model = UserDevice::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'device_uuid' => (string) Str::uuid(),
            'device_name' => $this->faker->optional()->word(),
            'device_os' => $this->faker->randomElement(['iOS', 'Android', 'Windows', 'Linux']),
            'device_type' => $this->faker->randomElement(['mobile', 'tablet', 'desktop']),
            'is_active' => true,
            'last_used_at' => $this->faker->optional()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
