<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tours ALTER COLUMN main_image DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement(<<<'SQL'
            UPDATE tours
            SET main_image = 'https://example.com/tour-placeholder.jpg'
            WHERE main_image IS NULL
        SQL);

        DB::statement('ALTER TABLE tours ALTER COLUMN main_image SET NOT NULL');
    }
};
