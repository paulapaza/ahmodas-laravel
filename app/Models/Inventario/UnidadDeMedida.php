<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadDeMedida extends Model
{
    use HasFactory;
    protected $table = 'p_unidad_medida';
    protected $fillable = ['codigo', 'nombre', 'estado'];
    public $timestamps = false;
}
