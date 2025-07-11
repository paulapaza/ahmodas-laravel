<?php 
namespace App\Models\Pos;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosOrderPayment extends Model
{
    use HasFactory;

    protected $table = 'pos_order_payments';

    protected $fillable = [
        'pos_order_id',
        'payment_method',
        'amount',
    ];

    public function order()
    {
        return $this->belongsTo(Posorder::class, 'pos_order_id');
    }
}