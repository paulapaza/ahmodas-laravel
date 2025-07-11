<?php
namespace App\Models\Pos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Posorder extends Model
{
    use HasFactory;

    protected $table = 'pos_order';

    protected $fillable = [
        'order_number',
        'customer_id',
        'total_amount',
        'status',
    ];

    public function orderLines()
    {
        return $this->hasMany(PosOrderLine::class, 'order_id');
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class, 'pos_order_id');
    }
}