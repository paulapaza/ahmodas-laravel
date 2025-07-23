<aside class="main-sidebar bg-xaccent elevation-4">
    <!-- Brand Logo -->
    <a href="{{ route('adminpanel') }}" class="brand-link text-light">
      <img src="{{ asset('img/AdminLTELogo.png') }}" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight">
       
        {{ Auth::user()->tienda->nombre ?? 'Sistema de Ventas' }}
      </span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
   
  

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
         
          <li class="nav-item">
            <a href="#" class="nav-link text-light">
            
              <i class="nav-icon fa-regular fa-rectangle-list"></i>
              <p>
                Dashboard
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('puntodeventa.pos') }}" class="nav-link text-light">
             
            <i class="nav-icon fa-solid fa-store"></i>
            
              <p>
                Punto de Venta
               
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('ventas.main')  }}" class="nav-link text-light">
             
            <i class="nav-icon fa-solid fa-chart-simple"></i>
            
              <p>
                ventas
              </p>
            </a>
          </li>
          <li class="nav-item">
            
            <a href="{{ route('inventario.main') }}" class="nav-link text-light">
             <i class="nav-icon  fa-solid fa-warehouse"></i> 
       
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