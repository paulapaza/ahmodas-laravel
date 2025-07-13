<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PosOrderStore extends FormRequest
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
        /*
        _token
            lUQBFpQrACVx16N8MpyzNZOykW3MnP5sXrku8Psr
            efectivo
            205
            tarjeta
            0
            yape
            0
            transferencia
            0
            total
            205
            productos[0][id]
            2
            productos[0][cantidad]
            2
            productos[0][precio_unitario]
            70
            productos[0][subtotal]
            140
            productos[1][id]
            1
            productos[1][cantidad]
            1
            productos[1][precio_unitario]
            65
            productos[1][subtotal]
            65
        */
        
        return [
            '_token' => 'required',
            'efectivo' => 'required|numeric|min:0',
            'tarjeta' => 'required|numeric|min:0',
            'yape' => 'required|numeric|min:0',
            'transferencia' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'codigo_tipo_comprobante' => 'required|string|in:01,03,12', // 01: Factura, 03: Boleta, 07: Nota de crédito, 12: Nota de débito
            'productos.*.id' => 'required|integer|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.subtotal' => 'required|numeric|min:0'
        ];
    }
}