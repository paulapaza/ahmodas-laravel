<?php

namespace App\Models\Facturacion\Sunat;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoPrecio extends Model
{
    use HasFactory;
    protected $table = 'sunat_tipo_precio';
}
