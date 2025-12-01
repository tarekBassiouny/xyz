<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_devices', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->string('device_uuid');
            $table->string('device_name')->nullable();
            $table->string('device_os');
            $table->string('device_type');

            $table->boolean('is_active')->default(true);

            $table->timestamp('last_used_at')->nullable();

            $table->timestamps();

            // Prevent multiple registrations of same device for the user
            $table->unique(['user_id', 'device_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_devices');
    }
};
