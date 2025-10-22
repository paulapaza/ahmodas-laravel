<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Miapp | {{ $titulo ?? 'Dashboard' }}</title>
    <meta http-equiv="Pragma" content="no-cache">

    <link rel="icon" href="{{ asset('img/icon.png') }}" type="image/png">

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    {{-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  --}}
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome/css/all.min.css') }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('css/print.css') }}">
    <!-- DataTables -->
    {{-- <link href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/v/dt/dt-2.0.7/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/datatables.min.css" rel="stylesheet"> --}}

    <link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/responsive.dataTables.min.css') }}">

    {{-- BootstrapVue CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-vue@2.21.2/dist/bootstrap-vue.min.css">

    <!-- custom css -->
    @stack('styles')

    <!-- estilos -->
    {{ $estilos ?? '' }}
</head>

<body class="sidebar-mini sidebar-collapse ">
    <div class="wrapper">

        <!-- Navbar -->
        @include('layouts.partials.navbar')
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        @include('layouts.partials.sidebar')
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header content-title">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                            <div class="content-title text-bold">

                                {{ $pagetitle ?? '' }}
                            </div>
                        </div><!-- /.col -->
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active">{{ $titulo ?? 'Dashboard' }}</li>
                            </ol>
                        </div><!-- /.col -->
                    </div><!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <div class="content">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col px-3">

                            {{ $slot ?? 'Dashboard' }}
                        </div>

                    </div>
                    <!-- /.row -->
                </div><!-- /.container-fluid -->
            </div>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Control Sidebar derecha-->
        @include('layouts.partials.rightsidebar')

        <!-- /.control-sidebar -->

        <!-- Main Footer -->
        @include('layouts.partials.footer')
    </div>
    <!-- ./wrapper -->

    <!-- REQUIRED SCRIPTS -->

    <!-- jQuery -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script> --}}
    <script src="{{ asset('js/jquery.min.js') }}"></script>

    <!-- Bootstrap 4 -->
    {{-- <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}

    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
 --}}
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>

    <!-- AdminLTE App -->
    <script src="{{ asset('js/adminlte.min.js') }}"></script>
    <script src="{{ asset('js/larajax.js') }}"></script>
    <!-- DataTables -->
    <script src="{{ asset('js/datatables.min.js') }}"></script>
    {{-- <script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.0.8/af-2.7.0/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/r-3.0.2/datatables.min.js"></script>
 --}}
    <!-- SweetAlert2 -->

    <script src="{{ asset('plugins/sweetalert2/sweetalert2.all.js') }}"></script>
    <!-- shared functions -->
    {{-- <script src="{{ asset('js/shared.js') }}" defer></script> --}}
    <!-- datatable buttons -->

    <!-- ============================================================
    =LIBRERIAS PARA EXPORTAR A ARCHIVOS
   ===============================================================-->
    {{--  <script src="https://cdn.datatables.net/buttons/2.0.0/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.0.0/js/buttons.print.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.colVis.min.js"></script>  --}}

    @yield('scripts_fechas')
    @yield('page_scripts')

    <!-- librerias bases para vue -->
    <script src="https://cdn.jsdelivr.net/npm/vue@2.6.14/dist/vue.js"></script>
    <script src="https://unpkg.com/vue-router@3.5.3/dist/vue-router.js"></script>
    <script src="https://unpkg.com/vuex@3.6.2/dist/vuex.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

    {{-- config --}}
    <script src="{{ asset('modules/config/axios.js') }}"></script>

    {{-- bootstrap-vue --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-vue@2.21.2/dist/bootstrap-vue.min.js"></script>

    {{-- helpers --}}
    <script src="{{ asset('modules/helpers/datatable.helper.js') }}"></script>
    <script src="{{ asset('modules/helpers/date.helper.js') }}"></script>

    <!-- Cargar módulos del store primero (definen window.userModule / window.cartModule) -->
    <script src="{{ asset('modules/store/user.js') }}"></script>
    <script src="{{ asset('modules/store/cart.js') }}"></script>
    <script src="{{ asset('modules/store/index.js') }}"></script>

    <!-- custom scripts -->
    @stack('scripts')
</body>

</html>
