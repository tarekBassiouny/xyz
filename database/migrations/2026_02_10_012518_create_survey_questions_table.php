<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_questions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')
                ->constrained('surveys')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->json('question_translations');
            $table->unsignedTinyInteger('type');
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('order_index')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['survey_id', 'order_index']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_questions');
    }
};
