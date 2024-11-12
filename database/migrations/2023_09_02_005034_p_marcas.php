<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PMarcas extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema :: create('p_marcas', function (Blueprint $table) {
            $table -> id();
            $table -> string('nombre', 100);
            $table -> string('descripcion', 255);
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
         Schema :: dropIfExists('p_marcas');
    }
}
