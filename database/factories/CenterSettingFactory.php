<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Center;
use App\Models\CenterSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class CenterSettingFactory extends Factory
{
    protected $model = CenterSetting::class;

    public function definition(): array
    {
        return [
            'center_id' => Center::factory(),
            'key' => $this->faker->unique()->slug(),
            'value' => [
                'en' => $this->faker->sentence(),
                'ar' => $this->faker->sentence(),
            ],
        ];
    }
}
