<?php 
namespace App\Models;
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
        'nacionalidad',
        'pais',
        'ubigeo',
        'direccion',
        'telefono',
        'mail',
        'estado',
    ];

    
}