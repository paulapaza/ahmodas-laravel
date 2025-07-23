<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas />
    </x-slot>
    <x-slot name="pagetitle">Venta nro {{ $PosOrder->id }}</x-slot>
    <div class="bg-trama detalle-recibo">
        <div class="paper col-12 col-md-6 col-lg-5 col-xl-4 mx-auto bg-white">
            <div class="cabecera">
                <div class="row">
                    @if ($PosOrder->estado == 'anulado')
                        <div class="alert alert-danger w-100" role="alert">
                            <h5 class="text-center">Anulado</h5>
                        </div>
                    @endif
                    @if ($PosOrder->cpe && $PosOrder->cpe->cpeBajas && $PosOrder->cpe->cpeBajas->count() >= 1)
                        <div class="alert alert-danger w-100" role="alert">
                            <h5 class="text-center">Baja Comunicada para este decoumento</h5>
                            <p class="text-center">Estado: {{ $PosOrder->cpe->cpeBajas->last()->aceptada_por_sunat == 1 ? 'Aceptada' : 'Rechazada' }}</p>
                        </div>
                    @endif   
                    <div class="col-12 text-center">
                        <h4>{{ $PosOrder->tipo_comprobante == '01' ? 'Factura' : ($PosOrder->tipo_comprobante == '03' ? 'Boleta' : 'Ticket') }}
                            :
                            {{ $PosOrder->serie }}-{{ $PosOrder->order_number }}</h4>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Cliente: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $PosOrder->cliente->nombre ?? 'Consumidor final' }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Fecha: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $PosOrder->order_date }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Cajero: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $PosOrder->user->name }}
                    </div>
                </div>
            </div>
            <div class="cuerpo">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Can</th>
                            <th>producto</th>
                            <th>pre/uni</th>
                            <th>subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($PosOrder->orderLines as $line)
                            <tr>
                                <td>{{ $line->quantity }}</td>
                                <td>{{ $line->producto->nombre }}</td>
                                <td class="text-right">{{ $line->precio_unitario }}</td>
                                <td class="text-right">{{ $line->subtotal }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            </div>
            <hr>
            <div class="pie">
                <div class="row">
                    <div class="col-7 pt-1">
                        <p class="text-bold text-right">Total {{ $PosOrder->moneda == 1 ? 's/. ' : '$ ' }}</p>
                    </div>
                    <div class="col-5 text-right">
                        <h3>{{ $PosOrder->total_amount }}</h3>
                    </div>
                </div>
                
                @if ($PosOrder->notasCredito->isNotEmpty())
                    <div class="row">
                        <div class="col-12">
                            <p class="text-bold">Notas de Crédito:</p>
                            @foreach ($PosOrder->notasCredito as $nota)
                                <p>{{ $nota->serie }}:{{ $nota->numero }} </p>
                                <p>{{ $nota->sunat_description}} </p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <p class="text-bold">Métodos de pago:</p>
                        @foreach ($PosOrder->payments as $payment)
                            <p>{{ $payment->payment_method }} : {{ $payment->amount }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @php
                $print_button = '<a href="#" class="btn btn-primary" onclick="window.print()">Imprimir</a>';
            @endphp
            @if ($PosOrder->cpe)
                @php
                    $print_button = "<a href='{$PosOrder->cpe->enlace_del_pdf}' target='_blank' class='btn btn-primary'>Imprimir Comprobante</a>";
                @endphp
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="text-bold">Comprobante Electrónico</p>
                        <p>Estado: {{ $PosOrder->cpe->aceptada_por_sunat == 0 ? 'Pendiente' : ($PosOrder->cpe->aceptada_por_sunat == 1 ? 'Aceptada' : 'Rechazada') }} 
                            <button class="btn btn-info" id="consultarEstadoCpe" cpe_id="{{ $PosOrder->cpe->id }}">Consultar Estado</button></p>
                        <p>Enlace: <a href="{{ $PosOrder->cpe->enlace }}"
                                target="_blank">{{ $PosOrder->cpe->enlace }}</a></p>
                        <p>XML: <a href="{{ $PosOrder->cpe->enlace_del_xml }}"
                                target="_blank">{{ $PosOrder->cpe->enlace_del_xml }}</a></p>
                    </div>
                </div>
                @if ($PosOrder->cpe && $PosOrder->cpe->cpeBajas && $PosOrder->cpe->cpeBajas->count() === 0)

                <div class="row mb-3">
                    <div class="col-4 text-center">
                            <button type="submit"class="btn btn-danger"  id="comunicarBajaCpe" cpe_id="{{ $PosOrder->cpe->id }}" 
                                {{$PosOrder->cpe->cpeBajas->count() >= 1 ? 'disabled' : ''}} >
                                
                                Anular en Sunat
                            </button>
                       
                    </div>
                    <div class="col-4 text-center">
                        <form action="{{ route('ventas.posorder.notadecredito', $PosOrder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning nota" tipo_de_comprobante="3" tipo_de_comprobante_a_modificar="{{$PosOrder->cpe->tipo_comprobante }}">Nota de
                                Crédito</button>
                        </form>
                    </div>

                    <div class="col-4 text-center">
                        <form action="{{ route('ventas.posorder.notadebito', $PosOrder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info nota" tipo_de_comprobante="4" tipo_de_comprobante_a_modificar="{{ $PosOrder->cpe->tipo_comprobante }}">Nota de
                                Débito</button>
                        </form>
                    </div>
                </div>
                @endif
            @endif
            <div class="row">
                <div class="col-6">
                    {!! $print_button !!}
                </div>
                <div class="col-6 text-right">
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Regresar</a>
                </div>
            </div>
        </div>
    </div>





</x-admin-layout>

<script>
    $(document).ready(function() {
        $('.nota').on('click', function(e) {
            e.preventDefault();
            var tipo_de_comprobante = $(this).attr('tipo_de_comprobante');
            var form = $(this).closest('form');
            //abrir modal con un selector para elegir el motivo de la nota y in impitu para
            //si el tipo de comprabante ses 3
            if (tipo_de_comprobante == '3') {
                Swal.fire({
                    title: 'Emitir de la Nota de Crédito',
                    input: 'select',
                    inputOptions: {
                        '1': 'anulación de la operación',
                        '2': 'anulación por error en el RUC',
                        '3': 'corrección por error en la descripción',
                        '4': 'descuento global',
                        '5': 'descuento por ítem',
                        '6': 'devolución total',
                        '7': 'devolución por ítem',
                        '8': 'bonificación',
                        '9': 'disminución en el valor',
                        '10': 'otros conceptos',
                        '11': 'ajustes afectos al IVAP',
                        '12': 'ajustes de operaciones de exportación',
                        '13': 'ajustes - montos y/o fechas de pago'
                    },
                    inputPlaceholder: 'Seleccione un motivo',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Agregar el motivo al formulario
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'tipo_de_nota',
                            value: result.value
                        }).appendTo(form);
                       
                        // aregar el tipo de comprobante al formulario
                        let codigo_tipo_comprobante = $(this).attr('tipo_de_comprobante');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'codigo_tipo_comprobante',
                            value: codigo_tipo_comprobante
                        }).appendTo(form);
                        // Enviar el formulario
                        form.submit();
                    }
                });
                return;
            }
            // Si el tipo de comprobante es 4, mostrar un mensaje diferente


            if (tipo_de_comprobante == '4') {
                Swal.fire({
                    title: 'Emitir la Nota de Débito',
                    input: 'select',
                    inputOptions: {
                        '1': 'intereses por mora',
                        '2': 'aumento de valor',
                        '3': 'penalidades',
                        '4': 'ajustes afectos al IVAP',
                        '5': 'ajustes de operaciones de exportación'
                    },
                    inputPlaceholder: 'Seleccione un motivo',
                    showCancelButton: true,
                    confirmButtonText: 'Continuar',
                    cancelButtonText: 'Cancelar',
                    inputAttributes: {
                        'aria-label': 'Seleccione un motivo'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Agregar el motivo al formulario
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'tipo_de_nota',
                            value: result.value
                        }).appendTo(form);
                        // Agregar un input para la descripción de la nota de débito
                            // aregar el tipo de comprobante al formulario
                        let codigo_tipo_comprobante = $(this).attr('tipo_de_comprobante');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'codigo_tipo_comprobante',
                            value: codigo_tipo_comprobante
                        }).appendTo(form);

                        // Enviar el formulario
                        form.submit();
                    }
                });
                return;
            }


        });

        // Consultar estado del CPE
        $('#consultarEstadoCpe').on('click', function(e) {
            e.preventDefault();
            var cpe_id = $(this).attr('cpe_id');
            $.ajax({
                url: "{{ route('ventas.posorder.consultarEstadoCpe', '') }}/" + cpe_id,
                type: 'GET',
                success: function(response) {
                    if (response.success) {

                        Swal.fire({
                            title: 'Estado del CPE',
                            text: 'El estado del CPE es: ' + (response.estado.aceptada_por_sunat == true ? 'Aceptada' : 'No Aceptada'),
                            footer: response.estado.sunat_description || '' + '<br>' +
                                'nota: ' + response.estado.sunat_note,
                            icon: 'info'
                        });
                    }
                }
            });
        });
        // Función para comunicar baja del CPE
        $('#comunicarBajaCpe').on('click', function(e) {
            e.preventDefault();
            cpe_id = $(this).attr('cpe_id');
            Swal.fire({
                title: 'Motivo de la baja',
                input: 'text',
                inputPlaceholder: 'Ingrese el motivo de la baja',
                showCancelButton: true,
                confirmButtonText: 'Comunicar Baja',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "{{ route('comunicarBajaCpe')}}" ,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            cpe_id: cpe_id,
                            motivo: result.value
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire({
                                    title: 'Baja Comunicada',
                                    text: 'La baja del CPE se ha comunicado, revise su estado estado.',
                                    footer: response.data.sunat_description || '' + '<br>' +
                                        'nota: ' + response.data.sunat_note,
                                    icon: 'success'
                                });
                            } else {
                                Swal.fire({
                                    title: 'Error',
                                    text: response.message,
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire({
                                title: 'Error',
                                text: xhr.responseJSON.message || 'Ocurrió un error al comunicar la baja.',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });
    });
</script>
