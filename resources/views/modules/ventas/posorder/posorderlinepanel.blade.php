<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas /> </x-slot>

    <x-slot name="pagetitle">
        <div class="d-flex justify-content-start align-items-center mb-2">
            Visor de detalle ventas por tienda:
            <button 
                class="btn btn-select-fecha bg-xsuccess ml-2" 
                type="button" onclick="Selecionarfecha()"
            >
                <span><i class="fas fa-calendar-alt "></i> 
                Filtro: Hoy
                </span>
            </button>
            <div class="form-group mb-0 ml-2">
                <select id="selectColumnas" class="form-control">
                    <option value=""># de columnas</option>
                    <option value="4">4 columnas</option>
                    <option value="3">3 columnas</option>
                    <option value="2">2 columnas</option>
                    <option value="1">1 columna</option>
                </select>
            </div>
        </div>
    </x-slot>
    <x-modalFechasPanel />

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
               // $columnClass = 'col-lg-4 col-md-6 col-12';
               $columnClass = 'col-md-6 col-12';
            } else {
                // Para más de 3 tiendas, usar 3 columnas en pantallas grandes
                //$columnClass = 'col-md-6 col-12';
                $columnClass = 'col-12 col-md-6 col-xl-4';
            }
        @endphp

        <div class="row">
            @foreach ($alltiendas as $tienda)
                <div class="{{ $columnClass }} mb-4 pos-initial-class">
                    <div class="card h-100">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 text-xaccent text-bold">
                                    <icon class="fas fa-store"></icon> {{ $tienda->nombre }}
                                </h5>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="showProducto{{ $tienda->id }}" checked
                                           onchange="toggleProductColumn(this, {{ $tienda->id }})">
                                    <label class="form-check-label text-sm" for="showProducto{{ $tienda->id }}">
                                         Ver Nombre Producto
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-sm mb-0" data-tienda-id="{{ $tienda->id }}">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            {{-- <th>Tipo Doc.</th> --}}
                                            <th>Nro Doc</th>
                                            <th>Hora</th>
                                            <th class="producto-column">Producto</th>
                                            <th>Alias</th>
                                            <th class="text-center">Can.</th>
                                            <th>Pre/U</th>
                                             @can('ver-visor-ventas-detalle-ganacia')
                                            <th>Ganan.</th><!-- Cambié 'Ganancia' a 'Ganan.' para que quepa mejor -->
                                             @endcan
                                            <th>subtotal</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $gananciaTotal = 0;
                                            $numeracion = 1;
                                        @endphp
                                        {{-- Mostrar las órdenes de la tienda actual --}}
                                        @foreach ($tienda->posOrders as $order)
                                            @php $ganancia = 0; @endphp
                                            @foreach ($order->orderLines as $line)
                                               @php 
                                                    // si el estado de la orden es 'anulado' o 'cancelado', no mostrar la línea
                                                    if ($order->estado == 'anulado' || $order->estado == 'cancelado') {
                                                        continue;
                                                    }
                                                    $lineGanancia = $line->quantity * ($line->price - $line->producto->costo_unitario);
                                                    $gananciaTotal += $lineGanancia;
                                                @endphp
                                                {{-- Solo mostrar líneas con ganancia positiva --}}
                                                <tr>
                                                    <td>{{ $numeracion++ }}</td>
                                                    {{--  <td>{{ $order->tipo_comprobante }}</td> --}}
                                                    <td>{{ $order->serie }}-{{ $order->order_number }}</td>
                                                    <td>{{ \Carbon\Carbon::parse($order->order_date)->format('H:i') }}
                                                    </td>
                                                    <td class="producto-column">{{ $line->producto->nombre }}</td>
                                                    <td>{{ $line->producto->alias }}</td>
                                                    <td class="text-center">{{ $line->quantity }}</td>
                                                    <td class="text-right">{{ number_format($line->price, 2) }}</td>
                                                    @can('ver-visor-ventas-detalle-ganacia')
                                                    <td class="text-right">
                                                        @if ($lineGanancia > 0)
                                                            {{ number_format($lineGanancia, 2) }}
                                                        @else
                                                            <span class="text-danger">0.00</span>
                                                        @endif
                                                    </td>
                                                    @endcan
                                                        {{-- Cambié 'Ganancia' a 'Ganan.' para que quepa mejor --}}
                                                    <td class="text-right">{{ number_format($line->subtotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                            @php $ganancia += $ganancia; @endphp
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
                            @php
                                $totalCompletado = $tienda->posOrders
                                    ->where('estado', 'completado')
                                    ->sum('total_amount');
                            @endphp
                            Total: {{ number_format($totalCompletado, 2) }}
                            <br>
                            @can('ver-visor-ventas-detalle-ganacia')
                            Ganancia Total: {{ number_format($gananciaTotal, 2) }}
                            @endcan
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
<script>
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
            } else if(fechaFin.includes('-') && !fechaInicio.includes('-')) {
                $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: ' + fechaInicio);
            }
            else {
                $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: Hoy');
            }


        }
        $('#modal-filter-fechas').on('click', '.filtrar-fechas', function() {
            fechaInicio = $('#fecha_inicio').val();
            fechaFin = $('#fecha_fin').val();
            cargarTabla(fechaInicio, fechaFin);
        });


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

    });

    function cargarTabla(fechaInicio = "", fechaFin = "") {
        window.location.href = `/ventas/visor/posorderline/${fechaInicio}/${fechaFin}`;

    }
    function toggleProductColumn(checkbox, tiendaId) {
        const table = document.querySelector(`table[data-tienda-id="${tiendaId}"]`);
        const productoColumns = table.querySelectorAll('.producto-column');
        
        productoColumns.forEach(column => {
            if (checkbox.checked) {
                column.style.display = '';
            } else {
                column.style.display = 'none';
            }
        });
    }

   
</script>
