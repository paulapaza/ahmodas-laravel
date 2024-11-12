<?php

namespace App\Http\Controllers\Facturacion\Sunat;

use App\Models\Facturacion\Sunat\TipoPrecio;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TipoPrecioController extends Controller
{
    
    public function index()
    {
        $tiposPrecio = TipoPrecio::all();
        return response()->json($tiposPrecio, 200);
    }

   
}
