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
        'product_id',
        'quantity',
        'price',
    ];

    public function order()
    {
        return $this->belongsTo(Posorder::class, 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Producto::class, 'product_id');
    }
}