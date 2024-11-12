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
        //id	idempresa	tipocomp	idserie	serie	correlativo	fecha_emision	codmoneda	op_gravadas	op_exoneradas	op_inafectas	igv	total	cliente_idOdoo	cliente_razon_social	cliente_direccion	cliente_VAT	cliente_pais	cliente_departamento	cliente_provincia	cliente_distrito	feestado	fecodigoerror	femensajesunat	nombrexml	xmlbase64	cdrbase64	forma_pago	monto_pendient
        Schema::create('invoice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('idempresa')->constrained('empresa')->onUpdate('cascade')->onDelete('cascade');
            $table->string('tipocomp', 2);
            $table->foreignId('idserie')->constrained('documento_serie_correlativo')->onUpdate('cascade')->onDelete('cascade');
            $table->string('serie', 4);
            $table->string('correlativo');
            $table->bigInteger('invoice_ref_id')->nullable();
            $table->date('fecha_emision');
            $table->time('hora_emision');
            $table->string('codmoneda', 3);
            $table->decimal('op_gravadas', 10, 2);
            $table->decimal('op_exoneradas', 10, 2);
            $table->decimal('op_inafectas', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('isc', 10, 2)->nullable();
            $table->decimal('total', 10, 2);
            $table->integer('cliente_Odoo');
            $table->string('cliente_razon_social', 254);
            $table->string('cliente_direccion', 254)->nullable();
            $table->string('cliente_tipo_doc',2);
            $table->string('cliente_VAT', 11);
            $table->string('cliente_pais', 11)->nullable();
            $table->string('cliente_departamento', 60)->nullable();
            $table->string('cliente_provincia', 60)->nullable();
            $table->string('cliente_distrito', 80)->nullable();
            $table->string('feestado', 1)->nullable();
            $table->string('fecodigoerror', 10)->nullable();
            $table->text('femensajesunat')->nullable();
            $table->string('hash_cpe',30)->nullable();
            $table->string('nombrexml', 60)->nullable();
            $table->text('xmlbase64')->nullable();
            $table->text('cdrbase64')->nullable();
            $table->string('forma_pago', 50);
            $table->decimal('monto_pendiente', 10, 2);
            $table->string('estado', 15)->default('activo');
            $table->timestamps();
            // hace de id clave primaria y unica

            $table->unique(['id']);
            // clave foranea a tipocomp
            $table->foreign('tipocomp')->references('codigo')->on('sunat_tipo_comprobante')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::dropIfExists('invoice');
    }
};
