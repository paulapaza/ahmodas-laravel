<?php

namespace App\Services\Inventario;

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
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

            $nombreFinalProducto = (!empty(trim($producto->alias ?? ''))) ? $producto->alias : $producto->nombre;
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
}
