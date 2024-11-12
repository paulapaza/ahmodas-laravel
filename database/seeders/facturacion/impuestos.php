<?php

namespace Database\Seeders\facturacion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class impuestos extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('facturacion_impuestos')->insert([
            'nombre' => '18% igv',
            'codigo_tipo_afectacion' => '10',
            'porcentaje' => '18.00',
            'incluido_en_precio' => 0,
            'estado' => 1,
            'secuencia' => 1,
        ]);
        DB::table('facturacion_impuestos')->insert([
            'nombre' => '18% igv incluido en el precio',
            'codigo_tipo_afectacion' => '10',
            'porcentaje' => '18.00',
            'incluido_en_precio' => 1,
            'estado' => 1,
            'secuencia' => 2,
        ]);
       //0& exo
       DB::table('facturacion_impuestos')->insert([
            'nombre' => '0% Exonerado',
            'codigo_tipo_afectacion' => '20',
            'porcentaje' => '0.00',
            'incluido_en_precio' => 0,
            'estado' => 1,
            'secuencia' => 3,
        ]);
        //0% inafecto
        DB::table('facturacion_impuestos')->insert([
            'nombre' => '0% Inafecto',
            'codigo_tipo_afectacion' => '30',
            'porcentaje' => '0.00',
            'incluido_en_precio' => 0,
            'estado' => 1,
            'secuencia' => 4,
        ]);
        
        //0% gratuito
        DB::table('facturacion_impuestos')->insert([
            'nombre' => '0% Gratuito',
            'codigo_tipo_afectacion' => '21',
            'porcentaje' => '0.00',
            'incluido_en_precio' => 0,
            'estado' => 1,
            'secuencia' => 5,
        ]);
    }
}
