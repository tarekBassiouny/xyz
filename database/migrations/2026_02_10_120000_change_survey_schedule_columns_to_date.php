<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('surveys')) {
            return;
        }

        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE surveys MODIFY start_at DATE NULL');
        DB::statement('ALTER TABLE surveys MODIFY end_at DATE NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('surveys')) {
            return;
        }

        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement('ALTER TABLE surveys MODIFY start_at TIMESTAMP NULL');
        DB::statement('ALTER TABLE surveys MODIFY end_at TIMESTAMP NULL');
    }
};
