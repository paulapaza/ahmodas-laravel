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
            $table->boolean('solo_codigo_barra')->default(0)->after('minutos_retraso_facturacion')->comment('0: ambos (código y lista), 1: solo código de barras o escáner');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tiendas', function (Blueprint $table) {
            $table->dropColumn('solo_codigo_barra');
        });
    }
};
