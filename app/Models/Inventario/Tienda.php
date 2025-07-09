<?php
namespace App\Models\Inventario;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'nombre',
        'direccion',
        'estado',
    ];

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_tienda')
            ->withPivot('stock')
            ->withTimestamps();
    }

    public function stockEnProducto($productoId)
    {
        return $this->productos->firstWhere('id', $productoId)?->pivot->stock ?? 0;
    }
}