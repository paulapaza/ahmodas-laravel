<table id="{{ $id ?? 'table' }}"  class="display responsive nowrap bordered shadow  " width="100%">
    {{-- class="stripe hover bordered w-100 shadow " --}}
    <thead class="">
        <tr>
         {{ $slot }}
        </tr>
    </thead>
    <tbody>
        {{ $tablebody ??''}}
    
    </tbody>
</table>