<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SurveyAnswer;
use App\Models\SurveyQuestion;
use App\Models\SurveyResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyAnswer>
 */
class SurveyAnswerFactory extends Factory
{
    protected $model = SurveyAnswer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'survey_response_id' => SurveyResponse::factory(),
            'survey_question_id' => SurveyQuestion::factory(),
            'answer_text' => null,
            'answer_number' => null,
            'answer_json' => null,
        ];
    }

    public function forRating(int $rating = 4): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer_number' => $rating,
        ]);
    }

    public function forText(string $text = 'Sample answer'): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer_text' => $text,
        ]);
    }

    public function forSingleChoice(int $optionId): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer_number' => $optionId,
        ]);
    }

    /**
     * @param  array<int>  $optionIds
     */
    public function forMultipleChoice(array $optionIds): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer_json' => $optionIds,
        ]);
    }

    public function forYesNo(bool $yes): static
    {
        return $this->state(fn (array $attributes): array => [
            'answer_number' => $yes ? 1 : 0,
        ]);
    }
}
