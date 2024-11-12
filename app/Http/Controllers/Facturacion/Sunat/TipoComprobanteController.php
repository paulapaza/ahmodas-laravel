<?php

namespace App\Http\Controllers\Facturacion\Sunat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Facturacion\Sunat\TipoComprobante;

class TipoComprobanteController extends Controller
{
    public function index()
    {
        $tipoComprobantes = TipoComprobante::all();
        return response()->json($tipoComprobantes, 200);
    }
}
