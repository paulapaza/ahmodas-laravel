<?php

namespace Database\Seeders\catalogos;



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tipo_de_afectacion extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        10 Gravado - Operación Onerosa
        11 Gravado – Retiro por premio
        12 Gravado – Retiro por donación
        13 Gravado – Retiro
        14 Gravado – Retiro por publicidad
        15 Gravado – Bonificaciones
        16 Gravado – Retiro por entrega a trabajadores
        17 Gravado – IVAP
        20 Exonerado - Operación Onerosa
        21 Exonerado – Transferencia Gratuita
        30 Inafecto - Operación Onerosa
        31 Inafecto – Retiro por Bonificación
        32 Inafecto – Retiro
        33 Inafecto – Retiro por Muestras Médicas
        34 Inafecto - Retiro por Convenio Colectivo
        35 Inafecto – Retiro por premio
        36 Inafecto - Retiro por publicidad
        40 Exportación de bienes o servicios
        */
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '10',
            'descripcion' => 'Gravado - Operación Onerosa',
            // 
            'codigo_tributo' => '1000',
            'nombre_tributo' => 'IGV',
            'tipo_tributo' => 'VAT',

            'estado' => 1,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '11',
            'descripcion' => 'Gravado - Retiro por premio',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '12',
            'descripcion' => 'Gravado - Retiro por donación',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '13',
            'descripcion' => 'Gravado - Retiro',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '14',
            'descripcion' => 'Gravado - Retiro por publicidad',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '15',
            'descripcion' => 'Gravado - Bonificaciones',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '16',
            'descripcion' => 'Gravado - Retiro por entrega a trabajadores',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '17',
            'descripcion' => 'Gravado - IVAP',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '20',
            'descripcion' => 'Exonerado - Operación Onerosa',
            'codigo_tributo' => '9997',
            'nombre_tributo' => 'EXO',
            'tipo_tributo' => 'VAT',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '21',
            'descripcion' => 'Exonerado - Transferencia Gratuita',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '30',
            'descripcion' => 'Inafecto - Operación Onerosa',
            'codigo_tributo' => '9998',
            'nombre_tributo' => 'INA',
            'tipo_tributo' => 'FRE',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '31',
            'descripcion' => 'Inafecto - Retiro por Bonificación',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '32',
            'descripcion' => 'Inafecto - Retiro',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '33',
            'descripcion' => 'Inafecto - Retiro por Muestras Médicas',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '34',
            'descripcion' => 'Inafecto - Retiro por Convenio Colectivo',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '35',
            'descripcion' => 'Inafecto - Retiro por premio',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '36',
            'descripcion' => 'Inafecto - Retiro por publicidad',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_afectacion')->insert([
            'codigo' => '40',
            'descripcion' => 'Exportación de bienes o servicios',
            'estado' => 0,
        ]);
    }
}