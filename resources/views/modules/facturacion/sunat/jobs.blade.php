<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion></x-menuFacturacion>
    </x-slot>

    <x-slot name="pagetitle">Cola de Envío (Trabajos Pendientes)</x-slot>

    <div class="container-fluid">
        <div class="card card-outline card-warning">
            <div class="card-header">
                <h3 class="card-title">Documentos en espera de envío a SUNAT</h3>
                <div class="card-tools">
                    <span class="badge badge-warning">{{ count($jobs) }} Pendientes</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID Job</th>
                                <th>Orden Relacionada</th>
                                <th>Tarea / Clase</th>
                                <th>Intentos</th>
                                <th>Disponible en</th>
                                <th>Creado el</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jobs as $job)
                            <tr>
                                <td>{{ $job->id }}</td>
                                <td>
                                    @if($job->order_id != 'N/A')
                                        <span class="badge badge-primary">Venta #{{ $job->order_id }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <code>{{ str_replace('App\\Jobs\\', '', $job->display_name) }}</code>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $job->attempts > 0 ? 'warning' : 'light' }}">
                                        {{ $job->attempts }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-{{ $job->available_at <= time() ? 'success' : 'muted' }}">
                                        {{ date('d/m/Y H:i:s', $job->available_at) }}
                                    </span>
                                </td>
                                <td>{{ date('d/m/Y H:i:s', $job->created_at) }}</td>
                                <td>
                                    @if($job->available_at <= time())
                                        <span class="badge badge-success">Listo para procesar</span>
                                    @else
                                        <span class="badge badge-info">Programado (Espera)</span>
                                    @endif
                                </td>
                                <td>
                                    @if($job->display_name == 'App\Jobs\SendCepToSunatJob')
                                        <form action="{{ route('facturacion.sunat.jobs.send_now', $job->id) }}" method="POST" onsubmit="return confirm('¿Seguro que desea enviar este documento a SUNAT ahora mismo?')">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-primary">
                                                <i class="fas fa-rocket"></i> Enviar Ya
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                    <p class="mb-0">No hay envíos pendientes en la cola.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-sm-6 text-muted">
                        <small><i class="fas fa-info-circle"></i> Los envíos a SUNAT están programados con 1 hora de retraso para permitir correcciones.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
