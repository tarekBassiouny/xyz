<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SurveyQuestionType;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyQuestion>
 */
class SurveyQuestionFactory extends Factory
{
    protected $model = SurveyQuestion::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_id' => Survey::factory(),
            'question_translations' => [
                'en' => fake()->sentence().'?',
                'ar' => 'سؤال '.fake()->word().'؟',
            ],
            'type' => fake()->randomElement(SurveyQuestionType::cases()),
            'is_required' => fake()->boolean(70),
            'order_index' => fake()->numberBetween(0, 10),
        ];
    }

    public function singleChoice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyQuestionType::SingleChoice,
        ]);
    }

    public function multipleChoice(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyQuestionType::MultipleChoice,
        ]);
    }

    public function rating(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyQuestionType::Rating,
        ]);
    }

    public function text(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyQuestionType::Text,
        ]);
    }

    public function yesNo(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyQuestionType::YesNo,
        ]);
    }

    public function required(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => true,
        ]);
    }

    public function optional(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_required' => false,
        ]);
    }
}
