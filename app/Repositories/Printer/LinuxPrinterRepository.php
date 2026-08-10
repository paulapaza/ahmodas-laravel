<?php

namespace App\Repositories\Printer;

use App\Contracts\Printer\PrinterRepositoryInterface;
use App\DTOs\Printer\PrinterDTO;

/**
 * LinuxPrinterRepository
 *
 * Obtiene impresoras en Linux usando comandos CUPS:
 * - lpstat -p  → lista impresoras y estado
 * - lpstat -v  → URI de cada impresora
 * - lpoptions  → atributos (shared, etc.)
 * - lpstat -d  → impresora predeterminada
 */
class LinuxPrinterRepository implements PrinterRepositoryInterface
{
    /**
     * Devuelve todas las impresoras registradas en CUPS.
     *
     * @return PrinterDTO[]
     */
    public function getAll(): array
    {
        $lineas = [];
        exec('lpstat -p 2>/dev/null', $lineas);

        if (empty($lineas)) {
            return [];
        }

        $uris        = $this->getUris();
        $compartidas = $this->getCompartidas();
        $default     = $this->getDefault();
        $impresoras  = [];

        foreach ($lineas as $linea) {
            // Formato: "printer NombreImpresora is idle.  enabled since..."
            if (!preg_match('/^printer\s+(\S+)\s+is\s+(\S+?)\.?(\s|$)/', $linea, $match)) {
                continue;
            }

            $nombre = $match[1];
            $uri    = $uris[$nombre] ?? '';
            $enRed  = $this->isEnRed($uri);

            $impresoras[] = new PrinterDTO(
                nombre:         $nombre,
                estado:         rtrim($match[2], '.'),
                compartida:     $compartidas[$nombre] ?? false,
                usb:            str_starts_with($uri, 'usb://'),
                enRed:          $enRed,
                ipHost:         $enRed ? $this->extractHost($uri) : null,
                predeterminada: ($default === $nombre),
            );
        }

        return $impresoras;
    }

    /**
     * Obtiene el mapa { nombre => uri } de todas las impresoras CUPS.
     */
    private function getUris(): array
    {
        $lineas = [];
        exec('lpstat -v 2>/dev/null', $lineas);

        $uris = [];
        foreach ($lineas as $linea) {
            if (preg_match('/^device for\s+(.+?):\s+(.+)$/', $linea, $m)) {
                $uris[trim($m[1])] = trim($m[2]);
            }
        }
        return $uris;
    }

    /**
     * Obtiene el mapa { nombre => bool } indicando si cada impresora está compartida.
     * Usa lpoptions -p que no requiere sudo.
     */
    private function getCompartidas(): array
    {
        $result = [];
        $lineas = [];
        exec('lpstat -p 2>/dev/null', $lineas);

        foreach ($lineas as $linea) {
            if (!preg_match('/^printer\s+(\S+)/', $linea, $m)) continue;

            $nombre = $m[1];
            $opts   = [];
            exec('lpoptions -p ' . escapeshellarg($nombre) . ' 2>/dev/null', $opts);
            preg_match('/printer-is-shared=(\w+)/', implode(' ', $opts), $sm);
            $result[$nombre] = (strtolower($sm[1] ?? 'false') === 'true');
        }

        return $result;
    }

    /**
     * Devuelve el nombre de la impresora predeterminada.
     */
    private function getDefault(): string
    {
        $lines = [];
        exec('lpstat -d 2>/dev/null', $lines);

        if (!empty($lines[0]) && preg_match('/system default destination:\s+(.+)/', $lines[0], $m)) {
            return trim($m[1]);
        }
        return '';
    }

    /**
     * Indica si la URI pertenece a una impresora de red.
     */
    private function isEnRed(string $uri): bool
    {
        foreach (['socket://', 'ipp://', 'ipps://', 'lpd://', 'smb://'] as $proto) {
            if (str_starts_with($uri, $proto)) return true;
        }
        return false;
    }

    /**
     * Extrae el host/IP de una URI de red.
     * Ej: socket://192.168.1.100:9100 → "192.168.1.100:9100"
     */
    private function extractHost(string $uri): ?string
    {
        $parsed = parse_url($uri);
        if (!$parsed || empty($parsed['host'])) return null;

        $host = $parsed['host'];
        $port = $parsed['port'] ?? null;
        return $port ? "$host:$port" : $host;
    }
}
