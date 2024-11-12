<?php

namespace App\Models\Inventario;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marca extends Model
{
    use HasFactory;
    protected $table = 'p_marcas';
    protected $fillable = ['nombre', 'descripcion', 'estado'];
    //sin updated_at y created_at
    public $timestamps = false;
}
