<?php

namespace Database\Seeders;



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class unidad_de_medida extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'NIU',
            'nombre' => 'UNIDAD (BIENES)',
            'estado' => 1,
        ]);
        //docena
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'DZN',
            'nombre' => 'DOCENA',
            'estado' => 1,
        ]);
        // ciento
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'CEN',
            'nombre' => 'CIENTO',
            'estado' => 1,
        ]);
        //millar
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'MLT',
            'nombre' => 'MILLAR',
            'estado' => 1,
        ]);
       
        //kilogramo
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'KGM',
            'nombre' => 'KILOGRAMO',
            'estado' => 1,
        ]);
        //litro
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'LTR',
            'nombre' => 'LITRO',
            'estado' => 1,
        ]);
        //metro
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'MTR',
            'nombre' => 'METRO',
            'estado' => 1,
        ]);
        //metro cuadrado
        DB::table('p_unidad_medida')->insert([
            'codigo' => 'ZZ',
            'nombre' => 'UNIDAD (SERVICIOS)',
            'estado' => 1,
        ]);
        
    }
}
