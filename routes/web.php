<?php

use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\UserController;
use App\Http\Controllers\Facturacion\CpeSerieController;
use App\Http\Controllers\Facturacion\ImpuestoController;
use App\Http\Controllers\Facturacion\Sunat\TipoAfectacionController;
use App\Http\Controllers\Facturacion\Sunat\TipoComprobanteController;
use App\Http\Controllers\Facturacion\Sunat\TipoDocumentoIdentidadController;
use App\Http\Controllers\Facturacion\Sunat\TipoPrecioController;
use App\Http\Controllers\Inventario\CategoriaController;
use App\Http\Controllers\Inventario\MarcaController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\StockController;
use App\Http\Controllers\Inventario\TiendaController;

use App\Http\Controllers\Pos\PosOrderController;
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
    Route::view('/punto-de-venta', 'modules.puntodeventa.pos')->name('puntodeventa.pos');
    //venta
    Route::post('/punto-de-venta/venta', [PosOrderController::class, 'store'])->name('puntodeventa.venta.store');
     /*************************
     Ventas
     ************************/
    Route::view('/ventas', 'modules.ventas.main')->name('ventas.main');
    // ventas
    Route::view('/ventas/ventas', 'modules.ventas.posorder.index')->name('ventas.posorder.index');
    Route::resource('/ventas/posorder', PosOrderController::class)->only(['index','show']);
    Route::get('ventas/posorder/{fecha_inicio?}/{fecha_fin?}', [posOrderController::class, 'indexByDate'])->name('playa.parqueo.indexByDate  ');
    // put for cancelhttp://svp.test/ventas/posorder/cancel/15 405 (Method Not Allowed)
    Route::put('/ventas/posorder/cancel/{id}', [PosOrderController::class, 'cancel'])->name('ventas.posorder.cancel');
    Route::get('/ventas/visor/posorder/{fecha_inicio?}/{fecha_fin?}', [PosOrderController::class, 'postOrderPanel'])->name('ventas.visor.posorderpanel');
    Route::get('/ventas/visor/posorderline', [PosOrderController::class, 'postOrderLinePanel'])->name('ventas.visor.posorderlinepanel');

    //clientes
    Route::view('/ventas/clientes', 'modules.clientes.index')->name('ventas.cliente.index');
    Route::resource('/ventas/cliente', \App\Http\Controllers\ClienteController::class)->except(['show']);
    Route::post('/ventas/posorder/anular/{id}', [PosOrderController::class, 'anular'])->name('ventas.posorder.anular.cpe');
    //nota de credito
    Route::post('/ventas/nota-de-credito/{id}', [PosOrderController::class, 'emitirNota'])->name('ventas.posorder.notadecredito');
    Route::post('/ventas/nota-de-debito/{id}', [PosOrderController::class, 'emitirNota'])->name('ventas.posorder.notadebito');

    /*************************
     MODULO DE INVENTARIO
     ************************/ 
    Route::view('/inventario','modules.inventario.main')->name('inventario.main');
    // productos
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

    // unidad de medida
    Route::resource('/inventario/tienda', TiendaController::class);
    // stock
    Route::get('/inventario/stock', [StockController::class, 'tiendasConStock']);
    /*************************
     MODULO DE FACTURACION
     ************************/
    Route::view('/facturacion','modules.facturacion.main')->name('facturacion.main');
    // tipo de afectación del IGV
    Route::view('/facturacion/tipos-afectacion-igv','modules.facturacion.sunat.tipo_afectacion.index')->name('facturacion.sunat.tipoafectacionigv.index');
    Route::resource('/facturacion/tipo-afectacion-igv', TipoAfectacionController::class)->only(['index']);
    // tipo de documento de identidad
    Route::view('/facturacion/tipos-documento-identidad','modules.facturacion.sunat.tipo_documento_identidad.index')->name('facturacion.sunat.tipodocumentoidentidad.index');
    Route::resource('/facturacion/tipo-documento-identidad', TipoDocumentoIdentidadController::class)->only(['index']);
    // tipo de comprobante
    Route::view('/facturacion/tipos-comprobante','modules.facturacion.sunat.tipo_comprobante.index')->name('facturacion.sunat.tipocomprobante.index');
    Route::resource('/facturacion/tipo-comprobante', TipoComprobanteController::class)->only(['index']);
    // tipoprecio
    Route::view('/facturacion/tipos-precio','modules.facturacion.sunat.tipo_precio.index')->name('facturacion.sunat.tipoprecio.index');
    Route::resource('/facturacion/tipo-precio', TipoPrecioController::class)->only(['index']);
    
    Route::view('/facturacion/configuracion/','modules.facturacion.configuracion.edit')->name('facturacion.configuracion.edit');
    
    Route::view('/facturacion/configuracion/impuestos','modules.facturacion.impuestos.index')->name('facturacion.impuestos.index');
    Route::resource('/facturacion/impuesto', ImpuestoController::class)->only(['index']);

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
    Route::view('/configuracion/usuarios', 'modules.configuracion.users.index')->name('configuracion.usuarios.index');
    Route::resource('/configuracion/user', UserController::class);
    // Configuracion de empresa
    Route::get('/configuracion/empresa', [EmpresaController::class, 'edit'])->name('empresa.edit');
    Route::put('/configuracion/empresa/datosComerciales', [EmpresaController::class, 'update_datosComerciales'])->name('empresa.update_datosComerciales');
    Route::put('/configuracion/empresa/datosTributarios', [EmpresaController::class, 'update_datosTributarios'])->name('update_datosTributarios');
    Route::put('/configuracion/empresa/datosFacturacionElectronica', [EmpresaController::class, 'update_facturacionElectronica'])->name('update_facturacionElectronica');
    Route::post('/configuracion/empresa/certificadodigital', [EmpresaController::class, 'update_certificadoDigital'])->name('update_certificadoDigital');
    Route::put('/configuracion/empresa/datosConsultaDocumentos', [EmpresaController::class, 'update_ConsultaDocumentos'])->name('supdateConsultaDocumentos');
    Route::put('/configuracion/empresa/datosGuiaRemision', [EmpresaController::class, 'update_GuiaRemision'])->name('store_GuiaRemision');
    




});

    
    