<!DOCTYPE html>

<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SVP | Punto de venta</title>
{{--   <meta name="restriccion_precio_minimo" content="{{ Auth::user()->restriccion_precio_minimo }}">
 --}}  <link rel="icon" href="{{ asset('img/icon.png') }}" type="image/png">
 <!-- Google Font: Source Sans Pro -->
{{--  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
 --}} <!-- Font Awesome Icons -->
<link rel="stylesheet" href="{{ asset('plugins/fontawesome/css/all.min.css') }}">
 <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <!-- DataTables -->
   
  <link rel="stylesheet" href="{{ asset('css/datatables.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/responsive.dataTables.min.css') }}">
  
  <!-- estilos de perfil -->
  {{$estilos ?? ''}}

</head>
<body class="sidebar-mini sidebar-collapse ">
<div class="wrapper">

 

  <!-- Main Sidebar Container -->
  @include('layouts.partials.sidebar')
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    

    <!-- Main content -->
    <div class="content">
      <div class="container-fluid">
        <div class="row">
            <div class="col">

                {{ $slot ?? 'Dashboard'}}
            </div>

        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
  </div>

  @include('layouts.partials.footer')
</div>
<script>
    window.restriccion_precio_minimo = @json(Auth::user()->restriccion_precio_minimo);
    window.currentUserPermissions = @json(Auth::user()->getAllPermissions()->pluck('name'));
</script>
<script src="{{ asset('js/jquery.min.js') }}"></script>

<script src="{{ asset('js/bootstrap.bundle.min.js') }}" defer></script>

<script src="{{ asset('js/adminlte.min.js') }}" defer></script>
<script src="{{ asset('js/larajax.js') }}" defer></script>
<script src="{{ asset('js/datatables.min.js') }}" defer></script>


<script src="{{ asset('plugins/sweetalert2/sweetalert2.all.js') }}"></script>
 @stack('scripts')
</body>
</html>