<?php

namespace App\Services\Inventario;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportStockService
{
    /**
     * Genera un archivo Excel con el stock de una tienda.
     * Solo incluye productos con stock > 0.
     * 
     * @param int|null $tiendaId
     * @return StreamedResponse
     */
    public function exportarStockPorTienda(?int $tiendaId): StreamedResponse
    {
        $productos = $this->obtenerProductosConStock($tiendaId);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock de Productos');

        // Configurar Cabeceras
        $cabeceras = [
            'Cód. Barras',
            'Nombre del Producto',
            'Stock',
            'Tienda',
            'Fecha de Registro'
        ];

        $columnas = ['A', 'B', 'C', 'D', 'E'];
        foreach ($cabeceras as $index => $titulo) {
            $col = $columnas[$index];
            $sheet->setCellValue($col . '1', $titulo);
        }

        // Configurar anchos fijos para columnas solicitadas y auto para las demás
        $sheet->getColumnDimension('A')->setWidth(16); // Código de barras
        $sheet->getColumnDimension('B')->setWidth(60); // Nombre del Producto
        $sheet->getColumnDimension('C')->setWidth(15); // Stock
        $sheet->getColumnDimension('D')->setAutoSize(true); // Tienda
        $sheet->getColumnDimension('E')->setAutoSize(true); // Fecha de Registro

        // Estilos de cabecera
        $sheet->getStyle('A1:E1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F5B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true, // Habilitar ajuste de texto por si el ancho es menor al título
            ],
        ]);

        // Poblar datos
        $row = 2;
        foreach ($productos as $producto) {
            $fechaRegistro = !empty($producto->fecha_registro) 
                ? date('d/m/Y h:i A', strtotime($producto->fecha_registro))
                : 'N/A';

            $alias = trim($producto->alias ?? '');
            $nombre = trim($producto->nombre ?? '');
            $nombreFinalProducto = ($alias !== '') ? $alias : (($nombre !== '') ? $nombre : '-');
            $nombreFinalTienda = (!empty(trim($producto->tienda_alias ?? ''))) ? $producto->tienda_alias : $producto->tienda_nombre;

            $sheet->setCellValue('A' . $row, $producto->codigo_barras);
            $sheet->setCellValue('B' . $row, $nombreFinalProducto);
            $sheet->setCellValue('C' . $row, $producto->stock);
            $sheet->setCellValue('D' . $row, $nombreFinalTienda);
            $sheet->setCellValue('E' . $row, $fechaRegistro);

            $row++;
        }

        // Alinear celdas
        if ($row > 2) {
            // Alinear verticalmente al centro todas las filas
            $sheet->getStyle('A2:E' . ($row - 1))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            
            // Alinear A, C, D, E al centro
            $sheet->getStyle('A2:A' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C2:E' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            
            // Alinear B a la izquierda
            $sheet->getStyle('B2:B' . ($row - 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        }

        return $this->generarRespuestaDescarga($spreadsheet, $tiendaId);
    }

    /**
     * Consulta la base de datos para obtener el stock mayor a 0.
     * Utiliza cursor() para optimizar el uso de memoria en grandes volúmenes de datos.
     * 
     * @param int|null $tiendaId
     * @return \Illuminate\Support\LazyCollection
     */
    private function obtenerProductosConStock(?int $tiendaId)
    {
        $query = DB::table('producto_tienda as pt')
            ->join('productos as p', 'pt.producto_id', '=', 'p.id')
            ->join('tiendas as t', 'pt.tienda_id', '=', 't.id')
            ->select(
                'p.codigo_barras',
                'p.alias',
                'p.nombre',
                't.alias as tienda_alias',
                't.nombre as tienda_nombre',
                'pt.stock',
                'p.created_at as fecha_registro'
            )
            ->where('pt.stock', '>', 0);

        if ($tiendaId) {
            $query->where('pt.tienda_id', $tiendaId);
        }

        return $query->orderBy('p.codigo_barras', 'desc')->cursor();
    }

    /**
     * Crea la respuesta HTTP en streaming para el archivo Excel.
     * 
     * @param Spreadsheet $spreadsheet
     * @param int|null $tiendaId
     * @return StreamedResponse
     */
    private function generarRespuestaDescarga(Spreadsheet $spreadsheet, ?int $tiendaId): StreamedResponse
    {
        $nombreTiendaArchivo = 'todas_las_tiendas';

        if ($tiendaId) {
            $tienda = DB::table('tiendas')->where('id', $tiendaId)->first();
            if ($tienda) {
                $nombreTiendaArchivo = (!empty(trim($tienda->alias ?? ''))) ? $tienda->alias : $tienda->nombre;
                // Sanitizar para que sea un nombre de archivo válido (reemplaza espacios y caracteres raros por _)
                $nombreTiendaArchivo = preg_replace('/[^A-Za-z0-9]/', '_', $nombreTiendaArchivo);
                // Evitar múltiples guiones bajos seguidos
                $nombreTiendaArchivo = preg_replace('/_+/', '_', $nombreTiendaArchivo);
                $nombreTiendaArchivo = trim($nombreTiendaArchivo, '_');
            }
        }

        $fileName = 'stock_' . strtolower($nombreTiendaArchivo) . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    /**
     * Genera un archivo Excel matricial con el stock de productos por tienda.
     * Columnas: Cód. Barras | Producto | Tienda 1 | Tienda 2 | ... | Total
     *
     * @param int|null $tiendaId
     * @param bool $ocultarSinStock
     * @param string|null $search
     * @return StreamedResponse
     */
    public function exportarMatrizProductos(?int $tiendaId = null, bool $ocultarSinStock = false, ?string $search = null): StreamedResponse
    {
        // 1. Obtener tiendas a mostrar (Tienda 2 primero, luego las demás ordenadas por ID)
        $tiendasQuery = DB::table('tiendas')->select('id', 'nombre', 'alias')
            ->orderByRaw("CASE WHEN LOWER(TRIM(alias)) = 'tienda 2' OR nombre LIKE '%002%' OR id = 4 THEN 0 ELSE 1 END, id ASC");
        if ($tiendaId) {
            $tiendasQuery->where('id', $tiendaId);
        }
        $tiendas = $tiendasQuery->get();

        if ($tiendas->isEmpty()) {
            $tiendas = DB::table('tiendas')->select('id', 'nombre', 'alias')
                ->orderByRaw("CASE WHEN LOWER(TRIM(alias)) = 'tienda 2' OR nombre LIKE '%002%' OR id = 4 THEN 0 ELSE 1 END, id ASC")
                ->get();
            $tiendaId = null;
        }

        // 2. Obtener mapa de stocks (producto_id => [tienda_id => stock])
        $ptQuery = DB::table('producto_tienda')->select('producto_id', 'tienda_id', 'stock');
        if ($tiendaId) {
            $ptQuery->where('tienda_id', $tiendaId);
        }
        $stocksRaw = $ptQuery->get();
        $stocksMap = [];
        foreach ($stocksRaw as $s) {
            $stocksMap[$s->producto_id][$s->tienda_id] = (float)$s->stock;
        }

        // 3. Obtener productos
        $productosQuery = DB::table('productos as p')
            ->select('p.id', 'p.codigo_barras', 'p.nombre', 'p.alias')
            ->orderByRaw("COALESCE(NULLIF(TRIM(p.codigo_barras), ''), p.id) DESC");

        if (!empty($search)) {
            $productosQuery->where(function ($q) use ($search) {
                $q->where('p.nombre', 'LIKE', "%{$search}%")
                  ->orWhere('p.alias', 'LIKE', "%{$search}%")
                  ->orWhere('p.codigo_barras', 'LIKE', "%{$search}%");
            });
        }

        $productos = $productosQuery->get();

        // 4. Filtrar por stock si ocultarSinStock está activo
        if ($ocultarSinStock) {
            $productos = $productos->filter(function ($p) use ($tiendas, $stocksMap, $tiendaId) {
                if ($tiendaId) {
                    $stockTienda = $stocksMap[$p->id][$tiendaId] ?? 0;
                    return $stockTienda > 0;
                }
                $totalStock = 0;
                foreach ($tiendas as $t) {
                    $totalStock += ($stocksMap[$p->id][$t->id] ?? 0);
                }
                return $totalStock > 0;
            });
        }

        // 5. Construir hoja de cálculo
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Stock de Productos');

        // Cabeceras
        $headers = [
            'Cód. Barras',
            'Producto',
        ];

        foreach ($tiendas as $t) {
            $aliasTienda = trim($t->alias ?? '');
            $nombreTienda = ($aliasTienda !== '') ? $aliasTienda : trim($t->nombre ?? '-');
            $headers[] = $nombreTienda;
        }

        $incluirTotal = count($tiendas) > 1;
        if ($incluirTotal) {
            $headers[] = 'Total';
        }

        $totalCols = count($headers);
        $lastColLetter = Coordinate::stringFromColumnIndex($totalCols);

        // Escribir cabeceras
        foreach ($headers as $idx => $titulo) {
            $colLetter = Coordinate::stringFromColumnIndex($idx + 1);
            $sheet->setCellValue($colLetter . '1', $titulo);
        }

        // Formato y anchos de columnas
        $sheet->getColumnDimension('A')->setWidth(18); // Código de barras
        $sheet->getColumnDimension('B')->setWidth(48); // Nombre del Producto
        for ($c = 3; $c <= $totalCols; $c++) {
            $colLetter = Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setWidth(18);
        }

        // Estilo de la fila de cabecera
        $sheet->getRowDimension(1)->setRowHeight(28);
        $sheet->getStyle("A1:{$lastColLetter}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '001F5B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
        ]);

        // 6. Poblar filas de productos
        $row = 2;
        foreach ($productos as $producto) {
            $sheet->getRowDimension($row)->setRowHeight(20);

            $alias = trim($producto->alias ?? '');
            $nombre = trim($producto->nombre ?? '');
            $nombreProd = ($alias !== '') ? $alias : (($nombre !== '') ? $nombre : '-');
            $sheet->setCellValueExplicit('A' . $row, $producto->codigo_barras ?? '', DataType::TYPE_STRING);
            $sheet->setCellValue('B' . $row, $nombreProd);

            $colIdx = 3;
            $sumaFila = 0;
            foreach ($tiendas as $t) {
                $stock = $stocksMap[$producto->id][$t->id] ?? 0;
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $row, $stock);
                $sumaFila += $stock;
                $colIdx++;
            }

            if ($incluirTotal) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $row, $sumaFila);
            }

            $row++;
        }

        $lastDataRow = $row - 1;

        // Estilos de celdas de datos
        if ($lastDataRow >= 2) {
            // Alinear verticalmente al centro
            $sheet->getStyle("A2:{$lastColLetter}{$lastDataRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            // Columna A (Cód. Barras) centrada
            $sheet->getStyle("A2:A{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            // Columna B (Producto) a la izquierda
            $sheet->getStyle("B2:B{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            // Columnas de stock y total centradas
            $startStockCol = Coordinate::stringFromColumnIndex(3);
            $sheet->getStyle("{$startStockCol}2:{$lastColLetter}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Resaltar en negrita la columna de Total si existe
            if ($incluirTotal) {
                $sheet->getStyle("{$lastColLetter}2:{$lastColLetter}{$lastDataRow}")->getFont()->setBold(true);
            }

            // Bordes suaves para todas las filas de datos
            $sheet->getStyle("A1:{$lastColLetter}{$lastDataRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'D0D5DD'],
                    ],
                ],
            ]);

            // 7. Fila de TOTALES al final
            $sheet->getRowDimension($row)->setRowHeight(24);
            $sheet->setCellValue('A' . $row, '');
            $sheet->setCellValue('B' . $row, 'TOTALES');

            $colIdx = 3;
            foreach ($tiendas as $t) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $row, "=SUM({$colLetter}2:{$colLetter}{$lastDataRow})");
                $colIdx++;
            }

            if ($incluirTotal) {
                $colLetter = Coordinate::stringFromColumnIndex($colIdx);
                $sheet->setCellValue($colLetter . $row, "=SUM({$colLetter}2:{$colLetter}{$lastDataRow})");
            }

            // Estilo fila de totales
            $sheet->getStyle("A{$row}:{$lastColLetter}{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '001F5B'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E9ECEF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'B0B5BD'],
                    ],
                ],
            ]);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        }

        // 8. Nombre de archivo dinámico
        $nombreFiltro = 'todas_las_tiendas';
        if ($tiendaId) {
            $tActual = $tiendas->firstWhere('id', $tiendaId);
            if ($tActual) {
                $nombreFiltro = (!empty(trim($tActual->alias ?? ''))) ? $tActual->alias : $tActual->nombre;
                $nombreFiltro = preg_replace('/[^A-Za-z0-9]/', '_', $nombreFiltro);
                $nombreFiltro = preg_replace('/_+/', '_', $nombreFiltro);
                $nombreFiltro = trim($nombreFiltro, '_');
            }
        }

        $sufijoStock = $ocultarSinStock ? '_con_stock' : '';
        $fileName = 'stock_productos_' . strtolower($nombreFiltro) . $sufijoStock . '_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);

        return new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}
