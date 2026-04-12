<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Facturacion</x-slot>
    </a>
</li>
<li class="nav-item d-none d-sm-inline-block">
        <a href="{{route('facturacion.configuracion.cpe.serie.list')}}" class="nav-link">Series y Correlativos</a>
</li>


<li class="nav-item dropdown">
    <a id="dropdownSubMenuSunat" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Sunat</a>
    <ul aria-labelledby="dropdownSubMenuSunat" class="dropdown-menu border-0 shadow">
        <li><a href="{{ route('facturacion.sunat.cpes') }}" class="dropdown-item">Comprobantes (CPEs)</a></li>
        <li><a href="{{ route('facturacion.sunat.jobs') }}" class="dropdown-item">Cola de Envío</a></li>
        <li><a href="{{ route('facturacion.sunat.failed_jobs') }}" class="dropdown-item">Envíos Fallidos</a></li>
    </ul>
</li>
