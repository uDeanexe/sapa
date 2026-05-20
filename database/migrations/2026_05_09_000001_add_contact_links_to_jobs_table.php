<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('whatsapp_number', 32)->nullable()->after('client_name');
            $table->string('google_maps_link', 2048)->nullable()->after('location');
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'google_maps_link',
            ]);
        });
    }
};
