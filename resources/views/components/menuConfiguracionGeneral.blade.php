<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Configuración</x-slot>
    </a>
</li>

<li class="nav-item">
    <a href="{{route('configuracion.ajustesGenerales.show')}}" class="nav-link ">Ajuste Generales</a>
</li>

<li class="nav-item ">
    <a  href="{{route('configuracion.usuarios.index')}}" class="nav-link ">Usuarios</a>
</li>


<li class="nav-item ">
    <a  href="{{route('empresa.edit')}}" class="nav-link ">Empresa</a>
</li>{{--
<li class="nav-item ">
    <a  href="{{route('dbodoo.view')}}" class="nav-link ">DB Odoo</a>
</li>
 --}}

