<?php

namespace App\Http\Controllers\Pos;

use App\Events\VentaRealizada;
use App\Http\Controllers\Controller;
use App\Http\Requests\PosOrderStore;
use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeBaja;
use App\Models\Inventario\Producto;
use App\Models\Inventario\Tienda;
use App\Models\Pos\PosOrder;
use App\Models\Pos\PosOrderLine;
use App\Services\CpeServices;
use Illuminate\Support\Facades\DB;
use App\Services\PosServices;
use App\Services\SalidaProductoService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//log
use Illuminate\Support\Facades\Log;

class PosOrderController extends Controller
{

    protected $salidaProductoService;
    protected $posService;

    public function __construct(SalidaProductoService $salidaProductoService, PosServices $posService)
    {
        $this->salidaProductoService = $salidaProductoService;
        $this->posService = $posService;
    }
    //index
    public function index()
    {
        // traer orden con relacion con tienda
        $orders = PosOrder::with('tienda', 'user')
            ->whereDate('order_date', now()->format('Y-m-d'))
            ->orderBy('order_date', 'desc')
            ->get();

        return response()->json($orders);
    }
    public function indexByDate($fecha_inicio = null, $fecha_fin = null)
    {
        return PosOrder::with(['tienda', 'user'])

            ->whereDate('order_date', '>=', $fecha_inicio ? Carbon::parse($fecha_inicio)->format('Y-m-d') : now()->format('Y-m-d'))
            ->whereDate('order_date', '<=', $fecha_fin ? Carbon::parse($fecha_fin)->endOfDay()->format('Y-m-d H:i:s') : now()->endOfDay()->format('Y-m-d H:i:s'))
            ->get();
    }
    // show
    public function show($id)
    {
        $PosOrder = PosOrder::with(['tienda', 'user', 'orderLines.producto', 'payments'])
            ->findOrFail($id);
        return view('modules.ventas.posorder.show', compact('PosOrder'));
    }

    public function store(PosOrderStore $request)
    {
        $posServices = new PosServices();
        $saleToken = $request->input('sale_token');

        if (!$saleToken) {
            return response()->json([
                'success' => false,
                'message' => 'Falta sale_token (idempotencia) en la solicitud.'
            ], 422);
        }

        // Si ya existe una orden con este token devolverla (idempotencia)
        $existing = PosOrder::where('sale_token', $saleToken)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'message' => 'Venta ya registrada (idempotente).',
                'pos_order' => $existing,
                'cpe_response' => null,
                'print_type' => Auth::user()->print_type,
            ]);
        }

        $tienda_id = Auth::user()->tienda_id;
        if (!$tienda_id) {
            return response()->json([
                'success' => false,
                'message' => 'El usuario no tiene una tienda asociada.',
            ], 400);
        }

        $codigo_tipo_comprobante = ['01' => '1', '03' => '2', '12' => '12'];

        DB::beginTransaction();
        try {
            $cpeSerie = $posServices->get_CpeSerie(
                $tienda_id,
                $codigo_tipo_comprobante[$request->input('codigo_tipo_comprobante')],
            );

            $cliente = $posServices->procesarCliente(
                $request->input('cliente'),
                $request->input('tipo_venta'),
                $request->input('codigo_tipo_comprobante')
            );

            $pos_order = PosOrder::create([
                'sale_token' => $saleToken,
                'serie' => $cpeSerie->serie,
                'order_number' => $cpeSerie->correlativo,
                'order_date' => now(),
                'tipo_comprobante' => $request->input('codigo_tipo_comprobante'),
                'total_amount' => $request->input('total'),
                'moneda' => (int)$request->input('moneda', 1),
                'tienda_id' => $tienda_id,
                'user_id' => Auth::user()->id,
                'cliente_id' => $cliente->id,
                'estado' => 'completed',
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

            $pos_order_lines = $request->input('productos', []);
            $datosInsert = [];
            $productos_cantidades = [];
            foreach ($pos_order_lines as $line) {
                $datosInsert[] = [
                    'pos_order_id' => $pos_order->id,
                    'producto_id' => $line['id'],
                    'quantity' => $line['cantidad'],
                    'price' => $line['precio_unitario'],
                    'subtotal' => $line['subtotal'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $productos_cantidades[$line['id']] = $line['cantidad'];
            }
            PosOrderLine::insert($datosInsert);

            // se guarda el historial de salida
            $this->salidaProductoService->guardarHistorialSalida($tienda_id, $productos_cantidades, 'venta');

            $posServices->actualizarStockProductos($tienda_id, $productos_cantidades, 'venta');

            // Aumentar correlativo dentro de la transacción
            $posServices->increase_CpeSerie($cpeSerie);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creando venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }

        // Enviar CPE y otros efectos secundarios fuera de la transacción
        $api_response = null;
        try {
            if (in_array($pos_order->tipo_comprobante, ['01', '03'])) {
                $cpeServices = new CpeServices();
                $tipo_venta = $request->input('tipo_venta', 'local');
                $api_response = $cpeServices->SendCep($cpeSerie, $cliente, $pos_order, null, null, $tipo_venta);
            }
        } catch (\Throwable $e) {
            Log::warning('Fallo envío CPE post-commit: ' . $e->getMessage());
        }

        try {
            if ($pos_order->tipo_comprobante == 12 && Auth::user()->print_type == 'red') {
                $printService = new \App\Services\PrintService();
                $printService->imprimirTicket($pos_order);
            }
        } catch (\Throwable $e) {
            Log::warning('Fallo impresión ticket: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'pos_order' => $pos_order,
            'cpe_response' => $api_response,
            'print_type' => Auth::user()->print_type,
        ]);
    }

    /* public function store(PosOrderStore $request)
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
            '12' => '12',
        ];
        $cpeSerie = $posServices->get_CpeSerie(
            $tienda_id,
            $codigo_tipo_comprobante[$request->input('codigo_tipo_comprobante')],
        );

        $cliente = $posServices->procesarCliente($request->input('cliente'), $request->input('tipo_venta'), $request->input('codigo_tipo_comprobante'));


        $pos_order = PosOrder::create([
            'serie' => $cpeSerie->serie, // Serie del comprobante
            'order_number' => $cpeSerie->correlativo, // Correlativo del comprobante 
            'order_date' => now(), // Fecha y hora actual
            'tipo_comprobante' => $request->input('codigo_tipo_comprobante'), // Tipo de comprobante, por ejemplo 'boleta', 'factura', etc.
            'total_amount' => $request->input('total'),
            'moneda' => (int)$request->input('moneda', 1), // Moneda, 1 para PEN, 2 para USD
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

     
        $datosInsert = [];

        foreach ($pos_order_lines as $line) {
            $datosInsert[] = [
                'pos_order_id' => $pos_order->id, // necesario al no usar la relación
                'producto_id' => $line['id'],
                'quantity' => $line['cantidad'],
                'price' => $line['precio_unitario'],
                'subtotal' => $line['subtotal'],
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $productos_cantidades[$line['id']] = $line['cantidad'];
        }

        // Inserta todas las líneas de una sola vez
        PosOrderLine::insert($datosInsert);
        // Actualizar el stock de los productos vendidos
        $posServices->actualizarStockProductos($tienda_id, $productos_cantidades, 'venta');

        $cpeServices = new CpeServices();
        // Enviar el CPE al servicio de facturación si el tiepo de doc es 01  y 03
       
        // Aumentar el correlativo del CPE
        $posServices->increase_CpeSerie($cpeSerie);

        DB::Commit();

         if (in_array($request->input('codigo_tipo_comprobante'), ['01', '03'])) {
            $tipo_venta = $request->input('tipo_venta', 'local'); // Obtener el tipo de venta, por defecto 'local'
            $api_response = $cpeServices->SendCep($cpeSerie, $cliente, $pos_order, null, null, $tipo_venta);
        }


         //try {
           // VentaRealizada::dispatch($pos_order); // no bloqueante
            //event(new VentaRealizada($pos_order)); // bloqueante
        //} catch (\Exception $e) {
            //Log::error('Error al despachar el evento VentaRealizada: ' . $e->getMessage());
           
        //} 
        // Imprimir el recibo
        if ($pos_order->tipo_comprobante == 12 && Auth::user()->print_type == 'red') {
            $printService = new \App\Services\PrintService();
            $printService->imprimirTicket($pos_order);
        }

        return response()->json([
            'success' => true,
            'message' => 'Venta registrada correctamente',
            'pos_order' => $pos_order,
            'cpe_response' => isset($api_response) ? $api_response : null,
            'print_type' => Auth::user()->print_type,
        ]);
    }
 */

    public function cancel($id)
    {
        DB::beginTransaction();

        try {
            //1. poner la orden en estado anulado
            $posOrder = PosOrder::findOrFail($id);
            if ($posOrder->estado === 'anulado') {
                return response()->json([
                    'success' => false,
                    'message' => 'La orden ya está anulada.',
                ], 400);
            }

            //SOLO SE PUEDE ANULLA ORDERN CON $tipo_comprobante 12
            if ($posOrder->tipo_comprobante != 12) {
                return response()->json([
                    'success' => false,
                    'message' => 'las boletas y facturas se anulan con notas de crédito o débito.',
                ], 400);
            }

            $posOrder->estado = 'anulado';
            $posOrder->save();
            //2. actualizar el stock de los productos vendidos
            $posServices = new PosServices();
            foreach ($posOrder->orderLines as $line) {
                // inicia actualizar la tabla de salida_productos
                $producto = Producto::find($line->producto_id);
                if ($producto) {
                    $stock = $producto->stockEnTienda($posOrder->tienda_id);
                    $this->salidaProductoService->create([
                        'producto_id'       => $line->producto_id,
                        'tienda_id'         => $posOrder->tienda_id,
                        'stock_antes'       => $stock,
                        'stock_despues'     => $stock + $line->quantity,
                        'cantidad_reducida' => $line->quantity,
                        'tipo'              => 4, // 4 = ingreso por anulacion
                        'pos_order_id'      => $id,
                        'comentario'        => "Anulación de <a href='/ventas/posorder/{$id}'>venta</a>",
                        'updated_at'        => now(),
                    ]);
                } else {
                    throw new \Exception('Producto no encontrado.');
                }
                // termina actualizar la tabla de salida_productos

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

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Orden anulada correctamente.',
                'data' => $posOrder,
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error anulando venta: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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
            return response()->json([
                'success' => false,
                'message' => 'La fecha de inicio no puede ser mayor que la fecha de fin',
            ], 400);
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
                ->select('id', 'tienda_id', 'serie', 'order_number', 'order_date', 'total_amount', 'estado')
                // Esto evita traer campos innecesarios como created_at, updated_at, etc.

                // 2.4 ORDENAR POR FECHA DESCENDENTE
                ->orderBy('order_date', 'Asc');
        }])->get();


        return view('modules.ventas.posorder.posorderpanel', compact('alltiendas'));
    }

    public function postOrderLinePanel($fecha_inicio = null, $fecha_fin = null)
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

        /* $alltiendas = Tienda::all(); */
        //2. CONSULTA OPTIMIZADA CON SELECT ESPECÍFICO
        $alltiendas = Tienda::with(['posOrders' => function ($query) use ($fecha_inicio, $fecha_fin) {
            // 2.1 FILTRAR POR RANGO DE FECHAS
            $query->whereBetween('order_date', [$fecha_inicio, $fecha_fin])
                // 2.2 SUBCONSULTA OPTIMIZADA PARA orderLines
                ->with(['orderLines' => function ($lineQuery) {
                    // 2.2.1 SOLO SELECCIONA LOS CAMPOS QUE NECESITAS
                    $lineQuery->select('id', 'pos_order_id', 'producto_id', 'quantity', 'price', 'subtotal')
                        ->with(['producto' => function ($productoQuery) {
                            // 2.2.2 SOLO SELECCIONA LOS CAMPOS QUE NECESITAS DE PRODUCTO
                            $productoQuery->select('id', 'nombre', 'alias', 'costo_unitario', 'precio_unitario');
                        }]);
                    // Esto reduce la cantidad de datos transferidos desde la BD
                }]);
            // 2.3 SOLO SELECCIONA LOS CAMPOS QUE NECESITAS DE posOrders
            $query->select('id', 'tienda_id', 'serie', 'order_number', 'order_date', 'total_amount', 'estado')
                // Esto evita traer campos innecesarios como created_at, updated_at, etc.
                // 2.4 ORDENAR POR FECHA DESCENDENTE
                ->orderBy('order_date', 'Asc');
        }])->get();

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
        $pos_order = PosOrder::findOrFail($id);



        $cpeSerie = $posServices->get_CpeSerie(
            $tienda_id,
            $request->input('codigo_tipo_comprobante'),
            $pos_order->cpe->tipo_comprobante // tipo de comprobante a modificar
        );
        // dd($cpeSerie);
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
            'tipo_comprobante' => $request->input('codigo_tipo_comprobante') == 3 ? '07' : '08',
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
        $cpeResponse = $cpeServices->SendCep($cpeSerie, $cliente, $pos_order, $request->input('tipo_de_nota'), $nota);
        // Aumentar el correlativo del CPE
        $posServices->increase_CpeSerie($cpeSerie);
        DB::commit();
        // mostramos la nota de credito 
        return redirect("/ventas/posorder/{$nota->id}")->with([
            'success' => true,
            'message' => 'Nota de crédito emitida correctamente.',
            'cpe_response' => $cpeResponse,
        ]);
    }
    // Consultar el estado del CPE
    public function consultarEstadoCpe($cpe_id)
    {
        //dd("Consultando estado del CPE con ID: $cpe_id");
        $cpeServices = new CpeServices();
        $estado = $cpeServices->consultarEstadoCpe($cpe_id);
        if (!$estado) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el estado del CPE.',
            ], 404);
        }
        //actulizar estado en la base de datos
        $cpe = Cpe::find($cpe_id);
        if ($cpe) {
            $cpe->aceptada_por_sunat = $estado['aceptada_por_sunat'];
            $cpe->sunat_description = $estado['sunat_description'] ?? '';
            $cpe->save();
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró el CPE con ID: ' . $cpe_id,
            ], 404);
        }
        return response()->json([
            'success' => true,
            'estado' => $estado,
        ]);
    }
    // comunicar baj de CPE
    public function comunicarBajaCpe(REQUEST $request)
    {
        //dd("Comunicando baja del CPE con ID: " . $request->input('cpe_id'));
        $cpeServices = new CpeServices();
        $cpe_id = $request->input('cpe_id');
        $motivo = $request->input('motivo');
        $respuesta = $cpeServices->comunicarBaja($cpe_id, $motivo);
        if (!$respuesta) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo comunicar la baja del CPE.',
            ], 404);
        }
        DB::beginTransaction();

        if (isset($respuesta['errors'])) {
            return response()->json([
                'success' => false,
                'message' => $respuesta['errors'] . 'Por favor, verifica si el documento esta registrado en la SUNAT. si no lo estas,
                  espera al menos 24 horas para volver a intentar la baja.',
            ], 400);
        }
        $cpeBaja = CpeBaja::create([
            'cpe_id' => $cpe_id,
            'motivo' => $motivo,
            'numero' => $respuesta['numero'] ?? null,
            'enlace' => $respuesta['enlace'] ?? null,
            'sunat_ticket_numero' => $respuesta['sunat_ticket_numero'] ?? null,
            'aceptada_por_sunat' => $respuesta['aceptada_por_sunat'] ?? false,
            'sunat_description' => $respuesta['sunat_description'] ?? null,
            'sunat_note' => $respuesta['sunat_note'] ?? null,
            'sunat_responsecode' => $respuesta['sunat_responsecode'] ?? '0',
            'sunat_soap_error' => $respuesta['sunat_soap_error'] ?? null,
            'xml_zip_base64' => $respuesta['xml_zip_base64'] ?? null,
            'pdf_zip_base64' => $respuesta['pdf_zip_base64'] ?? null,
            'cdr_zip_base64' => $respuesta['cdr_zip_base64'] ?? null,
            'enlace_del_pdf' => $respuesta['enlace_del_pdf'] ?? null,
            'enlace_del_xml' => $respuesta['enlace_del_xml'] ?? null,
            'enlace_del_cdr' => $respuesta['enlace_del_cdr'],
        ])->save();

        //anular la orden
        $posOrder = PosOrder::findOrFail($cpe_id);
        if ($posOrder->estado !== 'anulado') {
            $posOrder->estado = 'anulado';
            $posOrder->save();
            // actualizar el stock de los productos vendidos
            $posServices = new PosServices();
            foreach ($posOrder->orderLines as $line) {
                $posServices->updateStockProductoTienda(
                    $line->producto_id,
                    $posOrder->tienda_id,
                    'anulacion',
                    $line->quantity
                );
            }
        }
        DB::commit();
        return response()->json([
            'success' => true,
            'data' => $cpeBaja,
            'respuesta' => $respuesta,
        ]);
    }

    // consultar estado de la baja
    public function consultarComunicacionBaja($cpe_id)
    {
        $cpeServices = new CpeServices();
        $respuesta = $cpeServices->consultarEstadoBaja($cpe_id);
        if (!$respuesta) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo obtener el estado de la baja del CPE.',
            ], 404);
        }
        // crear o actualizar la comunicacion de baja
        $cpeBaja = CpeBaja::updateOrCreate(
            ['cpe_id' => $cpe_id],
            [
                'cpe_id' => $cpe_id,
                'numero' => $respuesta['numero'] ?? null,
                'enlace' => $respuesta['enlace'] ?? null,
                'sunat_ticket_numero' => $respuesta['sunat_ticket_numero'] ?? null,
                'aceptada_por_sunat' => $respuesta['aceptada_por_sunat'] ?? false,
                'sunat_description' => $respuesta['sunat_description'] ?? null,
                'sunat_note' => $respuesta['sunat_note'] ?? null,
                'sunat_responsecode' => $respuesta['sunat_responsecode'] ?? '0',
                'sunat_soap_error' => $respuesta['sunat_soap_error'] ?? null,
                'xml_zip_base64' => $respuesta['xml_zip_base64'] ?? null,
                'pdf_zip_base64' => $respuesta['pdf_zip_base64'] ?? null,
                'cdr_zip_base64' => $respuesta['cdr_zip_base64'] ?? null,
                'enlace_del_pdf' => $respuesta['enlace_del_pdf'] ?? null,
                'enlace_del_xml' => $respuesta['enlace_del_xml'] ?? null,
                'enlace_del_cdr' => $respuesta['enlace_del_cdr'] ?? null,
            ]
        );
        return response()->json([
            'success' => true,
            'estado' => $respuesta,
            'cpe_baja' => $cpeBaja,
        ]);
    }

    public function mostrarRecibo($id)
    {
        $pos_order = PosOrder::with('tienda')->findOrFail($id);

        if ($pos_order->tipo_comprobante != 12) {
            abort(404); // O simplemente return;
        }

        $plantilla = view('modules.print.recibo_print', compact('pos_order'))->render();

        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => [70, 300],
            'margin_left' => 2,
            'margin_right' => 3,
            'margin_top' => 0,
            'margin_bottom' => 0,
            'margin_header' => 0,
            'margin_footer' => 0,
        ]);

        $mpdf->WriteHTML($plantilla);
        return response($mpdf->Output('', 'S'), 200)->header('Content-Type', 'application/pdf');
    }

    public function generarExcel(Request $request)
    {
        // 1️⃣ Parámetros de fechas
        $fechaInicio = $request->input('fechaInicio') ?? '2000-01-01';
        $fechaFin = $request->input('fechaFin') ?? now()->format('Y-m-d');

        // 2️⃣ Obtener ventas con detalles
        $ventas = DB::table('pos_orders as po')
            ->select(
                'po.id as venta_id',
                'po.serie',
                'po.order_number',
                'po.order_date',
                'po.tipo_comprobante',
                'po.total_amount',
                'po.estado as estado_venta',
                'u.name as user_name',
                't.nombre as tienda_nombre',
                'p.alias as producto_nombre',
                'p.codigo_barras', // ✏️ NUEVO
                'pol.quantity',
                'pol.price',
                'pol.subtotal'
            )
            ->join('pos_order_lines as pol', 'pol.pos_order_id', '=', 'po.id')
            ->join('clientes as c', 'c.id', '=', 'po.cliente_id')
            ->join('tiendas as t', 't.id', '=', 'po.tienda_id')
            ->join('users as u', 'u.id', '=', 'po.user_id')
            ->join('productos as p', 'p.id', '=', 'pol.producto_id')
            ->whereBetween('po.order_date', [
                Carbon::parse($fechaInicio)->startOfDay(),
                Carbon::parse($fechaFin)->endOfDay()
            ])
            ->orderByDesc('po.order_date')
            ->orderByDesc('po.id')
            ->get();

        // 3️⃣ Agrupar por venta
        $ventasAgrupadas = $ventas->groupBy('venta_id');

        // 4️⃣ Crear Excel
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Ventas');

        // ✏️ CABECERA ACTUALIZADA
        $cabecera = [
            'ID Venta',
            'Serie',
            'N° Doc',
            'Fecha',
            'Tipo Comprobante',
            'Total',
            'Estado',
            'Usuario',
            'Tienda',
            'Código Barras',
            'Cantidad',
            'Producto',
            'Precio Unitario',
            'Subtotal'
        ];

        $alfabeto = range('A', 'N');
        foreach ($cabecera as $i => $titulo) {
            $sheet->setCellValue($alfabeto[$i] . '1', $titulo);
        }

        // Auto ajuste de ancho de columnas
        foreach ($alfabeto as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 5️⃣ Rellenar datos
        $row = 2;
        foreach ($ventasAgrupadas as $ventaId => $items) {
            $primera = true;

            foreach ($items as $item) {
                if ($primera) {
                    // Datos de la venta (solo en la primera fila)
                    $sheet->setCellValue('A' . $row, $item->venta_id);
                    $sheet->setCellValue('B' . $row, $item->serie);
                    $sheet->setCellValue('C' . $row, $item->order_number);
                    $sheet->setCellValue('D' . $row, $item->order_date);
                    $sheet->setCellValue('E' . $row, $this->posService->getNombreComprobante($item->tipo_comprobante));
                    $sheet->setCellValue('F' . $row, $item->total_amount);
                    $sheet->setCellValue('G' . $row, $item->estado_venta);
                    $sheet->setCellValue('H' . $row, $item->user_name);
                    $sheet->setCellValue('I' . $row, $item->tienda_nombre);
                }

                // ✏️ DETALLE EN COLUMNAS SEPARADAS
                $sheet->setCellValue('J' . $row, $item->codigo_barras);
                $sheet->setCellValue('K' . $row, $item->quantity);
                $sheet->setCellValue('L' . $row, $item->producto_nombre);
                $sheet->setCellValue('M' . $row, number_format($item->price, 2));
                $sheet->setCellValue('N' . $row, number_format($item->subtotal, 2));

                // Alinear verticalmente
                $sheet->getStyle('J' . $row . ':N' . $row)
                    ->getAlignment()
                    ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                
                 // NUEVO: Centrar horizontalmente las columnas J–N
                $sheet->getStyle('J' . $row . ':N' . $row)
                ->getAlignment()
                ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $row++;
                $primera = false;
            }
        }

        // 🔹 Alinear todas las columnas a la izquierda y arriba
        $sheet->getStyle('A1:I' . ($row - 1))
            ->getAlignment()
            ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER)
            ->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);

        // 🔹 Dar formato a la cabecera (Navy + blanco)
        $sheet->getStyle('A1:N1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F5B'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ],
        ]);

        // 6️⃣ Descargar Excel
        $filename = 'ventas_' . now()->format('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
