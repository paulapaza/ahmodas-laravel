<x-admin-layout>
    <x-slot name="menu">
        <x-menuVentas />
    </x-slot>
    <x-slot name="pagetitle">Control de Sincronizaciones</x-slot>

    <!-- Tarjetas de Resumen Histórico -->
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info shadow-sm">
                <div class="inner">
                    <h3>{{ $totalOrders }}</h3>
                    <p>Total Ventas Locales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success shadow-sm">
                <div class="inner">
                    <h3>{{ $totalSuccess }}</h3>
                    <p>Sincronizadas con Éxito</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger shadow-sm">
                <div class="inner">
                    <h3>{{ $totalFailed }}</h3>
                    <p>Sincronizaciones Fallidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning shadow-sm">
                <div class="inner">
                    <h3>{{ $totalPending }}</h3>
                    <p>Pendientes / Sin Intentar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Sección de Sincronización Manual por Rango de Fechas -->
    <div class="card card-primary card-outline shadow-sm">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-calendar-alt mr-1"></i> Sincronización por Rango de Fechas</h3>
        </div>
        <div class="card-body">
            <form id="form-rango-fechas">
                @csrf
                <div class="row align-items-end justify-content-center">
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="fecha_inicio" class="font-weight-bold">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <label for="fecha_fin" class="font-weight-bold">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="col-md-5 col-sm-12 mb-3 text-center text-md-left">
                        <button type="button" id="btn-calcular-stats" class="btn btn-outline-info mr-2">
                            <i class="fas fa-chart-pie mr-1"></i> Consultar Rango
                        </button>
                        <button type="button" id="btn-sincronizar-rango" class="btn btn-primary">
                            <i class="fas fa-sync-alt mr-1"></i> Sincronizar Pendientes
                        </button>
                    </div>
                </div>
            </form>

            <!-- Resultados en tiempo real del rango consultado -->
            <div id="rango-stats-panel" class="mt-4 d-none">
                <hr>
                <h5 class="mb-3 text-info"><i class="fas fa-info-circle mr-1"></i> Resumen del Rango Seleccionado</h5>
                <div class="row text-center">
                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-2 bg-light shadow-sm">
                            <span class="text-muted d-block">Ventas Totales</span>
                            <strong id="rango-total" class="h4 text-secondary">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-2 bg-light shadow-sm">
                            <span class="text-muted d-block">Sincronizadas</span>
                            <strong id="rango-success" class="h4 text-success">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-2 bg-light shadow-sm">
                            <span class="text-muted d-block">Fallidas</span>
                            <strong id="rango-failed" class="h4 text-danger">0</strong>
                        </div>
                    </div>
                    <div class="col-md-3 col-6 mb-2">
                        <div class="border rounded p-2 bg-light shadow-sm">
                            <span class="text-muted d-block">Pendientes</span>
                            <strong id="rango-pending" class="h4 text-warning">0</strong>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Detalles de Órdenes en el Rango -->
                <div class="table-responsive mt-3" style="max-height: 350px; overflow-y: auto;">
                    <table class="table table-sm table-striped table-hover table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Documento</th>
                                <th>Tienda</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                                <th>Detalle / Error</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody id="rango-orders-table-body">
                            <!-- Inyectado dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Listado de Fallos de Sincronización Registrados -->
    <div class="card card-danger card-outline shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title"><i class="fas fa-exclamation-triangle mr-1"></i> Errores de Sincronización Pendientes</h3>
            @if($failedSyncs->count() > 0)
                <button type="button" id="btn-retry-all" class="btn btn-sm btn-danger ml-auto">
                    <i class="fas fa-redo-alt mr-1"></i> Reintentar todos los fallidos
                </button>
            @endif
        </div>
        <div class="card-body">
            @if($failedSyncs->count() == 0)
                <div class="alert alert-success mb-0 text-center">
                    <h5><i class="icon fas fa-check"></i> ¡Excelente!</h5>
                    No hay fallos de sincronización pendientes en el sistema.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-striped table-hover table-valign-middle">
                        <thead>
                            <tr>
                                <th>ID Orden</th>
                                <th>Comprobante</th>
                                <th>Tienda</th>
                                <th>Fecha Venta</th>
                                <th>Monto</th>
                                <th>Mensaje de Error</th>
                                <th>Intentos</th>
                                <th>Última Modificación</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($failedSyncs as $sync)
                                <tr>
                                    <td>{{ $sync->pos_order_id }}</td>
                                    <td>
                                        @if($sync->posOrder)
                                            @php
                                                $tipo = $sync->posOrder->tipo_comprobante;
                                                $badge = 'badge-secondary';
                                                $name = 'Ticket';
                                                if ($tipo === '01') { $badge = 'badge-primary'; $name = 'Factura'; }
                                                elseif ($tipo === '03') { $badge = 'badge-success'; $name = 'Boleta'; }
                                            @endphp
                                            <span class="badge {{ $badge }}">{{ $name }}</span> 
                                            {{ $sync->posOrder->serie }}-{{ $sync->posOrder->order_number }}
                                        @else
                                            <span class="text-muted">Desconocido</span>
                                        @endif
                                    </td>
                                    <td>{{ $sync->posOrder->tienda->nombre ?? 'N/A' }}</td>
                                    <td>{{ $sync->posOrder->order_date ?? 'N/A' }}</td>
                                    <td>S/ {{ number_format($sync->posOrder->total_amount ?? 0, 2) }}</td>
                                    <td>
                                        <span class="text-danger" title="{{ $sync->error_message }}">
                                            {{ Str::limit($sync->error_message, 60) }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">{{ $sync->attempts }}</span>
                                    </td>
                                    <td>{{ $sync->updated_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button class="btn btn-xs btn-primary btn-retry" data-id="{{ $sync->id }}">
                                            <i class="fas fa-sync-alt mr-1"></i> Reintentar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $failedSyncs->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Scripts de Interacción AJAX -->
    @push('scripts')
    <script>
        $(document).ready(function() {
            // Consultar estadísticas de un rango
            $('#btn-calcular-stats').click(function() {
                const start = $('#fecha_inicio').val();
                const end = $('#fecha_fin').val();

                if (!start || !end) {
                    Swal.fire('Error', 'Seleccione un rango de fechas válido.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Cargando datos...',
                    html: 'Buscando ventas y estados de sincronización...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('ventas.sincronizaciones.stats') }}",
                    type: "GET",
                    data: {
                        fecha_inicio: start,
                        fecha_fin: end
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            $('#rango-stats-panel').removeClass('d-none');
                            $('#rango-total').text(response.stats.total);
                            $('#rango-success').text(response.stats.success);
                            $('#rango-failed').text(response.stats.failed);
                            $('#rango-pending').text(response.stats.pending);

                            let html = '';
                            if (response.orders.length === 0) {
                                html = '<tr><td colspan="8" class="text-center">No se encontraron ventas locales en este rango.</td></tr>';
                            } else {
                                response.orders.forEach(order => {
                                    let compType = 'Ticket';
                                    let badgeType = 'badge-secondary';
                                    if (order.tipo_comprobante === '01') { compType = 'Factura'; badgeType = 'badge-primary'; }
                                    else if (order.tipo_comprobante === '03') { compType = 'Boleta'; badgeType = 'badge-success'; }

                                    let statusBadge = '';
                                    let actionBtn = '';
                                    if (order.status === 'success') {
                                        statusBadge = '<span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sincronizado</span>';
                                    } else if (order.status === 'failed') {
                                        statusBadge = `<span class="badge badge-danger"><i class="fas fa-exclamation-circle mr-1"></i>Fallido (${order.attempts})</span>`;
                                        actionBtn = `<button class="btn btn-xs btn-primary btn-retry-single" data-order-id="${order.id}"><i class="fas fa-sync-alt"></i></button>`;
                                    } else {
                                        statusBadge = '<span class="badge badge-warning"><i class="fas fa-clock mr-1"></i>Pendiente</span>';
                                        actionBtn = `<button class="btn btn-xs btn-success btn-retry-single" data-order-id="${order.id}"><i class="fas fa-sync-alt"></i></button>`;
                                    }

                                    html += `
                                        <tr>
                                            <td>${order.id}</td>
                                            <td><span class="badge ${badgeType}">${compType}</span> ${order.serie}-${order.order_number}</td>
                                            <td>${order.tienda}</td>
                                            <td>${order.order_date}</td>
                                            <td>S/ ${parseFloat(order.total_amount).toFixed(2)}</td>
                                            <td>${statusBadge}</td>
                                            <td><span class="text-xs text-muted">${order.last_error || ''}</span></td>
                                            <td>${actionBtn}</td>
                                        </tr>
                                    `;
                                });
                            }
                            $('#rango-orders-table-body').html(html);
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo consultar el rango.', 'error');
                        }
                    },
                    error: function(err) {
                        Swal.close();
                        Swal.fire('Error', 'Fallo de comunicación con el servidor local.', 'error');
                    }
                });
            });

            // Sincronizar masivamente un rango
            $('#btn-sincronizar-rango').click(function() {
                const start = $('#fecha_inicio').val();
                const end = $('#fecha_fin').val();

                if (!start || !end) {
                    Swal.fire('Error', 'Seleccione un rango de fechas válido.', 'error');
                    return;
                }

                Swal.fire({
                    title: 'Sincronizando rango...',
                    html: 'Enviando ventas pendientes a la nube. Esto puede tomar unos minutos...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: "{{ route('ventas.sincronizaciones.sync') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        fecha_inicio: start,
                        fecha_fin: end
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Proceso completado',
                                text: response.message
                            }).then(() => {
                                // Refrescar estadísticas del rango e histórico
                                $('#btn-calcular-stats').click();
                            });
                        } else {
                            Swal.fire('Error', response.message || 'Error en sincronización.', 'error');
                        }
                    },
                    error: function(err) {
                        Swal.close();
                        Swal.fire('Error', 'Ocurrió un error en el servidor al sincronizar.', 'error');
                    }
                });
            });

            // Reintentar un registro de error específico (de la tabla principal de errores)
            $(document).on('click', '.btn-retry', function() {
                const id = $(this).data('id');
                const row = $(this).closest('tr');

                Swal.fire({
                    title: 'Reintentando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/ventas/sincronizaciones/retry/${id}`,
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Éxito', response.message, 'success').then(() => {
                                row.fadeOut(500, function() { $(this).remove(); });
                            });
                        } else {
                            Swal.fire('Fallo persistente', response.message, 'error').then(() => {
                                location.reload(); // Recarga para actualizar intentos
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error de red al reintentar.', 'error');
                    }
                });
            });

            // Reintentar/Sincronizar una orden individual de la tabla del rango de fechas
            $(document).on('click', '.btn-retry-single', function() {
                const orderId = $(this).data('order-id');

                Swal.fire({
                    title: 'Sincronizando orden...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: `/ventas/sincronizaciones/sync-single/${orderId}`,
                    type: "POST",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            Swal.fire('Éxito', response.message, 'success').then(() => {
                                $('#btn-calcular-stats').click();
                            });
                        } else {
                            Swal.fire('Error de sincronización', response.message, 'error').then(() => {
                                $('#btn-calcular-stats').click();
                            });
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('Error', 'Error de comunicación.', 'error');
                    }
                });
            });

            // Reintentar todos los errores pendientes
            $('#btn-retry-all').click(function() {
                Swal.fire({
                    title: '¿Reintentar todos los fallidos?',
                    text: "Se intentará volver a enviar todas las órdenes con error.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, reintentar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Procesando reintentos...',
                            html: 'Esto puede tardar unos momentos...',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('ventas.sincronizaciones.retry_all') }}",
                            type: "POST",
                            data: { _token: "{{ csrf_token() }}" },
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    Swal.fire('Completado', response.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.close();
                                Swal.fire('Error', 'Error de comunicación al reintentar masivamente.', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
    @endpush
</x-admin-layout>
