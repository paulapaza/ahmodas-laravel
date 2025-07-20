<?php

namespace App\Http\Controllers\Facturacion;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Facturacion\CpeBaja;

class CpeBajaController extends Controller
{
    public function index()
    {
        $cpeBajas = CpeBaja::all();
        return view('facturacion.cpe_bajas.index', compact('cpeBajas'));
    }
}
