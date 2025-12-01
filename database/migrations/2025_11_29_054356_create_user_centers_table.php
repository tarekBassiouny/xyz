<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_centers', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('center_id');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('center_id')->references('id')->on('centers')->cascadeOnDelete();

            $table->unique(['user_id', 'center_id']); // user cannot join same center twice
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_centers');
    }
};
