<?php
namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posorder extends Model
{
    use HasFactory;

    protected $table = 'pos_orders';

    protected $fillable = [
        'order_number',
        'customer_id',
        'tipo_comprobante',
        'serie',
        'order_date',
        'user_id',
        'cliente_id',
        'total_amount',
        'status',
    ];

    public function orderLines()
    {
        return $this->hasMany(PosOrderLine::class, 'pos_order_id');
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class, 'pos_order_id');
    }

    /*  public function lines(): HasMany
    {
        return $this->hasMany(PosorderLine::class, 'posorder_id'); // usa el nombre de la clave foránea real
    } */
}