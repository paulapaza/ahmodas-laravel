<?php

namespace App\DTOs\Printer;

/**
 * PrinterDTO
 *
 * Representa los datos de una impresora detectada en el sistema.
 * Es inmutable: se crea una vez y no se modifica.
 */
readonly class PrinterDTO
{
    public bool $esTiquetera;

    public function __construct(
        public string  $nombre,
        public string  $estado,
        public bool    $compartida,
        public bool    $usb,
        public bool    $enRed,
        public ?string $ipHost,
        public bool    $predeterminada,
    ) {
        $isTicket = false;
        $keywords = ['POS', '4 Inch', 'Thermal', 'Receipt', 'Ticket', 'XP-', 'TM-', 'ZDesigner', 'Impresora de tickets'];
        foreach ($keywords as $keyword) {
            if (stripos($nombre, $keyword) !== false) {
                $isTicket = true;
                break;
            }
        }
        $this->esTiquetera = $isTicket;
    }
}
