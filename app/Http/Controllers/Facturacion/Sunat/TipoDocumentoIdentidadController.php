<?php

namespace App\Http\Controllers\Facturacion\Sunat;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Facturacion\Sunat\TipoDocumentoIdentidad;

class TipoDocumentoIdentidadController extends Controller
{
    public function index()
    {
        $tipoDocumentoIdentidad = TipoDocumentoIdentidad::all();
        return response()->json($tipoDocumentoIdentidad, 200);
    }
}
