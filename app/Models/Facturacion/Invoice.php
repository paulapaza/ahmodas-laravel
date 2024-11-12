<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;
    protected $table = "invoice";
    //idempresa	tipocomp	idserie	serie	correlativo	fecha_emision	codmoneda	op_gravadas	op_exoneradas	op_inafectas	igv	total	cliente_Odoo	cliente_razon_social	cliente_direccion	cliente_VAT	cliente_pais	cliente_departamento	cliente_provincia	cliente_distrito	feestado	fecodigoerror	femensajesunat	nombrexml	xmlbase64	cdrbase64	forma_pago	monto_pendient
    protected $fillable = [
        'id',
        'idempresa',
        'tipocomp',
        'idserie',
        'serie',
        'correlativo',
        'fecha_emision',
        'codmoneda',
        'op_gravadas',
        'op_exoneradas',
        'op_inafectas',
        'igv',
        'total',
        'cliente_Odoo',
        'cliente_razon_social',
        'cliente_direccion',
        'cliente_VAT',
        'cliente_pais',
        'cliente_departamento',
        'cliente_provincia',
        'cliente_distrito',
        'feestado',
        'fecodigoerror',
        'femensajesunat',
        'nombrexml',
        'xmlbase64',
        'cdrbase64',
        'forma_pago',
        'monto_pendiente',
        'estado'
    ];

    
}
