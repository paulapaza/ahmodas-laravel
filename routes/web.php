<?php

use App\Http\Controllers\ApiConsultaController;
use App\Http\Controllers\Configuracion\PermissionController;
use App\Http\Controllers\Configuracion\RoleController;
use App\Http\Controllers\Configuracion\UserController;
use App\Http\Controllers\Facturacion\CpeSerieController;
use App\Http\Controllers\Inventario\CategoriaController;
use App\Http\Controllers\Inventario\MarcaController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\SalidaProductoController;
use App\Http\Controllers\Inventario\StockController;
use App\Http\Controllers\Inventario\TiendaController;

use App\Http\Controllers\Pos\PosOrderController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::get('/admin', function () {return view('layouts.admin');})->name('adminpanel');
    Route::get('/facturacion', function () {return view('facturacion.home');})->name('facturacion.home');
    /*************************
     Punto de Venta
     ************************/
    Route::view('/punto-de-venta', 'modules.puntodeventa.pos1')->name('puntodeventa.pos');
    //venta
    Route::post('/punto-de-venta/venta', [PosOrderController::class, 'store'])->name('puntodeventa.venta.store');
     /*************************
     Ventas
     ************************/
    Route::view('/ventas', 'modules.ventas.main')->name('ventas.main');
    // ventas
    Route::view('/ventas/ventas', 'modules.ventas.posorder.index')->name('ventas.posorder.index');
    Route::resource('ventas/posorder', PosOrderController::class)->only(['index','show']);
    Route::get('ventas/posorder/{fecha_inicio?}/{fecha_fin?}', [posOrderController::class, 'indexByDate'])->name('playa.parqueo.indexByDate  ');
    // put for cancelhttp://svp.test/ventas/posorder/cancel/15 405 (Method Not Allowed)
    Route::put('/ventas/posorder/cancel/{id}', [PosOrderController::class, 'cancel'])->name('ventas.posorder.cancel');
    Route::get('/ventas/visor/posorder/{fecha_inicio?}/{fecha_fin?}', [PosOrderController::class, 'postOrderPanel'])->name('ventas.visor.posorderpanel');
    Route::get('/ventas/visor/posorderline/{fecha_inicio?}/{fecha_fin?}', [PosOrderController::class, 'postOrderLinePanel'])->name('ventas.visor.posorderlinepanel');

    //clientes
    Route::view('/ventas/clientes', 'modules.clientes.index')->name('ventas.cliente.index');
    Route::resource('/ventas/cliente', \App\Http\Controllers\ClienteController::class)->except(['show']);
    Route::post('/ventas/posorder/anular/{id}', [PosOrderController::class, 'anular'])->name('ventas.posorder.anular.cpe');
    //nota de credito
    Route::post('/ventas/nota-de-credito/{id}', [PosOrderController::class, 'emitirNota'])->name('ventas.posorder.notadecredito');
    Route::post('/ventas/nota-de-debito/{id}', [PosOrderController::class, 'emitirNota'])->name('ventas.posorder.notadebito');
    //consultar estado del CPE
    Route::get('/consultar-estado-cpe/{cpe_id}', [PosOrderController::class, 'consultarEstadoCpe'])->name('ventas.posorder.consultarEstadoCpe');    
    Route::POST('/comunicar-baja', [PosOrderController::class, 'comunicarBajaCpe'])->name('comunicarBajaCpe');
    Route::get('/consultar-cumunicacion-de-baja/{cpe_id}', [PosOrderController::class, 'consultarComunicacionBaja'])->name('consultarComunicacionBaja');
    /*************************
     MODULO DE INVENTARIO
     ************************/ 
    Route::view('/inventario','modules.inventario.main')->name('inventario.main');
    
    /*=================================
    =            productos            =
    =================================*/
    // controlador
    Route::get('/inventario/salidas/listado', [SalidaProductoController::class, 'index'])->name('inventario.salidas.listado');
    Route::post('/inventario/salidas/reducir', [SalidaProductoController::class, 'store'])->name('inventario.salidas.reducir');
    Route::get('/inventario/salidas/historial/{producto_id}', [SalidaProductoController::class, 'history'])->name('inventario.salidas.historial');

    // vistas
    Route::get('/inventario/salidas/{any?}', function () {
        return view('modules.inventario.salidas.index');
    })->where('any', '.*')->name('inventario.salidas.index');

    Route::view('/inventario/productos','modules.inventario.producto.index')->name('inventario.productos.index');
    Route::resource('/inventario/producto', ProductoController::class);
    Route::post('/invetario/producto/buscar', [ProductoController::class, 'buscarProducto']);
    // tiendas
    Route::view('/inventario/tiendas','modules.inventario.tienda.index')->name('inventario.tiendas.index');
    Route::resource('/inventario/tienda', TiendaController::class);

    // categorias
    Route::view('/inventario/categorias','modules.inventario.categoria.index')->name('inventario.categorias.index');
    Route::resource('/inventario/categoria', CategoriaController::class);
    // marcas
    Route::view('/inventario/marcas','modules.inventario.marca.index')->name('inventario.marcas.index');
    Route::resource('/inventario/marca', MarcaController::class)->except(['show, create, edit']);

    
    // stock
    Route::get('/inventario/stock', [StockController::class, 'tiendasConStock']);
    /*************************
     MODULO DE FACTURACION
     ************************/
    Route::view('/facturacion','modules.facturacion.main')->name('facturacion.main');
    

    //cep_serie
    Route::get('/facturacion/serie', [CpeSerieController::class,'index'])->name('cpe.serie.index');
    Route::get('/facturacion/serie/{id}', [CpeSerieController::class,'getCorrelativo'])->name('cpe.serie.getCorrelativo');
    Route::post('/facturacion/serie-correlativo', [CpeSerieController::class,'getSerieCorrelativo'])->name('cpe.serie.getSerieCorrelativo');
    Route::view('/facturacion/cpe-series', 'modules.facturacion.CpeSeries')->name('facturacion.configuracion.cpe.serie.list'); 
    Route::resource('/facturacion/cpe-serie', CpeSerieController::class);

   
    /*************************
     MODULO DE CONFIGURACION
     ************************/    
    Route::view('/configuracion','modules.configuracion.main')->name('configuracion.main');
    // ajustes generales
    Route::view('/configuracion/general','modules.configuracion.ajustesgenerales.show')->name('configuracion.ajustesGenerales.show');
    // configuracion de usuarios
    //Route::view('/configuracion/usuarios', 'modules.configuracion.users.index')->name('configuracion.usuarios.index');
    //Route::resource('/configuracion/user', UserController::class);
 
    /*************************
     MODULO DE Usuarios roles y permisos
     ************************/ 
      
    Route::get('/usuarios', function () {
        return view('modules.configuracion.users.users');
    })->name('users');
    route::get('/user', [UserController::class, 'index'])->name('user.index');
    // aqui debe ir create
    route::post('/user', [UserController::class, 'store'])->name('user.store');
    route::get('/user/{id}', [UserController::class, 'show'])->name('user.show');
    // aqui debe ir edit
    route::patch('/user/{id}', [UserController::class, 'update'])->name('user.update');
    route::delete('/user/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    //resetPassword
    route::get('/user/resetpassword/{id}', [UserController::class, 'resetPassword'])->name('user.resetpassword');

    //rutas para roles
    Route::get('/roles', function () {return view('modules.configuracion.users.roles');})->name('roles');
    

    Route::get('/role', [RoleController::class, 'index'])->name('role.index');
    Route::post('/role', [RoleController::class, 'store'])->name('role.store');
    Route::get('/role/{id}', [RoleController::class, 'show'])->name('role.show');
    Route::put('/role/{id}', [RoleController::class, 'update'])->name('role.update');
    Route::delete('/role/{id}', [RoleController::class, 'destroy'])->name('role.destroy');

    //rutas para permisos
    Route::get('/permisos', function () {
        return view('modules.configuracion.users.permisos');
    })->name('permisos');
    Route::get('/permission', [PermissionController::class, 'index'])->name('permission.index');
    Route::post('/permission', [PermissionController::class, 'store'])->name('permission.store');
    Route::get('/permission/{id}', [PermissionController::class, 'show'])->name('permission.show');
    Route::patch('/permission/{id}', [PermissionController::class, 'update'])->name('permission.update');
    Route::delete('/permission/{id}', [PermissionController::class, 'destroy'])->name('permission.destroy');  

    // api de consulta ruc y dni
    Route::get('/consultar-ruc/{ruc}', [ApiConsultaController::class, 'consultarRuc'])->name('consultar.ruc');
    Route::get('/consultar-dni/{dni}', [ApiConsultaController::class, 'consultarDni'])->name('consultar.dni');  

   // ruta de impresion de ticket
   Route::get('/pos/imprimir-recibo/{id}', [PosOrderController::class, 'mostrarRecibo'])->name('posorder.imprimirRecibo');

});

    
    