<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bunny_webhook_logs', function (Blueprint $table): void {
            $table->id();
            $table->string('video_guid')->nullable()->index();
            $table->unsignedBigInteger('library_id')->nullable()->index();
            $table->integer('status')->nullable();
            $table->json('payload');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bunny_webhook_logs');
    }
};
