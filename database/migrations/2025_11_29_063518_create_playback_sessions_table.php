<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('playback_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->foreignId('video_id')
                ->constrained('videos')
                ->cascadeOnDelete();

            $table->foreignId('device_id')
                ->constrained('user_devices')
                ->cascadeOnDelete();

            $table->timestamp('started_at');
            $table->integer('last_position_seconds');
            $table->boolean('completed')->default(false);

            $table->timestamps();

            $table->index(['user_id', 'video_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('playback_sessions');
    }
};
