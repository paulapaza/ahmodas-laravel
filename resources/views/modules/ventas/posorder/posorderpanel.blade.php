<x-admin-layout>
    <div id="echoStatus" class="d-none"></div>
    @vite(['resources/js/app.js'])
    <x-slot name="menu">
        <x-menuVentas /> </x-slot>

    <x-slot name="pagetitle">
        <div class="d-flex justify-content-start align-items-center mb-2">
            Visor de ventas por tienda 
            <button 
                class="btn btn-select-fecha bg-xsuccess ml-2"
                type="button" 
                onclick="Selecionarfecha()"
            >
                <span>
                    <i class="fas fa-calendar-alt " id="filter"></i> 
                    Filtro: Hoy
                </span>
            </button>
            <div class="form-group d-inline-block mb-0 ml-2">
                <select id="selectColumnas" style="width: 115px;" class="form-control">
                    <option value=""># columnas</option>
                    <option value="4">4 columnas</option>
                    <option value="3">3 columnas</option>
                    <option value="2">2 columnas</option>
                    <option value="1">1 columna</option>
                </select>
            </div>
            <div class="form-group d-inline-block mb-0 ml-2">
                <select class="form-control" style="width: 135px;" id="usuario_id">
                    <option value="">vendedores</option>

                    @foreach ($usuarios as $u)
                        <option value="{{ $u->id }}"
                            {{ $vendedor_id == $u->id ? 'selected' : '' }}>
                            {{ $u->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-check d-inline-block ml-2">
                <input class="form-check-input" type="checkbox" id="playSound">
                <label class="form-check-label" for="playSound">
                    Sonido de notificación
                </label>
            </div>
        </div>
    </x-slot>
    <div class="panel-svp">
        <x-modalFechasPanel />
    </div>

    @php $numeracion = 1; @endphp

    {{-- Asegurarse de que $tiendas esté definido --}}
    @if ($alltiendas->count() > 1)
        @php
            $totalTiendas = $alltiendas->count();
            // Calcular el ancho de cada columna basado en el número de tiendas
            $columnClass = '';
            if ($totalTiendas == 1) {
                $columnClass = 'col-12';
            } elseif ($totalTiendas == 2) {
                $columnClass = 'col-md-6 col-12';
            } elseif ($totalTiendas == 3) {
                $columnClass = 'col-lg-4 col-md-6 col-12';
            } else {
                // Para más de 3 tiendas, usar 3 columnas en pantallas grandes
                $columnClass = 'col-lg-4 col-md-6 col-12';
            }
        @endphp

        <div class="row">
            @foreach ($alltiendas as $tienda)
                <div class="{{ $columnClass }} mb-4 pos-initial-class">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0 text-xaccent text-bold">
                                <icon class="fas fa-store"></icon> {{ $tienda->nombre }}
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>#</th>

                                            <th>Nro Doc</th>
                                            <th>Hora</th>
                                            <th class="text-right">efect.</th>
                                            <th class="text-right">yape</th>
                                            <th class="text-right">tarje.</th>
                                            {{-- <th class="text-right">transf.</th> --}}
                                            <th class="text-right">subTot</th>
                                            <th class="text-right">vendedor</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        {{-- Mostrar las órdenes de la tienda actual --}}
                                        @php
                                            $totalEfectivo = 0;
                                            $totalTarjeta = 0;
                                            $totalYape = 0;
                                            $totalTransferencia = 0;
                                            $totalGeneral = 0;
                                        @endphp
                                        @foreach ($tienda->posOrders as $order)
                                            @php
                                                // si el estado de la orden es 'anulado' o 'cancelado', no mostrar la línea
                                                if ($order->estado == 'anulado' || $order->estado == 'cancelado') {
                                                    continue;
                                                }
                                                // Inicializar montos de métodos de pago
                                                $efectivo = 0;
                                                $tarjeta = 0;
                                                $yape = 0;
                                                $transferencia = 0;

                                                // Recorrer los pagos de la orden
                                                foreach ($order->payments as $payment) {
                                                    switch (strtolower($payment->payment_method)) {
                                                        case 'efectivo':
                                                            $efectivo += $payment->amount;
                                                            break;
                                                        case 'tarjeta':
                                                            $tarjeta += $payment->amount;
                                                            break;
                                                        case 'yape':
                                                            $yape += $payment->amount;
                                                            break;
                                                        case 'transferencia':
                                                            $transferencia += $payment->amount;
                                                            break;
                                                    }
                                                }
                                                // Sumar a los totales
                                                $totalEfectivo += $efectivo;
                                                $totalTarjeta += $tarjeta;
                                                $totalYape += $yape;
                                                $totalTransferencia += $transferencia;
                                                $totalGeneral += $order->total_amount;
                                            @endphp
                                            <tr>
                                                <td>
                                                    {{ $numeracion++ }}
                                                </td>
                                                <td>
                                                    <a href="{{ route('posorder.show', $order->id) }}">
                                                        {{ $order->serie }}-{{ $order->order_number }}
                                                    </a>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('H:i') }}</td>
                                                <td class="text-right">
                                                    {{ $efectivo > 0 ? '' . number_format($efectivo, 2) : '' }}</td>
                                                <td class="text-right">
                                                    {{ $yape > 0 ? '' . number_format($yape, 2) : '' }}</td>
                                                <td class="text-right">
                                                    {{ $tarjeta > 0 ? '' . number_format($tarjeta, 2) : '' }}</td>
                                                {{-- <td class="text-right">
                                                    {{ $transferencia > 0 ? '' . number_format($transferencia, 2) : '' }}
                                                </td> --}}
                                                <td class="text-right">{{ number_format($order->total_amount, 2) }}
                                                </td>
                                                <td class="text-right">
                                                    {{ $order->user->name ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-right text-bold text-xaccent">

                            @php
                                $totalCompletado = $tienda->posOrders
                                    ->whereIn('estado', ['completado', 'completed'])
                                    ->sum('total_amount');
                            @endphp
                            Total: {{ number_format($totalCompletado, 2) }}

                            <div class="row mt-2">
                                <div class="col-6">
                                    <small class="text-muted">Tarjeta:</small>
                                    <strong> {{ number_format($totalTarjeta, 2) }}</strong>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">Total Efectivo:</small>
                                    <strong class="text-success"> {{ number_format($totalEfectivo, 2) }}</strong>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="text-muted">Transferencia:</small>
                                    <strong> {{ number_format($totalTransferencia, 2) }}</strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Yape:</small>
                                    <strong> {{ number_format($totalYape, 2) }}</strong>
                                </div>

                            </div>
                        </div>

                        {{-- SECCION DE DEVOLUCIONES --}}
                        @if($tienda->devoluciones && $tienda->devoluciones->count() > 0)
                            <div class="card-footer bg-light" style="border-top: 2px solid #ffc107;">
                                <h6 class="text-warning text-bold mb-2"><i class="fas fa-exchange-alt"></i> Cambios y Devoluciones</h6>
                                <div class="table-responsive">
                                    <table class="table table-sm mb-0" style="font-size: 0.8rem; background-color: #fff;">
                                        <thead>
                                            <tr>
                                                <th>Hora</th>
                                                <th>Vendedor</th>
                                                <th>Tipo</th>
                                                <th>Resumen</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tienda->devoluciones as $dev)
                                                <tr>
                                                    <td>{{ \Carbon\Carbon::parse($dev->created_at)->format('H:i') }}</td>
                                                    <td>{{ $dev->user ? $dev->user->name : 'N/A' }}</td>
                                                    <td>
                                                        @if($dev->tipo_movimiento === 'cambio')
                                                            <span class="badge badge-warning">Cambio</span>
                                                        @else
                                                            <span class="badge badge-info">Solo Dev.</span>
                                                        @endif
                                                    </td>
                                                    <td style="line-height: 1.2;">
                                                        @php
                                                            $devueltos = [];
                                                            $nuevos = [];
                                                            foreach($dev->detalles as $d) {
                                                                $nombre = $d->producto ? $d->producto->nombre : 'Prod. Eliminado';
                                                                $subtotal = number_format($d->subtotal, 2);
                                                                $texto = "{$d->cantidad}x {$nombre} <small class='text-muted'>(S/ {$subtotal})</small>";
                                                                if ($d->tipo_item === 'devuelto') {
                                                                    $devueltos[] = "<span class='text-danger'>← {$texto}</span>";
                                                                } else {
                                                                    $nuevos[] = "<span class='text-success'>→ {$texto}</span>";
                                                                }
                                                            }
                                                            $html = count($devueltos) > 0 ? implode('<br>', $devueltos) : '';
                                                            if (count($nuevos) > 0) {
                                                                if ($html !== '') $html .= '<hr style="margin:2px 0; border-color: #eee;">';
                                                                $html .= implode('<br>', $nuevos);
                                                            }
                                                        @endphp
                                                        {!! $html !!}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                @php $numeracion = 1; @endphp
            @endforeach
        </div>

    @endif
    <audio id="audioNuevaVenta" preload="auto">
        <source src="/audio/nueva-venta.wav" type="audio/wav">
    </audio>
</x-admin-layout>



<script>
    const audioVenta = document.getElementById('audioNuevaVenta');
    const checkbox = document.getElementById('playSound');
    // guardar el checkbox en el localStorage
    checkbox.checked = localStorage.getItem('playSound') === 'true';


    

    // Al marcar o desmarcar el checkbox
    checkbox.addEventListener('change', () => {
        const isChecked = checkbox.checked;
        // Guardar el estado en localStorage
        localStorage.setItem('playSound', isChecked);
        console.log("Checkbox playSound cambiado a:", isChecked);

        if (isChecked) {
            // Desbloquear audio con interacción
            audioVenta.play().then(() => {
                audioVenta.pause();
                audioVenta.currentTime = 0;
                console.log("Audio desbloqueado");
            }).catch((e) => {
                console.warn("No se pudo desbloquear el audio:", e);
            });
        }
    });
    document.addEventListener('DOMContentLoaded', function() {
        // Verificar si Echo está disponible
        if (typeof window.Echo === 'undefined') {
            console.error('Echo no está disponible');
            return;
        }



        try {
            // Escuchar el canal "ventas" y el evento VentaRealizada
            window.Echo.channel('ventas')
                .listen('.venta.realizada', (event) => {
                    console.log('✅ Evento "venta.realizada" recibido:', event);
                    console.log('Datos de la venta:', event.venta);
                    // SOLO QUIERO QUE SE RECARGE LA PAGINA CUANDO SE RECIBE UN EVENTO DE VENTA REALIZADA
                    if (event && event.venta) {
                        console.log('Recargando la página debido a una nueva venta realizada');
                        // mostrar un toaast de éxito
                        const Toast = Swal.mixin({
                            toast: true,
                            position: "top-end",
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true,
                            didOpen: (toast) => {
                                toast.onmouseenter = Swal.stopTimer;
                                toast.onmouseleave = Swal.resumeTimer;
                            }
                        });
                       
                        let total_cobrado = parseFloat(event.venta.total_amount).toFixed(2);

                        Toast.fire({
                            icon: "success",
                            title: "Nueva venta realizada",
                            html: '</b>Cobrado: <b>' + total_cobrado + ' Soles </b>',
                        });
                        // Intentar reproducir el audio precargado
                        if (document.getElementById('playSound').checked) {

                            try {
                                audioVenta.currentTime = 0;
                                audioVenta.play().catch(e => console.log('No se pudo reproducir el sonido:',
                                    e));
                            } catch (e) {
                                console.log('Audio no disponible');
                            }
                        }
                        // Recargar la página para reflejar los cambios
                        setTimeout(() => {
                            window.location.reload();
                        }, 3000);

                    } else {
                        console.warn('Evento "venta.realizada" recibido sin datos de venta');
                    }
                });
            // Log opcional del estado de conexión
            const connection = window.Echo.connector?.pusher?.connection;
            if (connection) {
                connection.bind('connected', () => console.log('🔌 Conectado a Reverb'));
                connection.bind('disconnected', () => console.warn('🔌 Desconectado de Reverb'));
                connection.bind('connecting', () => console.log('⏳ Conectando a Reverb...'));
                connection.bind('failed', () => console.error('❌ Falló conexión a Reverb'));
            }

        } catch (error) {
            console.error('Error configurando Echo:', error);
        }
    });

    $(document).ready(function() {
        const urlParts = window.location.pathname.split('/');
        const fechaInicio = urlParts[urlParts.length - 2];
        const fechaFin = urlParts[urlParts.length - 1];

        if (fechaInicio == fechaFin) {
            $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: ' + fechaInicio);

        } else {

            if (fechaInicio.includes('-') && fechaFin.includes('-')) {
                $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: ' + fechaInicio +
                    ' - ' + fechaFin);
            } else if (fechaInicio.includes('/') && fechaFin.includes('/')) {
                $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: ' + fechaInicio +
                    ' - ' + fechaFin);
            } else {
                $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: Hoy');
            }


        }

        {{-- selector de columnas --}}
        const initialClass = @json($columnClass);
        $('#selectColumnas').val('');
        $('.pos-initial-class').each(function() {
            $(this).attr('class', `${initialClass} mb-4 pos-initial-class`);
        });
        $('#selectColumnas').on('change', function() {
            const valor = $(this).val();
            let columns = '';

            if (valor == 4) columns = 'col-3'; 
            else if (valor == 3) columns = 'col-4'; 
            else if (valor == 2) columns = 'col-6'; 
            else if (valor == 1) columns = 'col-12'; 

            if (columns) {
                $('.pos-initial-class').each(function() {
                    $(this).attr('class', `${columns} mb-4 pos-initial-class`);
                });
            } else {
                $('.pos-initial-class').each(function() {
                    $(this).attr('class', `${initialClass} mb-4 pos-initial-class`);
                });
            }
        });

        // usuarios selector
        $('#usuario_id').on('change', function () {
            const fecha_inicial = @json($fecha_inicial);
            const fecha_final = @json($fecha_final);
            const vendedor_id = $(this).val() || '';

            cargarTabla(fecha_inicial, fecha_final, vendedor_id);
            console.log("Filtrando ventas por usuario ID:", fecha_inicial, fecha_final, vendedor_id);
            if (vendedor_id) {

            } else {
                console.log("Mostrando todas las ventas sin filtrar por usuario");
            }
        });
    });
    
    function cargarTabla(fechaInicio = "", fechaFin = "", userId = "") {
        window.location.href = `/ventas/visor/posorder/${fechaInicio}/${fechaFin}/${userId}`;
    }
</script>

