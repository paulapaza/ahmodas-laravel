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

        <!-- print_type -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="print_type" value="{{ __('Tipo de Impresion') }}" />
            <select id="print_type" wire:model="state.print_type" name="print_type"
                class="mt-1 block w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                <option value="" >Seleccione una opción</option>
                <option value="pdf">Pdf</option>
                <option value="local">Impresora Compartida Windows</option>
                <option value="red">Impresora en red</option>
            </select>
            <x-input-error for="state.print_type" class="mt-2" />
        </div>
        <!-- printer_ip -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="printer_ip" value="{{ __('IP de Impresora') }}" />
            <x-input id="printer_ip" wire:model="state.printer_ip" type="text" class="mt-1 block w-full" />
            <x-input-error for="state.printer_ip" class="mt-2" />
        </div>
        <!-- printer_name -->
        <div class="col-span-6 sm:col-span-4">
            <x-label for="printer_name" value="{{ __('Nombre de Impresora Compartida en windows') }}" />
            <x-input id="printer_name" wire:model="state.printer_name" type="text" class="mt-1 block w-full" />
            <x-input-error for="state.printer_name" class="mt-2" />
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
