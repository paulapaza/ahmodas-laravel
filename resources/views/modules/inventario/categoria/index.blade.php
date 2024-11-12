<x-admin-layout>
    <x-slot name="menu">
        <x-menuInventario></x-menuInventario>
    </x-slot>
    <x-slot name="pagetitle">Productos</x-slot>

    <x-table>
        <th>id</th>
        <th>Nombre</th>
        <th>descripcion</th>
        <th>Categoria Padre</th>
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
                    <label for="nombre_visible">Nombre Visible</label>
                    <input type="text" class="form-control" id="nombre_visible" name="nombre_visible" disabled>
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
                    <label for="categoria_padre_id">Categoria Padre</label>
                    <select class="form-control" id="categoria_padre_id" name="categoria_padre_id">
                        <option value="">Seleccione una categoria</option>

                    </select>
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
            route: "/inventario/categoria",
            subject: 'Categoria',
            model: "Categoria",
            csrf: csrf,
        };
        let table = new Larajax({
            data: dataCrud,
            columns: [{
                    data: 'id'
                },
                {
                    data: 'nombre_visible'
                },
                {
                    data: 'descripcion'
                },
                {
                    data: 'categoria_padre',
                    render: function(data) {
                        return (data) ? data.nombre : ''
                    }
                },
                {
                    data: 'estado',
                    render: function(data) {
                        return (data == 1) ? '<span class="badge bg-xsuccess">Activo</span>' : '<span class="badge bg-xsecondary text-white">Inactivo</span>'
                    }
                },

            ],
            actionsButtons: {
                edit: true,
                destroy:true
            },
            alingCenter: [3, 4]
        })

        // ejecutar funcion cargar categorias luego que la tba se haya cargado
        table.on('draw', function() {
            cargarCategorias();
        });
        // edit record

        $('#table').on('click', '.btn-edit', function() {
           // cargarCategorias();
            let rowData = ($(this).parents('tr').hasClass('child')) ?
                table.row($(this).parents().prev('tr')).data() :
                table.row($(this).parents('tr')).data();
         
           
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

        //subir foto
        $("#imageFile").change(function() {
            filePreview(this);
        });

        function filePreview(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    //$('#uploadForm + img').remove();
                    $('#preimg').html("");
                    $('#preimg').html('<img src="' + e.target.result + '" width="150" height="150"/>');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        // cargar categorias
        function cargarCategorias() {
            console.log('cargar categorias')
            $.ajax({
                url: '/inventario/categoria',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let html = '<option value="">ninguno</option>';
                    data.forEach(element => {
                        html += `<option value="${element.id}">${element.nombre_visible}</option>`;
                    });
                    $('#categoria_padre_id').html(html);
                }
            });
        }
        
    });
</script>
