<?php

namespace App\Http\Controllers\Facturacion\Sunat;

use App\Models\Facturacion\Sunat\TipoAfectacion;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TipoAfectacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tiposAfectacionIgv = TipoAfectacion::all();
        
        return response()->json($tiposAfectacionIgv, 200);
    }

  
   
}
