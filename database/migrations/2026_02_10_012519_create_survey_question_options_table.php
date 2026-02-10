<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_question_options', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_question_id')
                ->constrained('survey_questions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->json('option_translations');
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['survey_question_id', 'order_index']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_question_options');
    }
};
