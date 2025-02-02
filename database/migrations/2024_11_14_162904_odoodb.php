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
        Schema :: create('odoodb', function (Blueprint $table) {
            $table -> id();
            $table -> string('name');
            $table -> string('url');
            $table -> string('username');
            //pass texto grande 
            $table -> text('password');
            $table -> string('tipo_servidor');
            $table -> string('estado');
            $table -> timestamps();
        });
    }    

    public function down(): void
    {
        Schema::dropIfExists('dbodoo');
    }
};
