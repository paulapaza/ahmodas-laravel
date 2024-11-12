<?php

namespace Database\Seeders\catalogos;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tipo_precio extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Catálogo No. 11: Resumen diario de boletas de venta y notas electrónicas 
        // Código de tipo de valor de venta
        /* 
        01 Gravado
        02 Exonerado
        03 Inafecto
        04 Exportación
        05 Gratuitas 
        */
        DB::table('sunat_tipo_precio')->insert([
            'codigo' => '01',
            'descripcion' => 'Gravado',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_precio')->insert([
            'codigo' => '02',
            'descripcion' => 'Exonerado',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_precio')->insert([
            'codigo' => '03',
            'descripcion' => 'Inafecto',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_precio')->insert([
            'codigo' => '04',
            'descripcion' => 'Exportación',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_precio')->insert([
            'codigo' => '05',
            'descripcion' => 'Gratuitas',
            'estado' => 1,
        ]);
    }
}
