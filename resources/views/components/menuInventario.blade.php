<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Inventario</x-slot>
    </a>
</li>

{{-- <li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Operaciones</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="#" class="dropdown-item">Transferencias</a></li>
        <li><a href="#" class="dropdown-item">Ajuste de Inventario</a></li>
        
    </ul>
</li>   --}}
@unless(auth()->user()->hasRole('cajero'))
<li class="nav-item d-none d-sm-inline-block">
    <a href="{{route('inventario.productos.index')}}" class="nav-link">Productos</a>
</li>
<li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('inventario.tiendas.index')}}" class="nav-link">Tiendas</a>
</li>
<li class="nav-item d-none d-sm-inline-block">
    <a href="{{route('inventario.salidas.index')}}" class="nav-link">Stock</a>
</li>
@endunless

<li class="nav-item dropdown">
    <a id="dropdownTransacciones" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Transacciones</a>
    <ul aria-labelledby="dropdownTransacciones" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        @unless(auth()->user()->hasRole('cajero'))
        <li><a href="{{route('inventario.kardex.index')}}" class="dropdown-item">Movimientos (Kardex)</a></li>
        <li><a href="{{route('inventario.transacciones.index')}}" class="dropdown-item">Gestión de Stock</a></li>
        @endunless
        <li><a href="{{ auth()->user()->hasRole('cajero') ? route('inventario.traslados_almacen.historial') : route('inventario.traslados_almacen.index') }}" class="dropdown-item">Traslados desde Almacén</a></li>
        <li><a href="{{ auth()->user()->hasRole('cajero') ? route('inventario.traslados_tiendas.historial') : route('inventario.traslados_tiendas.index') }}" class="dropdown-item">Traslados hacia Almacén</a></li>
        <li><a href="{{ auth()->user()->hasRole('cajero') ? route('inventario.traslados_inter_tiendas.historial') : route('inventario.traslados_inter_tiendas.index') }}" class="dropdown-item">Traslados entre Tiendas</a></li>
    </ul>
</li>

@unless(auth()->user()->hasRole('cajero'))
<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Configuracion</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
               {{-- <li><a href="#" class="dropdown-item">Alamacenes</a></li>
        <li><a href="#" class="dropdown-item">Tipo de Operaciones</a></li> --}}
        <li class="dropdown-divider"></li>
        <li><a href="{{route('inventario.categorias.index')}}" class="dropdown-item">Categorias de Producto</a></li>
        <li><a href="{{route('inventario.marcas.index')}}" class="dropdown-item">Marcas de Producto</a></li>
        </ul>
</li>
@endunless
