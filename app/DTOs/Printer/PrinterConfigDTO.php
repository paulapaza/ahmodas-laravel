<?php

namespace App\DTOs\Printer;

/**
 * PrinterConfigDTO
 *
 * Representa la configuración de impresión resuelta,
 * lista para guardar en el modelo User.
 * Es inmutable: se crea una vez y no se modifica.
 */
readonly class PrinterConfigDTO
{
    public function __construct(
        public string  $printType,    // "local" | "red" | "pdf"
        public ?string $printerName,  // nombre de la impresora o null
        public ?string $printerIp,    // IP de la impresora (solo en red) o null
    ) {}

    /**
     * Convierte el DTO a array para usar con User::update().
     */
    public function toArray(): array
    {
        return [
            'print_type'   => $this->printType,
            'printer_name' => $this->printerName,
            'printer_ip'   => $this->printerIp,
        ];
    }
}
