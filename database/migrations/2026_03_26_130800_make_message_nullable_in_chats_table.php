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
    Schema::table('chats', function (Blueprint $table) {
        // Kita ubah kolom message agar boleh kosong (nullable)
        $table->text('message')->nullable()->change();
    });
}

public function down()
{
    Schema::table('chats', function (Blueprint $table) {
        $table->text('message')->nullable(false)->change();
    });
}

};
