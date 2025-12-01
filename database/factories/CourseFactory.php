<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use App\Models\Center;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseFactory extends Factory
{
    protected $model = Course::class;

    public function definition(): array
    {
        return [
            'center_id' => Center::factory(),
            'title_translations' => [
                'en' => $this->faker->sentence(),
                'ar' => 'دورة '.$this->faker->word(),
            ],
            'description_translations' => [
                'en' => $this->faker->paragraph(),
                'ar' => 'وصف: '.$this->faker->sentence(),
            ],
            'thumbnail_url' => $this->faker->optional()->imageUrl(640, 360),
            'difficulty_level' => $this->faker->numberBetween(0, 2),
            'language' => $this->faker->randomElement(['en', 'ar', 'de']),
            'course_code' => 'COURSE-'.$this->faker->unique()->numerify('####'),
            'category_id' => Category::factory(),
            'is_active' => true,
            'publish_at' => $this->faker->optional()->dateTimeBetween('-1 month', '+1 month'),
        ];
    }
}
