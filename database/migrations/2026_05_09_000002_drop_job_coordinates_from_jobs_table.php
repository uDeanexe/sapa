<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('jobs', 'latitude')) {
                $columns[] = 'latitude';
            }

            if (Schema::hasColumn('jobs', 'longitude')) {
                $columns[] = 'longitude';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            if (! Schema::hasColumn('jobs', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('google_maps_link');
            }

            if (! Schema::hasColumn('jobs', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }
};
