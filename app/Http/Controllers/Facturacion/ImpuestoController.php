<?php

namespace App\Http\Controllers\Facturacion;

use App\Models\Facturacion\Impuesto;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ImpuestoController extends Controller
{
        public function index()
    {
        $impuestos = Impuesto::all();
        return response()->json($impuestos, 200);
    }

   
}
