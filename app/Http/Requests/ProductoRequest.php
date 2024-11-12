<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductoRequest extends FormRequest
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
            'nombre' => 'required|string',

            'tipo' => 'required|in:A,S',
            'categoria_id' => 'required|integer',
            'unidad_medida_id' => 'required|integer',

            'precio_unitario' => 'required|numeric',
            'impuesto_cliente' => 'nullable|string',
            'costo_unitario' => 'required|numeric',
            'barcode' => 'required|string',
            
            'descripcion' => 'nullable|string',
            'img' => 'file|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'marca_id' => 'required|integer',
            'estado' => 'required|in:0,1,2',
            
            'impuesto_bolsa' => 'required|in:0,1',
            //'incluye_igv' => 'required|in:0,1',
            //'tipo_afectacion_igv' => 'required|string', depende del impuesto elegido

            //compra
            'compra_incluyeIgv' => 'required|in:0,1',
            'compra_tipo_afectacion_igv_codigo' => 'required|string',
        
            'stock_minimo' => 'nullable|numeric',
            'stock_maximo' => 'nullable|numeric',
            'stock_alerta' => 'nullable|numeric',


        ];
    }
}
