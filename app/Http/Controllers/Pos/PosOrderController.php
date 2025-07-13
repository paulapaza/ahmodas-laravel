<?php
namespace App\Http\Controllers\Pos;
use App\Http\Controllers\Controller;
use App\Http\Requests\PosOrderStore;
use App\Models\Pos\Posorder;
use Illuminate\Support\Facades\DB;
use App\Services\PosServices;
use Illuminate\Support\Facades\Auth;

class PosOrderController extends Controller
{
    // guardar venta
    public function store(PosOrderStore $request)
    {
        
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
        $cpeSerie = (new PosServices())->get_CpeSerie(
            $tienda_id,
            $request->input('codigo_tipo_comprobante'));
        
        $pos_order = PosOrder::create([
            'serie' => $cpeSerie->serie, // Serie del comprobante
            'order_number' => $cpeSerie->correlativo, // Correlativo del comprobante 
            'order_date' => now(), // Fecha y hora actual
            'tipo_comprobante' => $request->input('codigo_tipo_comprobante'), // Tipo de comprobante, por ejemplo 'boleta', 'factura', etc.
            'total_amount' => $request->input('total'),
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
        