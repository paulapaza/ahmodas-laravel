<li class="nav-item d-none d-sm-inline-block">
    <a href="" class="nav-link">
        <x-slot name="titulo">Odoo +Plus</x-slot>
    </a>
</li>


<li class="nav-item ">
    <a  href="{{route('odoocpe.pos_order.index')}}" class="nav-link ">Pos Order</a>
</li>

<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">BarCode</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="{{route('odoocpe.barcode.product')}}" class="dropdown-item">By Product </a></li>
        <li><a href="{{route('odoocpe.barcode.purchase')}}" class="dropdown-item">By Purchase</a></li>
        <li><a href="{{route('odoocpe.barcode.product')}}" class="dropdown-item">Price Tag</a></li>
    </ul>
</li>


<li class="nav-item dropdown">
    <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
        class="nav-link dropdown-toggle">Configuracion</a>
    <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow" style="left: 0px; right: inherit;">
        <li><a href="{{route('odoocpe.configuracion.odoo_db.index')}}" class="dropdown-item">Odoo Db</a></li>
    </ul>
</li>

<li class="nav-item ">
    <a  href="{{route('magento.main')}}" class="nav-link ">Magento</a>
</li>





