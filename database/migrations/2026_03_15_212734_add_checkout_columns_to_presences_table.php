<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('presences', function (Blueprint $table) {
        if (!Schema::hasColumn('presences', 'lat_out')) {
            $table->string('lat_out')->nullable();
        }
        if (!Schema::hasColumn('presences', 'lng_out')) {
            $table->string('lng_out')->nullable();
        }
        if (!Schema::hasColumn('presences', 'photo_out')) {
            $table->string('photo_out')->nullable();
        }
        if (!Schema::hasColumn('presences', 'notes_out')) {
            $table->text('notes_out')->nullable();
        }
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presences', function (Blueprint $table) {
            //
        });
    }
};
