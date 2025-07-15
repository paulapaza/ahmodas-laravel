<x-admin-layout>
    <x-slot name="menu">
    <x-menuVentas/>  </x-slot>
     
  <x-slot name="pagetitle">Visor de detalle ventas por tienda</x-slot>

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
                        <h5 class="card-title mb-0 text-xaccent text-bold"><icon class="fas fa-store"></icon>  {{ $tienda->nombre }}</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-sm mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        {{-- <th>Tipo Doc.</th> --}}
                                        <th>Nro Doc</th>
                                        <th>Hora</th>
                                        <th>Producto</th>
                                        <th class="text-center">Can.</th>
                                        <th>Pre/U</th>
                                        <th>subtotal</th>
                                        
                                    </tr>
                                </thead>
                                <tbody>
                                    {{-- Mostrar las órdenes de la tienda actual --}}
                                    @foreach ($tienda->posOrders as $order)
                                        @foreach ($order->orderLines as $line)
                                            <tr>
                                                <td>{{ $numeracion++ }}</td>
                                               {{--  <td>{{ $order->tipo_comprobante }}</td> --}}
                                                <td>{{ $order->serie }}-{{ $order->order_number }}</td>
                                                <td>{{ \Carbon\Carbon::parse($order->order_date)->format('H:m') }}</td>
                                                <td>{{ $line->producto->nombre }}</td>
                                                <td class="text-center">{{ $line->quantity }}</td>
                                                <td class="text-right">{{ number_format($line->price, 2) }}</td>
                                                <td class="text-right">{{ number_format($line->subtotal, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                                
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right text-bold text-xaccent">
                        {{-- Si necesitas un botón para ver detalles, descomentar la siguiente línea --}}
                        {{-- <a href="{{ route('ventas.posorder.index') }}?tienda_id={{ $tienda->id }}" class="btn btn-primary btn-sm">Ver Detalles</a> --}}
                        {{-- Si necesitas un botón para ver detalles, descomentar la siguiente línea --}}
{{--                         <a href="{{ route('ventas.posorder.index') }}?tienda_id={{ $tienda->id }}" class="btn btn-primary btn-sm">Ver Detalles</a>

 --}}                  
                    Total: {{ number_format($tienda->posOrders->sum('total_amount'), 2) }}
                  </div>
                </div>
            </div>
            @php $numeracion = 1; @endphp
        @endforeach
    </div>

    {{-- Estilos adicionales para impresión --}}
    <style>
        @media print {
            .row {
                display: flex !important;
                flex-wrap: wrap !important;
            }
            
            .col-12 {
                width: 100% !important;
            }
            
            .col-md-6 {
                width: 50% !important;
            }
            
            .col-lg-4 {
                width: 33.333% !important;
            }
            
            .card {
                break-inside: avoid;
                page-break-inside: avoid;
                border: 1px solid #dee2e6 !important;
                margin-bottom: 20px !important;
            }
            
            .card-header {
                background-color: #f8f9fa !important;
                border-bottom: 1px solid #dee2e6 !important;
                padding: 10px 15px !important;
            }
            
            .table-responsive {
                overflow: visible !important;
            }
            
            .table {
                font-size: 12px !important;
            }
            
            .table th,
            .table td {
                padding: 0.4rem !important;
                border: 1px solid #dee2e6 !important;
            }
        }
        
        @media screen {
            .table-responsive {
                max-height: 850px;
            }
        }
    </style>
@endif

</x-admin-layout>