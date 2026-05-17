<?php

namespace App\Models\Pos;

use App\Models\Inventario\Tienda;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PosDevolucion extends Model
{
    use HasFactory;

    protected $table = 'pos_devoluciones';

    protected $fillable = [
        'tienda_id',
        'user_id',
        'tipo_movimiento',
        'monto_devolucion',
        'monto_nuevo',
        'monto_diferencia',
        'metodo_pago',
        'motivo',
    ];

    public function detalles()
    {
        return $this->hasMany(PosDevolucionDetalle::class, 'pos_devolucion_id');
    }

    public function tienda()
    {
        return $this->belongsTo(Tienda::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
