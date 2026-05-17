<?php

namespace App\Models\Inventario;

use App\Models\Pos\PosOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Tienda extends Model
{
    protected $table = 'tiendas';

    protected $fillable = [
        'nombre',
        'alias',
        'direccion',
        'tienda_nota',
        'telefono',
        'estado',
        'mostrar_en_visor',
        'es_almacen',
        'minutos_retraso_facturacion',
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

    // relacion con PosOrder
    public function posOrders()
    {
        return $this->hasMany(PosOrder::class, 'tienda_id');
    }

    public function devoluciones()
    {
        return $this->hasMany(\App\Models\Pos\PosDevolucion::class, 'tienda_id');
    }
   
}
