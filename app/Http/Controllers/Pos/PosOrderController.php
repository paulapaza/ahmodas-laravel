<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Http\Requests\PosOrderStore;
use App\Models\Inventario\Tienda;
use App\Models\Pos\Posorder;
use App\Services\CpeServices;
use Illuminate\Support\Facades\DB;
use App\Services\PosServices;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        $codigo_tipo_comprobante = [
            '01' => '1',
            '03' => '2',
        ];
        $cpeSerie = $posServices->get_CpeSerie(
            $tienda_id,
            $codigo_tipo_comprobante[$request->input('codigo_tipo_comprobante')],
        );
       
        $cliente = $posServices->procesarCliente($request->input('cliente'));
        $pos_order = PosOrder::create([
            'serie' => $cpeSerie->serie, // Serie del comprobante
            'order_number' => $cpeSerie->correlativo, // Correlativo del comprobante 
            'order_date' => now(), // Fecha y hora actual
            'tipo_comprobante' => $request->input('codigo_tipo_comprobante'), // Tipo de comprobante, por ejemplo 'boleta', 'factura', etc.
            'total_amount' => $request->input('total'),
            'moneda' => (int)$request->input('moneda'), // Moneda, 1 para PEN, 2 para USD
            'tienda_id' => $tienda_id, // ID de la tienda del usuario autenticado
            'user_id' => Auth::user()->id, // ID del usuario autenticado
            'cliente_id' => $cliente->id, // ID del cliente, si se proporciona
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
        $cpeServices = new CpeServices();
        // Enviar el CPE al servicio de facturación si el tiepo de doc es 01  y 03
       
        if (in_array($request->input('codigo_tipo_comprobante'), ['01', '03'])) {
                       
            $api_response = $cpeServices->SendCep($cpeSerie, $cliente, $pos_order, $pos_order_lines);
        } 

        // Aumentar el correlativo del CPE
        $posServices->increase_CpeSerie($cpeSerie);

        DB::Commit();
        // Retornar una respuesta JSON

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'pos_order' => $pos_order,
            'cpe_response' => isset($api_response) ? $api_response : null,
        ]);
    }

    public function cancel($id)
    {
        //1. poner la orden en estado anulado
        $posOrder = Posorder::findOrFail($id);
        if ($posOrder->estado === 'anulado') {
            return response()->json([
                'success' => false,
                'message' => 'La orden ya está anulada.',
            ], 400);
        }

        $posOrder->estado = 'anulado';
        $posOrder->save();
        //2. actualizar el stock de los productos vendidos
        $posServices = new PosServices();
        foreach ($posOrder->orderLines as $line) {
            $posServices->updateStockProductoTienda(
                $line->producto_id,
                $posOrder->tienda_id,
                'anulacion',
                $line->quantity
            );
        }
        // 3. retornar los pagos
        $posOrder->payments()->each(function ($payment) {
            $payment->delete();
        });


        return response()->json([
            'success' => true,
            'message' => 'Orden anulada correctamente.',
            'data' => $posOrder,
        ]);
    }
    // postOrderBystore
    public function postOrderPanel($fecha_inicio = null, $fecha_fin = null)
    {

        $fecha_inicio = $fecha_inicio
            ? Carbon::parse($fecha_inicio)->startOfDay()  // Si existe, la parsea
            : now()->startOfDay();                        // Si no existe, usa hoy

        $fecha_fin = $fecha_fin
            ? Carbon::parse($fecha_fin)->endOfDay()       // Si existe, la parsea hasta el final del día
            : now()->endOfDay();

        // Validar que fecha_inicio no sea mayor que fecha_fin
        if ($fecha_inicio->gt($fecha_fin)) {
            throw new \Exception('La fecha de inicio no puede ser mayor que la fecha de fin');
        }

        /* $alltiendas = Tienda::with(['posOrders' => function ($query) use ($fecha_inicio, $fecha_fin) {
            $query->whereBetween('order_date', [$fecha_inicio, $fecha_fin])
                ->with(['payments']) // Cargar los pagos para cada orden
                ->orderBy('order_date', 'desc');
        }])->get(); */

        // 2. CONSULTA OPTIMIZADA CON SELECT ESPECÍFICO
        $alltiendas = Tienda::with(['posOrders' => function ($query) use ($fecha_inicio, $fecha_fin) {

            // 2.1 FILTRAR POR RANGO DE FECHAS
            $query->whereBetween('order_date', [$fecha_inicio, $fecha_fin])

                // 2.2 SUBCONSULTA OPTIMIZADA PARA PAYMENTS
                ->with(['payments' => function ($paymentQuery) {
                    // SOLO SELECCIONA LOS CAMPOS QUE NECESITAS
                    $paymentQuery->select('id', 'pos_order_id', 'payment_method', 'amount');
                    // Esto reduce la cantidad de datos transferidos desde la BD
                }])

                // 2.3 SOLO SELECCIONA LOS CAMPOS QUE NECESITAS DE posOrders
                ->select('id', 'tienda_id', 'serie', 'order_number', 'order_date', 'total_amount')
                // Esto evita traer campos innecesarios como created_at, updated_at, etc.

                // 2.4 ORDENAR POR FECHA DESCENDENTE
                ->orderBy('order_date', 'Asc');
        }])->get();


        return view('modules.ventas.posorder.posorderpanel', compact('alltiendas'));
    }

    public function postOrderLinePanel()
    {
        $alltiendas = Tienda::all();

        return view('modules.ventas.posorder.posorderlinepanel', compact('alltiendas'));
    }
    public function emitirNota($id, Request $request)
    {
       
        
        $posServices = new PosServices();
        
        $tienda_id = Auth::user()->tienda_id; // Obtener el ID de la tienda del usuario autenticado
        // Validar que el usuario tenga una tienda asociada
        if (!$tienda_id) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene una tienda asociada.',
            ], 400);
        }
        $pos_order = Posorder::findOrFail($id);

       
        
        $cpeSerie = $posServices->get_CpeSerie(
            $tienda_id,
            $request->input('codigo_tipo_comprobante'),
            $pos_order->cpe->tipo_comprobante // tipo de comprobante a modificar
        );
        
        // Validar que la serie y correlativo existan
        if (!$cpeSerie) {
            return response()->json([
                'success' => false,
                'message' => 'Serie y correlativo no encontrados.',
            ], 404);
        }
         // buscamos la orden 
       
        $cliente = $pos_order->cliente;
        DB::beginTransaction();
        $nota = PosOrder::create([
                'serie' => $cpeSerie->serie, // Serie del comprobante
                'order_number' => $cpeSerie->correlativo, // Correlativo del comprobante 
                'order_date' => now(), // Fecha y hora actual
                'tipo_comprobante' => $request->input('codigo_tipo_comprobante'), // Tipo de comprobante, por ejemplo 'boleta', 'factura', etc.
                'total_amount' => $pos_order->total_amount,
                'moneda' => $pos_order->moneda, // Moneda, 1 para PEN, 2 para USD
                'tienda_id' => $tienda_id, // ID de la tienda del usuario autenticado
                'user_id' => Auth::user()->id, // ID del usuario autenticado
                'cliente_id' => $cliente->id, // ID del cliente, si se proporciona
                'estado' => 'emitido', // Estado de la orden, puedes ajustarlo según tu lógica
            ]);
         // Procesar los productos vendidos
        $pos_order_lines = $pos_order->orderLines->map(function ($line) {
            return [
                'id' => $line->producto_id,
                'cantidad' => $line->quantity,
                'precio_unitario' => $line->price,
                'subtotal' => $line->subtotal,
            ];
        })->toArray();

        foreach ($pos_order_lines as $line) {
            $nota->orderlines()->create([
                'producto_id' => $line['id'],
                'quantity' => $line['cantidad'],
                'price' => $line['precio_unitario'],
                'subtotal' => $line['subtotal'],
            ]);
            $posServices->updateStockProductoTienda(
                $line['id'],
                $tienda_id,
                'nota_credito', // o 'nota_debito' según el tipo de nota
                $line['cantidad']
            );
        }
        // enviamos la orden al servicio de CPE
        $cpeServices = new CpeServices();
        $cpeResponse = $cpeServices->SendCep($cpeSerie, $cliente, $pos_order, $request->input('tipo_de_nota'),$nota);
        // Aumentar el correlativo del CPE
        $posServices->increase_CpeSerie($cpeSerie);
        DB::commit();
        return response()->json([
            'success' => true,
            'message' => 'Nota emitida correctamente',
            'nota' => $nota,
            'cpe_response' => $cpeResponse,
        ]);
        
    }
}
