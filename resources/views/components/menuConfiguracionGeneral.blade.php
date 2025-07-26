<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Configuración</x-slot>
    </a>
</li>

<li class="nav-item">
    <a href="{{route('configuracion.ajustesGenerales.show')}}" class="nav-link ">Ajuste Generales</a>
</li>

<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">Usuarios</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="{{route('users')}}" class="dropdown-item">Usuarios</a></li>
        <li><a href="{{route('roles')}}" class="dropdown-item">Roles</a></li>
        <li><a href="{{route('permisos')}}" class="dropdown-item">Permisos</a></li>
       <!--  <li class="dropdown-divider"></li> -->

    </ul>
</li>

<li class="nav-item ">
    <a  href="{{route('empresa.edit')}}" class="nav-link ">Empresa</a>
</li>{{--
<li class="nav-item ">
    <a  href="{{route('dbodoo.view')}}" class="nav-link ">DB Odoo</a>
</li>
 --}}

