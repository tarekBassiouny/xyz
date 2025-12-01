<?php

namespace Database\Seeders;

use App\Models\Pdf;
use App\Models\Section;
use Illuminate\Database\Seeder;

class PdfSeeder extends Seeder
{
    public function run(): void
    {
        Section::all()->each(function (Section $section) {
            Pdf::factory()
                ->count(6)
                ->create([
                    'section_id' => $section->id,
                ]);
        });
    }
}
