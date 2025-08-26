<?php
namespace App\Models\Pos;

use App\Models\Inventario\Tienda;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Cliente;

class PosOrder extends Model
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
        'tienda_id',
        'cliente_id',
        'total_amount',
        'moneda', // 1 for PEN, 2 for USD
    'estado',
    'sale_token',
    ];

    public function orderLines()
    {
        return $this->hasMany(PosOrderLine::class, 'pos_order_id');
    }

    public function payments()
    {
        return $this->hasMany(PosOrderPayment::class, 'pos_order_id');
    }

    // Relación con la tienda
    public function tienda()
    {
        return $this->belongsTo(Tienda::class, 'tienda_id');
    }
    // Relación con el usuario
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    /*  public function lines(): HasMany
    {
        return $this->hasMany(PosOrderLine::class, 'posorder_id'); // usa el nombre de la clave foránea real
    } */

    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'cliente_id'); // Asumiendo que el cliente es un usuario
    }
    // relacion con Cpe
    public function cpe()
    {
        return $this->hasOne(\App\Models\Facturacion\Cpe::class, 'pos_order_id', 'id');
    }   
    
    public function notasCredito()
    {
        return $this->hasMany(\App\Models\Facturacion\Cpe::class, 'comprobante_modificado_id', 'id')
                    ->where('tipo_comprobante', '3'); // Nota de crédito
    }

    public function notasDebito()
    {
        return $this->hasMany(\App\Models\Facturacion\Cpe::class, 'pos_order_id', 'id')
                    ->where('tipo_comprobante', '4'); // Nota de débito
    }
}