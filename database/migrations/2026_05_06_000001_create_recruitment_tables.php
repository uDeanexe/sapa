<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_openings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('division');
            $table->string('employment_type')->default('Full-time');
            $table->unsignedInteger('quota')->default(1);
            $table->string('status')->default('Draft');
            $table->string('priority')->default('Sedang');
            $table->string('sla')->nullable();
            $table->text('description')->nullable();
            $table->text('criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recruitment_opening_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 50);
            $table->string('position');
            $table->string('source')->default('Job Portal');
            $table->string('stage')->default('Applied');
            $table->unsignedTinyInteger('score')->default(0);
            $table->text('screening_notes')->nullable();
            $table->string('cv_path')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_candidates');
        Schema::dropIfExists('recruitment_openings');
    }
};
