<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('surveys', function (Blueprint $table): void {
            $table->id();
            $table->unsignedTinyInteger('scope_type')->index();
            $table->foreignId('center_id')
                ->nullable()
                ->constrained('centers')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->json('title_translations');
            $table->json('description_translations')->nullable();
            $table->unsignedTinyInteger('type');
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_mandatory')->default(false);
            $table->boolean('allow_multiple_submissions')->default(false);
            $table->date('start_at')->nullable()->index();
            $table->date('end_at')->nullable()->index();
            $table->foreignId('created_by')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['scope_type', 'center_id']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
