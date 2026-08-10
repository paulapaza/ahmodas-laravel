<?php

namespace App\Services\Printer;

use App\Contracts\Printer\PrinterRepositoryInterface;
use App\Repositories\Printer\LinuxPrinterRepository;
use App\Repositories\Printer\WindowsPrinterRepository;

/**
 * PrinterRepositoryFactory
 *
 * Responsabilidad única: decidir qué repositorio usar según el SO.
 *
 * Para agregar soporte a macOS en el futuro:
 *  1. Crear MacPrinterRepository implements PrinterRepositoryInterface
 *  2. Agregar el caso 'Darwin' aquí
 *  Sin tocar nada más.
 */
class PrinterRepositoryFactory
{
    public function make(): PrinterRepositoryInterface
    {
        return match (PHP_OS_FAMILY) {
            'Windows' => new WindowsPrinterRepository(),
            default   => new LinuxPrinterRepository(),  // Linux, Darwin, etc.
        };
    }
}
