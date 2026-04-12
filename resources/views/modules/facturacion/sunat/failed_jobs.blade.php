<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion></x-menuFacturacion>
    </x-slot>

    <x-slot name="pagetitle">Envíos Fallidos a SUNAT</x-slot>

    <div class="container-fluid">
        <div class="card card-outline card-danger">
            <div class="card-header">
                <h3 class="card-title text-danger"><i class="fas fa-exclamation-triangle"></i> Historial de Fallos</h3>
                <div class="card-tools">
                    <span class="badge badge-danger">{{ count($failedJobs) }} Errores Registrados</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover m-0">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Orden</th>
                                <th>Tarea</th>
                                <th>Error / Excepción</th>
                                <th>Fecha de Fallo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($failedJobs as $job)
                            <tr>
                                <td>{{ $job->id }}</td>
                                <td>
                                    @if($job->order_id != 'N/A')
                                        <a href="{{ route('posorder.show', $job->order_id) }}" class="badge badge-secondary" target="_blank">
                                            Venta #{{ $job->order_id }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td><code>{{ str_replace('App\\Jobs\\', '', $job->display_name) }}</code></td>
                                <td>
                                    <div class="text-danger small" style="max-height: 80px; max-width: 400px; overflow-y: auto; overflow-x: hidden;">
                                        <strong>Resumen:</strong> {{ Str::limit($job->exception, 200) }}
                                        <details>
                                            <summary class="text-primary pointer">Ver traza completa</summary>
                                            <pre class="bg-light p-2 mt-1" style="font-size: 10px; white-space: pre-wrap;">{{ $job->exception }}</pre>
                                        </details>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($job->failed_at)->diffForHumans() }}<br>
                                    <small class="text-muted">{{ $job->failed_at }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                    <p class="mb-0">No se han registrado fallos. ¡Excelente!</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted small">
                <i class="fas fa-info-circle"></i> Estos trabajos han agotado sus reintentos automáticos. Revise el error para solucionar el problema manualmente.
            </div>
        </div>
    </div>
</x-admin-layout>
