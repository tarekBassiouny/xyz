<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EnrollmentFactory extends Factory
{
    protected $model = Enrollment::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'course_id' => Course::factory(),
            'assigned_by' => User::factory(),
            'assigned_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'status' => $this->faker->randomElement(['active', 'expired', 'revoked']),
        ];
    }
}
