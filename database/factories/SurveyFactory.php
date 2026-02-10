<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\SurveyScopeType;
use App\Enums\SurveyType;
use App\Models\Center;
use App\Models\Survey;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Survey>
 */
class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $center = Center::factory();

        return [
            'scope_type' => SurveyScopeType::Center,
            'center_id' => $center,
            'title_translations' => [
                'en' => fake()->sentence(3),
                'ar' => 'استطلاع '.fake()->word(),
            ],
            'description_translations' => [
                'en' => fake()->paragraph(),
                'ar' => 'وصف الاستطلاع',
            ],
            'type' => fake()->randomElement(SurveyType::cases()),
            'is_active' => true,
            'is_mandatory' => fake()->boolean(30),
            'allow_multiple_submissions' => fake()->boolean(20),
            'start_at' => null,
            'end_at' => null,
            'created_by' => User::factory()->for($center, 'center'),
        ];
    }

    public function system(): static
    {
        return $this->state(fn (array $attributes): array => [
            'scope_type' => SurveyScopeType::System,
            'center_id' => null,
        ]);
    }

    public function center(Center $center): static
    {
        return $this->state(fn (array $attributes): array => [
            'scope_type' => SurveyScopeType::Center,
            'center_id' => $center->id,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function mandatory(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyType::Mandatory,
            'is_mandatory' => true,
        ]);
    }

    public function feedback(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyType::Feedback,
        ]);
    }

    public function poll(): static
    {
        return $this->state(fn (array $attributes): array => [
            'type' => SurveyType::Poll,
        ]);
    }
}
