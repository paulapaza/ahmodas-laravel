<?php

namespace Database\Seeders\catalogos;



use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class tipo_documento_identidad extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /*
        0 Doc.trib.no.dom.sin.ruc
        1 Doc. Nacional de identidad
        4 Carnet de extranjería
        6 Registro Único de contribuyentes
        7 Pasaporte
        A Ced. Diplomática de identidad
        B Documento identidad país residencia-no.d
        C Tax Identification Number - TIN – Doc Trib PP.NN
        D Identification Number - IN – Doc Trib PP. JJ
        E TAM- Tarjeta Andina de Migración
        */
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => '0',
            'descripcion' => 'Doc.trib.no.dom.sin.ruc',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => '1',
            'descripcion' => 'Doc. Nacional de identidad',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => '4',
            'descripcion' => 'Carnet de extranjería',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => '6',
            'descripcion' => 'Registro Único de contribuyentes',
            'estado' => 1,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => '7',
            'descripcion' => 'Pasaporte',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => 'A',
            'descripcion' => 'Ced. Diplomática de identidad',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => 'B',
            'descripcion' => 'Documento identidad país residencia-no.d',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => 'C',
            'descripcion' => 'Tax Identification Number - TIN – Doc Trib PP.NN',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => 'D',
            'descripcion' => 'Identification Number - IN – Doc Trib PP. JJ',
            'estado' => 0,
        ]);
        DB::table('sunat_tipo_documento_identidad')->insert([
            'codigo' => 'E',
            'descripcion' => 'TAM- Tarjeta Andina de Migración',
            'estado' => 0,
        ]);

    }
}
