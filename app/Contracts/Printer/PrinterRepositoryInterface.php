<?php

namespace App\Contracts\Printer;

use App\DTOs\Printer\PrinterDTO;

/**
 * Contrato para obtener la lista de impresoras del sistema.
 *
 * Cualquier implementación (Linux, Windows, macOS) debe cumplir este contrato.
 *
 * @return PrinterDTO[]
 */
interface PrinterRepositoryInterface
{
    /**
     * Devuelve todas las impresoras disponibles en el sistema.
     *
     * @return PrinterDTO[]
     */
    public function getAll(): array;
}
