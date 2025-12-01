<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('course_pdf', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('pdf_id');

            $table->foreign('course_id')->references('id')->on('courses')->cascadeOnDelete();
            $table->foreign('pdf_id')->references('id')->on('pdfs')->cascadeOnDelete();

            $table->unique(['course_id', 'pdf_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_pdf');
    }
};
