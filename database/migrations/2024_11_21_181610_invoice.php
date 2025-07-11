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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            // pos order
            $table->unsignedBigInteger('pos_order_id');
            /*
            {
            "tipo_de_comprobante": 1,
            "serie": "FFF1",
            "numero": 1,
            "enlace": "https://www.nubefact.com/cpe/d268f882-4554-a403c6712e6",
            "enlace_del_pdf": "",
            "enlace_del_xml": "",
            "enlace_del_cdr": "",
            "aceptada_por_sunat": true,
            "sunat_description": "La Factura numero FFF1-1, ha sido aceptada",
            "sunat_note": null,
            "sunat_responsecode": "0",
            "sunat_soap_error": "",
            "cadena_para_codigo_qr": "20600695771 | 01 | FFF1 | 000001 | ...",
            "codigo_hash": "xMLFMnbgp1/bHEy572RKRTE9hPY="
            }
            */
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
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('invoices');
    }
};
