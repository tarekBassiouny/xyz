<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'admin' => 'Full system administrator',
            'manager' => 'Center manager',
            'instructor' => 'Course instructor',
            'student' => 'Enrolled student',
            'support' => 'Support agent',
        ];

        foreach ($roles as $slug => $descEn) {
            Role::create([
                'name' => ucfirst($slug),
                'slug' => $slug,
                'description_translations' => [
                    'en' => $descEn,
                    'ar' => 'وصف '.$slug,
                ],
            ]);
        }
    }
}
