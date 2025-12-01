<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        Center::all()->each(function ($center) {
            Course::factory()
                ->count(10)
                ->create(['center_id' => $center->id]);
        });
    }
}
