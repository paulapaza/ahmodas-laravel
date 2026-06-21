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
                        <textarea id="prompt" rows="3" class="form-control form-control-lg" placeholder="Escribe aquí tu solicitud... (ej: Top 5 productos más vendidos)"></textarea>
                        
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

                    <!-- Interpretación de la IA -->
                    <div id="interpretation-container" class="alert alert-info border-0 d-none mb-4" style="background: linear-gradient(135deg, #e0e7ff 0%, #e0f2fe 100%); color: #3730a3; border-left: 5px solid #6366f1; border-radius: 8px;">
                        <h6 class="font-weight-bold mb-1" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: #4f46e5;"><i class="fas fa-brain mr-2"></i>La IA interpretó tu consulta como:</h6>
                        <p id="interpretation-text" class="m-0 font-weight-bold" style="font-size: 1.1rem; color: #1e1b4b;"></p>
                    </div>

                    {{-- Resultado de SQL (comentado para producción) --}}
                    {{-- <div id="sql-result-container" class="alert alert-light border d-none mb-4">
                        <h6 class="font-weight-bold text-dark mb-2"><i class="fas fa-code text-secondary mr-2"></i>Consulta SQL Generada por la IA:</h6>
                        <pre class="m-0 bg-dark p-3 rounded"><code id="sql-code" class="text-success"></code></pre>
                    </div> --}}

                    <!-- Contenedor del Gráfico Directo -->
                    <div id="result-container" class="border-0 bg-transparent d-none">
                        <div id="chart-container-target"></div>
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
            const chartContainerTarget = document.getElementById('chart-container-target');
            const sqlContainer = document.getElementById('sql-result-container');
            const sqlCode = document.getElementById('sql-code');
            const interpretationContainer = document.getElementById('interpretation-container');
            const interpretationText = document.getElementById('interpretation-text');

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
                // sqlContainer.classList.add('d-none'); // SQL oculto
                interpretationContainer.classList.add('d-none');
                chartContainerTarget.innerHTML = '';

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

                    // Mostrar Interpretación
                    if (data.title) {
                        interpretationText.textContent = data.title;
                        interpretationContainer.classList.remove('d-none');
                    }

                    // Mostrar SQL (comentado para producción)
                    // if (data.sql) {
                    //     sqlCode.textContent = data.sql;
                    //     sqlContainer.classList.remove('d-none');
                    // }

                    // Inyectar HTML directamente en la página
                    if (data.html) {
                        // 1. Limpiar completamente el contenedor anterior
                        chartContainerTarget.innerHTML = '';

                        // 2. Parsear el HTML recibido en un div temporal (SIN ejecutar scripts)
                        const tempDiv = document.createElement('div');
                        tempDiv.innerHTML = data.html;

                        // 3. Extraer y guardar el contenido de los scripts ANTES de moverlos al DOM
                        const scriptContents = [];
                        tempDiv.querySelectorAll('script').forEach(s => {
                            scriptContents.push(s.textContent);
                            s.remove(); // quitar del HTML para no duplicar ejecución
                        });

                        // 4. Mover TODOS los nodos hijo al contenedor real (soporta múltiples nodos raíz)
                        while (tempDiv.firstChild) {
                            chartContainerTarget.appendChild(tempDiv.firstChild);
                        }

                        // 5. Ejecutar cada script en el siguiente ciclo del event loop,
                        //    después de que el browser haya pintado el HTML en pantalla.
                        setTimeout(function() {
                            scriptContents.forEach(function(scriptText) {
                                try {
                                    // Ejecutar el script en el scope global
                                    // eslint-disable-next-line no-new-func
                                    (new Function(scriptText))();
                                } catch(e) {
                                    console.error('Error al ejecutar script del gráfico:', e);
                                }
                            });
                        }, 10);

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
