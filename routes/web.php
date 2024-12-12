<?php

use App\Http\Controllers\Configuracion\EmpresaController;
use App\Http\Controllers\Configuracion\UserController;
use App\Http\Controllers\Facturacion\ImpuestoController;
use App\Http\Controllers\Facturacion\SerieCorrelativoController;
use App\Http\Controllers\Facturacion\Sunat\TipoAfectacionController;
use App\Http\Controllers\Facturacion\Sunat\TipoComprobanteController;
use App\Http\Controllers\Facturacion\Sunat\TipoDocumentoIdentidadController;
use App\Http\Controllers\Facturacion\Sunat\TipoPrecioController;
use App\Http\Controllers\Inventario\CategoriaController;
use App\Http\Controllers\Inventario\MarcaController;
use App\Http\Controllers\Inventario\ProductoController;
use App\Http\Controllers\Inventario\UnidadDeMedidaController;
use App\Http\Controllers\Odoocpe\OdooClienteController;
use App\Http\Controllers\Odoocpe\OdooDbController;
use App\Http\Controllers\Odoocpe\OdooInvoiceController;
use App\Http\Controllers\Odoocpe\OdooUbigeoController;
use App\Http\Controllers\Odoocpe\PosOrderController;
use App\Http\Controllers\Odoocpe\ProductController;
use App\Http\Controllers\Odoocpe\PurchaseOrderController;
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
     MODULO DE INVENTARIO
     ************************/ 
    Route::view('/inventario','modules.inventario.main')->name('inventario.main');
    // productos
    Route::view('/inventario/productos','modules.inventario.producto.index')->name('inventario.productos.index');
    Route::resource('/inventario/producto', ProductoController::class);
    // categorias
    Route::view('/inventario/categorias','modules.inventario.categoria.index')->name('inventario.categorias.index');
    Route::resource('/inventario/categoria', CategoriaController::class);
    // marcas
    Route::view('/inventario/marcas','modules.inventario.marca.index')->name('inventario.marcas.index');
    Route::resource('/inventario/marca', MarcaController::class)->except(['show, create, edit']);
    // unidad de medida
    Route::view('/inventario/unidades-de-medida','modules.inventario.unidad_de_medida.index')->name('inventario.unidadmedida.index');
    Route::resource('/inventario/unidad-de-medida', UnidadDeMedidaController::class)->except(['show, create, edit']);
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

    Route::post('/facturacion/Serie', [SerieCorrelativoController::class,'getCorrelativo'])->name('getCorrelativo');
    Route::post('/facturacion/SerieCorrelativo', [SerieCorrelativoController::class,'getSerieCorrelativo'])->name('getSerieCorrelativo');
    
    /*************************
     MODULO ODOO CPE
     ************************/
    Route::view('/odoocpe','modules.odoocpe.main')->name('odoocpe.main');

    // pos order
    Route::view('/odoocpe/pos-order','modules.odoocpe.pos_order.index')->name('odoocpe.pos_order.index');
    Route::get('/odoocpe/pos-order/{fechainicio}/{fechafin}', [PosOrderController::class, 'indexbydate'])->name('odoocpe.pos_order.indexbydate');
    Route::resource('/odoocpe/pos-order', PosOrderController::class)->only(['store', 'show','update', 'destroy']);
        // ubigeo
        Route::get('/departamento', [OdooUbigeoController::class,'mostrarDepartamentos'])->name('mostrarDepartamentos');
        Route::POST('provincias/{state_id}', [OdooUbigeoController::class,'mostrarProvincias'])->name('mostrarProvincias');
        Route::POST('distritos/{city_id}', [OdooUbigeoController::class,'mostrarDistritos'])->name('mostrarDistritos');
        Route::POST('ubigeo/{city_id}', [OdooUbigeoController::class,'mostrarUbigeo'])->name('mostrarUbigeo');
        // cliente
    
        Route::POST('nuevoCliente', [OdooClienteController::class,'nuevoCliente'])->name('nuevoCliente');
        Route::POST('buscarCliente', [OdooClienteController::class,'buscarCliente'])->name('buscarCliente');
        Route::POST('obtenerDatosCliente', [OdooClienteController::class,'obtenerDatosCliente'])->name('obtenerDatosCliente');
       
        // crear cpe
        Route::POST('registrarFactura', [OdooInvoiceController::class,'create'])->name('registrarFactura');
    // Barcode
    Route::view('/odoocpe/barcode/product','modules.odoocpe.barcode.product.index')->name('odoocpe.barcode.product');
    Route::Post('/odoocpe/barcode/product/search', [ProductController::class,'search']);
    Route::Post('/odoocpe/barcode/product/print', [ProductController::class,'imprimirEtiquetas'])->name('imprimirEtiquetas');
    Route::Post('/odoocpe/barcode/product/print-price-tag', [ProductController::class,'imprimirEtiquetasDePrecio'])->name('imprimirEtiquetasDePrecio');


    
    Route::view('/odoocpe/barcode/purchase','modules.odoocpe.barcode.purchase.index')->name('odoocpe.barcode.purchase');
    Route::get('/odoocpe/purchase-order/{fechainicio}/{fechafin}', [PurchaseOrderController::class, 'indexbydate'])->name('odoocpe.purchase_order.indexbydate');
    Route::get('/odoocpe/barcode/purchase-order-lines/{ids}', [PurchaseOrderController::class, 'showPurchaseLines'])->name('odoocpe.purchase_order_lines.show');
/*     Route::get('/odoocpe/barcode/purchase-order/print-etiquetas/{purchase_id}', [PurchaseOrderController::class, 'imprimirEtiquetas'])->name('odoocpe.purchase_order.imprimirEtiquetas'); */

    
    // odoo db 
    Route::view('/odoocpe/configuracion/odoo-dbs','modules.odoocpe.configuracion.odoo_db.index')->name('odoocpe.configuracion.odoo_db.index');
    Route::resource('/odoocpe/configuracion/odoo-db', OdooDbController::class)->only(['index', 'store', 'update', 'destroy']);

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

    
    