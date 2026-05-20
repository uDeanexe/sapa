<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('presences', function (Blueprint $table) {
        // Cek dulu agar tidak error jika kolom sudah ada
        if (!Schema::hasColumn('presences', 'category')) {
            $table->string('category')->default('masuk')->after('user_id');
        }
        if (!Schema::hasColumn('presences', 'notes')) {
            $table->text('notes')->nullable()->after('category');
        }
        if (!Schema::hasColumn('presences', 'attachment')) {
            $table->string('attachment')->nullable()->after('notes');
        }
    });
}
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            
        });
    }
};
