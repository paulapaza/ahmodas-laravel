<?php

namespace App\Http\Controllers\Inventario;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductoRequest;
use App\Models\Inventario\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    public function index()
    {
       $productos = DB::table('productos')
                ->select('id', 'barcode', 'nombre', 'costo_unitario', 'precio_unitario','categoria_id', 'stock', 'estado')
                ->get();


        return response()->json($productos, 200);
    }
    //show
    public function show($id)
    {
        $producto = Producto::find($id);

        return response()->json($producto, 200);
    }
    public function store(ProductoRequest $request)
    {
        /*
            nombre: Colores largos x12 layconsa
            tipo: A
            unidad_medida_id: 1
            categoria_id: 1

            impuesto_id: 2
            incluye_igv: 1
            impuesto_bolsa: 0
            
            precio_unitario: 8.50
            costo_unitario: 542
            barcode: 4545
            descripcion: 
            img: (binary)
            marca_id: 1
            estado: 1
            
            stock_minimo: 
            stock_maximo: 
            stock_alerta: 

            compra_incluyeIgv: 1
            compra_tipo_afectacion_igv_codigo: 10
        */
        $producto = new Producto();
        $producto->nombre = $request->nombre;
        
        $producto->tipo = $request->tipo;
        $producto->unidad_medida_id = $request->unidad_medida_id;
        $producto->categoria_id = $request->categoria_id;
        
        
        //imnpuesto ventas
        $producto->impuesto_id = $request->impuesto_id;  // 2 --> 18% igv incluido en el precio	10	18.00	Si	2
        $producto->incluye_igv = $request->incluye_igv; // esta en el impuesto_id
        $producto->tipo_afectacion_igv = '10'; // esta en el impuesto_id
        $producto->impuesto_bolsa = $request->impuesto_bolsa; 
        
        
        $producto->precio_unitario = $request->precio_unitario;
        $producto->costo_unitario = $request->costo_unitario;
        
        $producto->barcode = $request->barcode;
        $producto->descripcion = $request->descripcion;
        $producto->marca_id = $request->marca_id;
        $producto->imagen = $request->imagen;
        $producto->estado = $request->estado;
        
        $producto->stock_minimo = $request->stock_minimo;
        $producto->stock_maximo = $request->stock_maximo;
        $producto->stock_alerta = $request->stock_alerta;

        //impuesto compras
        $producto->compra_incluyeIgv = $request->compra_incluyeIvg;
            // aqui poner el tipo de impuesto de compra
        $producto->compra_tipo_afectacion_igv_codigo = $request->compra_tipo_afectacion_igv_codigo;
        $producto->tipo_afectacion_igv_id = $request->tipo_afectacion_igv_id;
        $producto->save();

        
    

        return response()->json([
            "success" => true,
            "message" => "Producto creado correctamente",
           
        ], 201);
    }
    //update
    public function update(Request $request, $id)
    {
        $producto = Producto::find($id);
        $producto->tipo = $request->tipo;
        $producto->categoria_id = $request->categoria_id;
        $producto->nombre = $request->nombre;
        $producto->descripcion = $request->descripcion;
        $producto->marca_id = $request->marca_id;
        $producto->barcode = $request->barcode;
        $producto->imagen = $request->imagen;
        $producto->precio_unitario = $request->precio_unitario;
        $producto->costo_unitario = $request->costo_unitario;
        $producto->tipo_precio = $request->tipo_precio;
        $producto->tipo_afectacion_igv_id = $request->tipo_afectacion_igv_id;
        $producto->impuesto_bolsa = $request->impuesto_bolsa;
        $producto->unidad_medida_id = $request->unidad_medida_id;
        $producto->estado = $request->estado;
        $producto->save();

        return response()->json([
            "success" => true,
            "message" => "Producto actualizado correctamente",
           
        ], 201);
    }
}
