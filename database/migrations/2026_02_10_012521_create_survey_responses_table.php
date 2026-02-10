<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_responses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')
                ->constrained('surveys')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('center_id')
                ->constrained('centers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['survey_id', 'user_id', 'center_id', 'deleted_at'], 'survey_responses_unique');
            $table->index(['survey_id', 'user_id']);
            $table->index(['user_id', 'submitted_at']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};
