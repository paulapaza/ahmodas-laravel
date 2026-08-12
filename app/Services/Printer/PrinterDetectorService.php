<?php

namespace App\Services\Printer;

use App\Contracts\Printer\PrinterRepositoryInterface;
use App\Contracts\Printer\PrinterTypeResolverInterface;
use App\DTOs\Printer\PrinterConfigDTO;
use App\DTOs\Printer\PrinterDTO;
use Illuminate\Support\Facades\Log;

/**
 * PrinterDetectorService
 *
 * Responsabilidad única: ORQUESTAR la detección de impresoras.
 *
 * Escribe UN ÚNICO evento JSON estructurado por detección.
 * Esto permite:
 *  - Importarlo a una BD en el futuro (los campos ya están definidos)
 *  - Mostrarlo en una UI informativa
 *  - Parsearlo fácilmente con cualquier herramienta (jq, Kibana, etc.)
 */
class PrinterDetectorService
{
    private const LOG_CHANNEL = 'printer';

    public function __construct(
        private readonly PrinterRepositoryInterface   $repository,
        private readonly PrinterTypeResolverInterface $resolver,
    ) {}

    /**
     * Detecta la configuración de impresión y registra el evento.
     *
     * @param  int|null    $userId   ID del usuario que disparó la detección
     * @param  string|null $userName Nombre del usuario
     */
    public function detect(?int $userId = null, ?string $userName = null): PrinterConfigDTO
    {
        $startedAt = now();

        try {
            $printers = $this->repository->getAll();
            $config   = $this->resolver->resolve($printers);

            $this->logDetectionEvent(
                userId:    $userId,
                userName:  $userName,
                printers:  $printers,
                config:    $config,
                startedAt: $startedAt,
                duration:  defined('LARAVEL_START') ? (int) round((microtime(true) - LARAVEL_START) * 1000) : 0, // ms
            );

            return $config;
            
        } catch (\Throwable $e) {
            
            $this->logErrorEvent(
                userId:    $userId,
                userName:  $userName,
                exception: $e,
                startedAt: $startedAt,
                duration:  defined('LARAVEL_START') ? (int) round((microtime(true) - LARAVEL_START) * 1000) : 0
            );

            // Fallback seguro: Si algo explota, usar PDF para no interrumpir la venta
            return new PrinterConfigDTO(
                printType:   'pdf',
                printerName: null,
                printerIp:   null
            );
        }
    }

    /**
     * Escribe UN evento JSON estructurado con toda la información de la detección.
     *
     * Estructura del evento (espeja los campos de la migración futura):
     * {
     *   "event":           "printer_detection",
     *   "user_id":         1,
     *   "user_name":       "Juan",
     *   "os_family":       "Linux",
     *   "os":              "Linux x86_64",
     *   "duration_ms":     42,
     *   "printers_found":  2,
     *   "printers":        [ { ...PrinterDTO fields... } ],
     *   "print_type":      "local" | "red" | "pdf",
     *   "printer_name":    "HP-LaserJet",
     *   "printer_ip":      "192.168.1.50",
     *   "status":          "resolved" | "no_printers",
     *   "started_at":      "2026-08-09T18:29:00Z"
     * }
     *
     * @param PrinterDTO[] $printers
     */
    private function logDetectionEvent(
        ?int         $userId,
        ?string      $userName,
        array        $printers,
        PrinterConfigDTO $config,
        \Carbon\Carbon   $startedAt,
        int          $duration,
    ): void {
        $status = empty($printers) ? 'no_printers' : 'resolved';

        $payload = [
            // ── Identificación del evento ──────────────────────────
            'event'          => 'printer_detection',
            'started_at'     => $startedAt->toIso8601String(),
            'duration_ms'    => $duration,

            // ── Usuario ────────────────────────────────────────────
            'user_id'        => $userId,
            'user_name'      => $userName,

            // ── Entorno ────────────────────────────────────────────
            'os_family'      => PHP_OS_FAMILY,
            'os'             => PHP_OS,

            // ── Impresoras encontradas ─────────────────────────────
            'printers_found' => count($printers),
            'printers'       => array_map(
                fn(PrinterDTO $p) => [
                    'nombre'         => $p->nombre,
                    'estado'         => $p->estado,
                    'compartida'     => $p->compartida,
                    'usb'            => $p->usb,
                    'en_red'         => $p->enRed,
                    'ip_host'        => $p->ipHost,
                    'predeterminada' => $p->predeterminada,
                    'es_tiquetera'   => $p->esTiquetera,
                ],
                $printers
            ),

            // ── Resultado de la resolución ─────────────────────────
            'print_type'     => $config->printType,
            'printer_name'   => $config->printerName,
            'printer_ip'     => $config->printerIp,
            'status'         => $status,
        ];

        Log::channel(self::LOG_CHANNEL)->info('printer_detection', $payload);
    }

    /**
     * Escribe UN evento JSON estructurado para registrar un fallo crítico.
     */
    private function logErrorEvent(
        ?int           $userId,
        ?string        $userName,
        \Throwable     $exception,
        \Carbon\Carbon $startedAt,
        int            $duration,
    ): void {
        $payload = [
            'event'          => 'printer_detection_error',
            'started_at'     => $startedAt->toIso8601String(),
            'duration_ms'    => $duration,
            'user_id'        => $userId,
            'user_name'      => $userName,
            'os_family'      => PHP_OS_FAMILY,
            'os'             => PHP_OS,
            'printers_found' => 0,
            'printers'       => [],
            'print_type'     => 'pdf',
            'printer_name'   => null,
            'printer_ip'     => null,
            'status'         => 'error',
            'error_message'  => $exception->getMessage(),
            'error_file'     => $exception->getFile() . ':' . $exception->getLine(),
        ];

        Log::channel(self::LOG_CHANNEL)->error('printer_detection_error', $payload);
    }
}
