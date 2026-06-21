<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class OpenAiService
{
    /**
     * @var string
     */
    protected $apiKey;

    /**
     * @var string
     */
    protected $apiUrl;

    /**
     * Estado temporal para llamadas fluidas (Fluent API)
     */
    protected $model = 'gpt-4o-mini';
    protected $temperature = 0.7;
    protected $maxTokens = 1000;

    /**
     * OpenAiService constructor.
     */
    public function __construct()
    {
        $this->apiKey = config('services.openai.key');
        $this->apiUrl = 'https://api.openai.com/v1/chat/completions';
    }

    /**
     * Configura el modelo a usar para la siguiente consulta.
     *
     * @param string $model
     * @return self
     */
    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    /**
     * Configura la temperatura (creatividad/consistencia) para la siguiente consulta.
     *
     * @param float $temperature
     * @return self
     */
    public function setTemperature(float $temperature): self
    {
        $this->temperature = $temperature;
        return $this;
    }

    /**
     * Configura la cantidad máxima de tokens a generar.
     *
     * @param int $tokens
     * @return self
     */
    public function setMaxTokens(int $tokens): self
    {
        $this->maxTokens = $tokens;
        return $this;
    }

    /**
     * Restablece los valores por defecto del servicio.
     *
     * @return self
     */
    public function reset(): self
    {
        $this->model = 'gpt-4o-mini';
        $this->temperature = 0.7;
        $this->maxTokens = 1000;
        return $this;
    }

    /**
     * Envía una petición de chat a la API de OpenAI.
     *
     * @param array $messages Mensajes estructurados: [['role' => 'user', 'content' => '...']]
     * @param array $options Opciones adicionales para personalizar la llamada a la API
     * @return array La respuesta decodificada de OpenAI
     * @throws Exception
     */
    public function chat(array $messages, array $options = []): array
    {
        if (empty($this->apiKey)) {
            $this->logError('OpenAI API Key not configured in config/services.');
            throw new Exception('OpenAI API Key is not configured.');
        }

        // Combinar los valores del estado fluido con las opciones pasadas
        $payload = array_merge([
            'model'       => $this->model,
            'messages'    => $messages,
            'temperature' => $this->temperature,
            'max_tokens'  => $this->maxTokens,
        ], $options);

        // Si se define un formato de respuesta específico (ej. JSON object)
        if (isset($options['response_format'])) {
            $payload['response_format'] = $options['response_format'];
        }

        $timeout = $options['timeout'] ?? 120;

        try {
            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ])
            ->timeout($timeout)
            ->post($this->apiUrl, $payload);

            if ($response->failed()) {
                $this->logError('OpenAI API request failed.', [
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                    'payload' => $payload
                ]);
                throw new Exception('OpenAI API returned an error: ' . $response->body(), $response->status());
            }

            $data = $response->json();

            // Validación del finish_reason
            $finishReason = $data['choices'][0]['finish_reason'] ?? null;
            if ($finishReason === 'length') {
                $this->logWarning('OpenAI API response truncated due to token limit.', [
                    'usage' => $data['usage'] ?? 'unknown'
                ]);
            }

            // Después de cada llamada, restablecemos el estado para la siguiente consulta
            $this->reset();

            return $data;

        } catch (Exception $e) {
            $this->reset();
            $this->logError('Error calling OpenAI API: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    /**
     * Envía una pregunta directa de texto (Prompt único).
     *
     * @param string $prompt
     * @param string|null $systemPrompt
     * @param array $options
     * @return string
     * @throws Exception
     */
    public function ask(string $prompt, ?string $systemPrompt = null, array $options = []): string
    {
        $messages = [];

        if ($systemPrompt) {
            $messages[] = [
                'role'    => 'system',
                'content' => $systemPrompt
            ];
        }

        $messages[] = [
            'role'    => 'user',
            'content' => $prompt
        ];

        $response = $this->chat($messages, $options);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Consulta usando el modelo potente gpt-4o.
     *
     * @param string $prompt
     * @param string|null $systemPrompt
     * @return string
     */
    public function askGpt4o(string $prompt, ?string $systemPrompt = null): string
    {
        return $this->setModel('gpt-4o')
                    ->setTemperature(0.5)
                    ->ask($prompt, $systemPrompt);
    }

    /**
     * Consulta usando el modelo rápido y económico gpt-4o-mini.
     *
     * @param string $prompt
     * @param string|null $systemPrompt
     * @return string
     */
    public function askGpt4oMini(string $prompt, ?string $systemPrompt = null): string
    {
        return $this->setModel('gpt-4o-mini')
                    ->setTemperature(0.7)
                    ->ask($prompt, $systemPrompt);
    }

    /**
     * Generador especializado de sentencias SQL a partir de un esquema.
     *
     * @param string $naturalLanguagePrompt Lo que quieres que haga la consulta (ej. "Lista de usuarios y compras")
     * @param string $schemaContext Esquema de las tablas involucradas
     * @return string Sentencia SQL resultante
     */
    public function generateSql(string $naturalLanguagePrompt, string $schemaContext): string
    {
        $systemPrompt = $schemaContext . "\n" . "
            Eres un experto en bases de datos MySQL. 
            Debes devolver obligatoriamente un objeto JSON con exactamente cuatro campos:
            1. \"sql\": La consulta SQL MySQL para resolver la petición.
            2. \"title\": El título limpio y corregido del reporte que describe exactamente qué entendiste de la petición del usuario (ej: \"Cantidad de boletas y facturas de ventas03\" o \"Ventas por tienda\").
            3. \"group_by_key\": El nombre exacto de la columna seleccionada en el SELECT de la consulta por la cual se debe agrupar/clasificar el gráfico (el campo que identifica a cada barra en la lista del eje Y, ej: \"tipo_comprobante\" o \"tienda_nombre\" o \"producto_nombre\").
            4. \"chart_type\": El tipo de gráfico a usar. Valor \"horizontal\" (por defecto) o \"vertical\". Usa \"vertical\" solo cuando el usuario pida explícitamente un gráfico de barras verticales o de columnas. En todos los demás casos usa \"horizontal\".

            IMPORTANTE: Devuelve únicamente el objeto JSON válido. No uses bloques de código markdown ni explicaciones externas.

            REGLAS DE GENERACIÓN CRÍTICAS PARA EL SQL DE AHMODAS:
            1. **Prioridad de Alias en Productos y Tiendas**: Al hacer SELECT sobre productos o tiendas, debes priorizar su campo `alias`. Si el alias es NULL o está vacío (''), debes recurrir al campo `nombre`. 
               Usa obligatoriamente esta estructura en MySQL:
               - Para productos: `COALESCE(NULLIF(productos.alias, ''), productos.nombre) AS producto_nombre`
               - Para tiendas: `COALESCE(NULLIF(tiendas.alias, ''), tiendas.nombre) AS tienda_nombre`
            2. **Traducción de Códigos y Estados Numéricos (Obligatorio ELSE por defecto)**: Cuando la consulta involucre columnas con códigos o valores numéricos que representan tipos o estados, debes traducirlos obligatoriamente a sus nombres legibles en español usando `CASE WHEN` en el SQL, en lugar de mostrar los números directamente.
                - **CRÍTICO**: Todo `CASE WHEN` que uses para traducción **DEBE incluir obligatoriamente una cláusula `ELSE`** para que ningún registro devuelva `NULL`. Si el valor no coincide con los casos conocidos, en el `ELSE` debes retornar el valor original o la palabra `'Otros'`.
                - Si una columna puede contener valores NULL o vacíos, utiliza `COALESCE` o `CASE WHEN` para asignar un valor por defecto (como `'Otros'`) para que no queden categorías vacías sin nombre en el gráfico.
                - Para `pos_orders.tipo_comprobante` o `cpe_series.codigo_tipo_comprobante` (Códigos de SUNAT):
                  - '01' -> 'Factura'
                  - '03' -> 'Boleta'
                  - '12' -> 'Ticket'
                  - '07' -> 'Nota de Crédito'
                  - '08' -> 'Nota de Débito'
                  - Ejemplo: `CASE WHEN pos_orders.tipo_comprobante = \'01\' THEN \'Factura\' WHEN pos_orders.tipo_comprobante = \'03\' THEN \'Boleta\' WHEN pos_orders.tipo_comprobante = \'12\' THEN \'Ticket\' ELSE \'Otros\' END`
                - Para `cpes.tipo_comprobante` (Códigos de Nubefact):
                  - '1' o 1 -> 'Factura'
                  - '2' o 2 -> 'Boleta'
                  - '3' o 3 -> 'Nota de Crédito'
                  - '4' o 4 -> 'Nota de Débito'
                  - Ejemplo: `CASE WHEN cpes.tipo_comprobante = \'1\' THEN \'Factura\' WHEN cpes.tipo_comprobante = \'2\' THEN \'Boleta\' ELSE \'Otros\' END`
                - Para `salida_productos.tipo`:
                  - 1 -> 'Salida Manual'
                  - 2 -> 'Salida por Venta'
                - Para `moneda`:
                  - 1 -> 'Soles (S/)'
                  - 2 -> 'Dólares ($)'
                - Para estados de disponibilidad o estado activo (estado):
                  - 1 o true -> 'Activo' o 'Disponible'
                  - 0 o false -> 'Inactivo' o 'No Disponible'
             3. **Búsqueda de Nombres y Texto con LIKE**: Al filtrar o buscar por nombres de productos, nombres de tiendas, alias de productos, nombres de clientes o cualquier columna que contenga texto/nombres, no uses el operador de igualdad (`=`). En su lugar, usa el operador `LIKE` con comodines `%` a ambos lados para admitir variaciones en la escritura.
                - **Búsqueda de Usuarios/Cajeros**: Si el usuario busca o filtra por un cajero o usuario, debes buscar el texto tanto en `users.username` como en `users.name` utilizando la condición `OR`.
                  - Ejemplo: `WHERE (users.username LIKE '%ventas03%' OR users.name LIKE '%ventas03%')`
                  - Ejemplo incorrecto: `WHERE productos.nombre = 'YOU PITILLO'`
                  - Ejemplo correcto: `WHERE COALESCE(NULLIF(productos.alias, ''), productos.nombre) LIKE '%YOU PITILLO%'`
             4. **Estructura de Consultas: Detalle vs. Ranking/Top (CRÍTICO)**:
                - Debes identificar el **tipo de petición** del usuario para elegir la estrategia SQL correcta:
                
                - **TIPO A - Consulta de Detalle (sin GROUP BY)**: Cuando el usuario quiere listar todos los registros de una relación (ej: \"Cantidad de boletas de ventas03\", \"Productos en stock por tienda\", \"Ventas del mes\").
                  - **NO uses GROUP BY ni SUM/COUNT en el SQL**. Devuelve las filas individuales del 'Muchos'.
                  - El frontend agrupa y totaliza automáticamente usando el campo `group_by_key`.
                  - Ejemplo correcto: `SELECT tienda_nombre, producto_nombre, stock FROM producto_tienda JOIN ...` con `\"group_by_key\": \"tienda_nombre\"`.
                
                - **TIPO B - Consulta de Ranking/Top/Total (GROUP BY permitido)**: Cuando el usuario pregunta por el MÁS vendido, TOP N, o quiere un resumen/total por categoría (ej: \"Qué producto se vendió más\", \"Top 5 clientes\", \"Total de ventas por tienda\", \"Cuántas unidades se vendieron por producto\").
                  - **SÍ puedes usar GROUP BY, SUM(), COUNT(), ORDER BY, LIMIT** en estos casos.
                  - La consulta debe incluir: la columna de categoría (nombre del producto/tienda/etc.), la métrica agregada (total_vendido, total_ventas, cantidad, etc.), y columnas de detalle adicionales útiles.
                  - Ejemplo correcto para \"producto más vendido en mayo\":
                    `SELECT COALESCE(NULLIF(productos.alias, ''), productos.nombre) AS producto_nombre, SUM(pos_order_lines.quantity) AS total_vendido FROM pos_orders JOIN pos_order_lines ON pos_orders.id = pos_order_lines.pos_order_id JOIN productos ON pos_order_lines.producto_id = productos.id WHERE pos_orders.order_date >= '2026-05-01' AND pos_orders.order_date < '2026-06-01' GROUP BY productos.id, productos.nombre, productos.alias ORDER BY total_vendido DESC LIMIT 10`
                  - El `group_by_key` debe ser la columna de categoría (ej: `\"producto_nombre\"`).

             5. **JOINs Obligatorios (CRÍTICO - Nunca Omitir)**:
                - **REGLA DE ORO**: Si en el `SELECT` o `WHERE` referencias columnas de una tabla (ej: `productos.alias`, `productos.nombre`, `tiendas.alias`, `users.username`), esa tabla DEBE aparecer en el `FROM` o en un `JOIN`. Nunca asumas que una columna existe en otra tabla.
                - Para obtener el nombre/alias de un **producto** desde `pos_order_lines`, SIEMPRE incluye: `JOIN productos ON pos_order_lines.producto_id = productos.id`
                - Para obtener el nombre/alias de una **tienda** desde `pos_orders`, SIEMPRE incluye: `JOIN tiendas ON pos_orders.tienda_id = tiendas.id`
                - Para obtener datos del **usuario/cajero** desde `pos_orders`, SIEMPRE incluye: `JOIN users ON pos_orders.user_id = users.id`
                - Para precios o subtotales de línea, usa `pos_order_lines.price` y `pos_order_lines.subtotal` directamente (no hagas JOIN a `productos` solo para esto).
                - **Cuando uses GROUP BY con COALESCE/alias calculados**, agrupa por las columnas reales de la tabla, NO por el alias del SELECT:
                  - Correcto: `GROUP BY productos.id, productos.nombre, productos.alias`
                  - Incorrecto: `GROUP BY producto_nombre`
        ";

        // Usamos gpt-4o-mini con temperatura de 0 y forzamos JSON response format
        $response = $this->setModel('gpt-4o-mini')
                         ->setTemperature(0.0)
                         ->setMaxTokens(2000)
                         ->chat([
                             ['role' => 'system', 'content' => $systemPrompt],
                             ['role' => 'user', 'content' => $naturalLanguagePrompt]
                         ], [
                             'response_format' => ['type' => 'json_object']
                         ]);

        return $response['choices'][0]['message']['content'] ?? '';
    }

    /**
     * Genera sentencias SQL basadas específicamente en el esquema guardado de Ahmodas.
     *
     * @param string $naturalLanguagePrompt
     * @return string
     * @throws Exception
     */
    public function generateAhmodasSql(string $naturalLanguagePrompt): string
    {
        $schemaPath = storage_path('app/ahmodas_db_schema.md');

        if (!file_exists($schemaPath)) {
            throw new Exception('El archivo de esquema ahmodas_db_schema.md no existe en storage/app.');
        }

        $schemaContext = file_get_contents($schemaPath);

        return $this->generateSql($naturalLanguagePrompt, $schemaContext);
    }

    /**
     * Escribe un registro de error en el log dedicado de OpenAI.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/openai.log'),
        ])->error($message, $context);
    }

    /**
     * Escribe una advertencia en el log dedicado de OpenAI.
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::build([
            'driver' => 'single',
            'path'   => storage_path('logs/openai.log'),
        ])->warning($message, $context);
    }
}
