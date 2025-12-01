<?php

namespace Database\Seeders;

use App\Models\Center;
use App\Models\CenterSetting;
use Illuminate\Database\Seeder;

class CenterSettingSeeder extends Seeder
{
    public function run(): void
    {
        Center::all()->each(function (Center $center) {
            CenterSetting::factory()->create([
                'center_id' => $center->id,
                'key' => 'primary_color',
                'value' => '#3490dc',
            ]);

            CenterSetting::factory()->create([
                'center_id' => $center->id,
                'key' => 'secondary_color',
                'value' => '#ffed4a',
            ]);
        });
    }
}
