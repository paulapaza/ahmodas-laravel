<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Inventario</x-slot>
    </a>
</li>

<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Operaciones</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="#" class="dropdown-item">Transferencias</a></li>
        <li><a href="#" class="dropdown-item">Ajuste de Inventario</a></li>
        
    </ul>
</li>  
<li class="nav-item d-none d-sm-inline-block">
    <a href="{{route('inventario.productos.index')}}" class="nav-link">Productos</a>
</li>

<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Informes</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="#" class="dropdown-item">Inventario</a></li>
        <li><a href="#" class="dropdown-item">Moviento de productos</a></li>
        <li class="dropdown-divider"></li>
        <li><a href="#" class="dropdown-item">Valoracion de Inventario</a></li>
    </ul>
</li>    
<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Configuracion</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="#" class="dropdown-item">Alamacenes</a></li>
        <li><a href="#" class="dropdown-item">Tipo de Operaciones</a></li>
        <li class="dropdown-divider"></li>
        <li><a href="{{route('inventario.categorias.index')}}" class="dropdown-item">Categorias de Producto</a></li>
        <li><a href="{{route('inventario.marcas.index')}}" class="dropdown-item">Marcas de Producto</a></li>
        </ul>
</li>
