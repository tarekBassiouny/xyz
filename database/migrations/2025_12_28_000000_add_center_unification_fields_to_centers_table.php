<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('centers', function (Blueprint $table): void {
            if (! Schema::hasColumn('centers', 'onboarding_status')) {
                $table->string('onboarding_status')->default('DRAFT')->after('primary_color');
            }

            if (! Schema::hasColumn('centers', 'branding_metadata')) {
                $table->json('branding_metadata')->nullable()->after('onboarding_status');
            }

            if (! Schema::hasColumn('centers', 'storage_driver')) {
                $table->string('storage_driver')->default('spaces')->after('branding_metadata');
            }

            if (! Schema::hasColumn('centers', 'storage_root')) {
                $table->string('storage_root')->nullable()->after('storage_driver');
            }
        });

        Schema::table('centers', function (Blueprint $table): void {
            if (Schema::hasColumn('centers', 'bunny_library_id')) {
                $table->unique('bunny_library_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('centers', function (Blueprint $table): void {
            if (Schema::hasColumn('centers', 'onboarding_status')) {
                $table->dropColumn('onboarding_status');
            }

            if (Schema::hasColumn('centers', 'branding_metadata')) {
                $table->dropColumn('branding_metadata');
            }

            if (Schema::hasColumn('centers', 'storage_driver')) {
                $table->dropColumn('storage_driver');
            }

            if (Schema::hasColumn('centers', 'storage_root')) {
                $table->dropColumn('storage_root');
            }
        });

        Schema::table('centers', function (Blueprint $table): void {
            $table->dropUnique(['bunny_library_id']);
        });
    }
};
