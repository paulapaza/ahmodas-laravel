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
        Schema::create('cpe_bajas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cpe_id');
           
            $table->string('motivo')->nullable();
            $table->string('numero')->nullable();
            $table->string('enlace')->nullable();
            $table->string('sunat_ticket_numero')->nullable();
            $table->boolean('aceptada_por_sunat')->default(false);
            $table->string('sunat_description')->nullable();
            $table->string('sunat_note')->nullable();
            $table->string('sunat_responsecode')->default('0');
            $table->string('sunat_soap_error')->nullable();
            $table->string('xml_zip_base64')->nullable();
            $table->string('pdf_zip_base64')->nullable();
            $table->string('cdr_zip_base64')->nullable();
            $table->string('enlace_del_pdf')->nullable();
            $table->string('enlace_del_xml')->nullable();
            $table->string('enlace_del_cdr')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('cpe_bajas');
    }
};
