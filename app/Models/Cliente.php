<?php 
namespace App\Models;

use App\Models\Pos\Posorder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nombre',
        'tipo_documento_identidad',
        'numero_documento_identidad',
        'ubigeo',
        'direccion',
        'telefono',
        'email',
        'estado',
    ];
    
    public function posOrders()
    {
        return $this->hasMany(Posorder::class, 'cliente_id');
    }
    
}