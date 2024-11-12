<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DocumentoSerieCorrelativo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() 
    {
        Schema:: create ('documento_serie_correlativo', function (Blueprint $table) {
            $table -> id();
            $table -> string('tipo_documento', 2);
            $table -> string('serie', 8);
            $table -> integer('correlativo');
            $table -> string('estado', 15) -> default('activo');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
         Schema::dropIfExists('documento_serie_correlativo');
    }
}
