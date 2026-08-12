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
        // 1. Buscar impresoras que sean USB y estén Compartidas
        $usbCompartidas = array_filter($printers, fn($p) => $p->usb && $p->compartida);
        
        if (!empty($usbCompartidas)) {
            $elegida = $this->elegirTiqueteraOPrimera($usbCompartidas);
            return new PrinterConfigDTO(
                printType:   'local',
                printerName: $elegida->nombre,
                printerIp:   null,
            );
        }

        // 2. Si no hay, buscar impresoras que estén en Red y Compartidas
        $redCompartidas = array_filter($printers, fn($p) => $p->enRed && $p->compartida);
        
        if (!empty($redCompartidas)) {
            $elegida = $this->elegirTiqueteraOPrimera($redCompartidas);
            return new PrinterConfigDTO(
                printType:   'red',
                printerName: $elegida->nombre,
                printerIp:   $elegida->ipHost,
            );
        }

        // 3. Fallback: Cualquier otra impresora disponible → pdf
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

    /**
     * Si hay varias impresoras que cumplen la regla, elige la tiquetera.
     * Si ninguna es tiquetera, devuelve la primera normal.
     * 
     * @param PrinterDTO[] $candidatas
     */
    private function elegirTiqueteraOPrimera(array $candidatas): PrinterDTO
    {
        foreach ($candidatas as $printer) {
            if ($printer->esTiquetera) {
                return $printer;
            }
        }
        
        // Si no encontró ninguna tiquetera en las candidatas, devuelve la primera
        return reset($candidatas);
    }
}
