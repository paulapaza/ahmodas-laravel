<?php

namespace App\Services\AiReport;

class HtmlRenderer
{
    public function renderHtmlTemplate($userPrompt, $data, $groupByKey = null, $chartType = 'horizontal')
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

        .ai-report-container .tooltip-item-divider {
            border: none;
            border-top: 1px dashed rgba(255,255,255,0.18);
            margin: 6px 0;
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

    <!-- Tarjetas de Resumen KPI -->
    <div class=\"row mb-3\" id=\"kpiSummaryCards\">
        <div class=\"col-lg-3 col-md-6 col-sm-12 mb-2\">
            <div class=\"p-3 rounded shadow-sm d-flex align-items-center justify-content-between\" style=\"background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%); color: #ffffff; border-radius: 12px; height: 100%;\">
                <div>
                    <span class=\"d-block text-uppercase font-weight-bold\" style=\"font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.85;\">Total Ventas (Cantidad)</span>
                    <h3 class=\"mb-0 font-weight-bold\" id=\"kpiTotalValue\" style=\"font-size: 1.35rem; color: #ffffff;\">0</h3>
                </div>
                <div class=\"rounded-circle d-flex align-items-center justify-content-center\" style=\"width: 38px; height: 38px; background: rgba(255,255,255,0.2); flex-shrink: 0;\">
                    <i class=\"fas fa-receipt\" style=\"font-size: 1.1rem;\"></i>
                </div>
            </div>
        </div>
        <div class=\"col-lg-3 col-md-6 col-sm-12 mb-2\" id=\"kpiMoneyCardWrapper\">
            <div class=\"p-3 rounded shadow-sm d-flex align-items-center justify-content-between\" style=\"background: linear-gradient(135deg, #059669 0%, #10b981 100%); color: #ffffff; border-radius: 12px; height: 100%;\">
                <div>
                    <span class=\"d-block text-uppercase font-weight-bold\" style=\"font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.85;\">Monto Recaudado</span>
                    <h3 class=\"mb-0 font-weight-bold\" id=\"kpiMoneyValue\" style=\"font-size: 1.35rem; color: #ffffff;\">S/ 0.00</h3>
                </div>
                <div class=\"rounded-circle d-flex align-items-center justify-content-center\" style=\"width: 38px; height: 38px; background: rgba(255,255,255,0.2); flex-shrink: 0;\">
                    <i class=\"fas fa-coins\" style=\"font-size: 1.1rem;\"></i>
                </div>
            </div>
        </div>
        <div class=\"col-lg-3 col-md-6 col-sm-12 mb-2\">
            <div class=\"p-3 rounded shadow-sm d-flex align-items-center justify-content-between\" style=\"background: linear-gradient(135deg, #0ea5e9 0%, #06b6d4 100%); color: #ffffff; border-radius: 12px; height: 100%;\">
                <div>
                    <span class=\"d-block text-uppercase font-weight-bold\" style=\"font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.85;\" id=\"kpiSecondLabel\">Pico Máximo</span>
                    <h4 class=\"mb-0 font-weight-bold\" id=\"kpiSecondValue\" style=\"font-size: 1.05rem; color: #ffffff;\">-</h4>
                </div>
                <div class=\"rounded-circle d-flex align-items-center justify-content-center\" style=\"width: 38px; height: 38px; background: rgba(255,255,255,0.2); flex-shrink: 0;\">
                    <i class=\"fas fa-trophy\" style=\"font-size: 1.1rem;\"></i>
                </div>
            </div>
        </div>
        <div class=\"col-lg-3 col-md-6 col-sm-12 mb-2\">
            <div class=\"p-3 rounded shadow-sm d-flex align-items-center justify-content-between\" style=\"background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%); color: #ffffff; border-radius: 12px; height: 100%;\">
                <div>
                    <span class=\"d-block text-uppercase font-weight-bold\" style=\"font-size: 0.68rem; letter-spacing: 0.5px; opacity: 0.85;\">Promedio por Registro</span>
                    <h4 class=\"mb-0 font-weight-bold\" id=\"kpiAvgValue\" style=\"font-size: 1.05rem; color: #ffffff;\">0</h4>
                </div>
                <div class=\"rounded-circle d-flex align-items-center justify-content-center\" style=\"width: 38px; height: 38px; background: rgba(255,255,255,0.2); flex-shrink: 0;\">
                    <i class=\"fas fa-chart-line\" style=\"font-size: 1.1rem;\"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Cabecera de Columnas / Leyenda del Reporte -->
    <div class=\"chart-header-bar d-flex justify-content-between align-items-center mb-2 px-3 py-2 rounded\" style=\"background: #f1f5f9; border: 1px solid #cbd5e1; font-size: 0.82rem; font-weight: 700; color: #334155;\">
        <span id=\"chartHeaderEntity\"><i class=\"fas fa-list-ul mr-1 text-primary\"></i> <span id=\"headerEntityText\">Categoría / Período</span></span>
        <span id=\"chartHeaderMetric\"><i class=\"fas fa-chart-bar mr-1 text-primary\"></i> <span id=\"headerMetricText\">Métrica</span></span>
    </div>

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
            const reportTitleText = '{$title}';

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

            // Configurar textos dinámicos en la barra de cabecera
            const entityTextEl = document.getElementById('headerEntityText');
            const metricTextEl = document.getElementById('headerMetricText');
            if (entityTextEl) {
                let entityLabel = (mainEntityKey || 'Categoría').replace(/_/g, ' ');
                entityLabel = entityLabel.charAt(0).toUpperCase() + entityLabel.slice(1);
                entityTextEl.textContent = entityLabel;
            }
            if (metricTextEl) {
                let metricLabelName = isCountMetric ? 'Cantidad de Ventas / Registros' : (metricKey || 'Total').replace(/_/g, ' ');
                metricLabelName = metricLabelName.charAt(0).toUpperCase() + metricLabelName.slice(1);
                const lower = metricLabelName.toLowerCase();
                // Solo agregar (S/) si la métrica explícitamente se refiere a dinero
                if (lower.includes('soles') || lower.includes('monto') || lower.includes('amount') || lower.includes('subtotal') || lower.includes('precio') || lower.includes('price')) {
                    if (!lower.includes('soles') && !lower.includes('s/')) {
                        metricLabelName += ' (S/)';
                    }
                }
                metricTextEl.textContent = metricLabelName;
            }

            // Detectar si el titulo menciona un mes para formatear '11 de Agosto'
            const monthsList = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const matchedMonth = monthsList.find(m => reportTitleText.toLowerCase().includes(m.toLowerCase()));

            // 2. Agrupar datos por la entidad principal
            const grouped = {};
            data.forEach(item => {
                const rawVal = item[mainEntityKey] || 'Otros';
                let key = rawVal.toString();
                
                // Si la etiqueta es solo un numero (ej. 4, 5) y la entidad es dia/fecha o numero <= 31, formatear como 'Dia X' o 'X de Mes'
                const lowerEntity = (mainEntityKey || '').toLowerCase();
                if (/^[0-9]{1,2}$/.test(key) && (lowerEntity.includes('dia') || lowerEntity.includes('day') || lowerEntity.includes('fecha') || lowerEntity.includes('date') || lowerEntity.includes('order_date') || parseInt(key) <= 31)) {
                    key = matchedMonth ? (key + ' de ' + matchedMonth) : ('Día ' + key);
                } else if (key.startsWith('Día ') && matchedMonth) {
                    const numPart = key.replace('Día ', '').trim();
                    if (/^[0-9]{1,2}$/.test(numPart)) {
                        key = numPart + ' de ' + matchedMonth;
                    }
                }

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

            // Ordenar cronologicamente si las etiquetas parecen fechas con formato 'D de Mes' o 'D de Mes de Anno'
            const MONTHS_ES = { 'Enero':1,'Febrero':2,'Marzo':3,'Abril':4,'Mayo':5,'Junio':6,'Julio':7,'Agosto':8,'Septiembre':9,'Octubre':10,'Noviembre':11,'Diciembre':12 };
            function parseDateLabel(label) {
                // Formato: '11 de Agosto de 2025' o '11 de Agosto'
                const m = label.match(/^(\d{1,2})\s+de\s+([A-Za-záéíóúÁÉÍÓÚ]+)(?:\s+de\s+(\d{4}))?$/i);
                if (m) {
                    const day = parseInt(m[1]);
                    const month = MONTHS_ES[m[2]] || 0;
                    const year = m[3] ? parseInt(m[3]) : 0;
                    if (month > 0) return new Date(year || 2000, month - 1, day).getTime();
                }
                return null;
            }
            const firstDate = parseDateLabel(groupedArray[0]?.label || '');
            if (firstDate !== null) {
                groupedArray.sort((a, b) => {
                    const da = parseDateLabel(a.label);
                    const db = parseDateLabel(b.label);
                    if (da !== null && db !== null) return da - db;
                    return 0;
                });
            }

            const maxVal = Math.max(...groupedArray.map(g => Math.abs(g.totalValue)), 1);

            // Función auxiliar para dar formato legible a los números de las barras
            function formatDisplayValue(val) {
                const lowerMetric = (metricKey || '').toLowerCase();
                // Verificamos si es dinero explícitamente (soles, monto, precio...)
                if (lowerMetric.includes('soles') || lowerMetric.includes('monto') || lowerMetric.includes('amount') || lowerMetric.includes('subtotal') || lowerMetric.includes('precio') || lowerMetric.includes('price')) {
                    return 'S/ ' + val.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                } else if (isCountMetric || lowerMetric.includes('cantidad') || lowerMetric.includes('ventas') || lowerMetric.includes('quantity') || lowerMetric.includes('registros') || lowerMetric.includes('unidades')) {
                    return val.toLocaleString('es-PE') + (lowerMetric.includes('unidades') ? ' unid.' : ' ventas');
                }
                return val.toLocaleString('es-PE');
            }

            // Calcular y renderizar Tarjetas KPI de Resumen (Cantidad, Monto en Soles S/, Pico y Promedio)
            const grandTotalVal = groupedArray.reduce((sum, g) => sum + g.totalValue, 0);
            let maxGroupObj = groupedArray[0];
            groupedArray.forEach(g => {
                if (g.totalValue > (maxGroupObj ? maxGroupObj.totalValue : 0)) {
                    maxGroupObj = g;
                }
            });

            // Buscar columna de dinero acumulado en la muestra de data
            let moneyColumnKey = Object.keys(sampleItem).find(k => {
                const l = k.toLowerCase();
                return l.includes('soles') || l.includes('total_monto') || l.includes('monto') || l.includes('total_amount') || l.includes('amount') || l.includes('subtotal') || l.includes('precio') || l.includes('price');
            });

            let totalMoneyAccumulated = 0;
            if (moneyColumnKey) {
                totalMoneyAccumulated = data.reduce((sum, item) => sum + Number(item[moneyColumnKey] || 0), 0);
            }

            const kpiTotalEl = document.getElementById('kpiTotalValue');
            const kpiMoneyEl = document.getElementById('kpiMoneyValue');
            const kpiMoneyWrapper = document.getElementById('kpiMoneyCardWrapper');
            const kpiSecondValEl = document.getElementById('kpiSecondValue');
            const kpiSecondLblEl = document.getElementById('kpiSecondLabel');
            const kpiAvgValEl = document.getElementById('kpiAvgValue');

            if (kpiTotalEl) {
                kpiTotalEl.textContent = formatDisplayValue(grandTotalVal);
            }

            if (kpiMoneyEl && moneyColumnKey && totalMoneyAccumulated > 0) {
                kpiMoneyEl.textContent = 'S/ ' + totalMoneyAccumulated.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else if (kpiMoneyWrapper) {
                kpiMoneyWrapper.style.display = 'none';
            }

            if (kpiSecondValEl && maxGroupObj) {
                kpiSecondValEl.textContent = maxGroupObj.label + ' (' + formatDisplayValue(maxGroupObj.totalValue) + ')';
                if (kpiSecondLblEl) {
                    kpiSecondLblEl.textContent = 'Pico Máximo de Registro';
                }
            }

            if (kpiAvgValEl && groupedArray.length > 0) {
                const avgVal = grandTotalVal / groupedArray.length;
                kpiAvgValEl.textContent = formatDisplayValue(Math.round(avgVal * 10) / 10);
            }

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
                    valDiv.textContent = formatDisplayValue(group.totalValue);
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
                    valDiv.textContent = formatDisplayValue(group.totalValue);
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
                            } else {
                                tempHtml += '<hr class=\"tooltip-item-divider\">';
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
                    tooltipTarget.setAttribute('data-placement', 'top');
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
}
