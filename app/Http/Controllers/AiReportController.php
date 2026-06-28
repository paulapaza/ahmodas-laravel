<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\OpenAiService;
use App\Models\AiReportLog;
use Exception;

class AiReportController extends Controller
{
    /**
     * @var OpenAiService
     */
    protected $openAi;

    /**
     * Create a new controller instance.
     *
     * @param OpenAiService $openAi
     */
    public function __construct(OpenAiService $openAi)
    {
        $this->openAi = $openAi;
    }

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
        $startTime = microtime(true);
        $userId = $request->user()?->id;

        try {
            // PASO 1: Pedir a OpenAI que genere el SQL basado en el esquema de Ahmodas
            $sqlRaw = $this->openAi->generateAhmodasSql($userPrompt);
            
            $responseObj = json_decode($sqlRaw, true);
            if (json_last_error() === JSON_ERROR_NONE && isset($responseObj['sql'])) {
                $sql = $responseObj['sql'];
                $interpretedTitle = $responseObj['title'] ?? $userPrompt;
                $groupByKey = $responseObj['group_by_key'] ?? null;
                $chartType = $responseObj['chart_type'] ?? 'horizontal';
            } else {
                $sql = $this->extractSql($sqlRaw);
                $interpretedTitle = $userPrompt;
                $groupByKey = null;
                $chartType = 'horizontal';
            }
            
            if (!$sql) {
                AiReportLog::create([
                    'user_id' => $userId,
                    'prompt' => $userPrompt,
                    'generated_sql' => $sqlRaw,
                    'is_successful' => false,
                    'error_message' => 'La IA no pudo generar una consulta SQL válida.',
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000)
                ]);
                return response()->json(['error' => 'La IA no pudo generar una consulta SQL válida.', 'details' => $sqlRaw], 500);
            }

            // Ejecutar SQL (Modo solo lectura)
            // Seguridad básica: evitar DROP, DELETE, UPDATE, INSERT
            if (preg_match('/\b(DROP|DELETE|UPDATE|INSERT|ALTER|TRUNCATE|GRANT|REVOKE)\b/i', $sql)) {
                AiReportLog::create([
                    'user_id' => $userId,
                    'prompt' => $userPrompt,
                    'generated_sql' => $sql,
                    'is_successful' => false,
                    'error_message' => 'La consulta SQL generada contiene operaciones no permitidas.',
                    'execution_time_ms' => round((microtime(true) - $startTime) * 1000)
                ]);
                return response()->json(['error' => 'La consulta SQL generada contiene operaciones no permitidas.'], 400);
            }

            // Corrección automática de JOINs que la IA suele olvidar
            $sql = $this->fixSqlJoins($sql);

            $data = DB::select($sql);

            // Si no hay datos, retornamos inmediatamente
            if (empty($data)) {
                return response()->json([
                    'html' => '<div style="padding: 20px; text-align: center; color: #666; font-family: sans-serif;">No se encontraron registros para mostrar con los criterios indicados.</div>',
                    'sql' => $sql,
                    'data' => [],
                    'title' => $interpretedTitle
                ]);
            }

            // PASO 2: Renderizar el gráfico de forma instantánea usando una plantilla HTML/CSS nativa optimizada
            $html = $this->renderHtmlTemplate($interpretedTitle, $data, $groupByKey, $chartType);

            AiReportLog::create([
                'user_id' => $userId,
                'prompt' => $userPrompt,
                'generated_sql' => $sql,
                'is_successful' => true,
                'error_message' => null,
                'execution_time_ms' => round((microtime(true) - $startTime) * 1000)
            ]);

            return response()->json([
                'html' => $html,
                'sql' => $sql,
                'data' => $data,
                'title' => $interpretedTitle
            ]);

        } catch (Exception $e) {
            AiReportLog::create([
                'user_id' => $userId ?? null,
                'prompt' => $userPrompt ?? '',
                'generated_sql' => $sql ?? null,
                'is_successful' => false,
                'error_message' => $e->getMessage(),
                'execution_time_ms' => isset($startTime) ? round((microtime(true) - $startTime) * 1000) : null
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Detecta y corrige automáticamente JOINs faltantes en el SQL generado por la IA.
     * La IA frecuentemente referencia columnas de tablas (ej: productos.alias) sin incluir
     * el JOIN correspondiente. Este método lo inserta en el lugar correcto.
     *
     * @param string $sql
     * @return string
     */
    private function fixSqlJoins(string $sql): string
    {
        // Mapa: [tabla => [columna_clave_de_deteccion, join_a_insertar, tabla_origen_del_fk]]
        // La lógica: si el SQL menciona `tabla.columna` pero NO tiene `JOIN tabla`, lo añadimos.
        $joinRules = [
            'productos' => [
                'detect'     => '/\bproductos\.(alias|nombre|id|precio|codigo_barras)\b/i',
                'from_table' => 'pos_order_lines',
                'join'       => 'JOIN productos ON pos_order_lines.producto_id = productos.id',
                'alt_from'   => [
                    'producto_tienda' => 'JOIN productos ON producto_tienda.producto_id = productos.id',
                    'pos_devolucion_detalles' => 'JOIN productos ON pos_devolucion_detalles.producto_id = productos.id',
                    'almacen_traslados' => 'JOIN productos ON almacen_traslados.producto_id = productos.id',
                    'inter_tiendas_traslados' => 'JOIN productos ON inter_tiendas_traslados.producto_id = productos.id',
                    'tiendas_traslados' => 'JOIN productos ON tiendas_traslados.producto_id = productos.id',
                ],
            ],
            'tiendas' => [
                'detect'     => '/\btiendas\.(alias|nombre|id)\b/i',
                'from_table' => 'pos_orders',
                'join'       => 'JOIN tiendas ON pos_orders.tienda_id = tiendas.id',
                'alt_from'   => [
                    'producto_tienda' => 'JOIN tiendas ON producto_tienda.tienda_id = tiendas.id',
                    'almacen_traslados' => 'JOIN tiendas ON almacen_traslados.tienda_id = tiendas.id',
                    'inter_tiendas_traslados' => 'JOIN tiendas ON inter_tiendas_traslados.tienda_destino_id = tiendas.id',
                    'tiendas_traslados' => 'JOIN tiendas ON tiendas_traslados.tienda_id = tiendas.id',
                    'cpe_series' => 'JOIN tiendas ON cpe_series.tienda_id = tiendas.id',
                ],
            ],
            'users' => [
                'detect'     => '/\busers\.(username|name|id|email)\b/i',
                'from_table' => 'pos_orders',
                'join'       => 'JOIN users ON pos_orders.user_id = users.id',
                'alt_from'   => [
                    'pos_devoluciones' => 'JOIN users ON pos_devoluciones.user_id = users.id',
                    'almacen_traslados' => 'JOIN users ON almacen_traslados.created_by = users.id',
                ],
            ],
        ];

        foreach ($joinRules as $table => $rule) {
            // ¿El SQL menciona columnas de esta tabla?
            if (!preg_match($rule['detect'], $sql)) {
                continue;
            }

            // ¿Ya tiene el JOIN a esta tabla?
            if (preg_match('/\bJOIN\s+' . preg_quote($table, '/') . '\b/i', $sql)) {
                continue;
            }

            // Determinar qué JOIN usar según la tabla FROM principal del SQL
            $joinToAdd = $rule['join']; // JOIN por defecto
            foreach ($rule['alt_from'] as $fromTable => $altJoin) {
                // Si la tabla alternativa está en el FROM o en algún JOIN, usar ese JOIN
                if (preg_match('/\bFROM\s+' . preg_quote($fromTable, '/') . '\b/i', $sql) ||
                    preg_match('/\bJOIN\s+' . preg_quote($fromTable, '/') . '\b/i', $sql)) {
                    $joinToAdd = $altJoin;
                    break;
                }
            }

            // Insertar el JOIN: antes del WHERE, GROUP BY, ORDER BY, HAVING o LIMIT
            // o al final de la sección de JOINs (antes de la primera cláusula post-JOIN)
            $sql = preg_replace(
                '/\b(WHERE|GROUP\s+BY|ORDER\s+BY|HAVING|LIMIT)\b/i',
                $joinToAdd . ' $1',
                $sql,
                1
            );
        }

        return $sql;
    }

    /**
     * Renderiza una plantilla HTML premium con un gráfico de barras horizontales nativo.
     * 
     * @param string $userPrompt
     * @param array $data
     * @param string|null $groupByKey
     * @return string
     */
    private function renderHtmlTemplate($userPrompt, $data, $groupByKey = null, $chartType = 'horizontal')
    {
        $jsonData = json_encode($data, JSON_UNESCAPED_UNICODE);
        $title = mb_convert_case($userPrompt, MB_CASE_TITLE, "UTF-8");
        $title = str_replace(['"', "'"], '', $title);
        $chartType = in_array($chartType, ['horizontal', 'vertical']) ? $chartType : 'horizontal';

        return "
<div class=\"ai-report-container\">
    <style>
        .ai-report-container {
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --primary-grad: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            --negative-grad: linear-gradient(135deg, #f87171 0%, #ef4444 100%);
            --border-color: #e5e7eb;
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);

            font-family: 'Plus Jakarta Sans', sans-serif;
            width: 100%;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow-md);
            border: 1px solid var(--border-color);
            padding: 24px;
            box-sizing: border-box;
        }

        .ai-report-container h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 8px;
            color: var(--text-main);
        }

        .ai-report-container .subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .ai-report-container .chartWrapper {
            height: auto;
            max-height: 500px;
            overflow-y: auto;
            width: 100%;
            position: relative;
            padding-right: 8px;
            box-sizing: border-box;
        }

        /* Custom scrollbar */
        .ai-report-container .chartWrapper::-webkit-scrollbar {
            width: 6px;
        }
        .ai-report-container .chartWrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .ai-report-container .chartWrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .ai-report-container .chartWrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .ai-report-container .chart-row {
            display: flex;
            align-items: center;
            padding: 10px 8px;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
            border-radius: 8px;
            transition: background-color 0.2s;
        }

        .ai-report-container .chart-row:hover {
            background-color: #f1f5f9;
        }

        .ai-report-container .chart-label {
            width: 250px;
            min-width: 250px;
            font-size: 0.875rem;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-right: 16px;
            box-sizing: border-box;
            color: #334155;
        }

        .ai-report-container .chart-bar-wrapper {
            flex-grow: 1;
            display: flex;
            align-items: center;
            position: relative;
            margin-left: 10px;
        }

        .ai-report-container .chart-bar {
            height: 24px;
            border-radius: 6px;
            background: var(--primary-grad);
            min-width: 4px;
            transition: width 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            cursor: pointer;
        }

        .ai-report-container .chart-bar.negative {
            background: var(--negative-grad);
        }

        .ai-report-container .chart-row:hover .chart-bar {
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.2);
            transform: scaleY(1.03);
        }

        .ai-report-container .chart-row:hover .chart-bar.negative {
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2);
        }

        .ai-report-container .chart-value {
            font-size: 0.875rem;
            font-weight: 600;
            margin-left: 12px;
            color: var(--text-main);
            min-width: 50px;
            text-align: right;
            white-space: nowrap;
        }

        /* Bootstrap Tooltip overrides */
        .tooltip-inner.ai-report-bs-tooltip {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            padding: 0 !important;
            border-radius: 14px;
            font-size: 0.8125rem;
            line-height: 1.5;
            box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(99, 102, 241, 0.3);
            max-width: 320px !important;
            min-width: 180px;
            text-align: left;
        }
        .ai-report-container .bs-tooltip-top .arrow::before {
            border-top-color: #1e293b;
        }
        .ai-report-container .bs-tooltip-right .arrow::before {
            border-right-color: #0f172a;
        }

        .ai-report-container .tooltip-header {
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            padding: 10px 14px 8px;
            border-radius: 14px 14px 0 0;
        }

        .ai-report-container .tooltip-title {
            font-weight: 700;
            font-size: 0.875rem;
            color: #ffffff;
            margin: 0 0 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 260px;
        }

        .ai-report-container .tooltip-total-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 2px 10px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #fff;
        }

        .ai-report-container .tooltip-body {
            padding: 10px 14px 12px;
        }

        .ai-report-container .tooltip-section-label {
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748b;
            margin: 8px 0 4px;
        }

        .ai-report-container .tooltip-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 16px;
            padding: 3px 0;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .ai-report-container .tooltip-row:last-child {
            border-bottom: none;
        }

        .ai-report-container .tooltip-row-key {
            color: #94a3b8;
            font-size: 0.78rem;
            flex-shrink: 0;
        }

        .ai-report-container .tooltip-row-val {
            font-weight: 600;
            color: #f1f5f9;
            font-size: 0.8rem;
            text-align: right;
            word-break: break-word;
        }

        .ai-report-container .tooltip-more {
            font-size: 0.72rem;
            color: #6366f1;
            text-align: center;
            padding-top: 6px;
            font-style: italic;
        }


        /* CSS Modal Removido (usando Bootstrap) */
        /* === BARRAS VERTICALES === */
        .ai-report-container .chartWrapper.vertical {
            height: 380px;
            max-height: 380px;
            overflow-x: auto;
            overflow-y: hidden;
            display: flex;
            align-items: flex-end;
            padding-bottom: 0;
            padding-right: 0;
            gap: 0;
        }

        .ai-report-container .chart-col {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            min-width: 60px;
            flex: 1;
            padding: 0 6px;
            transition: filter 0.2s;
        }

        .ai-report-container .chart-col:hover {
            filter: brightness(1.08);
        }

        .ai-report-container .chart-col-value {
            font-size: 0.78rem;
            font-weight: 700;
            color: #334155;
            margin-bottom: 4px;
            white-space: nowrap;
        }

        .ai-report-container .chart-col-bar {
            width: 100%;
            border-radius: 6px 6px 0 0;
            background: linear-gradient(180deg, #6366f1 0%, #06b6d4 100%);
            transition: height 0.6s cubic-bezier(0.16, 1, 0.3, 1);
            min-height: 4px;
            cursor: pointer;
        }

        .ai-report-container .chart-col-bar.negative {
            background: linear-gradient(180deg, #f87171 0%, #ef4444 100%);
        }

        .ai-report-container .chart-col-label {
            font-size: 0.72rem;
            font-weight: 500;
            color: #64748b;
            text-align: center;
            padding: 6px 2px 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 80px;
            border-top: 2px solid #e2e8f0;
            width: 100%;
        }

        /* Forzar Tooltip Opaco (Bootstrap override) */
        .ai-report-container .tooltip.show {
            opacity: 1 !important;
        }
    </style>

    <h1>{$title}</h1>
    <div class=\"subtitle\">Reporte visual de datos en tiempo real</div>

    <div class=\"chartWrapper\" id=\"chartContainer\"></div>

    <!-- Modal Bootstrap 4 para detalle de fila -->
    <div class=\"modal fade\" id=\"detailModal\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">
        <div class=\"modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable\" role=\"document\">
            <div class=\"modal-content border-0 shadow-lg\" style=\"border-radius: 16px;\">
                <div class=\"modal-header border-bottom-0\">
                    <h5 class=\"modal-title font-weight-bold\" id=\"modalTitle\" style=\"color: #0f172a;\">Detalle</h5>
                    <button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-label=\"Close\">
                        <span aria-hidden=\"true\">&times;</span>
                    </button>
                </div>
                <div class=\"modal-body p-4\" id=\"modalBody\"></div>
            </div>
        </div>
    </div>


    <script>
        (function() {
            const data = {$jsonData};
            const groupByKeyFromAi = '{$groupByKey}';
            const chartType = '{$chartType}';

        if (data && data.length > 0) {
            // 1. Detectar claves dinámicamente
            const sampleItem = data[0] || {};
            let metricKey = '';
            let mainEntityKey = '';
            let breakdownKey = '';
            let isCountMetric = false;

            // Encontrar claves numéricas
            const numKeys = Object.keys(sampleItem).filter(k => typeof sampleItem[k] === 'number' || (!isNaN(parseFloat(sampleItem[k])) && isFinite(sampleItem[k])));
            
            // Buscar una métrica real para sumar (stock, total, amount, cantidad, etc.)
            let realMetricKey = numKeys.find(k => {
                const name = k.toLowerCase();
                return name.includes('stock') || name.includes('total') || name.includes('amount') || name.includes('cantidad') || name.includes('monto') || name.includes('precio') || name.includes('subtotal') || name.includes('quantity') || name.includes('price') || name.includes('costo') || name.includes('cost');
            });

            if (realMetricKey) {
                metricKey = realMetricKey;
                isCountMetric = false;
            } else {
                // Si las únicas numéricas son identificadores o códigos, contamos la cantidad de registros
                isCountMetric = true;
                metricKey = 'cantidad_registros';
            }

            const stringKeys = Object.keys(sampleItem).filter(k => typeof sampleItem[k] === 'string' && k !== metricKey);
            
            // Usar el group_by_key enviado por OpenAI si existe en las columnas devueltas
            if (groupByKeyFromAi && sampleItem.hasOwnProperty(groupByKeyFromAi)) {
                mainEntityKey = groupByKeyFromAi;
            } else {
                mainEntityKey = stringKeys.find(k => k.includes('producto') || k.includes('alias') || k.includes('nombre') || k.includes('name') || k.includes('cliente') || k.includes('categoria')) || stringKeys[0] || '';
            }
            breakdownKey = stringKeys.find(k => k !== mainEntityKey && (k.includes('tienda') || k.includes('sucursal') || k.includes('metodo') || k.includes('pago') || k.includes('payment') || k.includes('fecha') || k.includes('date')));

            // 2. Agrupar datos por la entidad principal
            const grouped = {};
            data.forEach(item => {
                const rawVal = item[mainEntityKey] || 'Otros';
                const key = rawVal.toString();
                if (!grouped[key]) {
                    grouped[key] = { 
                        label: key,
                        totalValue: 0,
                        rawItems: [] 
                    };
                }
                
                if (isCountMetric) {
                    grouped[key].totalValue += 1;
                } else {
                    grouped[key].totalValue += Number(item[metricKey] || 0);
                }
                grouped[key].rawItems.push(item);
            });

            const groupedArray = Object.values(grouped);
            const maxVal = Math.max(...groupedArray.map(g => Math.abs(g.totalValue)), 1);

            // 3. Renderizar y añadir eventos
            const container = document.getElementById('chartContainer');

            // =========================================================
            // RENDER HORIZONTAL
            // =========================================================
            function renderHorizontal() {
                groupedArray.forEach(group => {
                    const row = document.createElement('div');
                    row.className = 'chart-row';

                    const labelDiv = document.createElement('div');
                    labelDiv.className = 'chart-label';
                    labelDiv.textContent = group.label;
                    row.appendChild(labelDiv);

                    const barWrapper = document.createElement('div');
                    barWrapper.className = 'chart-bar-wrapper';

                    const bar = document.createElement('div');
                    bar.className = 'chart-bar';
                    if (group.totalValue < 0) {
                        bar.classList.add('negative');
                        bar.style.background = 'linear-gradient(135deg, #f87171 0%, #ef4444 100%)';
                    } else {
                        bar.style.background = 'linear-gradient(135deg, #6366f1 0%, #06b6d4 100%)';
                    }
                    const pct = Math.max(2, (Math.abs(group.totalValue) / maxVal) * 100);
                    bar.style.width = '0%';
                    barWrapper.appendChild(bar);

                    const valDiv = document.createElement('div');
                    valDiv.className = 'chart-value';
                    valDiv.textContent = group.totalValue;
                    barWrapper.appendChild(valDiv);

                    row.appendChild(barWrapper);
                    attachEvents(row, group, bar); // Pasar la barra como target del tooltip
                    container.appendChild(row);

                    setTimeout(() => { bar.style.width = pct + '%'; }, 50);
                });
            }

            // =========================================================
            // RENDER VERTICAL
            // =========================================================
            function renderVertical() {
                container.classList.add('vertical');
                const containerH = 300; // px disponibles para las barras

                groupedArray.forEach(group => {
                    const col = document.createElement('div');
                    col.className = 'chart-col';

                    // Valor encima de la barra
                    const valDiv = document.createElement('div');
                    valDiv.className = 'chart-col-value';
                    valDiv.textContent = group.totalValue;
                    col.appendChild(valDiv);

                    // Barra vertical
                    const bar = document.createElement('div');
                    bar.className = 'chart-col-bar';
                    if (group.totalValue < 0) bar.classList.add('negative');
                    const pctH = Math.max(4, (Math.abs(group.totalValue) / maxVal) * containerH);
                    bar.style.height = '0px';
                    col.appendChild(bar);

                    // Etiqueta debajo
                    const labelDiv = document.createElement('div');
                    labelDiv.className = 'chart-col-label';
                    labelDiv.textContent = group.label;
                    col.appendChild(labelDiv);

                    attachEvents(col, group, bar); // Pasar la barra como target del tooltip
                    container.appendChild(col);

                    setTimeout(() => { bar.style.height = pctH + 'px'; }, 50);
                });
            }

            // =========================================================
            // EVENTOS COMPARTIDOS (tooltip)
            // =========================================================
            function attachEvents(el, group, tooltipTarget) {

                // --- HTML DEL TOOLTIP ---
                const metricLabel = isCountMetric ? 'Registros' : (metricKey || 'Total');
                let html = '<div class=\"tooltip-header\">';
                html += '<div class=\"tooltip-title\">' + group.label + '</div>';
                html += '<span class=\"tooltip-total-badge\">&#9679; ' + metricLabel + ': ' + group.totalValue + '</span>';
                html += '</div>';
                html += '<div class=\"tooltip-body\">';

                const sampleRow = group.rawItems[0] || {};
                const allKeys = Object.keys(sampleRow).filter(k => k !== mainEntityKey);

                const priorityPatterns = ['nombre', 'name', 'descripcion', 'tipo', 'serie', 'numero',
                                          'fecha', 'date', 'total', 'amount', 'subtotal', 'precio',
                                          'quantity', 'stock', 'cantidad', 'alias', 'tienda', 'cliente',
                                          'cajero', 'comprobante', 'moneda', 'estado'];

                const MAX_FIELDS = 5;

                if (allKeys.length > 0) {
                    const keysToShow = allKeys.slice(0, MAX_FIELDS);
                    const itemsToShow = group.rawItems.slice(0, 5);
                    let addedRows = false;
                    
                    let tempHtml = '';
                    itemsToShow.forEach((item, index) => {
                        let hasVal = false;
                        let itemHtml = '';
                        keysToShow.forEach(k => {
                            const val = item[k];
                            if (val === null || val === undefined || val === '') return;
                            itemHtml += '<div class=\"tooltip-row\">';
                            itemHtml += '<span class=\"tooltip-row-key\">' + k.replace(/_/g, ' ') + '</span>';
                            itemHtml += '<span class=\"tooltip-row-val\">' + val + '</span>';
                            itemHtml += '</div>';
                            hasVal = true;
                            addedRows = true;
                        });
                        
                        if (hasVal) {
                            if (index === 0) {
                                tempHtml += '<div class=\"tooltip-section-label\">Detalle</div>';
                            }
                            tempHtml += itemHtml;
                        }
                    });

                    if (addedRows) {
                        html += tempHtml;
                    }
                }

                if (group.rawItems.length > 5) {
                    const remaining = group.rawItems.length - 5;
                    html += '<div class=\"tooltip-more\">+ ' + remaining + ' registro' + (remaining > 1 ? 's' : '') + ' más (clic para ver tabla)</div>';
                } else if (group.rawItems.length > 1) {
                    html += '<div class=\"tooltip-more\">Clic para ver tabla completa</div>';
                }

                html += '</div>';

                // Solo agregar atributos a la barrita (para que desaparezca al salir de ella)
                if (tooltipTarget) {
                    tooltipTarget.setAttribute('data-toggle', 'tooltip');
                    tooltipTarget.setAttribute('data-html', 'true');
                    tooltipTarget.setAttribute('title', html);
                    tooltipTarget.setAttribute('data-placement', chartType === 'vertical' ? 'top' : 'right');
                }

                // Evento Click en toda la fila/columna: Abre modal de Bootstrap
                el.addEventListener('click', function() {
                    // Ocultar el tooltip si está visible al hacer click
                    if (typeof window.$ !== 'undefined') {
                        window.$('[data-toggle=\"tooltip\"]').tooltip('hide');
                    }

                    const modalTitle = document.getElementById('modalTitle');
                    const modalBody = document.getElementById('modalBody');

                    modalTitle.textContent = group.label;

                    let htmlModal = '';
                    if (group.rawItems && group.rawItems.length > 0) {
                        htmlModal += '<div class=\"table-responsive\">';
                        htmlModal += '<table class=\"table table-hover table-striped table-bordered mb-0\">';
                        htmlModal += '<thead class=\"thead-light\"><tr>';
                        const keys = Object.keys(group.rawItems[0]);
                        keys.forEach(k => {
                            htmlModal += '<th>' + k.replace(/_/g, ' ').toUpperCase() + '</th>';
                        });
                        htmlModal += '</tr></thead><tbody>';
                        group.rawItems.forEach(item => {
                            htmlModal += '<tr>';
                            keys.forEach(k => {
                                let val = item[k];
                                if (val === null || val === undefined) val = '<span class=\"text-muted font-italic\">-</span>';
                                htmlModal += '<td>' + val + '</td>';
                            });
                            htmlModal += '</tr>';
                        });
                        htmlModal += '</tbody></table></div>';
                    } else {
                        htmlModal = '<div class=\"alert alert-secondary text-center\">No hay registros detallados.</div>';
                    }

                    modalBody.innerHTML = htmlModal;
                    
                    if (typeof window.$ !== 'undefined') {
                        window.$('#detailModal').modal('show');
                    }
                });
            }

            // Ejecutar el render correcto
            if (chartType === 'vertical') {
                renderVertical();
            } else {
                renderHorizontal();
            }

        } else {
            document.getElementById('chartContainer').innerHTML = '<div style=\\\"padding: 20px; text-align: center; color: #666;\\\">No hay registros para mostrar.</div>';
        }

        // Inicializar Tooltips de Bootstrap al final (cuando ya están en el DOM)
        setTimeout(function() {
            if (typeof window.$ !== 'undefined' && window.$.fn.tooltip) {
                window.$('[data-toggle=\"tooltip\"]').tooltip({
                    sanitize: false,
                    boundary: 'window',
                    container: '.ai-report-container',
                    template: '<div class=\"tooltip\" role=\"tooltip\"><div class=\"arrow\"></div><div class=\"tooltip-inner ai-report-bs-tooltip\"></div></div>'
                });
            } else {
                console.warn('Bootstrap 4 Tooltip no está disponible en window.$');
            }
        }, 100);

    })();
    </script>
</div>
";
    }

    private function extractSql($text)
    {
        $text = trim($text);
        $text = preg_replace('/^```sql\s+/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        return trim($text);
    }

    private function extractHtml($text)
    {
        $text = trim($text);
        $text = preg_replace('/^```html\s+/i', '', $text);
        $text = preg_replace('/\s*```$/', '', $text);
        return trim($text);
    }
}
