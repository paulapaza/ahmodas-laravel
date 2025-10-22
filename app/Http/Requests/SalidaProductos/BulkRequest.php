<?php

namespace App\Http\Requests\SalidaProductos;

use Illuminate\Foundation\Http\FormRequest;

class BulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            '*'                   => ['required', 'array'],
            '*.producto_id'       => ['required', 'integer', 'exists:productos,id'],
            '*.tienda_id'         => ['required', 'integer', 'exists:tiendas,id'],
            '*.stock_antes'       => ['required', 'integer', 'min:0'],
            '*.stock_despues'     => ['required', 'integer', 'min:0'],
            '*.cantidad_reducida' => ['required', 'integer', 'min:0'],
            '*.comentario'        => ['nullable', 'string', 'max:500'],
            '*.tipo'              => ['nullable', 'integer', 'in:1,2'],
        ];
    }
}
