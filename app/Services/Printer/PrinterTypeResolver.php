<?php

namespace App\Services\Printer;

use App\Contracts\Printer\PrinterTypeResolverInterface;
use App\DTOs\Printer\PrinterConfigDTO;
use App\DTOs\Printer\PrinterDTO;

/**
 * PrinterTypeResolver
 *
 * Responsabilidad única: aplicar la lógica de prioridad para determinar
 * qué tipo de impresión usar según la lista de impresoras disponibles.
 *
 * Prioridad:
 *  1. USB + compartida  → print_type = "local"
 *  2. En red            → print_type = "red"
 *  3. Cualquier otra    → print_type = "pdf"
 */
class PrinterTypeResolver implements PrinterTypeResolverInterface
{
    /**
     * Resuelve la configuración de impresión a partir de la lista de impresoras.
     *
     * @param  PrinterDTO[] $printers
     */
    public function resolve(array $printers): PrinterConfigDTO
    {
        // Prioridad 1: USB + compartida → local
        foreach ($printers as $printer) {
            if ($printer->usb && $printer->compartida) {
                return new PrinterConfigDTO(
                    printType:   'local',
                    printerName: $printer->nombre,
                    printerIp:   null,
                );
            }
        }

        // Prioridad 2: En red → red
        foreach ($printers as $printer) {
            if ($printer->enRed) {
                return new PrinterConfigDTO(
                    printType:   'red',
                    printerName: $printer->nombre,
                    printerIp:   $printer->ipHost,
                );
            }
        }

        // Prioridad 3: Cualquier otra impresora disponible → pdf
        if (!empty($printers)) {
            return new PrinterConfigDTO(
                printType:   'pdf',
                printerName: $printers[0]->nombre,
                printerIp:   null,
            );
        }

        // Sin impresoras → pdf con valores nulos
        return new PrinterConfigDTO(
            printType:   'pdf',
            printerName: null,
            printerIp:   null,
        );
    }
}
