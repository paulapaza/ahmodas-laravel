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
            /*
            {
            "tipo_de_comprobante": 2,
            "serie": "BBB1",
            "numero": 2,
            "enlace": "https://www.nubefact.com/cpe/f2a2a3d5-9843-40ea-a779-4418a9c6a08f",
            
            "aceptada_por_sunat": false,
            "sunat_description": null,
            "sunat_note": null,
            "sunat_responsecode": null,
            "sunat_soap_error": "",
            "anulado": false,
            "pdf_zip_base64": null,
            "xml_zip_base64": null,
            "cdr_zip_base64": null,
            "cadena_para_codigo_qr": "10406450258 | 03 | BBB1 | 000002 | 24.10 | 158.00 | 17/07/2025 | 1 | 00000000 | OiIY/Pg9DLK5gd3rV1VmByiMI9Bc7NY9eRnFSyUnfZg= |",
            "codigo_hash": "OiIY/Pg9DLK5gd3rV1VmByiMI9Bc7NY9eRnFSyUnfZg=",
            "codigo_de_barras": "10406450258 | 03 | BBB1 | 000002 | 24.10 | 158.00 | 17/07/2025 | 1 | 00000000 | OiIY/Pg9DLK5gd3rV1VmByiMI9Bc7NY9eRnFSyUnfZg= |",
            "key": "f2a2a3d5-9843-40ea-a779-4418a9c6a08f",
            "digest_value": "OiIY/Pg9DLK5gd3rV1VmByiMI9Bc7NY9eRnFSyUnfZg=",
                "enlace_del_pdf": "https://www.nubefact.com/cpe/f2a2a3d5-9843-40ea-a779-4418a9c6a08f.pdf",
                "enlace_del_xml": "https://www.nubefact.com/cpe/f2a2a3d5-9843-40ea-a779-4418a9c6a08f.xml",
            "enlace_del_cdr": null,
            */
            $table->string('tipo_comprobante'); // 1 for invoice
            $table->unsignedBigInteger('comprobante_modificado_id')->nullable();
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
            

            // Índices para optimizar consultas
            $table->index(['pos_order_id', 'codigo_tipo_comprobante']);
            $table->index(['comprobante_modificado_id']);
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
