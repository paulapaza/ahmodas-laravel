<?php

namespace App\Services;

use App\Models\Cliente;
use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
use App\Models\Inventario\Producto;
use Exception;
use Illuminate\Http\JsonResponse;

class PosServices

{
    // obtener numero de serie
    public function get_CpeSerie($tienda_id, $codigo_tipo_comprobante, $tipo_documento_a_modificar = null): ?CpeSerie
    {
        // si el codigo tipo comprobante es 07, buscar  si es para boleta o factura
        //dd ($tienda_id, $codigo_tipo_comprobante, $tipo_documento_a_modificar);
        //      1               3                           2
        if ($codigo_tipo_comprobante == '3') {
            
                if ($codigo_tipo_comprobante == 3) {
                    $tipo_de_comprobante = "07"; // Nota de crédito
                } elseif ($codigo_tipo_comprobante == 4) {
                    $tipo_de_comprobante = "08"; // Nota de débito
                } else {
                    throw new Exception("Tipo de comprobante no soportado: " . $codigo_tipo_comprobante);
                }
            if ($tipo_documento_a_modificar == '1') {
                $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', $tipo_de_comprobante)
                    ->where('tienda_id', $tienda_id)
                    ->where('serie', 'like', '%FC%') // buscar serie de boleta
                    ->where('estado', 'activo')
                    ->first();
            } else if ($tipo_documento_a_modificar = '2') {
                // si no se especifica tipo de documento, buscar el primero activo
                $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', $tipo_de_comprobante)
                    ->where('tienda_id', $tienda_id)
                    ->where('serie', 'like', '%BC%') // buscar serie de factura
                    ->where('estado', 'activo')
                    ->first();
            }
            
        }else if($codigo_tipo_comprobante == '4'){
              if ($codigo_tipo_comprobante == 3) {
                    $tipo_de_comprobante = "07"; // Nota de crédito
                } elseif ($codigo_tipo_comprobante == 4) {
                    $tipo_de_comprobante = "08"; // Nota de débito
                } else {
                    throw new Exception("Tipo de comprobante no soportado: " . $codigo_tipo_comprobante);
                }
            if ($tipo_documento_a_modificar == '1') {
                $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', $tipo_de_comprobante)
                    ->where('tienda_id', $tienda_id)
                    ->where('serie', 'like', '%FD%') // buscar serie de boleta
                    ->where('estado', 'activo')
                    ->first();
            } else if ($tipo_documento_a_modificar = '2') {
                // si no se especifica tipo de documento, buscar el primero activo
                $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', $tipo_de_comprobante)
                    ->where('tienda_id', $tienda_id)
                    ->where('serie', 'like', '%BD%') // buscar serie de factura
                    ->where('estado', 'activo')
                    ->first();
            }
        }
        else if ($codigo_tipo_comprobante == '1') {
            // buscar el CPE correspondiente al tipo de comprobante
            $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', "01") // Factura
                ->where('tienda_id', $tienda_id)
                ->where('estado', 'activo')
                ->first();
        }else if ($codigo_tipo_comprobante == '2') {
            // buscar el CPE correspondiente al tipo de comprobante
            $cpeSerie = CpeSerie::where('codigo_tipo_comprobante', "03") // Boleta
                ->where('tienda_id', $tienda_id)
                ->where('estado', 'activo')
                ->first();
        } else {
            throw new Exception("Tipo de comprobante no soportado: " . $codigo_tipo_comprobante);
        }
        
        return $cpeSerie;

        
    }
    // aumentar correlativo
    public function increase_CpeSerie($cpe_serie): CpeSerie
    {
        // Aumentar el correlativo del CPE
        $cpe_serie->correlativo++;
        $cpe_serie->save();
        return $cpe_serie;
    }
    // actulizar stock de producto en tienda en venta y devolucion
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
    public function procesarCliente($cliente): Cliente
    {
        // si el a
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
}       // obtener productos por tiend
