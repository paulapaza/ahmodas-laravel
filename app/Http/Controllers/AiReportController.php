<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Exception;

class AiReportController extends Controller
{
    public function index()
    {
        return view('modules.ai-reports.index');
    }

    public function generate(Request $request)
    {
        $request->validate([
            'prompt' => 'required|string|max:1000',
        ]);

        $userPrompt = $request->input('prompt');
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            return response()->json(['error' => 'API Key de Gemini no configurada en .env (GEMINI_API_KEY).'], 500);
        }

        try {
            // PASO 1: Pedir a Gemini que genere el SQL
            $sqlPrompt = $this->getSqlPrompt($userPrompt);
            $sqlResponse = $this->callGemini($sqlPrompt, $apiKey);
            
            $sql = $this->extractSql($sqlResponse);
            
            if (!$sql) {
                return response()->json(['error' => 'La IA no pudo generar una consulta SQL válida.', 'details' => $sqlResponse], 500);
            }

            // Ejecutar SQL (Modo solo lectura)
            // Seguridad básica: evitar DROP, DELETE, UPDATE, INSERT
            if (preg_match('/\b(DROP|DELETE|UPDATE|INSERT|ALTER|TRUNCATE|GRANT|REVOKE)\b/i', $sql)) {
                return response()->json(['error' => 'La consulta SQL generada contiene operaciones no permitidas.'], 400);
            }

            $data = DB::select($sql);

            // PASO 2: Pedir a Gemini que genere el Chart
            $chartPrompt = $this->getChartPrompt($userPrompt, $data);
            $chartResponse = $this->callGemini($chartPrompt, $apiKey);

            $html = $this->extractHtml($chartResponse);

            return response()->json(['html' => $html, 'sql' => $sql, 'data' => $data]);

        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function callGemini($text, $apiKey, $retry = 0)
    {
        // Usaremos gemini-2.0-flash que suele ser muy estable, o lo que pongas en tu .env
        $model = env('GEMINI_MODEL', 'gemini-flash-latest');
        
        $response = Http::timeout(60)->withHeaders([
            'Content-Type' => 'application/json',
            'X-goog-api-key' => $apiKey,
        ])->post("https://generativelanguage.googleapis.com/v1/models/{$model}:generateContent", [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.1,
            ]
        ]);

        if (!$response->successful()) {
            // Si hay un pico de demanda (503), intentamos hasta 3 veces automáticamente
            if ($response->status() == 503 && $retry < 3) {
                sleep(2); // Esperamos 2 segundos
                return $this->callGemini($text, $apiKey, $retry + 1);
            }
            throw new Exception('Error al comunicarse con Gemini: ' . $response->body());
        }

        $json = $response->json();
        return $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
    }

    private function getSqlPrompt($userPrompt)
    {
        return "
Eres un experto en bases de datos MySQL para el sistema ERP/POS Ahmodas.
Tu única tarea es generar una consulta SQL de solo lectura (SELECT) válida y eficiente que responda a la petición del usuario.
Devuelve ÚNICAMENTE el código SQL envuelto en bloques de código markdown: ```sql [consulta] ```. No agregues explicaciones, introducciones ni despedidas.

Estructura de la Base de Datos:
1. Catálogo e Inventario:
   - productos(id, nombre, precio_unitario, precio_minimo, precio_x_mayor, costo_unitario, categoria_id, marca_id, estado)
   - categorias(id, nombre)
   - marcas(id, nombre)
   - tiendas(id, nombre, es_almacen, estado)
   - producto_tienda(producto_id, tienda_id, stock) [Stock de cada producto por tienda]

2. Ventas y Clientes:
   - pos_orders(id, sale_token, order_number, serie, tipo_comprobante, order_date, total_amount, cliente_id, user_id, tienda_id, estado) [Nota: Para formar el correlativo completo o número de comprobante, puedes concatenar la serie y el correlativo, por ejemplo: CONCAT(serie, '-', order_number)]
   - pos_order_lines(id, pos_order_id, producto_id, quantity, price, subtotal)
   - pos_order_payments(id, pos_order_id, payment_method, amount)
   - clientes(id, nombre, numero_documento_identidad)
   - users(id, name, tienda_id)

3. Traslados y Devoluciones:
   - almacen_traslados (id, almacen_id, tienda_id, producto_id, cantidad, fecha)
   - tiendas_traslados (id, tienda_id, almacen_id, producto_id, cantidad, fecha)
   - inter_tiendas_traslados (id, tienda_origen_id, tienda_destino_id, producto_id, cantidad, fecha)
   - pos_devoluciones(id, tienda_id, user_id, tipo_movimiento, monto_devolucion)
   - pos_devolucion_detalles(pos_devolucion_id, producto_id, cantidad, precio_unitario, subtotal)

Reglas de Negocio Críticas:
- Solo considera ventas válidas y completadas usando: `pos_orders.estado = 'completed'`.
- Tipos de Comprobante (`pos_orders.tipo_comprobante`):
  - `'01'`: Factura
  - `'03'`: Boleta
  - `'12'`: Ticket/Nota de venta
- Métodos de Pago (`pos_order_payments`):
  - Una venta puede pagarse de forma mixta (combinando métodos).
  - Los métodos registrados son: `'efectivo'`, `'tarjeta'`, `'yape'`, y `'transferencia'`. El monto pagado en cada método está en `pos_order_payments.amount`.
- Para obtener las ventas totales en dinero, utiliza `SUM(pos_orders.total_amount)` o el campo `total_amount`.
- Si piden 'stock' o 'inventario disponible', consulta en `producto_tienda` sumando el campo `stock` y cruzando con `productos` y `tiendas`.
- Los traslados de mercadería se dividen por su dirección (almacen_traslados, tiendas_traslados, inter_tiendas_traslados).

Consulta del usuario a responder: \"$userPrompt\"
";
    }

    private function getChartPrompt($userPrompt, $data)
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        return "
Eres un experto desarrollador Frontend y analista de datos.
Se te proporcionará la consulta original de un usuario y los resultados en formato JSON obtenidos de la base de datos.
Tu tarea es generar un código HTML completo y autocontenido (con CSS inline y Javascript) que utilice la librería Chart.js (vía CDN: https://cdn.jsdelivr.net/npm/chart.js) para visualizar estos datos de la mejor manera (barras, pastel, líneas, o tabla si los datos no se prestan para graficar).
El resultado debe verse profesional, moderno, con colores agradables (como azul, indigo, cyan) y ser 100% funcional. 
Asegúrate de ponerle un height y width razonable al canvas del gráfico, por ejemplo height de 400px.
NO devuelvas ninguna explicación, SOLO el código HTML envuelto en ```html ... ```.

Consulta original: \"$userPrompt\"
Datos obtenidos de la BD (JSON):
$jsonData
";
    }

    private function extractSql($text)
    {
        if (preg_match('/```sql\s*(.*?)\s*```/is', $text, $matches)) {
            return trim($matches[1]);
        }
        // Fallback: tratar de encontrar algo que empiece con SELECT
        if (preg_match('/(SELECT.*?;)/is', $text, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function extractHtml($text)
    {
        if (preg_match('/```html\s*(.*?)\s*```/is', $text, $matches)) {
            return trim($matches[1]);
        }
        // Fallback: tratar de extraer de <html> a </html> o todo
        return trim($text);
    }
}
