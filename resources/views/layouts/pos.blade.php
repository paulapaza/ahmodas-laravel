<!DOCTYPE html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SVP | Punto de venta</title>
  <link rel="icon" href="{{ asset('img/icon.png') }}" type="image/png">
  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="{{ asset('css/adminlte.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
  <!-- DataTables -->
  <link href="https://cdn.datatables.net/responsive/3.0.1/css/responsive.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/v/dt/dt-2.0.7/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/datatables.min.css" rel="stylesheet">

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

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.0/jquery.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="{{ asset('js/adminlte.min.js') }}" defer></script>
<script src="{{ asset('js/larajax.js') }}" defer></script>
<script src="https://cdn.datatables.net/v/dt/jszip-3.10.1/dt-2.0.8/af-2.7.0/b-3.0.2/b-colvis-3.0.2/b-html5-3.0.2/b-print-3.0.2/r-3.0.2/datatables.min.js"></script>



<script src="{{ asset('plugins/sweetalert2/sweetalert2.all.js') }}"></script>
 @stack('scripts')
</body>
</html>