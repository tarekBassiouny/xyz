<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_assignments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('survey_id')
                ->constrained('surveys')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('assignable_type', 50);
            $table->unsignedBigInteger('assignable_id');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['survey_id', 'assignable_type', 'assignable_id', 'deleted_at'], 'survey_assignments_unique');
            $table->index(['assignable_type', 'assignable_id']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_assignments');
    }
};
