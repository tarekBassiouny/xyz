<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Database\Seeder;

class UserDeviceSeeder extends Seeder
{
    public function run(): void
    {
        // Assign 1–3 devices per user
        User::all()->each(function (User $user) {
            UserDevice::factory()
                ->count(rand(1, 3))
                ->create(['user_id' => $user->id]);
        });
    }
}
