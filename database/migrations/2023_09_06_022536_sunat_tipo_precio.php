<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SunatTipoPrecio extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema :: create('sunat_tipo_precio', function (Blueprint $table) {
          // Catálogo No. 11: Resumen diario de boletas de venta y notas electrónicas 
          // Código de tipo de valor de venta
            $table -> string('codigo', 2)->primary();
            $table -> string('descripcion', 50);
            $table -> tinyInteger('estado');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema :: dropIfExists('sunat_tipo_precio');
    }
}
