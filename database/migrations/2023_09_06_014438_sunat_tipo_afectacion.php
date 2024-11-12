<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SunatTipoAfectacion extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema:: create ('sunat_tipo_afectacion', function (Blueprint $table) {
          //Catálogo No. 07: Códigos de tipo de afectación del IGV
          // catalogo 07 - 10 gravado, 20 exonerado, 30 inafecto
            $table -> string('codigo',2)->primary();
            $table -> string('descripcion', 254);
         
            //Catálogo No. 05: Códigos de tipos de tributos
            // 1000 IGV, 9996 gratuito, 9997 exonerado, 9998 inafecto, 9999 otros
            $table -> string('codigo_tributo', 4)->nullable();
            $table -> string('nombre_tributo', 4)->nullable();
            $table -> string('tipo_tributo', 4)->nullable();
            //tipo precio
            //$table -> string('codigo_tipo_precio', 02);
            
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
            Schema::dropIfExists('sunat_tipo_afectacion_igv');
    }
}
