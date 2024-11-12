<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Empresa extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create ('empresa', function (Blueprint $table) {
            $table -> id();
            $table -> string('nombre_comercial', 100);
            $table -> string('descripcion_comercial', 254);
            $table -> string('direccion_comercial', 254);
            $table -> string('telefono', 15);
            $table -> string('email', 100);
            $table -> string('logo', 255);
            $table -> string('website', 255);
            $table -> string('facturacion_electronica',2);
          

            $table -> string('tipo_documento', 2)->nullable();
            $table -> string('nro_documento', 11)->nullable();
            $table -> string('razon_social', 200)->nullable();
            $table -> string('direccion_fiscal', 255)->nullable();
            $table -> string('pais', 60)->nullable();
            $table -> string('departamento', 60)->nullable();
            $table -> string('provincia', 100)->nullable();
            $table -> string('distrito', 100)->nullable();
            $table -> string('ubigeo', 10)->nullable();

            //tipo de entorno demo, produccion, interno
            $table -> string('soap_tipo', 12)->nullable();
            $table -> string('soap_envio', 12)->nullable();
            $table -> string('soap_usuario', 60)->nullable();
            $table -> string('soap_clave_usuario', 60)->nullable();

            //*Certificado Digital
            $table -> string('certificado_pass', 255)->nullable();
            $table -> date('certificado_caducidad')->nullable();
            $table -> string('certificado_path', 255)->nullable();

            /*****
             * consulta validador de documentos
             */
            $table -> string('validador_client_id', 255)->nullable();
            $table -> string('validador_client_secret', 40)->nullable();
           /***
            * guias de remision electronicas
            */
            $table -> string('guia_remision_client_id', 255)->nullable();
            $table -> string('guia_remision_client_secret', 40)->nullable();
            
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
            Schema :: dropIfExists('empresa');
    }
}
