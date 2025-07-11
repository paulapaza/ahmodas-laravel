<?php

namespace App\Models\Inventario;

use App\Models\User;
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
   //relacion con user
    public function users()
    {
        return $this->hasMany(User::class, 'tienda_id');
    }

    // relacion con cpe_series
   
}
