<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CorregirStockNegativoCommand extends Command
{
    protected $signature = 'inventario:corregir-stock-negativo {--corregir : Aplica la corrección llevando a 0 los stocks negativos}';

    protected $description = 'Reporta o corrige los registros con stock negativo en producto_tienda';

    public function handle()
    {
        $negativos = DB::table('producto_tienda as pt')
            ->join('productos as p', 'pt.producto_id', '=', 'p.id')
            ->join('tiendas as t', 'pt.tienda_id', '=', 't.id')
            ->where('pt.stock', '<', 0)
            ->select(
                'pt.id as pt_id',
                'p.id as producto_id',
                'p.codigo_barras',
                'p.nombre as producto',
                't.id as tienda_id',
                't.nombre as tienda',
                'pt.stock',
                'pt.updated_at'
            )
            ->orderBy('t.id')
            ->orderBy('p.nombre')
            ->get();

        if ($negativos->isEmpty()) {
            $this->info('¡Excelente! No hay ningún producto con stock negativo en la base de datos.');
            return 0;
        }

        $this->warn("Se encontraron {$negativos->count()} registros con stock negativo:");

        $headers = ['ID Prod', 'Código', 'Producto', 'Tienda', 'Stock Actual', 'Última Act.'];
        $rows = $negativos->map(fn($item) => [
            $item->producto_id,
            $item->codigo_barras,
            substr($item->producto, 0, 35),
            $item->tienda,
            $item->stock,
            $item->updated_at,
        ])->toArray();

        $this->table($headers, $rows);

        if (!$this->option('corregir')) {
            $this->comment('Para corregir automáticamente estos stocks a 0, ejecute:');
            $this->line('  php artisan inventario:corregir-stock-negativo --corregir');
            return 0;
        }

        if (!$this->confirm('¿Desea ajustar a 0 todos estos stocks negativos ahora?', true)) {
            $this->info('Operación cancelada.');
            return 0;
        }

        DB::beginTransaction();
        try {
            $now = now();
            $count = 0;

            foreach ($negativos as $item) {
                // Actualizar a 0 en producto_tienda
                DB::table('producto_tienda')
                    ->where('id', $item->pt_id)
                    ->update([
                        'stock' => 0,
                        'updated_at' => $now,
                    ]);

                // Registrar en historial salida_productos para trazabilidad
                DB::table('salida_productos')->insert([
                    'producto_id'       => $item->producto_id,
                    'tienda_id'         => $item->tienda_id,
                    'stock_antes'       => $item->stock,
                    'stock_despues'     => 0,
                    'cantidad_reducida' => 0,
                    'tipo'              => 3, // Ajuste/Ingreso
                    'motivo'            => 'ajuste_inventario',
                    'comentario'        => "Ajuste automático de stock negativo ({$item->stock}) a 0",
                    'created_at'        => $now,
                    'updated_at'        => $now,
                ]);

                $count++;
            }

            DB::commit();
            $this->info("¡Éxito! Se corrigieron {$count} registros de stock negativo a 0.");
            return 0;
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Error al corregir los stocks: ' . $e->getMessage());
            return 1;
        }
    }
}
