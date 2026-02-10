<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_response_id')
                ->constrained('survey_responses')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('survey_question_id')
                ->constrained('survey_questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->text('answer_text')->nullable();
            $table->integer('answer_number')->nullable();
            $table->json('answer_json')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['survey_response_id', 'survey_question_id']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_answers');
    }
};
