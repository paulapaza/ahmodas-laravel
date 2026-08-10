<?php

namespace App\Repositories\Printer;

use App\Contracts\Printer\PrinterRepositoryInterface;
use App\DTOs\Printer\PrinterDTO;

/**
 * WindowsPrinterRepository
 *
 * Obtiene impresoras en Windows usando PowerShell:
 * - Get-Printer      → lista impresoras, estado, shared, puerto
 * - Get-PrinterPort  → IP de puertos TCP/IP
 */
class WindowsPrinterRepository implements PrinterRepositoryInterface
{
    /**
     * Devuelve todas las impresoras disponibles en Windows.
     *
     * @return PrinterDTO[]
     */
    public function getAll(): array
    {
        $output = shell_exec(
            'powershell -NoProfile -Command "Get-Printer | Select-Object Name,PrinterStatus,Shared,PortName,Default | ConvertTo-Json"'
        );

        if (empty($output)) {
            return [];
        }

        $data = json_decode($output, true);

        // Si solo hay una impresora viene como objeto, no como array
        if (isset($data['Name'])) {
            $data = [$data];
        }

        if (!is_array($data)) {
            return [];
        }

        $puertos    = $this->getPuertos();
        $impresoras = [];

        foreach ($data as $imp) {
            $portName   = $imp['PortName'] ?? '';
            $compartida = (bool) ($imp['Shared'] ?? false);
            $esUsb      = $this->isUsb($portName);
            $enRed      = $this->isEnRed($portName);
            $ipHost     = $enRed
                ? ($puertos[$portName] ?? $this->extractIpFromPort($portName))
                : null;

            $impresoras[] = new PrinterDTO(
                nombre:         $imp['Name']            ?? '',
                estado:         (string) ($imp['PrinterStatus'] ?? 'Unknown'),
                compartida:     $compartida,
                usb:            $esUsb,
                enRed:          $enRed,
                ipHost:         $ipHost,
                predeterminada: (bool) ($imp['Default'] ?? false),
            );
        }

        return $impresoras;
    }

    /**
     * Obtiene el mapa { portName => ipAddress } de los puertos TCP/IP de Windows.
     */
    private function getPuertos(): array
    {
        $output = shell_exec(
            'powershell -NoProfile -Command "Get-PrinterPort | Select-Object Name,PrinterHostAddress | ConvertTo-Json"'
        );

        if (empty($output)) return [];

        $data = json_decode($output, true);
        if (isset($data['Name'])) $data = [$data];
        if (!is_array($data)) return [];

        $puertos = [];
        foreach ($data as $p) {
            $name = $p['Name'] ?? '';
            $ip   = $p['PrinterHostAddress'] ?? '';
            if ($name && $ip) {
                $puertos[$name] = $ip;
            }
        }
        return $puertos;
    }

    /**
     * Indica si el puerto es USB.
     * En Windows los puertos USB se llaman USB001, USB002, etc.
     */
    private function isUsb(string $portName): bool
    {
        return (bool) preg_match('/^USB\d+$/i', $portName);
    }

    /**
     * Indica si el puerto corresponde a una impresora de red.
     */
    private function isEnRed(string $portName): bool
    {
        // Puertos locales o virtuales conocidos
        $locales = ['PORTPROMPT:', 'SHRFAX:', 'nul:', 'FILE:'];
        foreach ($locales as $local) {
            if (strcasecmp($portName, $local) === 0) return false;
        }

        if ($this->isUsb($portName)) return false;

        // Contiene IP en el nombre del puerto
        if (preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $portName)) return true;

        // Convención "IP_192.168.x.x"
        if (str_starts_with(strtoupper($portName), 'IP_')) return true;

        // Ruta UNC → red SMB
        if (str_starts_with($portName, '\\\\')) return true;

        return false;
    }

    /**
     * Extrae la IP directamente del nombre del puerto si está embebida.
     * Ej: "IP_192.168.1.50" → "192.168.1.50"
     */
    private function extractIpFromPort(string $portName): ?string
    {
        if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $portName, $m)) {
            return $m[1];
        }
        return null;
    }
}
