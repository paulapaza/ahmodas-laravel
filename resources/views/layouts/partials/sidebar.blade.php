<aside class="main-sidebar bg-xaccent elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('adminpanel') }}" class="brand-link text-light">
      <img src="{{ asset('img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight">Numis PE</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
   
  

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          {{-- <li class="nav-item menu-open">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Starter Pages
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Active Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inactive Page</p>
                </a>
              </li>
            </ul>
          </li> --}}
        {{--   <li class="nav-item">
            
            <a href="{{ route('facturacion.home') }}" class="nav-link text-light">
             
              <i class="nav-icon  fa-solid fa-file-invoice"></i>

              
              <p>
                Facturacion
               
              </p>
            </a>
          </li> --}}
          <li class="nav-item">
            
            <a href="{{ route('inventario.main') }}" class="nav-link text-light">
             
            <i class="nav-icon fa-solid fa-box-archive"></i>
              <p>
                Inventario
               
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('facturacion.main') }}" class="nav-link text-light">
            <i class="nav-icon fa-regular fa-file-lines"></i>
              <p>
                facturacion
               
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('odoocpe.main') }}" class="nav-link text-light">
            <i class="nav-icon fa-regular fa-circle"></i>
              <p>
               Odoo +Plus
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('configuracion.main') }}" class="nav-link text-light">
             
              <i class="nav-icon fa-solid fa-sliders"></i>
              <p>
                Configuración
               
              </p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>