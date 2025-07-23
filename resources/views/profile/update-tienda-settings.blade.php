<x-form-section submit="updateTienda">
    <x-slot name="title">
        {{ __('Configuracion de tienda ') }}
    </x-slot>

    <x-slot name="description">
       
        * Asignacion para este usuario. elija la tienda</br>
        * cada tienda es independiente y tien su propio ruc.
        * puede haber mas de un usuario por tienda.
        <br />
    
    </x-slot>

    <x-slot name="form">

        <!-- tienda_id -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="tienda_id" value="{{ __('Tienda') }}" />
            <select id="tienda_id" wire:model="state.tienda_id" name="tienda_id"
                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="" >Seleccione una opción</option>
                @foreach ($tiendas as $tienda)
                    <option value="{{ $tienda->id }}">{{ $tienda->nombre }}</option>
                @endforeach
            </select>
            <x-input-error for="state.tienda_id" class="mt-2" />
        </div>


        

    </x-slot>

    <x-slot name="actions">
        <x-action-message class="me-3" on="saved">
            {{ __('Saved.') }}
        </x-action-message>

        <x-button wire:loading.attr="disabled" wire:target="photo">
            {{ __('Save') }}
        </x-button>
    </x-slot>
</x-form-section>
