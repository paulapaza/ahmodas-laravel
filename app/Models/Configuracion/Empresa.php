<?php

namespace App\Models\Configuracion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $table = 'empresa';
    //no campos updated_at y created_at
    public $timestamps = false;
    	

    protected $fillable = [
        'id',
        'nombre_comercial',
        'descripcion_comercial',
        'direccion_comercial',
        'telefono',
        'email',
        'logo',
        'website',
        'facturacion_electronica',

        'tipo_documento',
        'nro_documento',
        'razon_social',
        'direccion_fiscal',
        'pais',
        'departamento',
        'provincia',
        'distrito',
        'ubigeo',

        'soap_tipo',
        'soap_envio',

        'soap_usuario',
        'soap_clave_usuario',
        
        'validador_client_id',
        'validador_client_secret',
        'guia_remision_client_id',
        'guia_remision_client_secret',
        'estado'
    ];

}
