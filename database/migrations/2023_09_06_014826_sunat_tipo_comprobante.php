<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SunatTipoComprobante extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema :: create('sunat_tipo_comprobante', function (Blueprint $table) {
            //Catálogo No. 01: Código de Tipo de documento 
            $table -> string('codigo', 2)->primary();
            $table -> string('descripcion', 254);
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
            Schema::dropIfExists('sunat_tipo_comprobante');
    }
}
