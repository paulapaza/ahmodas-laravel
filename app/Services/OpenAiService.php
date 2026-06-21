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
            Debes devolver obligatoriamente un objeto JSON con exactamente tres campos:
            1. \"sql\": La consulta SQL MySQL para resolver la petición.
            2. \"title\": El título limpio y corregido del reporte que describe exactamente qué entendiste de la petición del usuario (ej: \"Cantidad de boletas y facturas de ventas03\" o \"Ventas por tienda\").
            3. \"group_by_key\": El nombre exacto de la columna seleccionada en el SELECT de la consulta por la cual se debe agrupar/clasificar el gráfico (el campo que identifica a cada barra en la lista del eje Y, ej: \"tipo_comprobante\" o \"tienda_nombre\" o \"producto_nombre\").

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
             4. **Estructura de Relaciones 1-a-N para Gráficos y Detalles (CRÍTICO)**:
                - Cuando interpretes la petición del usuario, identifica las entidades/tablas involucradas y establece siempre una relación de \"Uno a Muchos\" (1 a N), incluso si conceptualmente es de muchos a muchos.
                - El concepto \"Uno\" (ej: el cajero específico, la tienda específica, el cliente específico, el filtro principal) actúa como el filtro principal o agrupador principal.
                - El concepto \"Muchos\" (ej: las boletas/facturas emitidas por ese cajero, los productos y sus stocks en esa tienda, etc.) debe listarse verticalmente como filas/barras en el eje Y (hacia abajo).
                - **NO uses la cláusula GROUP BY ni funciones de agregación como SUM() o COUNT() en la consulta SQL** para totalizar por categoría, ya que el frontend requiere obligatoriamente que la consulta devuelva los registros detallados individuales (las filas individuales del 'Muchos') para poder mostrarlos en una tabla de detalle completa dentro del modal cuando el usuario haga clic en una barra del gráfico.
                - La consulta SQL debe seleccionar:
                  - Las columnas de detalle que son útiles para el usuario (ej: `productos.nombre`, `producto_tienda.stock`, `cpes.serie`, `cpes.numero`, `cpes.created_at`, etc.) para que se listen en el modal de detalle.
                  - La columna de categoría por la cual se agrupará el gráfico en el frontend (ej: `tipo_comprobante`, `tienda_nombre`, `producto_nombre`), la cual debe ser indicada en el campo JSON \"group_by_key\".
                - El cálculo de totales (sumas o conteos) se realiza automáticamente en el frontend sumando el valor de la columna métrica (ej: `stock`, `quantity`, `total`) o contando las filas de cada categoría.
                - **Ejemplo Incorrecto (Con GROUP BY y SUM)**:
                  `SELECT tiendas.nombre AS tienda_nombre, SUM(producto_tienda.stock) AS cantidad FROM producto_tienda JOIN tiendas ON ... GROUP BY tienda_nombre`
                - **Ejemplo Correcto (Registros detallados sin GROUP BY ni SUM)**:
                  `SELECT COALESCE(NULLIF(tiendas.alias, ''), tiendas.nombre) AS tienda_nombre, COALESCE(NULLIF(productos.alias, ''), productos.nombre) AS producto_nombre, producto_tienda.stock FROM producto_tienda JOIN productos ON producto_tienda.producto_id = productos.id JOIN tiendas ON producto_tienda.tienda_id = tiendas.id`
                  Y el campo JSON \"group_by_key\" correspondiente debe ser: `\"tienda_nombre\"`.
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
