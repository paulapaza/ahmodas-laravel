<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalidaProducto extends Model
{
    use HasFactory;

    protected $table = 'salida_productos';

    protected $fillable = [
        'producto_id',
        'tienda_id',
        'stock_antes',
        'stock_despues',
        'cantidad_reducida',
        'tipo',
        'pos_order_id',
        'traslado_id',
        'comentario',
        'producto_datos'
    ];

    protected $casts = [
        'producto_datos' => 'json'
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function traslado()
    {
        return $this->belongsTo(Traslado::class);
    }
}
