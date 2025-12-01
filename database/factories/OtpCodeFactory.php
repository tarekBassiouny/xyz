<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OtpCode;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OtpCodeFactory extends Factory
{
    protected $model = OtpCode::class;

    public function definition(): array
    {
        return [
            'phone' => $this->faker->e164PhoneNumber(),
            'country_code' => $this->faker->countryCode(),
            'otp' => (string) rand(100000, 999999),
            'token' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(5),
            'is_used' => false,
            'user_id' => null, // Optional — created on login
        ];
    }
}
