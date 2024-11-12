<nav class="main-header navbar navbar-expand bg-xprimary navbar-dark border-0">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <li class="nav-item d-none d-sm-inline-block">
            <a href="" class="nav-link">
                <div class="pagetitle">{{ $titulo ?? 'Dashboard' }}</div>
            </a>
        </li>
        {{ $menu ?? '' }}
        
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Navbar Search -->
      

        
        <li class="nav-item">
            <a class="nav-link" href="#" role="button" onclick="darkmode()">
                <i class="fa-solid fa-moon"></i>
            </a>
        </li>
        <!-- Messages Dropdown Menu -->
       
        <li class="nav-item dropdown">
            <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false" class="nav-link dropdown-toggle">{{ Auth::user()->name }} </a>
            <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow"
                style="left: 0px; right: inherit;">
                <li>
                    <a href="{{route('profile.show') }}" class="dropdown-item">Mi perfil</a>
                </li>
                <li>
                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}" x-data>
                        @csrf
                        <a href="{{ route('logout') }}"  class="dropdown-item"  onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            Cerrar Sesión
                        </a>
                    </form> 
                </li>

            </ul>
        </li>

    </ul>
</nav>