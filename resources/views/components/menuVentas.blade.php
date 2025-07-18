<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Ventas</x-slot>
    </a>
</li>



<li class="nav-item d-none d-sm-inline-block">
    <a href="{{route('ventas.posorder.index')}}" class="nav-link">Ventas</a>
</li>

 
<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Visor</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="{{route('ventas.visor.posorderpanel')}}" class="dropdown-item">Por Ventas</a></li>
        <li class="dropdown-divider"></li>
        <li><a href="{{route('ventas.visor.posorderlinepanel')}}" class="dropdown-item">Por Detalle de venta</a></li>
        
        </ul>
</li>
<li class="nav-item d-none d-sm-inline-block">
    <a href="{{route('ventas.cliente.index')}}" class="nav-link">clientes</a>
</li>