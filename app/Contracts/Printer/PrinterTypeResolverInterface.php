<?php

namespace App\Contracts\Printer;

use App\DTOs\Printer\PrinterConfigDTO;
use App\DTOs\Printer\PrinterDTO;

/**
 * Contrato para resolver la configuración de impresión
 * a partir de la lista de impresoras disponibles.
 */
interface PrinterTypeResolverInterface
{
    /**
     * Determina el tipo de impresión y la impresora a usar.
     *
     * @param  PrinterDTO[] $printers
     * @return PrinterConfigDTO
     */
    public function resolve(array $printers): PrinterConfigDTO;
}
