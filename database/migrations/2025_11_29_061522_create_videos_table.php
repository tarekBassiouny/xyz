<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('videos', function (Blueprint $table) {
            $table->id();

            $table->foreignId('section_id')
                ->constrained('sections')
                ->cascadeOnDelete();

            $table->json('title_translations');
            $table->json('description_translations')->nullable();

            $table->string('video_url');
            $table->integer('duration_seconds');
            $table->integer('order_index');

            $table->string('thumbnail_url')->nullable();
            $table->json('thumbnail_urls')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_video');
        Schema::dropIfExists('videos');
    }
};
