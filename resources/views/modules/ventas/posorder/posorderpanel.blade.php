<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas /> </x-slot>

    <x-slot name="pagetitle">Visor de ventas por tienda <button class="btn btn-select-fecha bg-xsuccess" type="button"
            onclick="Selecionarfecha()"><span><i class="fas fa-calendar-alt "></i> Filtro: Hoy</span></button>
    </x-slot>
    <x-modalFechas></x-modalFechas>

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
                <div class="{{ $columnClass }} mb-4">
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
                                            <th class="text-right">transf.</th>
                                            <th class="text-right">subTot</th>
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
                                                <td>{{ $numeracion++ }}</td>

                                                <td><a
                                                        href="{{ route('posorder.show', $order->id) }}">{{ $order->serie }}-{{ $order->order_number }}</a>
                                                </td>
                                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('H:m') }}</td>
                                                <td class="text-right">
                                                    {{ $efectivo > 0 ? '' . number_format($efectivo, 2) : '' }}</td>
                                                <td class="text-right">
                                                    {{ $yape > 0 ? '' . number_format($yape, 2) : '' }}</td>
                                                <td class="text-right">
                                                    {{ $tarjeta > 0 ? '' . number_format($tarjeta, 2) : '' }}</td>
                                                <td class="text-right">
                                                    {{ $transferencia > 0 ? '' . number_format($transferencia, 2) : '' }}
                                                </td>
                                                <td class="text-right">{{ number_format($order->total_amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>

                                </table>
                            </div>
                        </div>
                        <div class="card-footer text-right text-bold text-xaccent">

                            Total: {{ number_format($tienda->posOrders->sum('total_amount'), 2) }}

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
                    </div>
                </div>
                @php $numeracion = 1; @endphp
            @endforeach
        </div>

    @endif

</x-admin-layout>
<script>
    $(document).ready(function() {
        // Aquí puedes inicializar tu tabla si es necesario
        // Por ejemplo, si estás usando DataTables:
        // table = $('#yourTableId').DataTable();
        function cargarTabla(fechaInicio, fechaFin) {
            // Aquí puedes implementar la lógica para cargar la tabla con los datos filtrados por fecha
            // Por ejemplo, usando AJAX para obtener los datos del servidor y luego actualizar la tabla
            console.log("Cargando tabla desde " + fechaInicio + " hasta " + fechaFin);
            // Implementa tu lógica de carga de tabla aquí

        }
    });

   function cargarTabla(fechaInicio = "", fechaFin = "") {
      window.location.href = `/ventas/visor/posorder/${fechaInicio}/${fechaFin}`;
       
  }
</script>

  
