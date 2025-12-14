<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BunnyWebhookLog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BunnyWebhookLogFactory extends Factory
{
    protected $model = BunnyWebhookLog::class;

    public function definition(): array
    {
        return [
            'video_guid' => Str::uuid()->toString(),
            'library_id' => $this->faker->numberBetween(1, 999),
            'status' => $this->faker->numberBetween(0, 10),
            'payload' => [
                'Status' => $this->faker->numberBetween(0, 10),
            ],
        ];
    }
}
