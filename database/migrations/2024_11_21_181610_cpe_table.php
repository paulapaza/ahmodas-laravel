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
        // Invoice es el documento electronico boleta, factura, nota de credito, nota de debito
        // aqui se guarda la informacion de la documento electronico
        // es la respuesta de nubefact
        Schema::create('cpes', function (Blueprint $table) {
            $table->id();
            // pos order
            $table->unsignedBigInteger('pos_order_id');
            $table->unsignedBigInteger('comprobante_modificado_id')->nullable();
           
            $table->string('tipo_comprobante'); // 1 for invoice
            $table->string('serie');
            $table->integer('numero')->default(1);
            $table->string('enlace')->nullable();

            $table->string('enlace_del_pdf')->nullable();
            $table->string('enlace_del_xml')->nullable();

            $table->string('enlace_del_cdr')->nullable();   
            $table->boolean('aceptada_por_sunat');
            $table->string('sunat_description')->nullable();
            $table->string('sunat_note')->nullable();
            $table->string('sunat_responsecode')->default('0');
            $table->string('sunat_soap_error')->nullable();
            $table->string('cadena_para_codigo_qr')->nullable();
            $table->string('codigo_hash')->nullable();

            // Si no existe, agregar referencia al comprobante que modifica
            $table->foreign('comprobante_modificado_id')->references('id')->on('cpes');

         
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('cpes');
    }
};
