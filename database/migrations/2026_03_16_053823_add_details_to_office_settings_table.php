<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            // Kita gunakan after() agar kolom rapi di database
            if (!Schema::hasColumn('office_settings', 'check_in_time')) {
                $table->time('check_in_time')->default('08:00:00')->after('radius');
            }
            if (!Schema::hasColumn('office_settings', 'check_out_time')) {
                $table->time('check_out_time')->default('17:00:00')->after('check_in_time');
            }
            if (!Schema::hasColumn('office_settings', 'late_tolerance')) {
                $table->integer('late_tolerance')->default(15)->after('check_out_time');
            }
        });
    }

    public function down(): void
    {
        Schema::table('office_settings', function (Blueprint $table) {
            $table->dropColumn(['check_in_time', 'check_out_time', 'late_tolerance']);
        });
    }
};