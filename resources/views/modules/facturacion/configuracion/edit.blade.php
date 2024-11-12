<x-admin-layout>
    <x-slot name="menu">
        <x-menuFacturacion/>
    </x-slot>
    <x-slot name="pagetitle">Configuración de Facturación</x-slot>
    <div class="row">
        <div class="col-12 col-xl-8 container">
            
            <div class="card">
                <div class="card-header bg-xgray">
                    <p class="card-title text-xaccent text-bold">Impuestos predeterminados</p>
                </div>


                <form class="form-horizontal">
                    <div class="card-body">
                        <div class="form-group">
                           
                            <small >Estos impuestos se aplicarán por defecto al crear un producto</small>
                        </div>
                        <div class="form-group row">
                            <label for="inputEmail3" class="col-sm-2 col-form-label">Impuesto de venta</label>
                            <div class="col-sm-2">
                               <select class="form-control" id="impuesto_venta" name="impuesto_venta" required>
                                    <option value="0">0%</option>
                                    <option value="18">18%</option>
                                    <option value="19">19%</option>
                                    <option value="20">20%</option>
                                </select>
                            </div>
                        </div>
                            <small >Estos impuestos se aplicarán por defecto al comprar un producto</small>
                        <div class="form-group row">
                            <label for="inputPassword3" class="col-sm-2 col-form-label">Impuesto de Compra</label>
                            <div class="col-sm-2">
                                <select class="form-control" id="impuesto_compra" name="impuesto_compra" required>
                                    <option value="0">0%</option>
                                    <option value="18">18%</option>
                                    <option value="19">19%</option>
                                    <option value="20">20%</option>
                                </select>
                            </div>
                        </div>
                       
                    </div>

                    <div class="card-footer">
                        
                        <button type="submit" class="btn btn-xsuccess float-right">Guardar</button>
                    </div>

                </form>
            </div>

        </div>
    </div>


</x-admin-layout>

<script>
    $(document).ready(function() {



    });
</script>
