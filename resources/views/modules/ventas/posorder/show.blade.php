<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas/>
    </x-slot>
    <x-slot name="pagetitle">Venta nro {{ $posorder->id }}</x-slot>
    <div class="bg-trama detalle-recibo">
        <div class="paper col-12 col-md-6 col-lg-5 col-xl-4 mx-auto bg-white">
            <div class="cabecera">
                <div class="row">
                    @if ($posorder->estado == "anulado")
                        <div class="alert alert-danger w-100" role="alert">
                            <h5 class="text-center">Anulado</h5>
                        </div>
                    @endif
                    <div class="col-12 text-center">
                        <h4>{{ $posorder->tipo_comprobante == '01' ? 'Factura' : ($posorder->tipo_comprobante == '03' ? 'Boleta' : 'Ticket') 
                            }} :
                            {{ $posorder->serie }}-{{ $posorder->order_number }}</h4>
                    </div>
                </div>


                <div class="row">
                    <div class="col-12 col-md-3">
                        <p class="text-bold">Cliente: </p>
                    </div>
                    <div class="col-12 col-md-9">
                        {{ $posorder->cliente->nombre ?? 'Consumidor final'}}
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
                        <p class="text-bold text-right">Total s/.</p>
                    </div>
                    <div class="col-5 text-right">
                        <h3>{{ $posorder->total_amount }}</h3>
                    </div>
                </div>
               
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
                        <p>Enlace: <a href="{{ $posorder->cpe->enlace }}" target="_blank">{{ $posorder->cpe->enlace }}</a></p>
                        <p>XML: <a href="{{ $posorder->cpe->enlace_del_xml }}" target="_blank">{{ $posorder->cpe->enlace_del_xml }}</a></p>
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
    
</script>
