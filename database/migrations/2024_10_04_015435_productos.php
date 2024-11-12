<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Productos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema :: create('productos', function (Blueprint $table) {
            $table -> id();
            $table -> string('nombre', 100);
            
            $table -> string('tipo', 1)->default('A');
            $table -> foreign('categoria_id') -> references('id') -> on('categorias');
            $table -> foreign('unidad_medida_id') -> references('id') -> on('p_unidad_medida');
            
            $table -> foreignId('categoria_id')->nullable();
            $table -> foreignId('unidad_medida_id');
            
            
            $table -> float('precio_unitario', 8, 2);
            $table -> float('costo_unitario', 8, 2)->nullable();
            $table -> string('barcode', 13)->nullable();
            
            $table -> string('venta_impuesto_id', 2)->nullable();
            /* $table -> boolean('incluye_igv')->default(true);
            $table -> string('tipo_afectacion_igv_codigo', 2)->nullable();
            $table -> string('tipo_afectacion_igv', 2);
             */
            $table -> boolean('impuesto_bolsa')->default(false);
            
            
            $table -> string('descripcion', 255)->nullable();
            $table -> foreignId('marca_id') -> references('id') -> on('p_marcas');
            $table -> string('imagen', 255)->nullable();
            
            $table -> integer('stock')->default(0);
            $table -> tinyInteger('estado')->default(1);
            //compra
            $table -> float('stock_minimo', 8, 2)->nullable();
            $table -> float('stock_maximo', 8, 2)->nullable();
            $table -> float('stock_alerta', 8, 2)->nullable();
            
            $table -> string('compra_impuesto_id', 2)->nullable();
            
            $table -> timestamps();
        });
    }    

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema :: dropIfExists('productos');
    }
}
