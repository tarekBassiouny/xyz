<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Section;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    protected $model = Video::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),

            'title_translations' => [
                'en' => 'Video: '.$this->faker->sentence(),
                'ar' => 'فيديو: '.$this->faker->sentence(),
            ],

            'description_translations' => [
                'en' => $this->faker->paragraph(),
                'ar' => 'وصف: '.$this->faker->sentence(),
            ],

            'video_url' => $this->faker->url(),
            'duration_seconds' => $this->faker->numberBetween(30, 3600),
            'order_index' => $this->faker->numberBetween(1, 50),

            'thumbnail_url' => $this->faker->imageUrl(),
            'thumbnail_urls' => [
                'small' => $this->faker->imageUrl(),
                'medium' => $this->faker->imageUrl(),
                'large' => $this->faker->imageUrl(),
            ],
        ];
    }
}
