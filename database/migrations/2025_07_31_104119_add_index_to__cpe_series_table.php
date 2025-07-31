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
        Schema::table('cpe_series', function (Blueprint $table) {
            //add index to the 'serie' column 
            $table->index('serie');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('_cpe_series', function (Blueprint $table) {
            //drop index from the 'serie' column
            $table->dropIndex(['serie']);
        });
    }
};
