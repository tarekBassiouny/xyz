<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SurveyQuestion;
use App\Models\SurveyQuestionOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestionOption>
 */
class SurveyQuestionOptionFactory extends Factory
{
    protected $model = SurveyQuestionOption::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_question_id' => SurveyQuestion::factory(),
            'option_translations' => [
                'en' => fake()->words(3, true),
                'ar' => 'خيار '.fake()->word(),
            ],
            'order_index' => fake()->numberBetween(0, 5),
        ];
    }
}
