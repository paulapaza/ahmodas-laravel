<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
use App\Models\Inventario\Producto;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class PosServices

{

    // obtener numero de serie refactorizado
    public function get_CpeSerie($tienda_id, $codigo_tipo_comprobante, $tipo_documento_a_modificar = null): ?CpeSerie
    {
        $mapaTipos = [
            '3'  => '07', // Nota de crédito
            '4'  => '08', // Nota de débito
            '1'  => '01', // Factura
            '2'  => '03', // Boleta
            '12' => '12', // Cotización
        ];

        if (!array_key_exists($codigo_tipo_comprobante, $mapaTipos)) {
            throw new Exception("Tipo de comprobante no soportado: " . $codigo_tipo_comprobante);
        }

        $codigoInterno = $mapaTipos[$codigo_tipo_comprobante];
        $query = CpeSerie::where('codigo_tipo_comprobante', $codigoInterno)
            ->where('tienda_id', $tienda_id)
            ->where('estado', 'activo');

        // Para tipos 3 y 4 se requiere análisis de tipo_documento_a_modificar
        if (in_array($codigo_tipo_comprobante, ['3', '4'])) {
            if ($tipo_documento_a_modificar === '1') {
                $query->where('serie', 'like', 'F%');
            } elseif ($tipo_documento_a_modificar === '2') {
                $query->where('serie', 'like', 'B%');
            } else {
                throw new Exception("Tipo de documento no válido: " . $tipo_documento_a_modificar);
            }
        }
        // si la consulta no devuelve resultados, retornar lanzar error
        if ($query->count() === 0) {
            throw new Exception("No se encontró CPE serie para tienda, crea la serie en la configuración menu Facturación.");
        }
        return $query->first();
    }
    // aumentar correlativo
    public function increase_CpeSerie(CpeSerie $cpe_serie): CpeSerie
    {
        // Aumentar el correlativo del CPE
        $cpe_serie->correlativo++;
        $cpe_serie->save();
        return $cpe_serie;
    }
    // actulizar stock de producto en tienda en venta y devolucion para venta se utiliza otro este es solo para devoluciones 
    //este medto es lento 1.60 segundos
    public function updateStockProductoTienda($producto_id, $tienda_id, $tipo_transaccion, $cantidad): void
    {
        // Actualizar el stock del producto en la tienda
        // tipo_transaccion: 'venta' o 'devolucion'
        if ($tipo_transaccion === 'venta') {
            $this->decreaseStock($producto_id, $tienda_id, $cantidad);
        } elseif ($tipo_transaccion === 'anulacion') {
            $this->increaseStock($producto_id, $tienda_id, $cantidad);
        } elseif ($tipo_transaccion === 'nota_credito') {
            $this->increaseStock($producto_id, $tienda_id, $cantidad);
        } else {
            throw new Exception('Tipo de transacción no soportado: ' . $tipo_transaccion);
        }
    }
    // disminuir stock
    private function decreaseStock($producto_id, $tienda_id, $cantidad): void
    {
        $producto = Producto::find($producto_id);
        if ($producto) {
            $stock = $producto->stockEnTienda($tienda_id);
            //if ($stock >= $cantidad) {
            $producto->tiendas()->updateExistingPivot($tienda_id, ['stock' => $stock - $cantidad]);
            //} else {
            //    throw new \Exception('Stock insuficiente para la venta.');  
            //} 
        } else {
            throw new \Exception('Producto no encontrado.');
        }
    }
    // aumentar stock
    private function increaseStock($producto_id, $tienda_id, $cantidad): void
    {
        $producto = Producto::find($producto_id);
        if ($producto) {
            $stock = $producto->stockEnTienda($tienda_id);
            $producto->tiendas()->updateExistingPivot($tienda_id, ['stock' => $stock + $cantidad]);
        } else {
            throw new \Exception('Producto no encontrado.');
        }
    }
    public function procesarCliente($cliente, $tipo_venta = 'local', $codigo_tipo_comprobante = null): Cliente
    {
        // si tipo_venta es exportacion, usar cliente por defecto

        if ($tipo_venta === 'exportacion') {

            // Validación de campos obligatorios para boleta de exportación
            if ($codigo_tipo_comprobante == '2' && empty($cliente['nombre']) && empty($cliente['direccion'])) {
                throw new \Exception('Para una venta de exportación se  requiere nombre y dirección del cliente.');
            }
            if ($codigo_tipo_comprobante == '1' && empty($cliente['razonSocial']) && empty($cliente['direccion'])) {
                throw new \Exception('Para una venta de exportación se requiere nombre y dirección del cliente.');
            }
            if ($codigo_tipo_comprobante == '2') {
                $nombreCliente = trim(strtolower($cliente['nombre']));
            }
            if ($codigo_tipo_comprobante == '1') {
                $nombreCliente = trim(strtolower($cliente['razonSocial']));
            }

            // Normalizar nombre para evitar duplicados por espacios o mayúsculas

            // Guardar o actualizar cliente
            return Cliente::updateOrCreate(
                ['nombre' => $nombreCliente], // Condición de búsqueda
                [
                    'nombre' => $nombreCliente,
                    'tipo_documento_identidad' => 0,
                    'direccion' => $cliente['direccion'],
                    'numero_documento_identidad' => "BUSINESS-ID", // RUC por defecto
                ]
            );
        }


        if ($cliente === null || empty($cliente)) {
            return Cliente::find(1); // Cliente predeterminado (ej. "Cliente final")
        }

        $tipoDocumento = $cliente['tipo_documento'] ?? null;

        // Si es boleta (tipo 1) y datos vacíos, usar cliente por defecto
        if ($tipoDocumento == 1 && (
            empty($cliente['nombre']) ||
            empty($cliente['direccion']) ||
            empty($cliente['dni'])
        )) {
            return Cliente::find(1); // Cliente predeterminado (ej. "Cliente final")
        }

        // Si es factura (tipo 6) pero faltan datos, lanzar error
        if ($tipoDocumento == 6 && (
            empty($cliente['razonSocial']) ||
            empty($cliente['direccion']) ||
            empty($cliente['ruc'])
        )) {
            throw new \Exception('Para emitir una factura se requiere RUC, nombre y dirección del cliente.');
        }

        // Procesar según tipo de documento del cliente
        if ($tipoDocumento == 1) { // DNI
            return Cliente::updateOrCreate(
                ['numero_documento_identidad' => $cliente['dni']],
                [
                    'nombre' => $cliente['nombre'],
                    'tipo_documento_identidad' => 1,
                    'direccion' => $cliente['direccion'],
                ]
            );
        }

        if ($tipoDocumento == 6) { // RUC
            return Cliente::updateOrCreate(
                ['numero_documento_identidad' => $cliente['ruc']],
                [
                    'nombre' => $cliente['razonSocial'],
                    'tipo_documento_identidad' => 6,
                    'direccion' => $cliente['direccion'],
                ]
            );
        }

        // Si no se cumple ninguna de las anteriores, usar cliente por defecto
        return Cliente::find(1);
    }


    /**
     * Actualiza el stock de productos en una tienda.
     *
     * @param int $tienda_id ID de la tienda.
     * @param array $productos_cantidades Array asociativo con producto_id como clave y cantidad como valor.
     * @
     * param string $tipo_transaccion Tipo de transacción: 'venta', 'anulacion' o 'nota_credito'.
     * @throws \Exception Si el tipo de transacción no es válido.
     * @throws \Exception Si no se proporcionan productos o cantidades.
     * @return void
     */
    // Este método actualiza el stock de productos en una tienda según el tipo de transacción
    // 'venta', 'anulacion' o 'nota_credito'. Utiliza una consulta SQL para actualizar
    // múltiples productos en una sola operación, lo que mejora el rendimiento en comparación
    // con actualizaciones individuales.
    // Se espera que el array $productos_cantidades tenga la forma ['producto_id' => cantidad, ...].
    // El método lanza una excepción si el tipo de transacción no es válido o si no se proporcionan productos o cantidades.
    // Este método es más eficiente que el anterior porque utiliza una sola consulta SQL para actualizar
    // todos los productos en lugar de hacer múltiples consultas individuales.
    public function actualizarStockProductos(int $tienda_id, array $productos_cantidades, string $tipo_transaccion = 'venta'): void
    {
        if (!in_array($tipo_transaccion, ['venta', 'anulacion', 'nota_credito'])) {
            throw new \Exception("Tipo de transacción no soportado: $tipo_transaccion");
        }

        if (empty($productos_cantidades)) {
            return;
        }

        $cases = '';
        $ids = [];

        foreach ($productos_cantidades as $producto_id => $cantidad) {
            $id = (int) $producto_id;
            $cantidad = (float) $cantidad;
            $ids[] = $id;

            if (in_array($tipo_transaccion, ['anulacion', 'nota_credito'])) {
                $cases .= "WHEN producto_id = {$id} THEN stock + {$cantidad} ";
            } else {
                $cases .= "WHEN producto_id = {$id} THEN stock - {$cantidad} ";
            }
        }

        $idsList = implode(',', $ids);

        DB::update("
            UPDATE producto_tienda
            SET stock = CASE 
                {$cases}
                ELSE stock
            END
            WHERE tienda_id = ? AND producto_id IN ({$idsList})
        ", [$tienda_id]);
    }

    public function getNombreComprobante($codigo)
    {
        switch ($codigo) {
            case '01':
                return 'Factura';
            case '03':
                return 'Boleta';
            case '12':
                return 'Ticket';
            case '07':
                return 'Nota de Crédito';
            case '08':
                return 'Nota de Débito';
            default:
                return $codigo; // Devuelve el código original si no coincide
        }
    }
}
