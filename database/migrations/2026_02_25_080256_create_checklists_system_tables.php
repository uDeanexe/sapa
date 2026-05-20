<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
        public function up() {
        Schema::create('form_templates', function (Blueprint $table) {
        $table->id();
        $table->foreignId('division_id')->constrained()->onDelete('cascade'); // Pakai ini
        $table->string('tipe_form');
        $table->json('questions');
        $table->timestamps();
    });

        // 2. Tabel untuk menyimpan JAWABAN (Isian Karyawan)
        Schema::create('daily_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->json('answers');     // Tempat simpan semua jawaban karyawan
            $table->date('date');
            $table->string('tipe_form'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklists_system_tables');
    }
};
