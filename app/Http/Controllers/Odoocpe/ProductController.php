<?php

namespace App\Http\Controllers\Odoocpe;

use App\Http\Controllers\Controller;
use App\Models\OdooCpe\Odoo;
use Illuminate\Http\Request;

class ProductController extends Controller
{


    public function search(Request $request)
    {
        //dd($request->all());
        $Odoo = new Odoo;
        $productos = $Odoo->searchProduct($request->searchString);

        return response()->json($productos, 200);
    }

    public function imprimirEtiquetas(Request $request)
    {
        $productos = json_decode($request->productos, true);

        return view('modules.odoocpe.barcode.print_codbar', compact('productos'));
        
    }
}
