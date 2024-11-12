<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Facturacion</x-slot>
    </a>
</li>


{{-- <li class="nav-item d-none d-sm-inline-block">
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
</li>     --}}
<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Configuracion</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="{{route('facturacion.configuracion.edit')}}" class="dropdown-item">Configuracion de Facturacion</a></li>
        <li class="dropdown-divider"></li>
        <li><a href="{{route('facturacion.impuestos.index')}}" class="dropdown-item">Impuestos</a></li>
        <li class="dropdown-divider"></li>
        
        <li><a href="{{route('facturacion.sunat.tipoafectacionigv.index')}}" class="dropdown-item">Tipo Afectacion del IGV</a></li>
        <li><a href="{{route('facturacion.sunat.tipodocumentoidentidad.index')}}" class="dropdown-item">Tipo de Documento Identidad</a></li>
        <li><a href="{{route('facturacion.sunat.tipocomprobante.index')}}" class="dropdown-item">Tipo de Comprobante</a></li>
        <li><a href="{{route('facturacion.sunat.tipoprecio.index')}}" class="dropdown-item">Tipo de Precio</a></li>
        
    </ul>
</li>
