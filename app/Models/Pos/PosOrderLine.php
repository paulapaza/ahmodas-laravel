<?php
namespace App\Models\Pos;

use App\Models\Inventario\Producto;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class PosOrderLine extends Model
{
    use HasFactory;

    protected $table = 'pos_order_lines';

    protected $fillable = [
        'order_id',
        'producto_id',
        'quantity',
        'subtotal',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(PosOrder::class, 'order_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}