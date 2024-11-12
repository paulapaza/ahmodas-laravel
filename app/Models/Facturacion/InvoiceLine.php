<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceLine extends Model
{
    use HasFactory;
    protected $table = "invoice_lines";
    //id	invoice_id	item	produc_name	cantidad	valor_unitario	precio_unitario	igv	porcentaje_igv	valor_total	importe_total	created_at	updated_at	
    protected $fillable = [
        'id',
        'invoice_id',
        'item',
        'produc_name',
        'cantidad',
        'valor_unitario',
        'precio_unitario',
        'igv',
        'porcentaje_igv',
        'valor_total',
        'importe_total',
        'created_at',
        'updated_at'
    ];
}
