<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user (center 1 by default)
        $admin = User::factory()->create([
            'name' => 'System Admin',
            'phone' => '+10000000000',
            'email' => 'admin@example.com',
            'password' => 'admin123',
            'center_id' => 1,
        ]);

        $admin->roles()->attach(
            Role::where('slug', 'admin')->value('id')
        );

        // Create additional users
        User::factory()
            ->count(20)
            ->create();
    }
}
