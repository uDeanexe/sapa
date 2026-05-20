<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('area');
            $table->string('indicator');
            $table->unsignedTinyInteger('weight');
            $table->string('target');
            $table->string('measurement_method');
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });

        Schema::create('kpi_schedules', function (Blueprint $table) {
            $table->id();
            $table->string('period');
            $table->string('division')->default('Semua Divisi');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default('Draft');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('kpi_evaluations', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('division');
            $table->string('period');
            $table->unsignedTinyInteger('score');
            $table->string('grade', 2);
            $table->string('status')->default('Draft');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_evaluations');
        Schema::dropIfExists('kpi_schedules');
        Schema::dropIfExists('kpi_indicators');
    }
};
