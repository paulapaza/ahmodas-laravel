<div class="modal fade modal-fullscreen " id="modal-filter-fechas" tabindex="-1" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog ">
        <div class="modal-content">
            <form method="POST" id="Formulario" enctype="text/plain">
                <div class="modal-header custom-theme-bg">
                    <h5 class="modal-title" id="exampleModalLabel">
                        Filtrar por fechas
                    </h5>
                    <button class="btn" type="button" class="close" data-dismiss="modal" data-bs-dismiss="modal"
                        aria-label="Close">
                        <span aria-hidden="true"> <i class="fa-solid fa-circle-xmark fa-xl"></i></span>
                    </button>
                </div>
                <div class="modal-body">

                    <div class="">
                        @csrf
                        <input type="hidden" name="id" id="iptid">
                        <div class="row text-center justify-content-center mt-3">
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='De hoy' range='days'>Hoy</div>
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='De ayer' range='ayer'>Ayer</div>
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='De esta semana' range='week'>Esta
                                Semana</div>
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='Semana Pasada' range='semana_pasada'>
                                Semana Pasada
                            </div>
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='De este mes' range='month'>Este mes
                            </div>
                            <div class="col-5 m-2 btn bg-olive date_range" lbl='Del Mes pasado' range='mes_pasado'>Mes
                                Pasado</div>
                        </div>

                        <div class="mt-4 ">
                            <div class="row mb-3">
                                <div class="col-4 pt-2 text-right text-bold">Inicio</div>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control col-5">
                            </div>
                            <div class="row">
                                <div class="col-4 pt-2 text-right  text-bold">Fin</div>
                                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control col-5">
                            </div>
                            <div class="row">
                                <button type="button"
                                    class="btn bg-primary col-7 mt-5 mx-auto filtrar-fechas">Buscar</button>
                            </div>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn bg-secondary d-none d-md-block" data-dismiss="modal"
                        data-bs-dismiss="modal">Cerrar</button>

                </div>
            </form>
        </div>
    </div>
</div>
@section('scripts_fechas')
    <script src='https://cdn.jsdelivr.net/npm/temporal-polyfill@0.2.5/global.min.js'></script>
    <script>
       
        function Selecionarfecha() {
            $('#modal-filter-fechas').modal('show');
        }

        $('#modal-filter-fechas').on('click', '.filtrar-fechas', function() {
            fechaInicio = $('#fecha_inicio').val();
            fechaFin = $('#fecha_fin').val();
            table.destroy();
            cargarTabla(fechaInicio, fechaFin);
            $('#modal-filter-fechas').modal('hide');
        });
        $('#modal-filter-fechas').on("click", ".date_range", function() {
            var filter = $(this).attr('range');
            const hoy = Temporal.Now.plainDateTimeISO('America/Lima').toPlainDate();
            if (filter == "mes_pasado") {
                var fechaInicio = hoy.with({
                    month: hoy.month - 1,
                    day: 1
                }).toString();
                var fechaFin = hoy.with({
                    month: hoy.month,
                    day: 1
                }).subtract({
                    days: 1
                }).toString();
            } else if (filter == "ayer") {
                var fechaInicio = hoy.subtract({
                    days: 1
                }).toString();
                var fechaFin = fechaInicio

            } else if (filter == "semana_pasada") {
                let diaSemana = hoy.dayOfWeek; 
                let fechaIniciosemanapasada = hoy.subtract({
                    days: diaSemana + 6
                });
                let fechaFinsemanapasada = hoy.subtract({
                    days: diaSemana
                }); 
                fechaInicio = fechaIniciosemanapasada.toString(); 
                fechaFin = fechaFinsemanapasada.toString();
            } else if (filter == "days") {
                var fechaInicio = hoy.toString();
                var fechaFin = fechaInicio;
            } else if (filter == "week") {
                let diaSemana = hoy.dayOfWeek; 
                let fechaIniciox = hoy.subtract({
                    days: diaSemana - 1
                }); 
                fechaInicio = fechaIniciox.toString(); // YYYY-MM-DD
                fechaFin = hoy.toString(); // YYYY-MM-DD

            } else if (filter == "month") {
                var fechaInicio = hoy.with({
                    day: 1
                }).toString();
                var fechaFin = hoy.toString();

            } /* else if (filter == "year") {
                var fechaInicio = hoy.with({
                    month: 1,
                    day: 1
                }).toString();
            } */
           
            $("#modal-filter-fechas").modal("hide");
            //table.destroy();
            cargarTabla(fechaInicio, fechaFin);
             filtro = $(this).attr('lbl');
             $(".btn-select-fecha span").html('<i class="fas fa-calendar-alt"></i> Filtro: '+ $(this).attr('lbl'));
        });
    </script>
@endsection

       
