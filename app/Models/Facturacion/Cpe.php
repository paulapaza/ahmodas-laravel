<?php

namespace App\Models\Facturacion;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cpe extends Model
{
    use HasFactory;
    protected $table = "Cpes";

    //no usar timestamps ni create ni update
    public $timestamps = false;
  
    //relacion con PosOrder
    public function posOrder()
    {
        return $this->belongsTo(\App\Models\Pos\PosOrder::class, 'pos_order_id', 'id');
    }
    //relacion con CpeBaja
    public function cpeBajas()
    {
        return $this->hasMany(CpeBaja::class, 'cpe_id', 'id');
    }
    
}
