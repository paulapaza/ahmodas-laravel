<x-admin-layout>
    <x-slot name="pagetitle">Rol</x-slot>
    <x-slot name="menu">
        <x-menuConfiguracionGeneral/>
    </x-slot>




    @if (count($errors) > 0)
    <div class="alert alert-danger">
        <strong>Whoops!</strong> There were some problems with your input.<br><br>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif



    <div class="card col-6">
        <div class="card-header">
            Editar Permisos para el roll : <span class="text-bold"> {{ $role->name }}</span>
        </div>
        <form id="form">

            <div class="card-body">
                <div class="form-group">
                    <br />
                    @foreach($permissions as $value)
                    <div class="form-check">
                        <label class="form-check-label">
                            <input type="checkbox" class="form-check-input" name="" id="{{$value->name}}" value="{{ $value->name }}" {{in_array($value->id, $rolePermissions) ? 'checked' : ''}}>
                            {{ $value->descripcion }}
                        </label>
                    </div>
                    <br />
                    @endforeach
                </div>
            </div>
            <div class="card-footer d-flex justify-content-end">
                <a class="btn btn-secondary mr-3" href="{{ route('roles') }}"> Regresar</a>
                <button type="submit" class="btn btn-primary">Guardar Permisos</button>
            </div>
        </form>
    </div>



</x-admin-layout>
<script>
    $(document).ready(function() {
        $('#form').submit(function(e) {
            e.preventDefault();


            let permissions = [];
            $('input[type=checkbox]').each(function() {
                if ($(this).is(':checked')) {
                    permissions.push($(this).attr('id'));
                }
            });
            $.ajax({
                url: "{{ route('role.update', $role->id) }}",
                type: 'PUT',
                data: {
                    permission: permissions,
                    name: '{{ $role->name }}',
                    _token: "{{ csrf_token() }}"
                },
                success: function(response) {
                    swal_message_response(response)
                },
                error: function(errors) {
                    show_validate_errors(errors);
                }
            });




        });

    });
</script>