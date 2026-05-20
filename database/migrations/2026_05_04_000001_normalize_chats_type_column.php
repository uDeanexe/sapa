<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chats') || ! Schema::hasColumn('chats', 'type')) {
            return;
        }

        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE chats MODIFY type VARCHAR(20) NOT NULL DEFAULT 'text'");

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE chats ALTER COLUMN type TYPE VARCHAR(20)');
            DB::statement("ALTER TABLE chats ALTER COLUMN type SET DEFAULT 'text'");
        }
    }

    public function down(): void
    {
        // Keep the widened column: it is backward compatible and preserves existing audio rows.
    }
};
