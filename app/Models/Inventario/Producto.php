<?php

namespace App\Models\Inventario;

use App\Models\Pos\PosOrderLine;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;



class Producto extends Model
{
    use HasFactory;

    protected $casts = [
        // format 0.00
        'costo_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
    ];
    public function tiendas()
    {
        return $this->belongsToMany(Tienda::class, 'producto_tienda')
            ->withPivot('stock')
            ->withTimestamps();
    }

    public function stockEnTienda($tiendaId)
    {
        return $this->tiendas->firstWhere('id', $tiendaId)?->pivot->stock ?? 0;
    }
    // orderLines
    public function orderLines()
    {
        return $this->hasMany(PosOrderLine::class, 'producto_id'); 
    } 
}
