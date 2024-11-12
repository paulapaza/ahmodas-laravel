<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PUnidadMedida extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema :: create('p_unidad_medida', function (Blueprint $table) {
            $table -> id();
            $table -> string('codigo', 5);
            $table -> string('nombre', 60);
            $table -> tinyInteger('estado') -> default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema :: dropIfExists('p_unidad_medida');
    }
}
