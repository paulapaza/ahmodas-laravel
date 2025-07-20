<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Model;

class CpeBaja extends Model
{
    protected $table = 'cpe_bajas';

    protected $fillable = [
        'cpe_id',
        'numero',
        'enlace',
        'sunat_ticket_numero',
        'aceptada_por_sunat',
        'sunat_description',
        'sunat_note',
        'sunat_responsecode',
    ];

    public function cpe()
    {
        return $this->belongsTo(Cpe::class);
    }
}
