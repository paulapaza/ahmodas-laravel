<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SunatDocumentoIdentidad extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        schema:: create ('sunat_tipo_documento_identidad', function (Blueprint $table) {
            //Catálogo No. 06: Códigos de tipos de documentos de identidad
            $table -> string('codigo', 2)->primary();
            $table -> string('descripcion', 100);
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
        schema::dropIfExists('sunat_documento_identidad');

    }
}
