<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserCenterSeeder extends Seeder
{
    public function run(): void
    {
        $centers = Center::all();
        $users = User::all();

        // If no centers or no users exist, skip to avoid errors
        if ($centers->isEmpty() || $users->isEmpty()) {
            return;
        }

        foreach ($users as $user) {
            // Assign each user to 1–2 random centers
            $assignedCenters = $centers->random(
                min(2, max(1, $centers->count()))
            );

            foreach ($assignedCenters as $center) {
                $user->centers()->attach($center->id);
            }
        }
    }
}
