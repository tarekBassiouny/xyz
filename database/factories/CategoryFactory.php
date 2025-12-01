<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name_translations' => [
                'en' => $this->faker->word(),
                'ar' => 'فئة '.$this->faker->word(),
            ],
            'description_translations' => [
                'en' => $this->faker->sentence(),
                'ar' => 'وصف: '.$this->faker->sentence(),
            ],
            'is_active' => true,
        ];
    }
}
