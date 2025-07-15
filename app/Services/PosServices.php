<?php
namespace App\Services;

use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
use App\Models\Inventario\Producto;
use Illuminate\Http\JsonResponse;

class PosServices

    {
        // obtener numero de serie
        public function get_CpeSerie($tienda_id, $codigo_tipo_comprobante): ?CpeSerie
        {
            // Obtener el CPE correspondiente al tipo de comprobante
            return CpeSerie::where('codigo_tipo_comprobante', $codigo_tipo_comprobante)
                ->where('tienda_id', $tienda_id)
                ->where('estado', 'activo')
                ->first();

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
        // obtener productos por tienda
        
}       // obtener productos por tiend
       