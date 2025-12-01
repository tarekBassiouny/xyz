<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name_translations' => ['en' => 'Programming', 'ar' => 'البرمجة'],
                'description_translations' => ['en' => 'Dev & Code', 'ar' => 'تطوير وبرمجة'],
            ],
            [
                'name_translations' => ['en' => 'Design', 'ar' => 'التصميم'],
                'description_translations' => ['en' => 'UI/UX & Graphics', 'ar' => 'واجهة وتجربة المستخدم'],
            ],
            [
                'name_translations' => ['en' => 'Business', 'ar' => 'الأعمال'],
                'description_translations' => ['en' => 'Management & Marketing', 'ar' => 'إدارة وتسويق'],
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
