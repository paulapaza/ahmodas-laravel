<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion></x-menuFacturacion>
    </x-slot>

    <x-slot name="pagetitle">Comprobantes Enviados (CPEs)</x-slot>

    <div class="container-fluid">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Listado de Comprobantes Electrónicos</h3>
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
                                <th>Venta</th>
                                <th>Tipo</th>
                                <th>Serie-Número</th>
                                <th>Cliente</th>
                                <th>Estado SUNAT</th>
                                <th>Respuesta</th>
                                <th>Documentos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($cpes as $cpe)
                            <tr>
                                <td>{{ $cpe->id }}</td>
                                <td>
                                    <span class="badge badge-info">#{{ $cpe->posOrder->id ?? '?' }}</span>
                                </td>
                                <td>{{ $cpe->tipo_comprobante }}</td>
                                <td><strong>{{ $cpe->serie }}-{{ $cpe->numero }}</strong></td>
                                <td>{{ $cpe->posOrder->cliente->nombre ?? 'N/A' }}</td>
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
</x-admin-layout>
