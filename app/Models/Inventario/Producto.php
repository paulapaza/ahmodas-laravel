<?php

namespace App\Models\Inventario;


use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    
    protected $casts = [
        // format 0.00
        'costo_unitario' => 'decimal:2',
        'precio_unitario' => 'decimal:2',
    ];
    
}

