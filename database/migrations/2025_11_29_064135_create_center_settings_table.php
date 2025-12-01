<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('center_settings', function (Blueprint $table) {
            $table->id();

            $table->foreignId('center_id')
                ->constrained('centers')
                ->cascadeOnDelete();

            $table->string('key');
            $table->json('value')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['center_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('center_settings');
    }
};
