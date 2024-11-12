<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion/>
  </x-slot>
     
  <x-slot name="pagetitle">Impuestos</x-slot>

 

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>codigo_tipo_afectacion</th>
        <th>porcentaje</th>
        <th>incluido_en_precio</th>
        <th>secuencia</th>
        <th>estado</th>

    </x-table>

    <x-mymodal>
        @csrf
        <input type="hidden" id="id" name="id">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group">
                    <label for="nombre">Nombre</label>
                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <label for="descripcion">Descripcion</label>
                    <input type="text" class="form-control" id="descripcion" name="descripcion">
                </div>
            </div>
            
            <div class="col-md-12">
                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select class="form-control" id="estado" name="estado" required>
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
            </div>
        </div>

    </x-mymodal>

</x-admin-layout>
<script src="{{ asset('plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
<script>
    $(document).ready(function() {

        let csrf = $('input[name="_token"]').val();
        // Inicializamos las variables para tipo de envio por ajax en Store record
        let dataCrud = {
            route: "/facturacion/impuesto",
            subject: 'Impuestos', // Nombre de botones ejemplo crear...
            model: "impuesto",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            newRecordTopButton: false,
            columns: [
                
                {
                    data: 'id'
                },
                {
                    data: 'nombre'
                },
                {
                    data: 'codigo_tipo_afectacion'
                },
                {
                    data: 'porcentaje'
                },
                {
                    data: 'incluido_en_precio',
                    render: function(data) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Si</span>' : '<span class="badge bg-xsecondary text-white">No</span>'
                    }
                },
                {
                    data: 'secuencia'
                },
                {
                    data: 'estado',
                    render: function(data) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },
               
            ],
           /*  actionsButtons: {
                edit: true,
                destroy:true
            }, */
            alingCenter: [2,3,4,5],
            order: [0, 'asc'],
        })

       
       

     
        
    });
</script>
