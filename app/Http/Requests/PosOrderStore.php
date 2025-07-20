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
      
        
        return [
            '_token' => 'required',
            'efectivo' => 'required|numeric|min:0',
            'tarjeta' => 'required|numeric|min:0',
            'yape' => 'required|numeric|min:0',
            'transferencia' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'moneda' => 'required|string|in:1,2', // 1 for PEN, 2 for USD
            'codigo_tipo_comprobante' => 'required|string|in:01,03,12', // 01: Factura, 03: Boleta, 07: Nota de crédito, 12: Nota de débito
            'productos.*.id' => 'required|integer|exists:productos,id',
            'productos.*.cantidad' => 'required|integer|min:1',
            'productos.*.precio_unitario' => 'required|numeric|min:0',
            'productos.*.subtotal' => 'required|numeric|min:0',
            'cliente.dni' => 'nullable|regex:/^\d{8}$/',
            'cliente.nombre' => 'nullable|string|max:255',
            'cliente.direccion' => 'nullable|string|max:255|required_if:codigo_tipo_comprobante,01',
            'cliente.ruc' => 'required_if:codigo_tipo_comprobante,01|regex:/^\d{11}$/',
            'cliente.razonSocial' => 'required_if:codigo_tipo_comprobante,01|string|max:255',   
            'tipo_venta' => 'required|string|in:local,exportacion', // Validación para tipo de venta ,

        ];
    }
}