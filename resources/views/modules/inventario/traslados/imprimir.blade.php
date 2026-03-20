<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Traslado - {{ $traslado->codigo }}</title>
    <style>
        body { font-family: 'Helvetica', sans-serif; font-size: 12px; color: #333; }
        .voucher { width: 100%; max-width: 800px; margin: auto; border: 1px solid #eee; padding: 20px; }
        .header { display: flex; justify-content: space-between; border-bottom: 2px solid #007bff; padding-bottom: 10px; margin-bottom: 20px; }
        .header h1 { color: #007bff; margin: 0; font-size: 24px; }
        .codigo { font-size: 18px; font-weight: bold; color: #555; }
        .info-section { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .info-box { width: 48%; border: 1px solid #ddd; padding: 10px; border-radius: 4px; }
        .info-box h3 { margin-top: 0; font-size: 14px; border-bottom: 1px solid #ddd; padding-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #f8f9fa; border-bottom: 2px solid #dee2e6; padding: 10px; text-align: left; }
        td { border-bottom: 1px solid #eee; padding: 10px; }
        .footer { margin-top: 30px; text-align: center; color: #777; font-size: 10px; }
        .signature { margin-top: 50px; display: flex; justify-content: space-around; }
        .sig-box { width: 220px; border-top: 1px solid #333; text-align: center; padding-top: 5px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            .voucher { border: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right; margin-bottom: 10px; max-width: 800px; margin: 10px auto;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; border-radius: 4px; font-size: 14px;">Imprimir Comprobante</button>
    </div>

    <div class="voucher">
        <div class="header">
            <div>
                <h1>ALMACÉN SV</h1>
                <p>Comprobante de Traslado de Mercadería</p>
            </div>
            <div class="codigo">
                N° {{ $traslado->codigo }}
                <p style="font-size: 12px; font-weight: normal; margin-top: 5px;">Fecha: {{ $traslado->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        <div class="info-section">
            <div class="info-box">
                <h3>TIENDA ORIGEN</h3>
                <p><strong>Nombre:</strong> {{ $traslado->tiendaOrigen->nombre }}</p>
                <!-- <p><strong>Ubicación:</strong> {{ $traslado->tiendaOrigen->direccion ?? 'N/A' }}</p> -->
            </div>
            <div class="info-box">
                <h3>TIENDA DESTINO</h3>
                <p><strong>Nombre:</strong> {{ $traslado->tiendaDestino->nombre }}</p>
                <!-- <p><strong>Ubicación:</strong> {{ $traslado->tiendaDestino->direccion ?? 'N/A' }}</p> -->
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>Producto</th>
                    <th>Código de Barras</th>
                    <th style="text-align: center;">Cantidad</th>
                </tr>
            </thead>
            <tbody>
                @php $count = 1; @endphp
                @foreach($traslado->items as $item)
                @if($item->tipo == 4) {{-- Solo mostramos la salida de origen para el detalle del traslado --}}
                <tr>
                    <td>{{ $count++ }}</td>
                    <td>{{ $item->producto->nombre }}</td>
                    <td>{{ $item->producto->codigo_barras }}</td>
                    <td style="text-align: center;"><strong>{{ $item->cantidad_reducida }}</strong></td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>

        @if($traslado->comentario)
        <div style="margin-top: 20px; padding: 10px; background: #fdfdfe; border: 1px dashed #ddd;">
            <strong>Comentario:</strong> {{ $traslado->comentario }}
        </div>
        @endif

        <div class="signature">
            <div class="sig-box">Entrega Conforme<br><small>(Almacén Origen)</small></div>
            <div class="sig-box">Recibe Conforme<br><small>(Almacén Destino)</small></div>
        </div>

        <div class="footer">
            <p>Generado por: {{ $traslado->user->name }} | {{ now()->format('d/m/Y H:i:s') }}</p>
            <p>Este documento es un comprobante interno de movimiento de inventario.</p>
        </div>
    </div>
</body>
</html>
