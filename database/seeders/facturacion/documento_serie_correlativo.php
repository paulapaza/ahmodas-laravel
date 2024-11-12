<?php

namespace Database\Seeders\facturacion;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class documento_serie_correlativo extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '01',
                'serie' => 'F001',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '01',
                'serie' => 'F002',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);

    
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '03',
                'serie' => 'B001',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '07',
                'serie' => 'FN01',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '07',
                'serie' => 'BN01',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '08',
                'serie' => 'FD01',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => '08',
                'serie' => 'BD01',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);

        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => 'RC',
                'serie' => '20210225',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
        DB::table('documento_serie_correlativo')->insert(
            [
                'tipo_documento' => 'RA',
                'serie' => '20210225',
                'correlativo' => 1,
                'estado' => 'activo'
            ]);
    }
}
