<x-admin-layout>
    <x-slot name="pagetitle">Reportes Inteligentes</x-slot>

    <div class="content">
        <div class="container-fluid pt-3">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h3 class="card-title font-weight-bold text-primary" style="font-size: 1.5rem;">
                        <i class="fas fa-magic mr-2"></i> Pregúntale a tus datos
                    </h3>
                </div>
                
                <div class="card-body">
                    <p class="text-muted mb-4">
                        Escribe tu consulta en lenguaje natural (ej. "Muéstrame un gráfico de barras con las ventas del mes pasado agrupadas por tienda") y la Inteligencia Artificial se encargará de extraer la información y visualizarla de forma interactiva.
                    </p>

                    <!-- Formulario -->
                    <div class="form-group mb-4">
                        <textarea id="prompt" rows="4" class="form-control form-control-lg" placeholder="Escribe aquí tu solicitud... (ej: Top 5 productos más vendidos)"></textarea>
                        
                        <div class="mt-3 d-flex justify-content-between align-items-center">
                            <button id="btn-generate" class="btn btn-primary btn-lg px-4 shadow-sm">
                                <i class="fas fa-paper-plane mr-2"></i> Generar Respuesta
                            </button>
                            
                            <span id="loading-indicator" class="text-primary font-weight-bold d-none">
                                <i class="fas fa-circle-notch fa-spin mr-2"></i>
                                La IA está procesando tu solicitud... esto puede tomar unos segundos.
                            </span>
                        </div>
                    </div>

                    <!-- Alertas de Error -->
                    <div id="error-alert" class="alert alert-danger d-none shadow-sm" role="alert">
                        <h5><i class="icon fas fa-ban"></i> ¡Ocurrió un Error!</h5>
                        <span id="error-message"></span>
                    </div>

                    <!-- Resultado de SQL (Solo para debug/info) -->
                    <div id="sql-result-container" class="alert alert-light border d-none mb-4">
                        <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-code text-secondary mr-2"></i>Consulta SQL Generada por la IA:</h6>
                        <pre class="m-0 bg-dark p-3 rounded"><code id="sql-code" class="text-success"></code></pre>
                    </div>

                    <!-- Contenedor del Iframe -->
                    <div id="result-container" class="border rounded bg-light d-none" style="height: 600px; overflow: hidden;">
                        <iframe id="chart-frame" class="w-100 h-100 border-0" sandbox="allow-scripts allow-same-origin"></iframe>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Script de lógica -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnGenerate = document.getElementById('btn-generate');
            const promptInput = document.getElementById('prompt');
            const loadingIndicator = document.getElementById('loading-indicator');
            const errorAlert = document.getElementById('error-alert');
            const errorMessage = document.getElementById('error-message');
            const resultContainer = document.getElementById('result-container');
            const chartFrame = document.getElementById('chart-frame');
            const sqlContainer = document.getElementById('sql-result-container');
            const sqlCode = document.getElementById('sql-code');

            btnGenerate.addEventListener('click', async function() {
                const prompt = promptInput.value.trim();
                
                if (!prompt) {
                    alert('Por favor escribe una consulta.');
                    return;
                }

                // Reset UI
                btnGenerate.disabled = true;
                loadingIndicator.classList.remove('d-none');
                errorAlert.classList.add('d-none');
                resultContainer.classList.add('d-none');
                sqlContainer.classList.add('d-none');
                chartFrame.srcdoc = '';

                try {
                    const response = await fetch('{{ route("ai-reports.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ prompt: prompt })
                    });

                    const data = await response.json();

                    if (!response.ok) {
                        throw new Error(data.error || 'Ocurrió un error inesperado al comunicarse con el servidor.');
                    }

                    // Mostrar SQL
                  //   if (data.sql) {
                  //       sqlCode.textContent = data.sql;
                  //       sqlContainer.classList.remove('d-none');
                  //   }

                    // Inyectar HTML en iframe
                    if (data.html) {
                        chartFrame.srcdoc = data.html;
                        resultContainer.classList.remove('d-none');
                    } else {
                        throw new Error('La IA no devolvió un código HTML válido.');
                    }

                } catch (error) {
                    errorMessage.textContent = error.message;
                    errorAlert.classList.remove('d-none');
                } finally {
                    btnGenerate.disabled = false;
                    loadingIndicator.classList.add('d-none');
                }
            });
        });
    </script>
</x-admin-layout>
