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
        Schema::table('tiendas', function (Blueprint $table) {
            $table->integer('minutos_retraso_facturacion')->default(0)->after('token_facturacion')
                ->comment('Minutos de espera antes de enviar a SUNAT. 0 = Inmediato.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn('minutos_retraso_facturacion');
        });
    }
};
