<x-admin-layout>
    <x-slot name="menu">
    <x-menuOdoocpe></x-menuOdoocpe>
  </x-slot>
     
  <x-slot name="pagetitle">Magento</x-slot>
    <div class="container mx-auto px-4">
        <div class="py-8">
            <div class="flex justify-between">
                <div class="w-full lg:w-1/2">
                    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="sku">
                                SKU
                            </label>
                            <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="sku" type="text" placeholder="SKU">
                        </div>
                        <div class="flex items-center justify-between">
                            <button id="buscarProducto"!class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="button">
                                Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>

<script>
   $(document).ready(function(){
        $('#buscarProducto').click(function(){
            var sku = $('#sku').val();
            $.ajax({
                url: '/odoocpe/magento/producto/'+sku,
                type: 'GET',
                success: function(response){
                    console.log(response);
                }
            });
        });
    });
</script>
