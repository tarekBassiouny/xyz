<?php

namespace Database\Seeders;

use App\Models\Section;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    public function run(): void
    {
        Section::all()->each(function (Section $section) {
            Video::factory()
                ->count(5)
                ->create([
                    'section_id' => $section->id,
                ]);
        });
    }
}
