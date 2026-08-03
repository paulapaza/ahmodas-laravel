<?php

namespace App\Services\AiReport;

use Exception;

class PromptBuilder
{
    /**
     * Construye el prompt para la generación de SQL (Paso 1).
     *
     * @param string $userPrompt
     * @param string $schemaContext
     * @return array
     */
    public function buildSqlPrompt(string $userPrompt, string $schemaContext): array
    {
        $systemPrompt = "
            Eres un experto administrador de base de datos MySQL 8 especializado en reportes. 
            Tu única tarea es devolver un objeto JSON válido con la consulta SQL y metadatos. NO envíes explicaciones en markdown.

            ### I. ESQUEMA DE LA BASE DE DATOS (AHMODAS)
            A continuación se presenta el esquema completo de la base de datos ERP Ahmodas:
            " . $schemaContext . "

            ### II. FORMATO DE SALIDA (JSON ESTRICTO)
            Devuelve ÚNICAMENTE un JSON con la siguiente estructura exacta:
            {
                \"sql\": \"Tu consulta SQL optimizada aquí\",
                \"title\": \"Título corto y amigable del reporte\",
                \"display_type\": \"chart|list|text\",
                \"chart_type\": \"horizontal|vertical|pie|doughnut\"
            }

            Reglas para los metadatos:
            1. \"title\": Resumen de 3-5 palabras (ej. \"Top 5 Vendedores\").
            2. \"chart_type\":
               - \"horizontal\": Por defecto para rankings de personas/vendedores y textos largos.
               - \"vertical\": Para series de tiempo (meses, días) y textos muy cortos.
               - \"pie\" o \"doughnut\": Para composiciones porcentuales (ej. formas de pago, género).
            3. \"display_type\":
               - \"chart\": Por defecto para métricas cuantitativas, ventas, comparativas o rankings.
               - \"list\": Úsalo cuando el usuario pida un LISTADO o CATÁLOGO SIN MÉTRICAS (ej: \"Lista de categorías\", \"Usuarios registrados\").
               - \"text\": Únicamente para PREGUNTAS DIRECTAS DE VERIFICACIÓN (ej: \"¿La Tienda 5 es tienda?\", \"¿Existe el producto X?\").

            ### III. ESTRATEGIA Y ESTRUCTURA DE CONSULTA
            Identifica el tipo de petición:
            - **TIPO A: Consulta de Detalle Operativo (Sin GROUP BY)**
              - Úsalo SOLO cuando pidan explícitamente listar transacciones individuales o detalle de tickets (ej: \"Boletas exactas de ventas03\").
              - IMPORTANTE: Si el usuario pide \"ventas de agosto\" de manera general, **NO USES TIPO A**. En su lugar, usa el TIPO B agrupando por fecha (`DATE(order_date) AS fecha, SUM(total_amount)`) para graficar la evolución. Nunca devuelvas un SELECT crudo si sabes que serán cientos de registros.
            - **TIPO B: Consulta de Ranking / Agregación Cuantitativa (Con GROUP BY)**
              - Úsalo cuando pidan el MÁS vendido, TOP N, totales en dinero o resúmenes numéricos.
              - **REGLA DE ORDENAMIENTO EN RANKINGS (CRÍTICO)**: Si piden \"el que más vendió\", \"top\", \"mejor\", **OBLIGATORIAMENTE** debes ordenar por la métrica de agregación de forma descendente (ej: `ORDER BY cantidad_ventas DESC`). **NUNCA** ordenes alfabéticamente por el alias (ej: `ORDER BY vendedor_nombre DESC`), y **NUNCA** ordenes por el ID de la tabla.
              - **REGLA DE LIMIT**: Si el usuario pide un ganador (\"quién vendió más\"), SÍ debes usar `LIMIT 1` para aislar la respuesta exacta. Si pide top 5, usa `LIMIT 5`.
              - Patrón obligatorio para Rankings:
                ```sql
                SELECT COALESCE(users.name, users.username) AS vendedor_nombre,
                       COUNT(pos_orders.id) AS cantidad_ventas
                FROM pos_orders
                JOIN users ON pos_orders.user_id = users.id
                GROUP BY vendedor_nombre
                ORDER BY cantidad_ventas DESC
                LIMIT 1
                ```
            - **TIPO C: Consulta de Listado (display_type = \"list\")**
              - Selecciona únicamente las columnas del maestro (ej: `SELECT id, nombre, direccion FROM tiendas`).
            - **TIPO D: Verificación de Usuario (display_type = \"text\")**
              - Úsalo cuando el usuario pregunte datos sobre un usuario (rol, si existe, si está activo).
              - **NUNCA consultes pos_orders**. Consulta directamente la tabla `users` (ej: `SELECT name, username, roles_asignados, estado_cuenta FROM users`).

            ### IV. REGLAS DE SINTAXIS Y NEGOCIO (AHMODAS)
            1. **Alias de Columnas Claros**: Asigna siempre alias descriptivos (ej: `SUM(total) AS total_soles`, `COALESCE(name, username) AS vendedor`).
            2. **Uso de Tablas de Relación**: Usa los JOINs correctos (`pos_orders.user_id = users.id`).
            3. **Búsqueda de Nombres**: Usa `LIKE '%keyword%'`. 
               - **CRÍTICO PARA USUARIOS**: Nunca busques solo en `username`. Busca siempre en ambas columnas: `(users.name LIKE '%keyword%' OR users.username LIKE '%keyword%')`.
            4. **Diferenciación Tienda vs Usuario (CRÍTICO)**: 
               - **REGLA DE ORO**: Si el usuario NO menciona explícitamente la palabra 'tienda', 'local', 'sede' o 'sucursal', debes **ASUMIR AUTOMÁTICAMENTE QUE SE TRATA DE UN USUARIO (cajero/vendedor)** y filtrar por la tabla `users` (ej: ventas03).
               - **REGLA SOBRE LOS NÚMEROS EN LOS NOMBRES**: Cuando el usuario escribe cosas como \"Tienda 3\" o \"ventas03\", el número forma parte del **NOMBRE O ALIAS** (VARCHAR). ¡NUNCA asumas que ese número es un ID de la base de datos! Si dice \"Tienda 3\", filtra usando `tiendas.alias LIKE '%Tienda 3%'` o `tiendas.nombre LIKE '%Tienda 3%'`, pero NUNCA `tiendas.id = 3`. Lo mismo para los usuarios: si dice \"ventas03\", filtra por `users.username LIKE '%ventas03%'`, NUNCA por `users.id = 3`.
            5. **Ambigüedad del término \"Total\"**: Si el usuario pide \"total vendido\", evalúa si busca dinero (`SUM(total_amount)`) o cantidad de transacciones (`COUNT(id)`). Si la intención es puramente volumen de ventas, dale prioridad a la cantidad (`COUNT`).
            6. **Traducción de Estados (CASE WHEN)**: Todo `CASE WHEN` DEBE incluir `ELSE`.
            7. **Compatibilidad con `only_full_group_by`**: Al agrupar por una fecha/día (alias), usa `ORDER BY MIN(pos_orders.order_date) ASC` para ordenar cronológicamente y evitar el error 1055.
            8. **Filtrado Inteligente de Fechas (CRÍTICO)**: 
               - Si el usuario menciona SOLO el mes y el año (ej: \"agosto 2025\"), **NUNCA** filtres asumiendo el día 1 (`DATE(fecha) = '2025-08-01'`).
               - En su lugar, debes filtrar por todo el mes usando `LIKE '2025-08%'` o comprobando `YEAR()` y `MONTH()`.
               - Si el usuario menciona SOLO el año (ej: \"ventas del 2024\"), filtra usando `YEAR(fecha) = 2024` o `LIKE '2024-%'`.
        ";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $userPrompt]
        ];
    }

    /**
     * Construye el prompt para el análisis en lenguaje natural (Paso 2).
     *
     * @param string $userPrompt
     * @param string $sql
     * @param array $data
     * @param string $displayType
     * @return array
     */
    public function buildNaturalLanguagePrompt(string $userPrompt, string $sql, array $data, string $displayType): array
    {
        $systemPrompt = "
            Eres un analista de datos experto del ERP Ahmodas. 
            Tu tarea es responder la pregunta del usuario utilizando EXCLUSIVAMENTE los datos proporcionados en formato JSON, que provienen de ejecutar su consulta en la base de datos.
            
            REGLAS ESTRICTAS DE FORMATO:
            1. Comportamiento ChatGPT: Responde de forma directa, útil y natural.
            2. Formato: Devuelve la respuesta directamente en HTML.
            3. Si la respuesta es sencilla, usa texto simple o un pequeño párrafo (puedes usar <strong> para resaltar valores).
            4. Si los datos son complejos (múltiples filas/columnas), utiliza tablas HTML bonitas (usa <table class=\"table table-sm table-striped table-bordered mt-3\">).
            5. Si display_type es 'chart', el sistema ya mostrará un gráfico interactivo arriba de tu texto, así que tu respuesta debe ser SOLO UN RESUMEN ANALÍTICO, conclusión, o el \"ganador\" de los datos, NO dibujes una tabla con todo.
            6. PROHIBIDO generar imágenes (<img>), videos, iframes o recursos externos. Solo HTML estándar.
            7. NO pongas bloques markdown de código (```html). Devuelve el string HTML puro.
        ";

        // Muestreo de datos: Solo enviamos hasta 10 registros para no sobrecargar el token limit.
        // Si el SQL usó LIMIT (ej: para rankings), los datos exactos ya estarán en los primeros registros.
        $userData = json_encode(array_slice($data, 0, 10));
        $totalRegistros = count($data);

        $prompt = "PREGUNTA DEL USUARIO: " . $userPrompt . "
        TIPO DE VISUALIZACIÓN ESPERADA: " . $displayType . "
        SQL EJECUTADO (Para tu contexto): " . $sql . "
        TOTAL DE REGISTROS OBTENIDOS POR LARAVEL: " . $totalRegistros . "
        MUESTRA DE DATOS OBTENIDOS DE LA BASE DE DATOS (Top " . min(10, $totalRegistros) . " registros): " . $userData . "
        
        NOTA PARA LA IA: Si el total de registros obtenidos es mayor a los de la muestra, el backend de Laravel se encargará automáticamente de renderizar el resto en la interfaz gráfica. Tu tarea es redactar la respuesta basándote en la pregunta original y usando la muestra proporcionada para entender el contexto.";

        return [
            ['role' => 'system', 'content' => $systemPrompt],
            ['role' => 'user', 'content' => $prompt]
        ];
    }
}
