<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('center_id')
                ->constrained('centers')
                ->cascadeOnDelete();

            $table->json('title_translations');
            $table->json('description_translations')->nullable();

            $table->string('thumbnail_url')->nullable();

            $table->tinyInteger('difficulty_level'); // 0 = beginner, 1 = intermediate, 2 = advanced
            $table->string('language', 10);

            $table->string('course_code')->unique();

            $table->foreignId('category_id')
                ->nullable()
                ->constrained('categories')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->timestamp('publish_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
