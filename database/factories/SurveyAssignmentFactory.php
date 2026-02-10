<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SurveyAssignableType;
use App\Models\Course;
use App\Models\Survey;
use App\Models\SurveyAssignment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyAssignment>
 */
class SurveyAssignmentFactory extends Factory
{
    protected $model = SurveyAssignment::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $course = Course::factory();

        return [
            'survey_id' => Survey::factory(),
            'assignable_type' => SurveyAssignableType::Course,
            'assignable_id' => $course,
        ];
    }

    public function forCourse(Course $course): static
    {
        return $this->state(fn (array $attributes): array => [
            'assignable_type' => SurveyAssignableType::Course,
            'assignable_id' => $course->id,
        ]);
    }
}
