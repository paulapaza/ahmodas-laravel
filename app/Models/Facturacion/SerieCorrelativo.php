<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SerieCorrelativo extends Model
{
    use HasFactory;
    protected $table = 'documento_serie_correlativo';

    protected $fillable = [
        'id',
        'tipo_documento',
        'serie',
        'correlativo'
    ];
    // no timestamps
    public $timestamps = false;
    
}
