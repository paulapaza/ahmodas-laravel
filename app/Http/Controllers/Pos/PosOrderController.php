<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosOrderStore;
use App\Models\Pos\Posorder;
use Illuminate\Support\Facades\DB;
use App\Services\PosServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PosOrderController extends Controller
{

    //index
    public function index()
    {
        // traer orden con relacion con tienda
        $orders = Posorder::with('tienda', 'user')->get();

        return response()->json($orders);
    }
    public function indexByDate($fecha_inicio = null, $fecha_fin = null)
    {
        return Posorder::with(['tienda', 'user'])

            ->whereDate('order_date', '>=', $fecha_inicio ? Carbon::parse($fecha_inicio)->format('Y-m-d') : now()->format('Y-m-d'))
            ->whereDate('order_date', '<=', $fecha_fin ? Carbon::parse($fecha_fin)->endOfDay()->format('Y-m-d H:i:s') : now()->endOfDay()->format('Y-m-d H:i:s'))
            ->get();
    }
    // show
    public function show($id)
    {
        $posorder = Posorder::with(['tienda', 'user', 'orderLines.producto', 'payments'])
            ->findOrFail($id);
        return view('modules.ventas.posorder.show', compact('posorder'));
    }
   
    public function store(PosOrderStore $request)
    {
        $posServices = new PosServices();
        DB::beginTransaction();
        // Aquí puedes implementar la lógica para guardar la venta
        $tienda_id = Auth::user()->tienda_id; // Obtener el ID de la tienda del usuario autenticado
        // Validar que el usuario tenga una tienda asociada
        if (!$tienda_id) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene una tienda asociada.',
            ], 400);
        }
        $cpeSerie = $posServices->get_CpeSerie(
            $tienda_id,
            $request->input('codigo_tipo_comprobante')
        );

        $pos_order = PosOrder::create([
            'serie' => $cpeSerie->serie, // Serie del comprobante
            'order_number' => $cpeSerie->correlativo, // Correlativo del comprobante 
            'order_date' => now(), // Fecha y hora actual
            'tipo_comprobante' => $request->input('codigo_tipo_comprobante'), // Tipo de comprobante, por ejemplo 'boleta', 'factura', etc.
            'total_amount' => $request->input('total'),
            'tienda_id' => $tienda_id, // ID de la tienda del usuario autenticado
            'user_id' => Auth::user()->id, // ID del usuario autenticado
            'cliente_id' => $request->input('cliente_id', 1), // ID del cliente, si se proporciona
            'estado' => 'completed', // Estado de la orden, puedes ajustarlo según tu lógica
        ]);

        $payment_methods = [
            'efectivo' => $request->input('efectivo', 0),
            'tarjeta' => $request->input('tarjeta', 0),
            'yape' => $request->input('yape', 0),
            'transferencia' => $request->input('transferencia', 0),
        ];
        foreach ($payment_methods as $method => $amount) {
            if ($amount > 0) {
                $pos_order->payments()->create([
                    'payment_method' => $method,
                    'amount' => $amount,
                ]);
            }
        }

        // Procesar los productos vendidos
        $pos_order_lines = $request->input('productos', []);

        foreach ($pos_order_lines as $line) {
            $pos_order->orderlines()->create([
                'producto_id' => $line['id'],
                'quantity' => $line['cantidad'],
                'price' => $line['precio_unitario'],
                'subtotal' => $line['subtotal'],
            ]);
             $posServices->updateStockProductoTienda(
                $line['id'],
                $tienda_id,
                'venta',
                $line['cantidad']
            );
        }
       

        // Aumentar el correlativo del CPE
        (new PosServices())->increase_CpeSerie($cpeSerie);
        DB::Commit();
        // Retornar una respuesta JSON

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'data' => $pos_order,
        ]);
    }
}
