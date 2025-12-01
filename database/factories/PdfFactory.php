<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Pdf;
use App\Models\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class PdfFactory extends Factory
{
    protected $model = Pdf::class;

    public function definition(): array
    {
        return [
            'section_id' => Section::factory(),

            'title_translations' => [
                'en' => 'PDF: '.$this->faker->sentence(),
                'ar' => 'ملف: '.$this->faker->sentence(),
            ],

            'description_translations' => [
                'en' => $this->faker->paragraph(),
                'ar' => 'وصف: '.$this->faker->sentence(),
            ],

            'file_url' => $this->faker->url(),
            'order_index' => $this->faker->numberBetween(1, 50),
        ];
    }
}
