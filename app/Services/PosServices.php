<?php
namespace App\Services;

use App\Models\Facturacion\Cpe;
use App\Models\Facturacion\CpeSerie;
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
        
    }