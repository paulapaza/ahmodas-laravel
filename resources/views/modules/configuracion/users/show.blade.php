
<x-admin-layout>
    <x-slot name="titulo">Datos de usuario</x-slot>
    @if ($user == null)
        <div class="alert alert-danger" role="alert">
            No se encontro el usuario o no existe.
        </div>
    @else
        <div class="col-md-8 col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informacion de Usuario</h3>
                    <div class="card-tools">
                        <!-- Buttons, labels, and many other things can be placed here! -->
                        <!-- Here is a label for example -->
                        <span class="badge badge-primary">Activo</span>
                    </div>
                    <!-- /.card-tools -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
    
                    <div class="mb-2 row">
                        <div class="col-md-3 ">
                            <img class="rounded" src="../storage/{{ $user->profile_photo_path }}" alt="image"
                                width="180px ">
                        </div>
                        <div class="col-md-9 ">
                            <div class="row">
                                <div class="col-12">
                                    <h4 class="border-bottom bg-gray-200 pb-2 mb-3">{{ $user->name }}</h4>
                                    <b>Id: </b> {{ $user->id }}<br>
                                    <b>Username: </b> {{ $user->username }}<br>
                                    <b>Rol: </b> {{ $user->role }}<br>
                                    <b>E-mail: </b> {{ $user->email }}<br>
                                    <b>Creado: </b> {{ $user->created_at }}<br>
                                    <b>Actualizado: </b>{{ $user->updated_at }}<br>
   
                                </div>
                                
                            </div>
    
                        </div>
                    </div>
    
                </div>
                <!-- /.card-body -->
                <div class="pb-3 card-footer">
                    <a href="{{ route('users') }}" class="btn btn-primary float-right">Regresar</a>
                </div>
                <!-- /.card-footer -->
            </div>
            <!-- /.card -->

        </div>

        
    @endif


</x-admin-layout>


