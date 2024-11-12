<x-admin-layout>
    <x-slot name="menu">
    <x-menuFacturacion></x-menuFacturacion>
  </x-slot>
     
  <x-slot name="pagetitle">Tipos de Comprobante - Catálogo 01 Sunat</x-slot>

 

    <x-table>
        <th>Codigo</th>
        <th>Descripcion</th>
        <th>Estado</th>
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
            route: "/facturacion/tipo-comprobante",
            subject: 'Tipo de comprobante',
            model: "TipoComprobante",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            newRecordTopButton: false,
            columns: [
                {
                    data: 'codigo'
                },
                
                {
                    data: 'descripcion'
                },
                {
                    data: 'estado',
                    render: function(data) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },

            ],
            alingCenter: [2],
            order: [2, 'asc'],
        })

       
        // edit record

        $('#table').on('click', '.btn-edit', function() {
           // cargarCategorias();
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
            
            // ocultar el option de la categoria seleccionada (para que no se pueda seleccionar a si misma)
            $('#categoria_padre_id option').show();
            $('#categoria_padre_id option[value=' + rowData.id + ']').hide();
            // buscar la categoria padre en la columna de la tabla
            
          /*   table.rows().data().each(function(element) {
                    $('#categoria_padre_id option[value=' + element.id + ']').hide();
            }); */
           
            edit_record(rowData, table, $(this));

        });

        // store record
        $(document).on('click', '.btn-store', function() {
           
            no_send_form('#form');
            let form = document.getElementById('form');
            if (!form.checkValidity()) {
                return;
            }
            let formData = new FormData(form);
            // console.log(formData);
            //store_record(dataCrud, formData, table) == true ? cargarCategorias() : console.log('error, no cargo');
            store_record(dataCrud, formData, table);
        });

        // destroy record
        $('#table').on("click", ".btn-destroy", function() {
          

            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();

            destroy_record(dataCrud, table, rowData)
        });

     
        
    });
</script>
