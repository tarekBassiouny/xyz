<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();

            $table->string('phone');
            $table->string('country_code', 5);

            $table->string('otp', 10);
            $table->string('token', 100);

            $table->timestamp('expires_at');
            $table->boolean('is_used')->default(false);

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();

            // SPEED UP lookups:
            $table->index(['phone', 'token']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
