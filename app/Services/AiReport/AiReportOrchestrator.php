<?php

namespace App\Services\AiReport;

use Illuminate\Support\Facades\DB;
use Exception;

class AiReportOrchestrator
{
    protected OpenAiClient $openAiClient;
    protected PromptBuilder $promptBuilder;
    protected HtmlRenderer $htmlRenderer;

    public function __construct(
        OpenAiClient $openAiClient,
        PromptBuilder $promptBuilder,
        HtmlRenderer $htmlRenderer
    ) {
        $this->openAiClient = $openAiClient;
        $this->promptBuilder = $promptBuilder;
        $this->htmlRenderer = $htmlRenderer;
    }

    /**
     * Orquesta el flujo de 2 pasos para generar un reporte con IA.
     * 
     * @param string $userPrompt
     * @return array
     * @throws Exception
     */
    public function generateReport(string $userPrompt): array
    {
        // 1. Obtener el esquema
        $schemaPath = storage_path('app/ahmodas_db_schema.md');
        if (!file_exists($schemaPath)) {
            throw new Exception('El archivo de esquema ahmodas_db_schema.md no existe en storage/app.');
        }
        $schemaContext = file_get_contents($schemaPath);

        // PASO 1: Pedir a OpenAI que genere el SQL
        $sqlMessages = $this->promptBuilder->buildSqlPrompt($userPrompt, $schemaContext);
        $sqlResponse = $this->openAiClient->chat('gpt-4o-mini', $sqlMessages, 0.0, [
            'response_format' => ['type' => 'json_object'],
            'max_tokens' => 2000
        ]);

        $sqlRaw = $sqlResponse['choices'][0]['message']['content'] ?? '';
        $responseObj = json_decode($sqlRaw, true);

        if (json_last_error() === JSON_ERROR_NONE && isset($responseObj['sql'])) {
            $sql = $responseObj['sql'];
            $interpretedTitle = $responseObj['title'] ?? $userPrompt;
            $displayType = $responseObj['display_type'] ?? 'chart';
            $chartType = $responseObj['chart_type'] ?? 'horizontal';
        } else {
            // Fallback si la IA no devolvió JSON estricto
            $sql = $this->extractSql($sqlRaw);
            $interpretedTitle = $userPrompt;
            $displayType = 'chart';
            $chartType = 'horizontal';
        }

        if (empty($sql)) {
            throw new Exception('La IA no pudo generar una consulta SQL válida. Respuesta cruda: ' . $sqlRaw);
        }

        // Seguridad básica
        if (preg_match('/\b(DROP|DELETE|UPDATE|INSERT|ALTER|TRUNCATE|GRANT|REVOKE)\b/i', $sql)) {
            throw new Exception('La consulta SQL generada contiene operaciones no permitidas.');
        }

        // Correcciones menores de sintaxis
        $sql = $this->fixSqlJoins($sql);
        $sql = $this->fixSqlGroupByCompatibility($sql);

        // EJECUCIÓN LOCAL
        $data = DB::select($sql);

        if (empty($data)) {
            return [
                'html' => '<div style="padding: 20px; text-align: center; color: #666; font-family: sans-serif;">No se encontraron registros para mostrar con los criterios indicados.</div>',
                'sql' => $sql,
                'data' => [],
                'title' => $interpretedTitle
            ];
        }

        // Refinar displayType según contexto local
        if ($displayType === 'chart') {
            if (preg_match('/\b(es una tienda|es un almacén|es tienda|es almacén|existe\b|qué es\b|quién es\b|cuál es el ruc|cuál es el correo)\b/i', $userPrompt)) {
                $displayType = 'text';
            } else if (preg_match('/\b(listado de|lista de|listar|nombres de tiendas|tiendas existentes|categorías existentes|marcas existentes|catalogo de|catálogo de)\b/i', $userPrompt)) {
                $displayType = 'list';
            }
        }

        // PASO 2: Solicitar a la IA que interprete los datos (con límite de muestras)
        $nlMessages = $this->promptBuilder->buildNaturalLanguagePrompt($userPrompt, $sql, $data, $displayType);
        $nlResponse = $this->openAiClient->chat('gpt-4o-mini', $nlMessages, 0.2, [
            'max_tokens' => 1500
        ]);

        $aiAnswerHtml = trim($nlResponse['choices'][0]['message']['content'] ?? '');
        $aiAnswerHtml = preg_replace('/^```html\s*|\s*```$/i', '', $aiAnswerHtml);

        // PASO 3: Renderizar la interfaz
        if ($displayType === 'text' || $displayType === 'list') {
            $html = '<div class="ai-text-response p-3">' . $aiAnswerHtml . '</div>';
        } else {
            // Extraer groupByKey de forma dinámica (primer columna string)
            $sampleRow = (array) $data[0];
            $groupByKey = null;
            foreach ($sampleRow as $key => $val) {
                if (!is_numeric($val) && $key !== 'id') {
                    $groupByKey = $key;
                    break;
                }
            }

            $chartHtml = $this->htmlRenderer->renderHtmlTemplate($interpretedTitle, $data, $groupByKey, $chartType);
            $html = '<div class="ai-chart-analysis mb-3 p-3 bg-light rounded border"><strong>Análisis IA:</strong><br>' . $aiAnswerHtml . '</div>' . $chartHtml;
        }

        return [
            'html' => $html,
            'sql' => $sql,
            'data' => $data,
            'title' => $interpretedTitle
        ];
    }

    /**
     * Extrae SQL de una respuesta markdown.
     */
    private function extractSql($text): string
    {
        if (preg_match('/```sql\s*(.*?)\s*```/is', $text, $matches)) {
            return trim($matches[1]);
        }
        return trim($text);
    }

    /**
     * Corrige posibles olvidos en JOINs de la IA.
     */
    private function fixSqlJoins(string $sql): string
    {
        if (preg_match('/\busers\b/i', $sql) && preg_match('/\bpos_orders\b/i', $sql) && !preg_match('/\bJOIN\b/i', $sql)) {
            $sql = preg_replace('/\bFROM pos_orders\b/i', 'FROM pos_orders JOIN users ON pos_orders.user_id = users.id', $sql);
        }
        if (preg_match('/\bproductos\b/i', $sql) && preg_match('/\bsalida_productos\b/i', $sql) && !preg_match('/\bJOIN\b/i', $sql)) {
            $sql = preg_replace('/\bFROM salida_productos\b/i', 'FROM salida_productos JOIN productos ON salida_productos.producto_id = productos.id', $sql);
        }
        return $sql;
    }

    /**
     * Asegura compatibilidad con only_full_group_by en algunas sintaxis heredadas.
     */
    private function fixSqlGroupByCompatibility(string $sql): string
    {
        $sql = preg_replace('/ORDER BY\s+pos_orders\.order_date\s+(ASC|DESC)/i', 'ORDER BY MIN(pos_orders.order_date) $1', $sql);
        $sql = preg_replace('/ORDER BY\s+order_date\s+(ASC|DESC)/i', 'ORDER BY MIN(order_date) $1', $sql);
        return $sql;
    }
}
