<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas />
    </x-slot>
    <x-slot name="pagetitle">Venta nro {{ $posorder->id }}</x-slot>
    <div class="bg-trama detalle-recibo">
        <div class="paper col-12 col-md-6 col-lg-5 col-xl-4 mx-auto bg-white">
            <div class="cabecera">
                <div class="row">
                    @if ($posorder->estado == 'anulado')
                        <div class="alert alert-danger w-100" role="alert">
                            <h5 class="text-center">Anulado</h5>
                        </div>
                    @endif
                    <div class="col-12 text-center">
                        <h4>{{ $posorder->tipo_comprobante == '01' ? 'Factura' : ($posorder->tipo_comprobante == '03' ? 'Boleta' : 'Ticket') }}
                            :
                            {{ $posorder->serie }}-{{ $posorder->order_number }}</h4>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Cliente: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $posorder->cliente->nombre ?? 'Consumidor final' }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Fecha: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $posorder->order_date }}
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Cajero: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $posorder->user->name }}
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
                        @foreach ($posorder->orderLines as $line)
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
                        <p class="text-bold text-right">Total {{ $posorder->moneda == 1 ? 's/. ' : '$ ' }}</p>
                    </div>
                    <div class="col-5 text-right">
                        <h3>{{ $posorder->total_amount }}</h3>
                    </div>
                </div>
                
                @if ($posorder->notasCredito->isNotEmpty())
                    <div class="row">
                        <div class="col-12">
                            <p class="text-bold">Notas de Crédito:</p>
                            @foreach ($posorder->notasCredito as $nota)
                                <p>{{ $nota->serie }}:{{ $nota->numero }} </p>
                                <p>{{ $nota->sunat_description}} </p>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="row">
                    <div class="col-12">
                        <p class="text-bold">Métodos de pago:</p>
                        @foreach ($posorder->payments as $payment)
                            <p>{{ $payment->payment_method }} : {{ $payment->amount }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
            @php
                $print_button = '<a href="#" class="btn btn-primary" onclick="window.print()">Imprimir</a>';
            @endphp
            @if ($posorder->cpe)
                @php
                    $print_button = "<a href='{$posorder->cpe->enlace_del_pdf}' target='_blank' class='btn btn-primary'>Imprimir Comprobante</a>";
                @endphp
                <div class="row">
                    <div class="col-12 text-center">
                        <p class="text-bold">Comprobante Electrónico</p>
                        <p>Estado: {{ $posorder->cpe->aceptada_por_sunat }}</p>
                        <p>Enlace: <a href="{{ $posorder->cpe->enlace }}"
                                target="_blank">{{ $posorder->cpe->enlace }}</a></p>
                        <p>XML: <a href="{{ $posorder->cpe->enlace_del_xml }}"
                                target="_blank">{{ $posorder->cpe->enlace_del_xml }}</a></p>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-4 text-center">
                        <form action="{{ route('ventas.posorder.anular.cpe', $posorder->id) }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-danger tipo_de_comprobante_a_anular={{ $posorder->cpe->tipo_de_comprobante }}">Anular
                                Venta</button>
                        </form>
                    </div>
                    <div class="col-4 text-center">
                        <form action="{{ route('ventas.posorder.notadecredito', $posorder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-warning nota" tipo_de_comprobante="3" tipo_de_comprobante_a_modificar="{{$posorder->cpe->tipo_comprobante }}">Nota de
                                Crédito</button>
                        </form>
                    </div>

                    <div class="col-4 text-center">
                        <form action="{{ route('ventas.posorder.notadebito', $posorder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-info nota" tipo_de_comprobante="4" tipo_de_comprobante_a_modificar="{{ $posorder->cpe->tipo_comprobante }}">Nota de
                                Débito</button>
                        </form>
                    </div>
                </div>
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
    });
</script>
