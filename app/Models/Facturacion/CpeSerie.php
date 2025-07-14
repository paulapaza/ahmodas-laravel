<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CpeSerie extends Model
{
    use HasFactory;
    protected $table = 'cpe_series';
    protected $fillable = [
        'id',
        'tienda_id',
        'codigo_tipo_comprobante',
        'serie',
        'correlativo',
        'estado',
    ];
    // no timestamps
    public $timestamps = false;

    // Relationships
    public function tienda()
    {
        return $this->belongsTo('App\Models\Inventario\Tienda', 'tienda_id');
    }
    
}
