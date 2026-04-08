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
        Schema::table('almacen_traslados', function (Blueprint $table) {
            $table->date('fecha')->after('producto_id')->nullable()->index();
        });

        // Poblar fecha para registros existentes antes de aplicar restricción de unicidad
        DB::table('almacen_traslados')->update([
            'fecha' => DB::raw('CAST(created_at AS DATE)')
        ]);

        Schema::table('almacen_traslados', function (Blueprint $table) {
            $table->date('fecha')->nullable(false)->change();

            // MySQL requiere que las columnas con FK tengan un índice. 
            // Al borrar el único existente, debemos asegurar que existan otros o crearlos temporalmente.
            $table->index('tienda_id'); 
            $table->index('producto_id');

            $table->dropUnique(['tienda_id', 'producto_id']); 
            $table->unique(['tienda_id', 'producto_id', 'fecha']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('almacen_traslados', function (Blueprint $table) {
            $table->dropUnique(['tienda_id', 'producto_id', 'fecha']);
            $table->unique(['tienda_id', 'producto_id']);
            $table->dropIndex(['tienda_id']);
            $table->dropIndex(['producto_id']);
            $table->dropColumn('fecha');
        });
    }
};
