<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('centers', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->tinyInteger('type'); // 0=unbranded, 1=branded
            $table->json('name_translations');
            $table->json('description_translations')->nullable();
            $table->string('logo_url')->nullable();
            $table->string('primary_color')->nullable();
            $table->integer('default_view_limit');
            $table->boolean('allow_extra_view_requests');
            $table->boolean('pdf_download_permission');
            $table->integer('device_limit');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('centers');
    }
};
