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
            // revisar si el codigo de barras es unico solo en la creacion
            // si es edicion, no validar que sea unico
            'codigo_barras' => $this->isMethod('post') ? 'nullable|string|max:13|unique:productos,codigo_barras' : 'nullable|string|max:13',
            'nombre' => 'required|string|max:100',
            'costo_unitario' => 'nullable|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'precio_minimo' => 'required|numeric|min:0',
            'marca_id' => 'required|exists:marcas,id',
            'categoria_id' => 'required|exists:categorias,id',
            'stocks' => 'required|array',
            'stocks.*' => 'nullable|numeric'
           
        ];
    }
}
