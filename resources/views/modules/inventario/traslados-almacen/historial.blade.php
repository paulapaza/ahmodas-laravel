<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Traslados desde Almacén</x-slot>

    @push('styles')
    <style>
        .card-title.uppercase {
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
        }
        .bg-xprimary {
            background-color: #4e73df;
        }
        .shadow-xs {
            box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .075) !important;
        }
        .table-sm-text {
            font-size: 0.85rem;
        }
    </style>
    @endpush

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="card-title mb-0 text-primary uppercase">
                                <i class="fas fa-history mr-2"></i> Traslados desde Almacén
                            </h6>
                            <a href="{{ route('inventario.traslados_almacen.index') }}" class="btn btn-primary btn-sm shadow-sm">
                                <i class="fas fa-arrow-left mr-1"></i> Volver a Gestión
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros de Historial -->
                        <div class="row mb-4">
                            <div class="col-md-3">
                                <div class="form-group small">
                                    <label class="font-weight-bold">Desde:</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_inicio">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group small">
                                    <label class="font-weight-bold">Hasta:</label>
                                    <input type="date" class="form-control form-control-sm" name="fecha_fin">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group small">
                                    <label class="font-weight-bold">Producto:</label>
                                    <input type="text" class="form-control form-control-sm" placeholder="Buscar por nombre...">
                                </div>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block mb-3">
                                    <i class="fas fa-filter mr-1"></i> Filtrar
                                </button>
                            </div>
                        </div>

                        <!-- Tabla de Historial -->
                        <div class="table-responsive">
                            <table class="table table-hover table-striped table-sm mb-0 table-sm-text">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="pl-3 py-3 border-top-0">Cant. Enviada</th>
                                        <th class="py-3 border-top-0">Producto / Código</th>
                                        <th class="py-3 border-top-0">Tienda Destino</th>
                                        <th class="py-3 border-top-0">Fecha y Hora</th>
                                        <th class="text-center py-3 border-top-0">Vendido</th>
                                        <th class="text-center py-3 border-top-0">Devuelto</th>
                                        <th class="text-center py-3 border-top-0 pr-3">Disp. Actual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Ejemplos demostrativos -->
                                    <tr>
                                        <td class="pl-3 align-middle font-weight-bold">10</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold">Producto Demo A</div>
                                            <div class="small text-muted">Cód. Producto: <span class="font-mono text-primary">BAR-123456</span></div>
                                        </td>
                                        <td class="align-middle">Tienda Principal</td>
                                        <td class="align-middle small font-weight-bold">02/04/2026 14:30:15</td>
                                        <td class="text-center align-middle">3</td>
                                        <td class="text-center align-middle">0</td>
                                        <td class="text-center align-middle pr-3 text-primary font-weight-bold">7</td>
                                    </tr>
                                    <tr>
                                        <td class="pl-3 align-middle font-weight-bold">25</td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold">Producto Demo B</div>
                                            <div class="small text-muted">Cód. Producto: <span class="font-mono text-primary">BAR-789012</span></div>
                                        </td>
                                        <td class="align-middle">Sucursal Norte</td>
                                        <td class="align-middle small font-weight-bold">01/04/2026 09:15:42</td>
                                        <td class="text-center align-middle">5</td>
                                        <td class="text-center align-middle text-danger">1</td>
                                        <td class="text-center align-middle pr-3 text-primary font-weight-bold">19</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap">
                            <span class="small text-muted mb-2 mb-md-0">Mostrando 2 registros históricos</span>
                            <nav aria-label="Navegación de historial">
                                <ul class="pagination pagination-sm mb-0">
                                    <li class="page-item disabled">
                                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Anterior</a>
                                    </li>
                                    <li class="page-item active">
                                        <a class="page-link" href="#">1</a>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="#">Siguiente</a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
