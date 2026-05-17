<?php

namespace App\Models\Pos;

use App\Models\Inventario\Producto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDevolucionDetalle extends Model
{
    use HasFactory;

    protected $table = 'pos_devolucion_detalles';

    protected $fillable = [
        'pos_devolucion_id',
        'producto_id',
        'tipo_item',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    public function devolucion()
    {
        return $this->belongsTo(PosDevolucion::class, 'pos_devolucion_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }
}
