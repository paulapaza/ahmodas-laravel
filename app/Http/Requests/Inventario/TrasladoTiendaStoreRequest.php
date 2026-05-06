<?php

namespace App\Http\Requests\Inventario;

use Illuminate\Foundation\Http\FormRequest;

class TrasladoTiendaStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'traslados' => 'required|array|min:1',
            'traslados.*.producto_id' => 'required|integer|exists:productos,id',
            'traslados.*.tienda_id' => 'required|integer|exists:tiendas,id',
            'traslados.*.cantidad' => 'required|integer|min:1',
            
            'traslados.*.tienda_stock_anterior' => 'required|integer',
            'traslados.*.almacen_stock_anterior' => 'required|integer',
            'traslados.*.tienda_stock_posterior' => 'required|integer',
            'traslados.*.almacen_stock_posterior' => 'required|integer',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'traslados.required' => 'No hay traslados para procesar.',
            'traslados.array' => 'El formato de los traslados es inválido.',
            'traslados.*.producto_id.exists' => 'Uno de los productos seleccionados no existe.',
            'traslados.*.cantidad.min' => 'La cantidad a trasladar debe ser al menos 1.',
        ];
    }
}
