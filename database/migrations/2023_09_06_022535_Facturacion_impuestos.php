<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FacturacionImpuestos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema :: create('facturacion_impuestos', function (Blueprint $table) {
            $table -> id();
            $table -> string('nombre', 60);
            $table -> string('codigo_tipo_afectacion', 5)->nullable();
            $table -> string('porcentaje',10)->nullable();
            $table -> boolean('incluido_en_precio');
            $table -> tinyInteger('estado');
            $table -> tinyInteger('secuencia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
            Schema :: dropIfExists('facturacion_impuestos');
    }
}
