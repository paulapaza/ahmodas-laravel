<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SalidaProductoService
{
  public function bulk(array $items): void
  {
    $idsProductos = collect($items)->pluck('producto_id')->unique()->values();
    $productos = DB::table('productos')
      ->whereIn('id', $idsProductos)
      ->get()
      ->keyBy('id');

    $now = now();
    $salidas = [];

    foreach ($items as $item) {
      $producto = $productos->get($item['producto_id']);

      $salidas[] = [
        'producto_id'       => $item['producto_id'],
        'tienda_id'         => $item['tienda_id'],
        'stock_antes'       => $item['stock_antes'],
        'stock_despues'     => $item['stock_despues'],
        'cantidad_reducida' => $item['cantidad_reducida'],
        'tipo'              => $item['tipo'],
        'producto_datos'    => json_encode($producto),
        'comentario'        => $item['comentario'] ?? null,
        'created_at'        => $now,
        'updated_at'        => $now,
      ];
    }

    DB::table('salida_productos')->insert($salidas);
  }

  public function create(array $item): void
  {
    $producto = DB::table('productos')->find($item['producto_id']);

    DB::table('salida_productos')->insert([
      'producto_id'       => $item['producto_id'],
      'tienda_id'         => $item['tienda_id'],
      'stock_antes'       => $item['stock_antes'],
      'stock_despues'     => $item['stock_despues'],
      'cantidad_reducida' => $item['cantidad_reducida'],
      'tipo'              => $item['tipo'] ?? null,
      'pos_order_id'      => $item['pos_order_id'] ?? null,
      'producto_datos'    => json_encode($producto) ?? null,
      'comentario'        => $item['comentario'] ?? null,
      'created_at'        => now(),
      'updated_at'        => now(),
    ]);
  }

  public function guardarHistorialSalida(int $tienda_id, array $productos_cantidades, string $nombreTipo): void
  {
    if ($nombreTipo === 'manual') $tipo = 1;
    elseif ($nombreTipo === 'venta') $tipo = 2;
    else $tipo = null;

    $productos = DB::table('producto_tienda as pt')
      ->join('productos as p', 'p.id', '=', 'pt.producto_id')
      ->where('pt.tienda_id', $tienda_id)
      ->whereIn('pt.producto_id', array_keys($productos_cantidades))
      ->select('pt.producto_id', 'pt.stock')
      ->get();

    $now = now();
    $salidas = [];

    foreach ($productos as $producto) {
      $cantidadReducida = $productos_cantidades[$producto->producto_id] ?? 0;
      $stockAntes = (int) $producto->stock;
      $stockDespues = max(0, $stockAntes - $cantidadReducida);

      $salidas[] = [
        'producto_id'       => $producto->producto_id,
        'tienda_id'         => $tienda_id,
        'stock_antes'       => $stockAntes,
        'stock_despues'     => $stockDespues,
        'cantidad_reducida' => $cantidadReducida,
        'tipo'              => $tipo,
        'producto_datos'    => null,
        'comentario'        => null,
        'created_at'        => $now,
        'updated_at'        => $now,
      ];
    }

    // Directamente registra las salidas aquí
    $this->bulk($salidas);
  }
}
