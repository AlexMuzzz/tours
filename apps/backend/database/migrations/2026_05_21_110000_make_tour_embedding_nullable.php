<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE tour_embeddings ALTER COLUMN embedding DROP NOT NULL');
    }

    public function down(): void
    {
        DB::statement("UPDATE tour_embeddings SET embedding = '[]'::jsonb WHERE embedding IS NULL");
        DB::statement('ALTER TABLE tour_embeddings ALTER COLUMN embedding SET NOT NULL');
    }
};
