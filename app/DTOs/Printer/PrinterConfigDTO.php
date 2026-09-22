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
        $data = [
            'print_type'   => $this->printType,
        ];

        // Solo actualizar el nombre si no es nulo (así no borramos el anterior si no se detectó nada)
        if ($this->printerName !== null) {
            $data['printer_name'] = $this->printerName;
        }

        // Solo actualizar la IP si no es nula (así conservamos la IP guardada en BD)
        if ($this->printerIp !== null) {
            $data['printer_ip'] = $this->printerIp;
        }

        return $data;
    }
}
