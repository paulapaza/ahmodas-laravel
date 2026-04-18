<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion></x-menuFacturacion>
    </x-slot>

    <x-slot name="pagetitle">Comprobantes Enviados (CPEs)</x-slot>

    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <form action="{{ route('facturacion.sunat.actualizar_estados') }}" method="POST" class="form-inline float-left" onsubmit="disableButton(this)">
                    @csrf
                    <label class="mr-2 mb-0">Desde:</label>
                    <input type="date" name="fecha_inicio" value="{{ now()->subDay()->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
                    <label class="mr-2 mb-0">Hasta:</label>
                    <input type="date" name="fecha_fin" value="{{ now()->subDay()->format('Y-m-d') }}" class="form-control form-control-sm mr-2">
                    <button type="submit" class="btn btn-sm btn-primary mr-2" id="btn-actualizar" onclick="return confirm('¿Actualizar estados de CPEs en el rango de fechas seleccionado?')">
                        <i class="fas fa-sync-alt"></i> Actualizar
                    </button>
                </form>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Fecha Emisión</th>
                                <th>Venta</th>
                                <th>Tipo</th>
                                <th>Serie-Número</th>
                                <th>Cliente</th>
                                <th>Tienda</th>
                                <th>Estado SUNAT</th>
                                <th>Respuesta</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cpes as $cpe)
                            <tr>
                                <td>{{ $cpe->id }}</td>
                                <td>{{ $cpe->created_at ? $cpe->created_at->format('d/m/Y H:i') : ($cpe->posOrder && $cpe->posOrder->created_at ? $cpe->posOrder->created_at->format('d/m/Y H:i') : '--') }}</td>
                                <td>
                                    @if($cpe->posOrder)
                                        <a href="{{ url('/ventas/posorder/' . $cpe->posOrder->id) }}" class="badge badge-info text-white" target="_blank">#{{ $cpe->posOrder->id }}</a>
                                    @else
                                        <span class="badge badge-info">#?</span>
                                    @endif
                                </td>
                                <td>{{ $cpe->tipo_comprobante }}</td>
                                <td><strong>{{ $cpe->serie }}-{{ $cpe->numero }}</strong></td>
                                <td>{{ $cpe->posOrder->cliente->nombre ?? 'N/A' }}</td>
                                <td>{{ $cpe->posOrder->tienda->alias ?? $cpe->posOrder->tienda->nombre ?? 'N/A' }}</td>
                                <td>
                                    @if($cpe->aceptada_por_sunat)
                                        <span class="badge badge-success"><i class="fas fa-check-circle"></i> Aceptada</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times-circle"></i> No Aceptada</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $cpe->sunat_description }}</small>
                                </td>
                                <td>
                                    <div class="btn-group">
                                        @if($cpe->enlace_del_pdf)
                                            <a href="{{ $cpe->enlace_del_pdf }}" target="_blank" class="btn btn-xs btn-outline-danger" title="PDF">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                        @endif
                                        @if($cpe->enlace_del_xml)
                                            <a href="{{ $cpe->enlace_del_xml }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="XML">
                                                <i class="fas fa-file-code"></i>
                                            </a>
                                        @endif
                                        @if($cpe->enlace_del_cdr)
                                            <a href="{{ $cpe->enlace_del_cdr }}" target="_blank" class="btn btn-xs btn-outline-success" title="CDR">
                                                <i class="fas fa-check-double"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $cpes->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function disableButton(form) {
            const button = document.getElementById('btn-actualizar');
            button.disabled = true;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
        }
    </script>
</x-admin-layout>
