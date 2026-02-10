<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Center;
use App\Models\Survey;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SurveyResponse>
 */
class SurveyResponseFactory extends Factory
{
    protected $model = SurveyResponse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $center = Center::factory();

        return [
            'survey_id' => Survey::factory()->for($center, 'center'),
            'user_id' => User::factory()->for($center, 'center'),
            'center_id' => $center,
            'submitted_at' => now(),
        ];
    }
}
