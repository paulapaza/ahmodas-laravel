<?php

/**
 * impresoras.php
 * -------------------------------------------------------
 * Detecta todas las impresoras instaladas en el sistema
 * usando CUPS (lpstat) y dispositivos USB físicos.
 *
 * Uso: php impresoras.php
 *
 * Requisitos: CUPS instalado (lpstat, lpoptions disponibles)
 * -------------------------------------------------------
 */

// ============================================================
// 1. IMPRESORAS CUPS
// ============================================================

/**
 * Obtiene el mapa { nombre => uri } de todas las impresoras CUPS.
 */
function getUrisImpresoras(): array
{
    exec('lpstat -v', $lineas);
    $uris = [];
    foreach ($lineas as $linea) {
        // Formato: "device for NombreImpresora: uri://..."
        if (preg_match('/^device for\s+(.+?):\s+(.+)$/', $linea, $m)) {
            $uris[trim($m[1])] = trim($m[2]);
        }
    }
    return $uris;
}

/**
 * Obtiene el mapa { nombre => bool } indicando si cada impresora está compartida.
 * Usa lpoptions -p <nombre> que no requiere permisos de root.
 */
function getCompartidas(): array
{
    $result = [];
    exec('lpstat -p', $lineas);
    foreach ($lineas as $linea) {
        if (preg_match('/^printer\s+(\S+)/', $linea, $m)) {
            $nombre = $m[1];
            $opts   = [];
            exec('lpoptions -p ' . escapeshellarg($nombre), $opts);
            $optsStr = implode(' ', $opts);

            preg_match('/printer-is-shared=(\w+)/', $optsStr, $sm);
            $valor           = strtolower($sm[1] ?? 'false');
            $result[$nombre] = ($valor === 'true');
        }
    }
    return $result;
}

/**
 * Determina el tipo de conexión según el esquema de la URI.
 */
function getTipoConexion(string $uri): string
{
    if (str_starts_with($uri, 'usb://'))      return 'USB';
    if (str_starts_with($uri, 'socket://'))   return 'Red (Socket/IP)';
    if (str_starts_with($uri, 'ipp://'))      return 'Red (IPP)';
    if (str_starts_with($uri, 'ipps://'))     return 'Red (IPPS seguro)';
    if (str_starts_with($uri, 'lpd://'))      return 'Red (LPD)';
    if (str_starts_with($uri, 'smb://'))      return 'Red (SMB/Windows)';
    if (str_starts_with($uri, 'cups-pdf:'))   return 'Virtual PDF';
    if (str_starts_with($uri, 'file://'))     return 'Virtual/Archivo';
    if ($uri === '/dev/null')                 return 'Virtual (null)';
    return 'Desconocido';
}

/**
 * Indica si la URI pertenece a una impresora de red.
 */
function isEnRed(string $uri): bool
{
    foreach (['socket://', 'ipp://', 'ipps://', 'lpd://', 'smb://'] as $proto) {
        if (str_starts_with($uri, $proto)) return true;
    }
    return false;
}

/**
 * Indica si la URI pertenece a una impresora USB registrada en CUPS.
 */
function isUsbCups(string $uri): bool
{
    return str_starts_with($uri, 'usb://');
}

/**
 * Extrae el host/IP y puerto de una URI de red.
 */
function getHostDesdeUri(string $uri): string
{
    $parsed = parse_url($uri);
    if (!$parsed || empty($parsed['host'])) return '';
    $host = $parsed['host'];
    $port = $parsed['port'] ?? null;
    return $port ? "$host:$port" : $host;
}

/**
 * Devuelve la lista completa de impresoras CUPS con todos sus atributos.
 */
function getImpresorasCups(): array
{
    $impresoras  = [];
    exec('lpstat -p', $lineas);
    $uris        = getUrisImpresoras();
    $compartidas = getCompartidas();

    exec('lpstat -d', $defLine);
    $default = '';
    if (!empty($defLine[0]) && preg_match('/system default destination:\s+(.+)/', $defLine[0], $dm)) {
        $default = trim($dm[1]);
    }

    foreach ($lineas as $linea) {
        if (preg_match('/^printer\s+(\S+)\s+is\s+(\S+?)\.?(\s|$)/', $linea, $match)) {
            $nombre = $match[1];
            $estado = rtrim($match[2], '.');
            $uri    = $uris[$nombre] ?? '';
            $tipo   = getTipoConexion($uri);
            $enRed  = isEnRed($uri);
            $esUsb  = isUsbCups($uri);

            $impresoras[] = [
                'nombre'          => $nombre,
                'estado'          => $estado,
                'compartida'      => ($compartidas[$nombre] ?? false) ? 'Si' : 'No',
                'uri_dispositivo' => $uri,
                'tipo_conexion'   => $tipo,
                'usb_fisico'      => $esUsb ? 'Si' : 'No',
                'en_red'          => $enRed ? 'Si' : 'No',
                'ip_host'         => $enRed ? getHostDesdeUri($uri) : 'N/A (local)',
                'predeterminada'  => ($default === $nombre) ? 'Si' : 'No',
            ];
        }
    }

    return $impresoras;
}

// ============================================================
// 2. DISPOSITIVOS USB FISICOS (sin necesidad de CUPS)
// ============================================================

/**
 * Detecta impresoras USB fisicamente conectadas leyendo /sys/bus/usb/devices.
 * La clase USB 07 corresponde a impresoras. No requiere sudo.
 */
function getImpresorasUSBSys(): array
{
    $impresoras = [];
    $base = '/sys/bus/usb/devices';

    if (!is_dir($base)) return $impresoras;

    foreach (scandir($base) as $dev) {
        if ($dev === '.' || $dev === '..') continue;
        $ruta = "$base/$dev";

        $clase = '';
        if (file_exists("$ruta/bDeviceClass")) {
            $clase = trim(file_get_contents("$ruta/bDeviceClass"));
        }

        // Si clase = 00, buscar en interfaces
        if ($clase === '00') {
            foreach (glob("$ruta/$dev:*") as $iface) {
                if (file_exists("$iface/bInterfaceClass")) {
                    $ifaceClass = trim(file_get_contents("$iface/bInterfaceClass"));
                    if ($ifaceClass === '07') { $clase = '07'; break; }
                }
            }
        }

        if ($clase !== '07') continue;

        $impresoras[] = [
            'dispositivo' => $dev,
            'fabricante'  => file_exists("$ruta/manufacturer") ? trim(file_get_contents("$ruta/manufacturer")) : 'Desconocido',
            'modelo'      => file_exists("$ruta/product")      ? trim(file_get_contents("$ruta/product"))      : 'Desconocido',
            'serial'      => file_exists("$ruta/serial")       ? trim(file_get_contents("$ruta/serial"))       : '',
            'id_vendor'   => file_exists("$ruta/idVendor")     ? trim(file_get_contents("$ruta/idVendor"))     : '',
            'id_product'  => file_exists("$ruta/idProduct")    ? trim(file_get_contents("$ruta/idProduct"))    : '',
        ];
    }

    return $impresoras;
}

/**
 * Detecta impresoras USB usando lsusb filtrando por vendors conocidos de impresoras.
 * Util como metodo alternativo cuando /sys/bus/usb no reporta clase 07.
 */
function getImpresorasUSBLsusb(): array
{
    exec('lsusb 2>/dev/null', $lineas);
    $impresoras = [];

    // Vendors USB conocidos de impresoras (ID hexadecimal)
    $vendorsImpresoras = [
        '03f0' => 'HP',
        '04a9' => 'Canon',
        '04b8' => 'Epson',
        '0924' => 'Xerox',
        '04e8' => 'Samsung',
        '0dd4' => 'Custom (POS)',
        '154f' => 'SEWOO (POS)',
        '28e9' => 'Star Micronics',
        '0519' => 'Star Micronics',
        '1fc9' => 'Citizen',
        '0a5f' => 'Zebra',
        '0896' => 'Datamax-ONeil',
        '067b' => 'Prolific (adaptador USB-Paralelo)',
    ];

    foreach ($lineas as $linea) {
        // Formato: "Bus 001 Device 004: ID abcd:1234 Fabricante Modelo"
        if (preg_match('/Bus\s+(\d+)\s+Device\s+(\d+):\s+ID\s+([0-9a-f]{4}):([0-9a-f]{4})\s+(.+)/i', $linea, $m)) {
            $idVendor = strtolower($m[3]);
            if (isset($vendorsImpresoras[$idVendor])) {
                $impresoras[] = [
                    'bus'             => $m[1],
                    'device'          => $m[2],
                    'id_vendor'       => $idVendor,
                    'id_product'      => strtolower($m[4]),
                    'descripcion'     => trim($m[5]),
                    'marca_detectada' => $vendorsImpresoras[$idVendor],
                ];
            }
        }
    }

    return $impresoras;
}

// ============================================================
// 3. SALIDA
// ============================================================

$impresoras = getImpresorasCups();
$usbSys     = getImpresorasUSBSys();
$usbLsusb   = getImpresorasUSBLsusb();

echo "============================================\n";
echo "    DETECCION DE IMPRESORAS - LINUX\n";
echo "============================================\n\n";

echo "[ IMPRESORAS REGISTRADAS EN CUPS ]\n";
echo str_repeat('-', 50) . "\n";
if (empty($impresoras)) {
    echo "  (ninguna impresora registrada en CUPS)\n";
} else {
    foreach ($impresoras as $i => $imp) {
        echo "  Impresora #" . ($i + 1) . "\n";
        echo "  +- Nombre:          " . $imp['nombre']          . "\n";
        echo "  +- Estado:          " . $imp['estado']           . "\n";
        echo "  +- Compartida:      " . $imp['compartida']       . "\n";
        echo "  +- URI Dispositivo: " . $imp['uri_dispositivo']  . "\n";
        echo "  +- Tipo Conexion:   " . $imp['tipo_conexion']    . "\n";
        echo "  +- USB Fisico:      " . $imp['usb_fisico']       . "\n";
        echo "  +- En Red:          " . $imp['en_red']           . "\n";
        echo "  +- IP / Host:       " . $imp['ip_host']          . "\n";
        echo "  +- Predeterminada:  " . $imp['predeterminada']   . "\n";
        echo "\n";
    }
}

echo "[ DISPOSITIVOS USB FISICOS - /sys/bus/usb (clase 07 = Impresora) ]\n";
echo str_repeat('-', 50) . "\n";
if (empty($usbSys)) {
    echo "  (ninguna impresora USB fisica detectada)\n";
} else {
    foreach ($usbSys as $u) {
        echo "  [{$u['dispositivo']}] {$u['fabricante']} {$u['modelo']}";
        if ($u['serial']) echo " | Serial: {$u['serial']}";
        echo " | ID: {$u['id_vendor']}:{$u['id_product']}\n";
    }
}

echo "\n[ DISPOSITIVOS USB - LSUSB (vendors de impresoras conocidos) ]\n";
echo str_repeat('-', 50) . "\n";
if (empty($usbLsusb)) {
    echo "  (ninguna impresora conocida detectada via lsusb)\n";
} else {
    foreach ($usbLsusb as $u) {
        echo "  Bus {$u['bus']} Dev {$u['device']}: [{$u['id_vendor']}:{$u['id_product']}] {$u['descripcion']} ({$u['marca_detectada']})\n";
    }
}

echo "\n[ JSON COMPLETO ]\n";
echo str_repeat('-', 50) . "\n";
echo json_encode([
    'impresoras_cups'    => $impresoras,
    'usb_fisicas_sys'    => $usbSys,
    'usb_fisicas_lsusb'  => $usbLsusb,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
