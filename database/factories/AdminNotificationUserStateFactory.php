<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\AdminNotification;
use App\Models\AdminNotificationUserState;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AdminNotificationUserState>
 */
class AdminNotificationUserStateFactory extends Factory
{
    protected $model = AdminNotificationUserState::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'admin_notification_id' => AdminNotification::factory(),
            'user_id' => User::factory(),
            'read_at' => null,
        ];
    }
}
