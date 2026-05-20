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
        Schema::table('chats', function (Blueprint $table) {
            if (! Schema::hasColumn('chats', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->after('user_id');
            }

            // Foreign key/index may already exist in some environments; keep this migration safe.
            // Laravel does not expose a simple "hasForeign" check, so we try to add the FK only when possible.
            // If the FK already exists, the migration will still succeed on most DBs by ignoring duplicate errors.
            try {
                $table->foreign('parent_id')->references('id')->on('chats')->onDelete('set null');
            } catch (\Throwable $e) {
                // no-op
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chats', function (Blueprint $table) {
            if (Schema::hasColumn('chats', 'parent_id')) {
                try { $table->dropForeign(['parent_id']); } catch (\Throwable $e) {}
                try { $table->dropIndex(['parent_id']); } catch (\Throwable $e) {}
                $table->dropColumn('parent_id');
            }
        });
    }
};
