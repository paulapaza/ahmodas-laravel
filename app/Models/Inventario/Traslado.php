<?php

namespace App\Models\Inventario;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Traslado extends Model
{
    use HasFactory;

    protected $fillable = [
        'tienda_origen_id',
        'tienda_destino_id',
        'user_id',
        'codigo',
        'comentario'
    ];

    public function tiendaOrigen()
    {
        return $this->belongsTo(Tienda::class, 'tienda_origen_id');
    }

    public function tiendaDestino()
    {
        return $this->belongsTo(Tienda::class, 'tienda_destino_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(SalidaProducto::class, 'traslado_id');
    }
}
